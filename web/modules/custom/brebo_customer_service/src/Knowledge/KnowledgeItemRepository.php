<?php

declare(strict_types=1);

namespace Drupal\brebo_customer_service\Knowledge;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Read-only projection of canonical BREBO KnowledgeItems for customer service.
 */
final class KnowledgeItemRepository {

  private const BUNDLE = 'brebo_knowledge_item';
  private const SEED_PREFIX = 'BREBO-WEB-SEED:';

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  public function itemsByTopic(): array {
    $items = [];
    foreach ($this->loadSeedNodes() as $node) {
      $item = $this->project($node);
      if ($item !== NULL) {
        $items[$item['topic']][] = $item;
      }
    }
    return $items;
  }

  public function find(string $slug): ?array {
    foreach ($this->loadSeedNodes($slug) as $node) {
      $item = $this->project($node);
      if ($item !== NULL && $item['slug'] === $slug) {
        return $item;
      }
    }
    return NULL;
  }

  /**
   * Returns only canonical items that pass the complete AI approval gate.
   */
  public function aiItems(): array {
    $approved = [];
    foreach ($this->loadSeedNodes() as $node) {
      $item = $this->project($node);
      if ($item !== NULL && KnowledgeApproval::isAiApproved($item)) {
        $approved[] = $item;
      }
    }
    return $approved;
  }

  /**
   * @return \Drupal\node\NodeInterface[]
   */
  private function loadSeedNodes(?string $slug = NULL): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', self::BUNDLE)
      ->condition('field_knowledge_basis', self::SEED_PREFIX . ($slug ?? ''), 'CONTAINS')
      ->sort('nid');

    $ids = $query->execute();
    return $ids === [] ? [] : $storage->loadMultiple($ids);
  }

  private function project(NodeInterface $node): ?array {
    $basisText = (string) $node->get('field_knowledge_basis')->value;
    $slug = $this->lineValue($basisText, self::SEED_PREFIX);
    $topic = $this->lineValue($basisText, 'Onderwerp:');

    if ($slug === NULL || $topic === NULL) {
      return NULL;
    }

    $status = strtolower($this->lineValue($basisText, 'Status:') ?? KnowledgeApproval::STATUS_EDITORIAL);
    $sources = $this->meaningfulListValue($this->lineValue($basisText, 'Bronnen:'));
    $validity = $this->meaningfulValue($this->lineValue($basisText, 'Geldigheid:'));
    $review = $this->meaningfulValue($this->lineValue($basisText, 'Deskundige controle:'));
    $aiRelease = strtolower($this->lineValue($basisText, 'AI-vrijgave:') ?? 'nee');

    return [
      'nid' => (int) $node->id(),
      'slug' => $slug,
      'topic' => $topic,
      'title' => $node->label(),
      'summary' => (string) $node->get('field_knowledge_observation')->value,
      'meaning' => (string) $node->get('field_knowledge_meaning')->value,
      'risk' => (string) $node->get('field_knowledge_risk')->value,
      'next_step' => (string) $node->get('field_knowledge_next_step')->value,
      'basis_text' => $basisText,
      'basis' => [
        'sources' => $sources,
        'validity_checked_at' => $validity,
      ],
      'regie' => (string) $node->get('field_knowledge_regie')->value,
      'realization' => (string) $node->get('field_knowledge_realization')->value,
      'published' => $node->isPublished(),
      'public' => $node->isPublished(),
      'status' => $status,
      'ai_approved' => in_array($aiRelease, ['ja', 'yes', 'true', '1'], TRUE),
      'reviewed_by' => $review,
      'reviewed_at' => $validity,
    ];
  }

  private function lineValue(string $text, string $prefix): ?string {
    foreach (preg_split('/\R/', $text) ?: [] as $line) {
      $line = trim($line);
      if (str_starts_with($line, $prefix)) {
        $value = trim(substr($line, strlen($prefix)));
        return $value !== '' ? $value : NULL;
      }
    }
    return NULL;
  }

  private function meaningfulValue(?string $value): ?string {
    if ($value === NULL) {
      return NULL;
    }
    $normalized = strtolower(trim($value));
    foreach (['nog niet', 'niet vastgesteld', 'niet gecontroleerd', 'niet uitgevoerd', 'geen'] as $empty) {
      if (str_contains($normalized, $empty)) {
        return NULL;
      }
    }
    return trim($value) !== '' ? trim($value) : NULL;
  }

  private function meaningfulListValue(?string $value): array {
    $value = $this->meaningfulValue($value);
    if ($value === NULL) {
      return [];
    }
    return array_values(array_filter(array_map('trim', preg_split('/[;,]/', $value) ?: [])));
  }

}
