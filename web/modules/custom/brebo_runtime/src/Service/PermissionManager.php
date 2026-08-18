<?php

declare(strict_types=1);

namespace Drupal\brebo_runtime\Service;

use Drupal\brebo_runtime\Contract\LifecyclePolicyInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Dwingt statusgebonden lifecycle-rechten af.
 */
final class PermissionManager {

  public function assertAllowed(
    string $status,
    AccountInterface $account,
    LifecyclePolicyInterface $policy,
  ): void {
    // Drupal user 1 behoudt de standaard platformbeheerder-bypass.
    if ((int) $account->id() === 1) {
      return;
    }

    $permission = $policy->requiredPermission($status);
    if ($permission !== NULL && !$account->hasPermission($permission)) {
      throw new AccessDeniedHttpException(sprintf('Geen toestemming voor lifecycle-status %s.', $status));
    }
  }

}
