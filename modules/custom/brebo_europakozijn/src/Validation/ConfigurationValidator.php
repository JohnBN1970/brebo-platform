<?php

namespace Drupal\brebo_europakozijn\Validation;

use Drupal\brebo_europakozijn\ValueObject\FrameConfiguration;

/**
 * Validates BREBO configurator invariants.
 *
 * Only application-level invariants belong here until verified Europakozijn
 * factory/product rules have been imported as authoritative product data.
 */
final class ConfigurationValidator {

  private const TYPES = ['vast', 'draaikiep', 'deur'];
  private const COLOURS = ['RAL 7016', 'RAL 9010', 'RAL 9005'];
  private const GLASS_TYPES = ['HR++', 'Triple'];

  /**
   * Returns structured validation messages.
   */
  public function validate(FrameConfiguration $configuration): array {
    $errors = [];

    if (!in_array($configuration->type, self::TYPES, TRUE)) {
      $errors[] = $this->error('type', 'unknown_type', 'Kies een geldig kozijntype.');
    }
    if ($configuration->widthMm <= 0) {
      $errors[] = $this->error('width_mm', 'invalid_width', 'Voer een geldige breedte in.');
    }
    if ($configuration->heightMm <= 0) {
      $errors[] = $this->error('height_mm', 'invalid_height', 'Voer een geldige hoogte in.');
    }
    if ($configuration->fields < 1 || $configuration->fields > 3) {
      $errors[] = $this->error('fields', 'invalid_fields', 'Kies één, twee of drie vakken.');
    }
    if (!in_array($configuration->colour, self::COLOURS, TRUE)) {
      $errors[] = $this->error('colour', 'unknown_colour', 'Kies een beschikbare kleur.');
    }
    if (!in_array($configuration->glass, self::GLASS_TYPES, TRUE)) {
      $errors[] = $this->error('glass', 'unknown_glass', 'Kies een beschikbaar glastype.');
    }

    return [
      'valid' => $errors === [],
      'errors' => $errors,
      'product_rules_verified' => FALSE,
    ];
  }

  private function error(string $field, string $code, string $message): array {
    return ['field' => $field, 'code' => $code, 'message' => $message];
  }

}
