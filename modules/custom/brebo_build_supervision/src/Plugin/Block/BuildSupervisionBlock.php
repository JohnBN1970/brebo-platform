<?php

declare(strict_types=1);

namespace Drupal\brebo_build_supervision\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides the BREBO Bouwbegeleiding page block.
 */
#[Block(
  id: 'brebo_build_supervision_block',
  admin_label: new TranslatableMarkup('BREBO – Bouwbegeleiding'),
  category: new TranslatableMarkup('BREBO')
)]
final class BuildSupervisionBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#theme' => 'brebo_build_supervision',
      '#attached' => [
        'library' => [
          'brebo_build_supervision/page',
        ],
      ],
      '#cache' => [
        'max-age' => -1,
      ],
    ];
  }

}
