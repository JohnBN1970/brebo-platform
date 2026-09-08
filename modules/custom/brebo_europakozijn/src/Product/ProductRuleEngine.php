<?php

declare(strict_types=1);

namespace Drupal\brebo_europakozijn\Product;

/**
 * Evaluates verified BREBO/product rules without polluting generic geometry.
 */
final class ProductRuleEngine {

  public const RULESET_VERSION = '2026-09-08.1';
  public const BREBO_DK_MAX_FIELD_WIDTH_MM = 1000;

  public function evaluateField(string $brand, ?string $system, string $function, int $widthMm, int $heightMm): ProductRuleResult {
    if ($function === 'draaikiep' && $widthMm > self::BREBO_DK_MAX_FIELD_WIDTH_MM) {
      return ProductRuleResult::blocked(
        'brebo.dk.max_field_width',
        sprintf('Dit vak is %d mm breed. Draai-kiep is toegestaan tot maximaal %d mm. Voeg een stijl toe of kies Vast glas.', $widthMm, self::BREBO_DK_MAX_FIELD_WIDTH_MM),
        'BREBO productregel',
      );
    }

    return ProductRuleResult::allowed();
  }

}
