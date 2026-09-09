<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_europakozijn\Unit\Wind;

use Drupal\brebo_europakozijn\Wind\WindLoadCalculator;
use PHPUnit\Framework\TestCase;

final class WindLoadCalculatorTest extends TestCase {

  public function testDesignPressureUsesExplicitTraceableInputs(): void {
    $calculator = new WindLoadCalculator();
    self::assertSame(1.08, $calculator->designPressure(0.6, 0.8, -0.4, 1.5));
  }

  public function testVerifiedCalculationCanPass(): void {
    $result = (new WindLoadCalculator())->calculate(
      0.6,
      0.8,
      -0.4,
      1.5,
      'TEST-NORM-VERSION',
      'CALC-001',
      TRUE,
    );

    self::assertSame('passed', $result['state']);
    self::assertSame(1.08, $result['design_pressure_kpa']);
    self::assertTrue($result['verified']);
    self::assertSame([], $result['issues']);
  }

  public function testUnverifiedCalculationNeverPasses(): void {
    $result = (new WindLoadCalculator())->calculate(
      0.6,
      0.8,
      -0.4,
      1.5,
      'TEST-NORM-VERSION',
      'CALC-002',
      FALSE,
    );

    self::assertSame('blocked', $result['state']);
    self::assertFalse($result['verified']);
    self::assertNotEmpty($result['issues']);
  }

  public function testMissingTraceabilityIsRejected(): void {
    $this->expectException(\InvalidArgumentException::class);
    (new WindLoadCalculator())->calculate(0.6, 0.8, -0.4, 1.5, '', 'CALC-003', TRUE);
  }

}
