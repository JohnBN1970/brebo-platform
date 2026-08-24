<?php

declare(strict_types=1);

namespace Drupal\brebo_maintenance_renovation\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

#[Block(
  id: 'brebo_maintenance_renovation_block',
  admin_label: new TranslatableMarkup('BREBO – Onderhoud & renovatie'),
  category: new TranslatableMarkup('BREBO')
)]
final class MaintenanceRenovationBlock extends BlockBase {

  public function build(): array {
    return [
      '#theme' => 'brebo_maintenance_renovation',
      '#attached' => [
        'library' => ['brebo_maintenance_renovation/maintenance_renovation'],
      ],
      '#cache' => ['max-age' => -1],
    ];
  }

}
