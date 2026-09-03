<?php

declare(strict_types=1);

namespace Drupal\brebo_customer_service\Knowledge;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Idempotent bridge from the temporary website catalog to KnowledgeItem nodes.
 *
 * This importer never updates or deletes existing KnowledgeItems. It only
 * creates a catalog item when no exact seed marker is already present.
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
          continue;
        }

        /** @var \Drupal\node\NodeInterface $node */
        $node = $storage->create($this->values($topic, $item, $marker));
        $node->save();
        $created[] = $item['slug'];
      }
    }

    return ['created' => $created, 'existing' => $existing, 'errors' => $errors];
  }

  private function values(string $topic, array $item, string $marker): array {
    $summary = (string) $item['summary'];
    return [
      'type' => self::BUNDLE,
      'title' => (string) $item['title'],
      // Seed objects are deliberately unpublished until human review.
      'status' => NodeInterface::NOT_PUBLISHED,
      'field_knowledge_observation' => $summary,
      'field_knowledge_meaning' => 'Redactionele seed uit de BREBO Kennisbibliotheek. Mogelijke betekenis en oorzaken moeten inhoudelijk en met bronnen worden beoordeeld voordat deze kennis wordt vrijgegeven.',
      'field_knowledge_risk' => 'Urgentie en risico zijn nog niet gevalideerd. Geen projectspecifieke conclusie trekken zonder beoordeling van de werkelijke situatie.',
      'field_knowledge_next_step' => 'Controleer de feitelijke situatie, relevante bouwdelen en beschikbare documentatie. Vul daarna bron, geldigheid en deskundige beoordeling aan.',
      'field_knowledge_basis' => $marker . "\nStatus: editorial\nOnderwerp: " . $topic . "\nBronnen: nog niet vastgesteld\nGeldigheid: nog niet gecontroleerd\nDeskundige controle: nog niet uitgevoerd\nAI-vrijgave: nee",
      'field_knowledge_regie' => 'Bepaal na inhoudelijke beoordeling welke informatie, inspectie of besluitvorming nodig is en wie daarvoor verantwoordelijk is.',
      'field_knowledge_realization' => 'Nog geen oplossingsrichting als BREBO-kennis vrijgegeven. Eerst oorzaak, randvoorwaarden en bronbasis valideren.',
    ];
  }

  private function marker(string $slug): string {
    return 'BREBO-WEB-SEED:' . $slug;
  }

}
