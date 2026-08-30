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

  /**
   * Returns public knowledge grouped by the stable editorial topic key.
   */
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

  /**
   * Finds one canonical KnowledgeItem by its stable seed slug.
   */
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
   * Loads only KnowledgeItems that originated from the controlled web seed.
   *
   * Publication is deliberately not required yet: the current customer-service
   * library already exposes these editorial entries. This repository changes
   * the source of that presentation, not its approval policy.
   *
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
    $basis = (string) $node->get('field_knowledge_basis')->value;
    $slug = $this->lineValue($basis, self::SEED_PREFIX);
    $topic = $this->lineValue($basis, 'Onderwerp:');

    if ($slug === NULL || $topic === NULL) {
      return NULL;
    }

    return [
      'nid' => (int) $node->id(),
      'slug' => $slug,
      'topic' => $topic,
      'title' => $node->label(),
      'summary' => (string) $node->get('field_knowledge_observation')->value,
      'meaning' => (string) $node->get('field_knowledge_meaning')->value,
      'risk' => (string) $node->get('field_knowledge_risk')->value,
      'next_step' => (string) $node->get('field_knowledge_next_step')->value,
      'basis' => $basis,
      'regie' => (string) $node->get('field_knowledge_regie')->value,
      'realization' => (string) $node->get('field_knowledge_realization')->value,
      'published' => $node->isPublished(),
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

}
