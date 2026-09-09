<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_europakozijn\Unit\Glass;

use Drupal\brebo_europakozijn\Glass\GlassCandidateSelector;
use PHPUnit\Framework\TestCase;

final class GlassCandidateSelectorTest extends TestCase {

  public function testSelectsLightestVerifiedSuitableCandidate(): void {
    $selector = new GlassCandidateSelector();
    $result = $selector->select([
      [
        'id' => 'heavy',
        'verified' => TRUE,
        'source_reference' => 'Kenniscentrum Glas windlasttabel',
        'source_version' => 'fixture-1',
        'wind_resistance_kpa' => 1.5,
        'max_width_mm' => 2000,
        'max_height_mm' => 2500,
        'glass_type' => 'insulating',
        'weight_kg_m2' => 35.0,
        'composition' => 'fixture-heavy',
      ],
      [
        'id' => 'light',
        'verified' => TRUE,
        'source_reference' => 'Kenniscentrum Glas windlasttabel',
        'source_version' => 'fixture-1',
        'wind_resistance_kpa' => 1.2,
        'max_width_mm' => 2000,
        'max_height_mm' => 2500,
        'glass_type' => 'insulating',
        'weight_kg_m2' => 25.0,
        'composition' => 'fixture-light',
      ],
    ], 0.9, 1200, 1500, 'standard');

    self::assertSame('passed', $result['state']);
    self::assertSame('light', $result['recommended']['id']);
    self::assertSame(0.75, $result['recommended']['utilization']);
  }

  public function testRejectsUnverifiedOrUntraceableCandidate(): void {
    $selector = new GlassCandidateSelector();
    $result = $selector->select([
      [
        'id' => 'candidate',
        'verified' => FALSE,
        'source_reference' => '',
        'source_version' => '',
        'wind_resistance_kpa' => 2.0,
        'max_width_mm' => 3000,
        'max_height_mm' => 3000,
        'glass_type' => 'insulating',
        'weight_kg_m2' => 20.0,
      ],
    ], 0.8, 1000, 1000, 'standard');

    self::assertSame('blocked', $result['state']);
    self::assertNull($result['recommended']);
    self::assertCount(1, $result['rejected']);
    self::assertCount(3, $result['rejected'][0]['reasons']);
  }

  public function testSafetyApplicationRequiresSafetyGlassType(): void {
    $selector = new GlassCandidateSelector();
    $result = $selector->select([
      [
        'id' => 'float',
        'verified' => TRUE,
        'source_reference' => 'fixture',
        'source_version' => 'fixture-1',
        'wind_resistance_kpa' => 2.0,
        'max_width_mm' => 3000,
        'max_height_mm' => 3000,
        'glass_type' => 'insulating',
        'weight_kg_m2' => 20.0,
      ],
      [
        'id' => 'laminated',
        'verified' => TRUE,
        'source_reference' => 'fixture',
        'source_version' => 'fixture-1',
        'wind_resistance_kpa' => 2.0,
        'max_width_mm' => 3000,
        'max_height_mm' => 3000,
        'glass_type' => 'laminated',
        'weight_kg_m2' => 25.0,
      ],
    ], 0.8, 1000, 1000, 'fall_protection');

    self::assertSame('laminated', $result['recommended']['id']);
  }

}
