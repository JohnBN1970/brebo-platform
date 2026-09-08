<?php

namespace Drupal\brebo_europakozijn\Contract;

/**
 * Canonical payload contract shared by web, Sparingsmeter and Office clients.
 */
final readonly class ConfiguratorPayload {

  public function __construct(
    public int $schemaVersion,
    public string $source,
    public array $geometry,
    public array $productSelection,
    public array $finish,
  ) {}

  public function toArray(): array {
    return [
      'schema_version' => $this->schemaVersion,
      'source' => $this->source,
      'geometry' => $this->geometry,
      'product_selection' => $this->productSelection,
      'finish' => $this->finish,
    ];
  }

}
