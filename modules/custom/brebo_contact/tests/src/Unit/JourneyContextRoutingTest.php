<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_contact\Unit;

use PHPUnit\Framework\TestCase;

final class JourneyContextRoutingTest extends TestCase {

  public function testProductionJourneyContextDestinations(): void {
    $source = file_get_contents(dirname(__DIR__, 3) . '/src/Form/ContactMessageForm.php');
    self::assertIsString($source);

    self::assertSame('knowledge', $this->contextDestination($source, 'staat-inzicht'));
    self::assertSame('knowledge', $this->contextDestination($source, 'advies-nodig'));
    self::assertSame('knowledge', $this->contextDestination($source, 'mjop-rapport'));
    self::assertSame('supervision', $this->contextDestination($source, 'offerte-bestek'));
    self::assertSame('supervision', $this->contextDestination($source, 'uitvoering-toezicht'));
    self::assertSame('realisation', $this->contextDestination($source, 'onderhoud-herstel'));
    self::assertSame('realisation', $this->contextDestination($source, 'vervangen-verduurzamen'));
  }

  public function testProductionJourneyDestinationUrls(): void {
    $source = file_get_contents(dirname(__DIR__, 3) . '/src/Form/ContactMessageForm.php');
    self::assertIsString($source);

    self::assertSame('/kennis-advies', $this->destinationUrl($source, 'knowledge'));
    self::assertSame('/bouwbegeleiding', $this->destinationUrl($source, 'supervision'));
    self::assertSame('/onderhoud-renovatie', $this->destinationUrl($source, 'realisation'));
  }

  private function contextDestination(string $source, string $context): string {
    self::assertMatchesRegularExpression('/\\$contextDestination\\s*=\\s*\\[(.*?)\\];/s', $source);
    preg_match('/\\$contextDestination\\s*=\\s*\\[(.*?)\\];/s', $source, $blockMatch);
    $block = $blockMatch[1] ?? '';

    $pattern = "/'" . preg_quote($context, '/') . "'\\s*=>\\s*'([^']+)'/";
    self::assertMatchesRegularExpression($pattern, $block, 'Missing production routing entry for ' . $context);
    preg_match($pattern, $block, $match);

    return $match[1] ?? '';
  }

  private function destinationUrl(string $source, string $destination): string {
    self::assertMatchesRegularExpression('/\\$destinationInfo\\s*=\\s*\\[(.*?)\\];/s', $source);
    preg_match('/\\$destinationInfo\\s*=\\s*\\[(.*?)\\];/s', $source, $blockMatch);
    $block = $blockMatch[1] ?? '';

    $pattern = "/'" . preg_quote($destination, '/') . "'\\s*=>\\s*\\[(.*?)(?=\\n\\s*'[^']+'\\s*=>\\s*\\[|$)/s";
    self::assertMatchesRegularExpression($pattern, $block, 'Missing destination info for ' . $destination);
    preg_match($pattern, $block, $destinationMatch);
    $destinationBlock = $destinationMatch[1] ?? '';

    self::assertMatchesRegularExpression("/'url'\\s*=>\\s*'([^']+)'/", $destinationBlock);
    preg_match("/'url'\\s*=>\\s*'([^']+)'/", $destinationBlock, $urlMatch);

    return $urlMatch[1] ?? '';
  }

}
