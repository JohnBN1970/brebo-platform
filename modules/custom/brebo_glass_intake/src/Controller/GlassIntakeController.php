<?php

declare(strict_types=1);

namespace Drupal\brebo_glass_intake\Controller;

use Drupal\Core\Controller\ControllerBase;

final class GlassIntakeController extends ControllerBase {

  public function build(): array {
    return [
      '#theme' => 'brebo_glass_intake',
      '#attached' => [
        'library' => [
          'brebo_glass_intake/intake',
        ],
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
