<?php

declare(strict_types=1);

namespace Drupal\brebo_europakozijn\Wind;

/**
 * Calculates design wind pressure from explicit, traceable norm inputs.
 *
 * This deliberately does not infer q_p(z), pressure coefficients or safety
 * factors from postcode/floor data. Those inputs must come from a separately
 * verified source before a wind check can be marked as passed.
 */
final class WindLoadCalculator {

  public const ENGINE_VERSION = '2026-09-09.1';

  public function designPressure(
    float $peakVelocityPressureKpa,
    float $externalPressureCoefficient,
    float $internalPressureCoefficient,
    float $partialFactor,
  ): float {
    if ($peakVelocityPressureKpa <= 0 || $partialFactor <= 0) {
      throw new \InvalidArgumentException('Winddruk en veiligheidsfactor moeten groter zijn dan nul.');
    }

    $netCoefficient = abs($externalPressureCoefficient - $internalPressureCoefficient);
    if ($netCoefficient <= 0) {
      throw new \InvalidArgumentException('Het netto drukverschil moet groter zijn dan nul.');
    }

    return round($peakVelocityPressureKpa * $netCoefficient * $partialFactor, 3);
  }

  /**
   * @return array{state:string,design_pressure_kpa:float,engine_version:string,standard_reference:string,calculation_reference:string,verified:bool,issues:string[]}
   */
  public function calculate(
    float $peakVelocityPressureKpa,
    float $externalPressureCoefficient,
    float $internalPressureCoefficient,
    float $partialFactor,
    string $standardReference,
    string $calculationReference,
    bool $verified,
  ): array {
    if (trim($standardReference) === '' || trim($calculationReference) === '') {
      throw new \InvalidArgumentException('Normversie en bronberekening zijn verplicht.');
    }

    $designPressure = $this->designPressure(
      $peakVelocityPressureKpa,
      $externalPressureCoefficient,
      $internalPressureCoefficient,
      $partialFactor,
    );

    $issues = [];
    if (!$verified) {
      $issues[] = 'Windbelastingberekening is nog niet door een bevoegde deskundige geverifieerd.';
    }

    return [
      'state' => $verified ? 'passed' : 'blocked',
      'design_pressure_kpa' => $designPressure,
      'engine_version' => self::ENGINE_VERSION,
      'standard_reference' => trim($standardReference),
      'calculation_reference' => trim($calculationReference),
      'verified' => $verified,
      'issues' => $issues,
    ];
  }

}
