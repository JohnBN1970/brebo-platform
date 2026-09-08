<?php

namespace Drupal\brebo_europakozijn\Pricing;

use Drupal\brebo_europakozijn\ValueObject\FrameConfiguration;

/**
 * First empirical gross-price predictor for Europakozijn.
 *
 * This deliberately predicts observed supplier/configurator output instead of
 * pretending to reconstruct every hidden supplier costing rule. Coefficients
 * are versioned calibration data and must only be changed from measured runs.
 */
final class PriceIntelligence {

  public const MODEL_VERSION = '2026-09-08.1';
  public const BREBO_DISCOUNT_PERCENT = 49.0;

  /**
   * Same-run controlled measurements available at model creation.
   */
  private const CALIBRATION = [
    'ideal4000' => [
      'fixed_1200x1200' => 242.17,
      'dk_delta' => 118.10,
    ],
    'ideal7000_nl' => [
      'fixed_1200x1200' => 325.71,
      'fixed_980x1360' => 311.06,
      'dk_delta_mean' => 119.62,
    ],
  ];

  /**
   * Predicts a gross configurator price and applies BREBO discount separately.
   *
   * V1 intentionally supports only calibrated simple fixed/DK frames. Mullion,
   * transom, door and option effects are not guessed: unsupported configurations
   * return reliability E rather than silently inventing a precise price.
   */
  public function estimate(FrameConfiguration $configuration, string $system = 'ideal7000_nl'): PriceEstimate {
    $area = ($configuration->widthMm * $configuration->heightMm) / 1_000_000;
    $perimeter = 2 * ($configuration->widthMm + $configuration->heightMm) / 1000;

    $components = [
      'system' => $system,
      'area_m2' => round($area, 4),
      'outer_perimeter_m' => round($perimeter, 3),
      'method' => 'empirical_v1',
    ];

    if ($configuration->fields !== 1 || !in_array($configuration->type, ['vast', 'draai-kiep'], TRUE)) {
      return $this->unsupported($components + ['reason' => 'uncalibrated_geometry_or_function']);
    }

    $gross = match ($system) {
      'ideal4000' => $this->ideal4000($configuration, $components),
      'ideal7000_nl' => $this->ideal7000Nl($configuration, $components),
      default => NULL,
    };

    if ($gross === NULL) {
      return $this->unsupported($components + ['reason' => 'uncalibrated_system']);
    }

    // V1 uncertainty is deliberately conservative until blind validation exists.
    $band = max(15.0, $gross * 0.08);
    $net = $gross * (1 - self::BREBO_DISCOUNT_PERCENT / 100);

    return new PriceEstimate(
      grossExpected: $gross,
      grossLow: max(0, $gross - $band),
      grossHigh: $gross + $band,
      reliability: 'C',
      breboDiscountPercent: self::BREBO_DISCOUNT_PERCENT,
      netExpected: $net,
      modelVersion: self::MODEL_VERSION,
      components: $components,
    );
  }

  private function ideal4000(FrameConfiguration $configuration, array &$components): float {
    // Local first-order geometry adjustment around the controlled 1200x1200
    // anchor. It is intentionally weak; historical data will replace this with
    // fitted coefficients once imported as structured observations.
    $anchor = self::CALIBRATION['ideal4000']['fixed_1200x1200'];
    $areaDelta = (($configuration->widthMm * $configuration->heightMm) - 1_440_000) / 1_000_000;
    $fixed = $anchor + (85.0 * $areaDelta);
    $function = $configuration->type === 'draai-kiep' ? self::CALIBRATION['ideal4000']['dk_delta'] : 0.0;
    $components['fixed_anchor'] = $anchor;
    $components['function_delta'] = $function;
    return $fixed + $function;
  }

  private function ideal7000Nl(FrameConfiguration $configuration, array &$components): float {
    // Interpolate the fixed-frame level from two same-run measured anchors using
    // outer perimeter. This is a predictor, not a claim about supplier costing.
    $p1 = 4.8;
    $v1 = self::CALIBRATION['ideal7000_nl']['fixed_1200x1200'];
    $p2 = 4.68;
    $v2 = self::CALIBRATION['ideal7000_nl']['fixed_980x1360'];
    $perimeter = 2 * ($configuration->widthMm + $configuration->heightMm) / 1000;
    $slope = ($v1 - $v2) / ($p1 - $p2);
    $fixed = $v1 + (($perimeter - $p1) * $slope);
    $function = $configuration->type === 'draai-kiep' ? self::CALIBRATION['ideal7000_nl']['dk_delta_mean'] : 0.0;
    $components['fixed_anchor_1200x1200'] = $v1;
    $components['function_delta'] = $function;
    return $fixed + $function;
  }

  private function unsupported(array $components): PriceEstimate {
    return new PriceEstimate(
      grossExpected: 0.0,
      grossLow: 0.0,
      grossHigh: 0.0,
      reliability: 'E',
      breboDiscountPercent: self::BREBO_DISCOUNT_PERCENT,
      netExpected: 0.0,
      modelVersion: self::MODEL_VERSION,
      components: $components,
    );
  }

}
