<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_runtime\Unit;

use Drupal\brebo_runtime\Service\ConfigurableLifecyclePolicy;
use Drupal\brebo_runtime\Service\PermissionManager;
use Drupal\Core\Session\AccountInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Test de domeinneutrale runtimehelpers.
 *
 * @group brebo_runtime
 */
final class RuntimeServicesTest extends TestCase {

  public function testConfigurableLifecyclePolicy(): void {
    $policy = new ConfigurableLifecyclePolicy(
      ['concept', 'approved', 'archived'],
      ['approved'],
      ['approved' => 'approve domain items', 'archived' => 'archive domain items'],
    );

    self::assertSame(['concept', 'approved', 'archived'], $policy->statuses());
    self::assertFalse($policy->isPublished('concept'));
    self::assertTrue($policy->isPublished('approved'));
    self::assertSame('approve domain items', $policy->requiredPermission('approved'));
    self::assertNull($policy->requiredPermission('concept'));
  }

  public function testUnknownLifecycleStatusIsRejected(): void {
    $policy = new ConfigurableLifecyclePolicy(['concept']);

    $this->expectException(InvalidArgumentException::class);
    $policy->isPublished('unknown');
  }

  public function testPermissionManagerAllowsAuthorizedAccount(): void {
    $policy = new ConfigurableLifecyclePolicy(
      ['concept', 'approved'],
      ['approved'],
      ['approved' => 'approve domain items'],
    );
    $account = $this->createMock(AccountInterface::class);
    $account->method('id')->willReturn(2);
    $account->method('hasPermission')->with('approve domain items')->willReturn(TRUE);

    (new PermissionManager())->assertAllowed('approved', $account, $policy);
    self::addToAssertionCount(1);
  }

  public function testPermissionManagerRejectsUnauthorizedAccount(): void {
    $policy = new ConfigurableLifecyclePolicy(
      ['concept', 'approved'],
      ['approved'],
      ['approved' => 'approve domain items'],
    );
    $account = $this->createMock(AccountInterface::class);
    $account->method('id')->willReturn(2);
    $account->method('hasPermission')->willReturn(FALSE);

    $this->expectException(AccessDeniedHttpException::class);
    (new PermissionManager())->assertAllowed('approved', $account, $policy);
  }

  public function testUserOneBypassesLifecyclePermission(): void {
    $policy = new ConfigurableLifecyclePolicy(
      ['approved'],
      ['approved'],
      ['approved' => 'approve domain items'],
    );
    $account = $this->createMock(AccountInterface::class);
    $account->method('id')->willReturn(1);
    $account->expects(self::never())->method('hasPermission');

    (new PermissionManager())->assertAllowed('approved', $account, $policy);
    self::addToAssertionCount(1);
  }

}
