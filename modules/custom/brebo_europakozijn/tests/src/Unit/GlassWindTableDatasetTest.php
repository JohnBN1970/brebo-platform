<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_europakozijn\Unit;

use Drupal\brebo_europakozijn\Glass\GlassWindTableDataset;
use Drupal\brebo_europakozijn\Glass\GlassWindTableSource;
use PHPUnit\Framework\TestCase;

final class GlassWindTableDatasetTest extends TestCase {

  public function testDatasetFailsClosedUntilNumericRowsAreVerified(): void {
    $dataset = new GlassWindTableDataset(new GlassWindTableSource());

    self::assertFalse($dataset->canSelectComposition());
    self::assertFalse($dataset->metadata()['numeric_rows_verified']);
    self::assertSame('kcg_windstuwdruk', $dataset->metadata()['pressure_semantics']);
    self::assertSame([], $dataset->rows('idg_2x_float'));
  }

  public function testUnknownFamilyIsRejected(): void {
    $dataset = new GlassWindTableDataset(new GlassWindTableSource());

    $this->expectException(\InvalidArgumentException::class);
    $dataset->rows('invented_family');
  }

}
