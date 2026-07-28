<?php

declare(strict_types=1);

namespace Drupal\brebo_knowledge\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\State\StateInterface;
use RuntimeException;

/**
 * Genereert onveranderlijke functionele identifiers voor KnowledgeItem.
 */
final class StableIdGenerator {

  private const COUNTER_KEY = 'brebo_knowledge.stable_id_counter';
  private const LOCK_NAME = 'brebo_knowledge.stable_id_generator';

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly StateInterface $state,
    private readonly LockBackendInterface $lock,
  ) {}

  /**
   * Reserveert de eerstvolgende identifier in formaat KI-000001.
   */
  public function generate(): string {
    if (!$this->lock->acquire(self::LOCK_NAME, 10.0)) {
      throw new RuntimeException('Kon geen lock verkrijgen voor KnowledgeItem stable-ID-generatie.');
    }

    try {
      $current = $this->state->get(self::COUNTER_KEY);
      if (!is_int($current)) {
        $current = $this->detectHighestExistingNumber();
      }

      $next = $current + 1;
      if ($next > 999999) {
        throw new RuntimeException('De beschikbare KnowledgeItem stable-ID-reeks is uitgeput.');
      }

      // Eerst reserveren, daarna teruggeven. Verwijderen of een mislukte opslag
      // maakt een eenmaal gereserveerd nummer daardoor nooit opnieuw bruikbaar.
      $this->state->set(self::COUNTER_KEY, $next);

      return sprintf('KI-%06d', $next);
    }
    finally {
      $this->lock->release(self::LOCK_NAME);
    }
  }

  /**
   * Bepaalt uitsluitend bij eerste ingebruikname het hoogste bestaande nummer.
   */
  private function detectHighestExistingNumber(): int {
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

    return $highest;
  }

}
