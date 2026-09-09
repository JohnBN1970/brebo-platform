<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_europakozijn\Unit\Glass;

use Drupal\brebo_europakozijn\Glass\GlassWindTableSource;
use PHPUnit\Framework\TestCase;

final class GlassWindTableSourceTest extends TestCase {

  public function testNormalFieldIsWithinPublicTableScope(): void {
    $source = new GlassWindTableSource();
    $result = $source->assessScope(1200, 1500);

    self::assertSame('within_scope', $result['state']);
    self::assertSame(1.8, $result['area_m2']);
    self::assertSame([], $result['reasons']);
  }

  public function testLargeFieldRequiresTechnicalReview(): void {
    $source = new GlassWindTableSource();
    $result = $source->assessScope(3500, 3000);

    self::assertSame('technical_review', $result['state']);
    self::assertContains('supplier_review_area_over_10m2', $result['reasons']);
  }

  public function testSmallDimensionRequiresSupplierReview(): void {
    $source = new GlassWindTableSource();
    $result = $source->assessScope(450, 1200);

    self::assertSame('technical_review', $result['state']);
    self::assertContains('supplier_review_small_dimension', $result['reasons']);
  }

}
