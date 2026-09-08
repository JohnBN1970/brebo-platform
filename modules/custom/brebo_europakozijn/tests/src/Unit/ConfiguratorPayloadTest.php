<?php

namespace Drupal\Tests\brebo_europakozijn\Unit;

use Drupal\brebo_europakozijn\Contract\ConfiguratorPayload;
use PHPUnit\Framework\TestCase;

final class ConfiguratorPayloadTest extends TestCase {

  public function testGeometryStaysSeparateFromBrandSelection(): void {
    $payload = new ConfiguratorPayload(
      schemaVersion: 3,
      source: 'europakozijn_web_configurator',
      geometry: [
        'width_mm' => 1200,
        'height_mm' => 1500,
        'fields' => [['id' => 'r0c0', 'function' => 'vast', 'width_mm' => 1200, 'height_mm' => 1500]],
      ],
      productSelection: [
        'brand' => 'rehau',
        'system' => NULL,
      ],
      finish: [
        'colour' => 'RAL 7016',
        'glass' => 'HR++',
      ],
    );

    $data = $payload->toArray();

    self::assertSame(1200, $data['geometry']['width_mm']);
    self::assertArrayNotHasKey('brand', $data['geometry']);
    self::assertSame('rehau', $data['product_selection']['brand']);
  }

}
