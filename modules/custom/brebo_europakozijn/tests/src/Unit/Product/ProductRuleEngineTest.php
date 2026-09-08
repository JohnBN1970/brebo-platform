<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_europakozijn\Unit\Product;

use Drupal\brebo_europakozijn\Product\ProductRuleEngine;
use PHPUnit\Framework\TestCase;

final class ProductRuleEngineTest extends TestCase {

  public function testTiltTurnAtMaximumWidthIsAllowed(): void {
    $result = (new ProductRuleEngine())->evaluateField('aluplast', NULL, 'draaikiep', 1000, 1500);
    self::assertTrue($result->allowed);
  }

  public function testTiltTurnOverMaximumWidthIsBlocked(): void {
    $result = (new ProductRuleEngine())->evaluateField('aluplast', NULL, 'draaikiep', 1001, 1500);
    self::assertFalse($result->allowed);
    self::assertSame('brebo.dk.max_field_width', $result->code);
    self::assertStringContainsString('1001 mm', $result->message);
    self::assertSame('BREBO productregel', $result->authority);
  }

  public function testFixedFieldIsNotBlockedByTiltTurnRule(): void {
    $result = (new ProductRuleEngine())->evaluateField('schueco', NULL, 'vast', 1800, 1500);
    self::assertTrue($result->allowed);
  }

}
