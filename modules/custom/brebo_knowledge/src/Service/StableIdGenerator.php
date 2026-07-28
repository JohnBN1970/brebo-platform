<?php

declare(strict_types=1);

namespace Drupal\brebo_knowledge\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Genereert onveranderlijke functionele identifiers voor KnowledgeItem.
 */
final class StableIdGenerator {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Genereert de eerstvolgende vrije identifier in formaat KI-000001.
   */
  public function generate(): string {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'brebo_knowledge_item')
      ->exists('field_brebo_stable_id')
      ->execute();

    $highest = 0;
    foreach ($storage->loadMultiple($ids) as $node) {
      $value = (string) $node->get('field_brebo_stable_id')->value;
      if (preg_match('/^KI-(\d{6})$/', $value, $matches)) {
        $highest = max($highest, (int) $matches[1]);
      }
    }

    return sprintf('KI-%06d', $highest + 1);
  }

}
