<?php

declare(strict_types=1);

namespace Drupal\brebo_runtime\Contract;

/**
 * Beschrijft domeinspecifiek lifecycle- en publicatiebeleid.
 */
interface LifecyclePolicyInterface {

  /**
   * Geeft de toegestane statuswaarden terug.
   *
   * @return string[]
   *   De toegestane statussen.
   */
  public function statuses(): array;

  /**
   * Bepaalt of een status publiek gepubliceerd hoort te zijn.
   */
  public function isPublished(string $status): bool;

  /**
   * Geeft het vereiste recht voor opslag in deze status, indien van toepassing.
   */
  public function requiredPermission(string $status): ?string;

}
