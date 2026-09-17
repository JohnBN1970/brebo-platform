<?php

declare(strict_types=1);

namespace Drupal\Tests\brebo_contact\Unit;

use PHPUnit\Framework\TestCase;

final class JourneyContextRoutingTest extends TestCase {

  public function testJourneyContextDestinationIntent(): void {
    $knowledge = [
      'staat-inzicht',
      'onderhoudsplanning',
      'keuze-onduidelijk',
      'risico-kosten',
      'lekkage-tocht',
      'schade-slijtage',
      'glas-condens',
      'functioneren',
      'advies-nodig',
      'doel-onduidelijk',
      'mjop-rapport',
      'tekening-kozijnstaat',
      'fotos-overig',
    ];
    $supervision = [
      'offerte-bestek',
      'voorbereiding',
      'inkoop-aanbesteding',
      'uitvoering-toezicht',
      'oplevering-nazorg',
    ];
    $realisation = [
      'vervangen-verduurzamen',
      'probleem-oplossen',
      'onderhoud-herstel',
    ];

    self::assertContains('staat-inzicht', $knowledge);
    self::assertContains('offerte-bestek', $supervision);
    self::assertContains('onderhoud-herstel', $realisation);
  }

}
