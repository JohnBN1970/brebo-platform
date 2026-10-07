<?php

declare(strict_types=1);

namespace Drupal\brebo_customer_service\Knowledge;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Idempotent bridge from the temporary website catalog to KnowledgeItem nodes.
 *
 * This importer never deletes existing KnowledgeItems. It creates missing
 * catalog items and may refresh untouched editorial seed nodes. Reviewed,
 * sourced or published KnowledgeItems are never overwritten.
 */
final class KnowledgeItemSeedImporter {

  private const BUNDLE = 'brebo_knowledge_item';
  private const REQUIRED_FIELDS = [
    'field_knowledge_observation',
    'field_knowledge_meaning',
    'field_knowledge_risk',
    'field_knowledge_next_step',
    'field_knowledge_basis',
    'field_knowledge_regie',
    'field_knowledge_realization',
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly EntityFieldManagerInterface $entityFieldManager,
  ) {}

  /**
   * Performs a read-only preflight. No entity is written by this method.
   */
  public function preflight(): array {
    $errors = [];
    $node_type = $this->entityTypeManager->getStorage('node_type')->load(self::BUNDLE);
    if ($node_type === NULL) {
      $errors[] = 'Contenttype brebo_knowledge_item ontbreekt.';
      return $errors;
    }

    $definitions = $this->entityFieldManager->getFieldDefinitions('node', self::BUNDLE);
    foreach (self::REQUIRED_FIELDS as $field_name) {
      if (!isset($definitions[$field_name])) {
        $errors[] = sprintf('Vereist veld %s ontbreekt.', $field_name);
      }
    }
    return $errors;
  }

  /**
   * Creates missing seed objects and returns a non-destructive result summary.
   */
  public function import(): array {
    $errors = $this->preflight();
    if ($errors) {
      return ['created' => [], 'existing' => [], 'errors' => $errors];
    }

    $created = [];
    $existing = [];
    $refreshed = [];
    $storage = $this->entityTypeManager->getStorage('node');

    foreach (KnowledgeCatalog::items() as $topic => $items) {
      foreach ($items as $item) {
        $marker = $this->marker($item['slug']);
        $ids = $storage->getQuery()
          ->accessCheck(FALSE)
          ->condition('type', self::BUNDLE)
          ->condition('field_knowledge_basis', $marker, 'CONTAINS')
          ->range(0, 2)
          ->execute();

        if (count($ids) > 1) {
          $errors[] = sprintf('Seed %s is dubbel aanwezig; niets overschreven.', $item['slug']);
          continue;
        }
        if ($ids) {
          $existing[] = $item['slug'];
          $node = $storage->load(reset($ids));
          if ($node instanceof NodeInterface && $this->isUntouchedEditorialSeed($node)) {
            foreach ($this->contentValues($item) as $field => $value) {
              $node->set($field, $value);
            }
            $node->setNewRevision(TRUE);
            $node->setRevisionLogMessage('Redactionele website-seed bijgewerkt vanuit KnowledgeCatalog; nog niet publiek vrijgegeven.');
            $node->save();
            $refreshed[] = $item['slug'];
          }
          continue;
        }

        /** @var \Drupal\node\NodeInterface $node */
        $node = $storage->create($this->values($topic, $item, $marker));
        $node->save();
        $created[] = $item['slug'];
      }
    }

    return ['created' => $created, 'existing' => $existing, 'refreshed' => $refreshed, 'errors' => $errors];
  }

  private function values(string $topic, array $item, string $marker): array {
    return [
      'type' => self::BUNDLE,
      'title' => (string) $item['title'],
      // Seed objects are deliberately unpublished until human review.
      'status' => NodeInterface::NOT_PUBLISHED,
      ...$this->contentValues($item),
      'field_knowledge_basis' => $marker . "\nStatus: editorial\nOnderwerp: " . $topic . "\nBronnen: nog niet vastgesteld\nGeldigheid: nog niet gecontroleerd\nDeskundige controle: nog niet uitgevoerd\nPublieke vrijgave: nee\nAI-vrijgave: nee",
    ];
  }

  private function contentValues(array $item): array {
    $guidance = $item['guidance'] ?? [];
    $meaningParts = [];
    foreach ($guidance as $section) {
      if (is_array($section) && count($section) >= 2) {
        $meaningParts[] = trim((string) $section[0]) . ': ' . trim((string) $section[1]);
      }
    }

    return [
      'field_knowledge_observation' => (string) $item['summary'],
      'field_knowledge_meaning' => $meaningParts !== []
        ? implode("\n\n", $meaningParts)
        : (string) $item['summary'],
      'field_knowledge_risk' => 'Urgentie en risico hangen af van oorzaak, omvang, ontwikkeling, functie en mogelijke gevolgschade. Trek zonder projectspecifieke beoordeling geen automatische conclusie over noodzaak of maatregel.',
      'field_knowledge_next_step' => 'Leg de feitelijke situatie, locatie, omvang en relevante omstandigheden vast. Beoordeel daarna oorzaak, samenhang en randvoorwaarden voordat een maatregel wordt gekozen.',
      'field_knowledge_regie' => 'Maak expliciet welke informatie nog ontbreekt, welke aannames worden gebruikt en welke technische keuze of vervolgstap eerst moet worden besloten.',
      'field_knowledge_realization' => 'Kies uitvoering pas nadat oorzaak, technische randvoorwaarden en gewenste prestatie voldoende zijn vastgesteld.',
    ];
  }

  private function isUntouchedEditorialSeed(NodeInterface $node): bool {
    if ($node->isPublished()) {
      return FALSE;
    }

    $basis = (string) $node->get('field_knowledge_basis')->value;
    if (!str_contains($basis, 'Status: editorial')) {
      return FALSE;
    }

    foreach (['Bronnen:', 'Geldigheid:', 'Deskundige controle:'] as $prefix) {
      foreach (preg_split('/\R/', $basis) ?: [] as $line) {
        $line = trim($line);
        if (!str_starts_with($line, $prefix)) {
          continue;
        }
        $value = strtolower(trim(substr($line, strlen($prefix))));
        if ($value !== '' && !str_contains($value, 'nog niet') && !str_contains($value, 'niet vastgesteld') && !str_contains($value, 'niet gecontroleerd') && !str_contains($value, 'niet uitgevoerd')) {
          return FALSE;
        }
      }
    }

    return TRUE;
  }

  private function marker(string $slug): string {
    return 'BREBO-WEB-SEED:' . $slug;
  }

}
