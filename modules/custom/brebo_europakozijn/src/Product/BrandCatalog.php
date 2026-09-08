<?php

namespace Drupal\brebo_europakozijn\Product;

/**
 * Canonical brands offered by Europakozijn.
 *
 * Geometry is intentionally brand-independent. Product systems, technical
 * limits and pricing are attached after the geometry has been defined.
 */
final class BrandCatalog {

  private const BRANDS = [
    'aluplast' => 'Aluplast',
    'koemmerling' => 'Kömmerling',
    'rehau' => 'REHAU',
    'schueco' => 'Schüco',
  ];

  public function all(): array {
    return self::BRANDS;
  }

  public function has(string $brand): bool {
    return isset(self::BRANDS[$brand]);
  }

  public function label(string $brand): ?string {
    return self::BRANDS[$brand] ?? NULL;
  }

}
