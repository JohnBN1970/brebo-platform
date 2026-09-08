<?php

namespace Drupal\Tests\brebo_europakozijn\Unit;

use Drupal\brebo_europakozijn\Product\BrandCatalog;
use PHPUnit\Framework\TestCase;

final class BrandCatalogTest extends TestCase {

  public function testCatalogContainsExactlyTheFourOfferedBrands(): void {
    $catalog = new BrandCatalog();

    self::assertSame([
      'aluplast' => 'Aluplast',
      'koemmerling' => 'Kömmerling',
      'rehau' => 'REHAU',
      'schueco' => 'Schüco',
    ], $catalog->all());
  }

  public function testUnknownBrandIsRejected(): void {
    $catalog = new BrandCatalog();

    self::assertTrue($catalog->has('aluplast'));
    self::assertFalse($catalog->has('windows4u'));
    self::assertNull($catalog->label('unknown'));
  }

}
