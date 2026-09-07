<?php

namespace Drupal\brebo_europakozijn\ValueObject;

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
    return new self(
      type: (string) ($values['type'] ?? ''),
      widthMm: (int) ($values['width_mm'] ?? 0),
      heightMm: (int) ($values['height_mm'] ?? 0),
      fields: (int) ($values['fields'] ?? 0),
      colour: (string) ($values['colour'] ?? ''),
      glass: (string) ($values['glass'] ?? ''),
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
