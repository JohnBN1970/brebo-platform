<?php

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Builds the public Europakozijn configurator MVP.
 */
final class EuropakozijnConfiguratorController extends ControllerBase {

  /**
   * Returns the configurator shell.
   */
  public function build(): array {
    return [
      '#theme' => 'brebo_europakozijn_configurator',
      '#attached' => [
        'library' => [
          'brebo_europakozijn/configurator',
        ],
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
