<?php

declare(strict_types=1);

namespace Drupal\brebo_maintenance_renovation\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides the BREBO Onderhoud & renovatie page block.
 */
#[Block(
  id: 'brebo_maintenance_renovation_block',
  admin_label: new TranslatableMarkup('BREBO – Onderhoud & renovatie'),
  category: new TranslatableMarkup('BREBO')
)]
final class MaintenanceRenovationBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#theme' => 'brebo_maintenance_renovation',
      '#attached' => [
        'library' => [
          'brebo_maintenance_renovation/page',
        ],
      ],
      '#cache' => [
        'max-age' => -1,
      ],
    ];
  }

}
