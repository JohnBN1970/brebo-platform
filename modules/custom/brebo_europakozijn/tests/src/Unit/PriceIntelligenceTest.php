<?php

namespace Drupal\Tests\brebo_europakozijn\Unit;

use Drupal\brebo_europakozijn\Pricing\PriceIntelligence;
use Drupal\brebo_europakozijn\ValueObject\FrameConfiguration;
use PHPUnit\Framework\TestCase;

final class PriceIntelligenceTest extends TestCase {

  public function testIdeal7000NlFixedAnchor(): void {
    $estimate = (new PriceIntelligence())->estimate($this->configuration('vast', 1200, 1200), 'ideal7000_nl');
    self::assertEqualsWithDelta(325.71, $estimate->grossExpected, 0.01);
    self::assertEqualsWithDelta(166.11, $estimate->netExpected, 0.01);
    self::assertSame(49.0, $estimate->breboDiscountPercent);
  }

  public function testIdeal7000NlDkUsesObservedFunctionDelta(): void {
    $estimate = (new PriceIntelligence())->estimate($this->configuration('draai-kiep', 1200, 1200), 'ideal7000_nl');
    self::assertEqualsWithDelta(445.33, $estimate->grossExpected, 0.01);
  }

  public function testIdeal4000ControlledAnchors(): void {
    $fixed = (new PriceIntelligence())->estimate($this->configuration('vast', 1200, 1200), 'ideal4000');
    $dk = (new PriceIntelligence())->estimate($this->configuration('draai-kiep', 1200, 1200), 'ideal4000');
    self::assertEqualsWithDelta(242.17, $fixed->grossExpected, 0.01);
    self::assertEqualsWithDelta(360.27, $dk->grossExpected, 0.01);
  }

  public function testUncalibratedMultiFieldConfigurationIsNotGuessed(): void {
    $estimate = (new PriceIntelligence())->estimate($this->configuration('vast', 1200, 1200, 2), 'ideal7000_nl');
    self::assertSame('E', $estimate->reliability);
    self::assertSame(0.0, $estimate->grossExpected);
  }

  private function configuration(string $type, int $width, int $height, int $fields = 1): FrameConfiguration {
    return new FrameConfiguration(
      type: $type,
      widthMm: $width,
      heightMm: $height,
      fields: $fields,
      colour: 'wit',
      glass: 'hr++',
    );
  }

}
