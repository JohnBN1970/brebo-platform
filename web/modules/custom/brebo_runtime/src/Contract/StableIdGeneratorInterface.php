<?php

declare(strict_types=1);

namespace Drupal\brebo_runtime\Contract;

/**
 * Genereert een onveranderlijke functionele identifier.
 */
interface StableIdGeneratorInterface {

  /**
   * Reserveert en retourneert de eerstvolgende identifier.
   */
  public function generate(): string;

}
