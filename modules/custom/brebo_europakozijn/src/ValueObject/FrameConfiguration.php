<?php

namespace Drupal\brebo_europakozijn\ValueObject;

use InvalidArgumentException;

/**
 * Immutable canonical input for an Europakozijn configuration.
 *
 * Product/factory-specific limits deliberately do not live here. This object
 * only describes the customer's chosen configuration in a stable schema.
 */
final readonly class FrameConfiguration {

  public const SCHEMA_VERSION = 1;

  public function __construct(
    public string $type,
    public int $widthMm,
    public int $heightMm,
    public int $fields,
    public string $colour,
    public string $glass,
  ) {}

  /**
   * Builds a configuration from an untrusted payload.
   */
  public static function fromArray(array $values): self {
    if (($values['schema_version'] ?? self::SCHEMA_VERSION) !== self::SCHEMA_VERSION) {
      throw new InvalidArgumentException('unsupported_schema_version');
    }

    foreach (['width_mm', 'height_mm', 'fields'] as $field) {
      if (!array_key_exists($field, $values) || !is_int($values[$field])) {
        throw new InvalidArgumentException('invalid_integer:' . $field);
      }
    }

    foreach (['type', 'colour', 'glass'] as $field) {
      if (!array_key_exists($field, $values) || !is_string($values[$field])) {
        throw new InvalidArgumentException('invalid_string:' . $field);
      }
    }

    return new self(
      type: $values['type'],
      widthMm: $values['width_mm'],
      heightMm: $values['height_mm'],
      fields: $values['fields'],
      colour: $values['colour'],
      glass: $values['glass'],
    );
  }

  /**
   * Returns the versioned representation for storage/API transport.
   */
  public function toArray(): array {
    return [
      'schema_version' => self::SCHEMA_VERSION,
      'type' => $this->type,
      'width_mm' => $this->widthMm,
      'height_mm' => $this->heightMm,
      'fields' => $this->fields,
      'colour' => $this->colour,
      'glass' => $this->glass,
    ];
  }

}
