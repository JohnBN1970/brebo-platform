<?php

declare(strict_types=1);

namespace Drupal\brebo_building_focus\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

#[Block(
  id: 'brebo_building_focus_block',
  admin_label: new TranslatableMarkup('BREBO – Uw gebouw: focus'),
  category: new TranslatableMarkup('BREBO')
)]
final class BuildingFocusBlock extends BlockBase {

  public function build(): array {
    return [
      '#theme' => 'brebo_building_focus',
      '#attached' => [
        'library' => ['brebo_building_focus/building_focus'],
      ],
      '#cache' => ['max-age' => -1],
    ];
  }

}
