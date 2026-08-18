<?php

declare(strict_types=1);

namespace Drupal\brebo_runtime\Service;

use Drupal\brebo_runtime\Contract\StableIdGeneratorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\State\StateInterface;
use RuntimeException;

/**
 * Domeinneutrale basis voor oplopende, nooit hergebruikte stable IDs.
 */
abstract class AbstractStableIdGenerator implements StableIdGeneratorInterface {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly StateInterface $state,
    private readonly LockBackendInterface $lock,
  ) {}

  final public function generate(): string {
    if (!$this->lock->acquire($this->lockName(), 10.0)) {
      throw new RuntimeException('Kon geen lock verkrijgen voor stable-ID-generatie.');
    }

    try {
      $current = $this->state->get($this->counterKey());
      if (!is_int($current)) {
        $current = $this->detectHighestExistingNumber();
      }

      $next = $current + 1;
      if ($next > $this->maximumNumber()) {
        throw new RuntimeException('De beschikbare stable-ID-reeks is uitgeput.');
      }

      // Reserveer eerst. Een verwijderde of mislukte opslag hergebruikt het
      // nummer daardoor nooit.
      $this->state->set($this->counterKey(), $next);

      return sprintf('%s-%0' . $this->numberWidth() . 'd', $this->prefix(), $next);
    }
    finally {
      $this->lock->release($this->lockName());
    }
  }

  abstract protected function entityTypeId(): string;

  abstract protected function bundleFieldName(): string;

  abstract protected function bundle(): string;

  abstract protected function stableIdFieldName(): string;

  abstract protected function prefix(): string;

  abstract protected function counterKey(): string;

  abstract protected function lockName(): string;

  protected function numberWidth(): int {
    return 6;
  }

  protected function maximumNumber(): int {
    return (10 ** $this->numberWidth()) - 1;
  }

  private function detectHighestExistingNumber(): int {
    $storage = $this->entityTypeManager->getStorage($this->entityTypeId());
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition($this->bundleFieldName(), $this->bundle())
      ->exists($this->stableIdFieldName())
      ->execute();

    $pattern = sprintf('/^%s-(\d{%d})$/', preg_quote($this->prefix(), '/'), $this->numberWidth());
    $highest = 0;
    foreach ($storage->loadMultiple($ids) as $entity) {
      $value = (string) $entity->get($this->stableIdFieldName())->value;
      if (preg_match($pattern, $value, $matches)) {
        $highest = max($highest, (int) $matches[1]);
      }
    }

    return $highest;
  }

}
