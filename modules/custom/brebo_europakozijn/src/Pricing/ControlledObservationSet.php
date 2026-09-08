<?php

namespace Drupal\brebo_europakozijn\Pricing;

/**
 * Controlled glazed observations captured in one supplier quote run.
 *
 * Keeping these pairs together lets the model learn observed deltas without
 * inventing a physical explanation for every supplier pricing difference.
 */
final class ControlledObservationSet {

  public const QUOTE_RUN = 'WEB-26-1152537';

  /**
   * @return \Drupal\brebo_europakozijn\Pricing\PriceObservation[]
   */
  public function all(): array {
    return [
      $this->observation('i4000-fixed-1200', 'ideal4000', 1200, 1200, 'vast', 242.17),
      $this->observation('i4000-dk-1200', 'ideal4000', 1200, 1200, 'draai-kiep', 360.27),
      $this->observation('i7000nl-fixed-1200', 'ideal7000_nl', 1200, 1200, 'vast', 325.71),
      $this->observation('i7000nl-dk-1200', 'ideal7000_nl', 1200, 1200, 'draai-kiep', 446.43),
      $this->observation('i7000nl-fixed-980x1360', 'ideal7000_nl', 980, 1360, 'vast', 311.06),
      $this->observation('i7000nl-dk-980x1360', 'ideal7000_nl', 980, 1360, 'draai-kiep', 429.58),
    ];
  }

  private function observation(string $id, string $system, int $width, int $height, string $function, float $price): PriceObservation {
    return new PriceObservation(
      observationId: $id,
      quoteRunId: self::QUOTE_RUN,
      observedAt: '2026-09-07T22:01:18+02:00',
      system: $system,
      widthMm: $width,
      heightMm: $height,
      verticalMullions: [],
      horizontalTransoms: [],
      fields: [['id' => 'field-1', 'function' => $function]],
      glass: '4/16/4-24mm-GrA',
      grossSupplierPrice: $price,
      datasetRole: 'calibration',
      sourceType: 'controlled_quote',
    );
  }

}
