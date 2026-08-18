<?php

declare(strict_types=1);

namespace Drupal\brebo_runtime\Service;

use Drupal\brebo_runtime\Contract\LifecyclePolicyInterface;
use InvalidArgumentException;

/**
 * Klein configureerbaar lifecyclebeleid zonder domeinkennis.
 */
final class ConfigurableLifecyclePolicy implements LifecyclePolicyInterface {

  /**
   * @param string[] $statuses
   *   Toegestane statussen.
   * @param string[] $publishedStatuses
   *   Statussen die als gepubliceerd gelden.
   * @param array<string, string> $permissionMap
   *   Vereist recht per status.
   */
  public function __construct(
    private readonly array $statuses,
    private readonly array $publishedStatuses = [],
    private readonly array $permissionMap = [],
  ) {
    if ($statuses === []) {
      throw new InvalidArgumentException('Lifecyclebeleid vereist minimaal één status.');
    }

    foreach ([...$publishedStatuses, ...array_keys($permissionMap)] as $status) {
      if (!in_array($status, $statuses, TRUE)) {
        throw new InvalidArgumentException(sprintf('Status %s ontbreekt in het lifecyclebeleid.', $status));
      }
    }
  }

  public function statuses(): array {
    return $this->statuses;
  }

  public function isPublished(string $status): bool {
    $this->assertKnownStatus($status);
    return in_array($status, $this->publishedStatuses, TRUE);
  }

  public function requiredPermission(string $status): ?string {
    $this->assertKnownStatus($status);
    return $this->permissionMap[$status] ?? NULL;
  }

  private function assertKnownStatus(string $status): void {
    if (!in_array($status, $this->statuses, TRUE)) {
      throw new InvalidArgumentException(sprintf('Ongeldige lifecycle-status: %s.', $status));
    }
  }

}
