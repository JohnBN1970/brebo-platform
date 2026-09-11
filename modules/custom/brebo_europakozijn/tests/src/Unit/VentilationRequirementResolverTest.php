<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_europakozijn\Unit;

use Drupal\brebo_europakozijn\Ventilation\VentilationRequirementResolver;
use PHPUnit\Framework\TestCase;

final class VentilationRequirementResolverTest extends TestCase {

  public function testRoomTypeAloneNeverRequiresFrameVentilation(): void {
    $result = (new VentilationRequirementResolver())->resolve([
      'room_type' => 'woonkamer',
    ]);

    self::assertSame('needs_assessment', $result['status']);
    self::assertNull($result['frame_supply_required']);
    self::assertNull($result['required_capacity_dm3_s']);
  }

  public function testVerifiedSufficientExistingProvisionStopsFrameRoute(): void {
    $result = (new VentilationRequirementResolver())->resolve([
      'room_type' => 'woonkamer',
      'existing_provision_verified' => TRUE,
      'existing_provision_sufficient' => TRUE,
      'existing_provision_authority' => 'verified_assessment',
      'existing_provision_source_reference' => 'assessment-1',
    ]);

    self::assertSame('existing_provision_sufficient', $result['status']);
    self::assertFalse($result['frame_supply_required']);
  }

  public function testVerifiedNoRequirementStopsFrameRoute(): void {
    $result = (new VentilationRequirementResolver())->resolve([
      'ventilation_requirement_verified' => TRUE,
      'ventilation_required' => FALSE,
      'requirement_authority' => 'verified_rule_source',
      'requirement_source_reference' => 'rule-1',
    ]);

    self::assertSame('not_required', $result['status']);
    self::assertFalse($result['frame_supply_required']);
  }

  public function testVerifiedRequirementDoesNotAutomaticallyMeanFrameSupply(): void {
    $result = (new VentilationRequirementResolver())->resolve([
      'ventilation_requirement_verified' => TRUE,
      'ventilation_required' => TRUE,
      'requirement_authority' => 'verified_rule_source',
      'requirement_source_reference' => 'rule-2',
    ]);

    self::assertSame('requirement_verified_route_unknown', $result['status']);
    self::assertNull($result['frame_supply_required']);
  }

  public function testOnlyVerifiedFrameRouteCanBecomeRequired(): void {
    $result = (new VentilationRequirementResolver())->resolve([
      'ventilation_requirement_verified' => TRUE,
      'ventilation_required' => TRUE,
      'frame_supply_route_verified' => TRUE,
      'frame_supply_required' => TRUE,
      'requirement_authority' => 'verified_rule_source',
      'requirement_source_reference' => 'rule-3',
    ]);

    self::assertSame('required', $result['status']);
    self::assertTrue($result['frame_supply_required']);
    self::assertNull($result['required_capacity_dm3_s']);
  }

}
