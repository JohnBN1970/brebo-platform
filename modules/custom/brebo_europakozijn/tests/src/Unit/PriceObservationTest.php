<?php

namespace Drupal\Tests\brebo_europakozijn\Unit;

use Drupal\brebo_europakozijn\Pricing\ControlledObservationSet;
use Drupal\brebo_europakozijn\Pricing\PriceObservation;
use PHPUnit\Framework\TestCase;

final class PriceObservationTest extends TestCase {

  public function testGeometryFeaturesAreDerivedWithoutChangingObservedPrice(): void {
    $observation = new PriceObservation(
      observationId: 'style-example',
      quoteRunId: 'test-run',
      observedAt: '2026-09-08T08:00:00+02:00',
      system: 'ideal7000_nl',
      widthMm: 1800,
      heightMm: 1400,
      verticalMullions: [['position_mm' => 900, 'length_mm' => 1400]],
      horizontalTransoms: [],
      fields: [
        ['id' => 'left', 'function' => 'vast'],
        ['id' => 'right', 'function' => 'vast'],
      ],
      glass: '4/16/4-24mm-GrA',
      grossSupplierPrice: 500.00,
      datasetRole: 'experimental',
    );

    self::assertEqualsWithDelta(2.52, $observation->outerAreaM2(), 0.0001);
    self::assertEqualsWithDelta(6.4, $observation->outerPerimeterM(), 0.0001);
    self::assertSame(500.00, $observation->grossSupplierPrice);
    self::assertCount(1, $observation->verticalMullions);
    self::assertCount(2, $observation->fields);
  }

  public function testControlledSetPreservesSameRunObservedDeltas(): void {
    $observations = (new ControlledObservationSet())->all();
    $byId = [];
    foreach ($observations as $observation) {
      $byId[$observation->observationId] = $observation;
      self::assertSame(ControlledObservationSet::QUOTE_RUN, $observation->quoteRunId);
      self::assertSame('calibration', $observation->datasetRole);
    }

    self::assertEqualsWithDelta(118.10, $byId['i4000-dk-1200']->grossSupplierPrice - $byId['i4000-fixed-1200']->grossSupplierPrice, 0.001);
    self::assertEqualsWithDelta(120.72, $byId['i7000nl-dk-1200']->grossSupplierPrice - $byId['i7000nl-fixed-1200']->grossSupplierPrice, 0.001);
    self::assertEqualsWithDelta(118.52, $byId['i7000nl-dk-980x1360']->grossSupplierPrice - $byId['i7000nl-fixed-980x1360']->grossSupplierPrice, 0.001);
  }

}
