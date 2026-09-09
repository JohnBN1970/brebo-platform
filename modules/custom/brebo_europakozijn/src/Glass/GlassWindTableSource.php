<?php

declare(strict_types=1);

namespace Drupal\brebo_europakozijn\Glass;

/**
 * Describes the verified source contract for glass wind-load table data.
 *
 * Numeric table rows are deliberately not embedded here. They may only be
 * added after exact source extraction and verification. This prevents a
 * search snippet, OCR result or inferred value becoming technical truth.
 */
final class GlassWindTableSource {

  public const SOURCE_ID = 'kenniscentrum_glas_windlasttabellen';
  public const SOURCE_VERSION = '2017-11';
  public const SOURCE_URL = 'https://www.kenniscentrumglas.nl/vaktechnische-documentatie/';

  /**
   * Returns the source and scope that every imported table row must retain.
   *
   * @return array<string, mixed>
   *   Traceable source metadata and hard scope limits.
   */
  public function metadata(): array {
    return [
      'source_id' => self::SOURCE_ID,
      'source_version' => self::SOURCE_VERSION,
      'source_url' => self::SOURCE_URL,
      'publisher' => 'Kenniscentrum Glas',
      'verified' => TRUE,
      'scope' => [
        'orientation' => 'vertical',
        'orientation_degrees_min' => 80,
        'orientation_degrees_max' => 100,
        'support' => 'four_sided',
        'load_case' => 'wind_only',
        'maximum_area_m2' => 10.0,
        'smallest_dimension_supplier_review_below_mm' => 500,
      ],
      'families' => [
        'idg_2x_float',
        'idg_1x_laminated',
        'idg_2x_laminated',
      ],
    ];
  }

  /**
   * Checks whether a glass field is inside the public table scope.
   *
   * This does not select a composition; it only guards the source scope.
   *
   * @return array<string, mixed>
   *   Scope state and explicit reasons.
   */
  public function assessScope(float $widthMm, float $heightMm, float $orientationDegrees = 90.0, string $support = 'four_sided'): array {
    $reasons = [];

    if ($widthMm <= 0 || $heightMm <= 0) {
      return ['state' => 'blocked', 'reasons' => ['invalid_dimensions'], 'source' => $this->metadata()];
    }

    $areaM2 = ($widthMm * $heightMm) / 1_000_000;
    if ($orientationDegrees < 80 || $orientationDegrees > 100) {
      $reasons[] = 'outside_vertical_orientation_scope';
    }
    if ($support !== 'four_sided') {
      $reasons[] = 'outside_four_sided_support_scope';
    }
    if ($areaM2 > 10.0) {
      $reasons[] = 'supplier_review_area_over_10m2';
    }
    if (min($widthMm, $heightMm) < 500.0) {
      $reasons[] = 'supplier_review_small_dimension';
    }

    return [
      'state' => $reasons === [] ? 'within_scope' : 'technical_review',
      'area_m2' => round($areaM2, 3),
      'reasons' => $reasons,
      'source' => $this->metadata(),
    ];
  }

}
