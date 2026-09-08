<?php

namespace Drupal\brebo_europakozijn\Pricing;

/**
 * Immutable price prediction result.
 *
 * Gross supplier/configurator price and BREBO purchasing conditions are kept
 * separate deliberately. A discount is a commercial condition, not a property
 * of the frame configuration.
 */
final readonly class PriceEstimate {

  public function __construct(
    public float $grossExpected,
    public float $grossLow,
    public float $grossHigh,
    public string $reliability,
    public float $breboDiscountPercent,
    public float $netExpected,
    public string $modelVersion,
    public array $components = [],
  ) {}

  public function toArray(): array {
    return [
      'gross' => [
        'expected' => round($this->grossExpected, 2),
        'low' => round($this->grossLow, 2),
        'high' => round($this->grossHigh, 2),
      ],
      'reliability' => $this->reliability,
      'brebo_discount_percent' => $this->breboDiscountPercent,
      'net_purchase_expected' => round($this->netExpected, 2),
      'model_version' => $this->modelVersion,
      'components' => $this->components,
    ];
  }

}
