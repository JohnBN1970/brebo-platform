<?php

namespace Drupal\brebo_europakozijn\Pricing;

/**
 * One immutable observed supplier/configurator price.
 *
 * Observations preserve what was actually configured and priced. They do not
 * explain supplier costing and they never mix BREBO margin into the observed
 * gross supplier price.
 */
final readonly class PriceObservation {

  public function __construct(
    public string $observationId,
    public string $quoteRunId,
    public string $observedAt,
    public string $system,
    public int $widthMm,
    public int $heightMm,
    public array $verticalMullions,
    public array $horizontalTransoms,
    public array $fields,
    public string $glass,
    public float $grossSupplierPrice,
    public string $datasetRole = 'calibration',
    public string $sourceType = 'controlled_quote',
  ) {
    if ($this->widthMm <= 0 || $this->heightMm <= 0) {
      throw new \InvalidArgumentException('Observation dimensions must be positive.');
    }
    if ($this->grossSupplierPrice < 0) {
      throw new \InvalidArgumentException('Observed gross supplier price cannot be negative.');
    }
    if (!in_array($this->datasetRole, ['calibration', 'validation', 'experimental'], TRUE)) {
      throw new \InvalidArgumentException('Unknown dataset role.');
    }
  }

  public function outerAreaM2(): float {
    return ($this->widthMm * $this->heightMm) / 1_000_000;
  }

  public function outerPerimeterM(): float {
    return 2 * ($this->widthMm + $this->heightMm) / 1000;
  }

  public function toArray(): array {
    return [
      'observation_id' => $this->observationId,
      'quote_run_id' => $this->quoteRunId,
      'observed_at' => $this->observedAt,
      'system' => $this->system,
      'width_mm' => $this->widthMm,
      'height_mm' => $this->heightMm,
      'outer_area_m2' => round($this->outerAreaM2(), 4),
      'outer_perimeter_m' => round($this->outerPerimeterM(), 3),
      'vertical_mullions' => $this->verticalMullions,
      'horizontal_transoms' => $this->horizontalTransoms,
      'fields' => $this->fields,
      'glass' => $this->glass,
      'gross_supplier_price' => $this->grossSupplierPrice,
      'dataset_role' => $this->datasetRole,
      'source_type' => $this->sourceType,
    ];
  }

}
