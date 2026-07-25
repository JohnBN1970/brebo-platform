<?php

declare(strict_types=1);

namespace Drupal\brebo_core\Drush\Commands;

use Drupal\brebo_core\Service\PlatformInspector;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Drush-commando's voor veilige BREBO-platformdiagnostiek.
 */
final class BreboPlatformCommands extends DrushCommands {

  use AutowireTrait;

  public function __construct(
    #[Autowire(service: 'brebo_core.platform_inspector')]
    private readonly PlatformInspector $inspector,
  ) {
    parent::__construct();
  }

  #[CLI\Command(name: 'brebo:doctor', aliases: ['brebo-doctor'])]
  #[CLI\Usage(name: 'drush brebo:doctor', description: 'Controleert de gezondheid van het BREBO-platform.')]
  public function doctor(): int {
    $checks = $this->inspector->doctor();
    $this->printChecks($checks);
    $score = $this->inspector->score($checks);
    $this->io()->newLine();
    $this->io()->writeln(sprintf('<info>BREBO Platform Health: %d%%</info>', $score));

    $critical = array_filter($checks, static fn(array $check): bool => $check['status'] === 'CRITICAL');
    return $critical === [] ? self::EXIT_SUCCESS : self::EXIT_FAILURE;
  }

  #[CLI\Command(name: 'brebo:integrity', aliases: ['brebo-integrity'])]
  #[CLI\Usage(name: 'drush brebo:integrity', description: 'Controleert vocabularies en velden zonder wijzigingen uit te voeren.')]
  public function integrity(): int {
    $checks = $this->inspector->integrity();
    $this->printChecks($checks);
    return count(array_filter($checks, static fn(array $check): bool => !$check['ok'])) === 0
      ? self::EXIT_SUCCESS
      : self::EXIT_FAILURE;
  }

  #[CLI\Command(name: 'brebo:statistics', aliases: ['brebo-stats'])]
  #[CLI\Usage(name: 'drush brebo:statistics', description: 'Toont kennisstatistieken van Dienst, Project en taxonomie.')]
  public function statistics(): int {
    $rows = [];
    foreach ($this->inspector->statistics() as $label => $value) {
      $rows[] = [$label, (string) $value];
    }
    $this->io()->table(['Onderdeel', 'Aantal'], $rows);
    return self::EXIT_SUCCESS;
  }

  #[CLI\Command(name: 'brebo:readiness', aliases: ['brebo-readiness'])]
  #[CLI\Usage(name: 'drush brebo:readiness', description: 'Meet of de content gereed is voor context, zoeken en AI.')]
  public function readiness(): int {
    $rows = [];
    foreach ($this->inspector->readiness() as $label => $value) {
      $rows[] = [$label, (string) $value];
    }
    $this->io()->table(['Controle', 'Aantal'], $rows);
    return self::EXIT_SUCCESS;
  }

  private function printChecks(array $checks): void {
    $rows = [];
    foreach ($checks as $check) {
      $rows[] = [$check['controle'], $check['status'], $check['waarde']];
    }
    $this->io()->table(['Controle', 'Status', 'Waarde'], $rows);
  }

}
