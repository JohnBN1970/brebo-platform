<?php

namespace Drupal\Tests\brebo_europakozijn\Unit;

use Drupal\brebo_europakozijn\Validation\ConfigurationValidator;
use Drupal\brebo_europakozijn\ValueObject\FrameConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\brebo_europakozijn\Validation\ConfigurationValidator
 * @group brebo_europakozijn
 */
final class ConfigurationValidatorTest extends TestCase {

  public function testValidBaseConfiguration(): void {
    $configuration = new FrameConfiguration('draaikiep', 1200, 1500, 2, 'RAL 7016', 'HR++');
    $result = (new ConfigurationValidator())->validate($configuration);

    self::assertTrue($result['valid']);
    self::assertSame([], $result['errors']);
    self::assertFalse($result['product_rules_verified']);
  }

  public function testRejectsInvalidInput(): void {
    $configuration = new FrameConfiguration('onbekend', 0, -1, 8, 'paars', 'enkel');
    $result = (new ConfigurationValidator())->validate($configuration);

    self::assertFalse($result['valid']);
    self::assertCount(6, $result['errors']);
  }

  public function testStorageSchemaIsVersioned(): void {
    $configuration = new FrameConfiguration('vast', 1000, 1000, 1, 'RAL 9010', 'Triple');

    self::assertSame(1, $configuration->toArray()['schema_version']);
  }

}
