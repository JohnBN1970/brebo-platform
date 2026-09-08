<?php

namespace Drupal\Tests\brebo_europakozijn\Unit;

use Drupal\brebo_europakozijn\ValueObject\FrameGeometry;
use PHPUnit\Framework\TestCase;

final class FrameGeometryTest extends TestCase {

  public function testSingleVerticalMullionCreatesTwoFields(): void {
    $geometry = new FrameGeometry(1800, 1400, 1, 0);

    self::assertSame(2, $geometry->fieldCount());
    self::assertSame([
      ['position_mm' => 900, 'length_mm' => 1400],
    ], $geometry->verticalMullions());
    self::assertSame([], $geometry->horizontalTransoms());
  }

  public function testSingleHorizontalTransomCreatesTwoFields(): void {
    $geometry = new FrameGeometry(1800, 1400, 0, 1);

    self::assertSame(2, $geometry->fieldCount());
    self::assertSame([
      ['position_mm' => 700, 'length_mm' => 1800],
    ], $geometry->horizontalTransoms());
  }

  public function testGridCreatesExpectedFieldCountAndEvenPositions(): void {
    $geometry = new FrameGeometry(1800, 1500, 2, 2);

    self::assertSame(9, $geometry->fieldCount());
    self::assertSame([600, 1200], array_column($geometry->verticalMullions(), 'position_mm'));
    self::assertSame([500, 1000], array_column($geometry->horizontalTransoms(), 'position_mm'));
  }

}
