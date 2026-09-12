<?php

namespace Drupal\brebo_europakozijn\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Builds the public Europakozijn projectstukken intake page.
 */
final class EuropakozijnProjectstukkenController extends ControllerBase {

  /**
   * Returns the projectstukken page.
   */
  public function build(): array {
    return [
      '#theme' => 'brebo_europakozijn_projectstukken',
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
