<?php

declare(strict_types=1);

namespace Drupal\brebo_benefits\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides the BREBO benefits homepage block.
 */
#[Block(
  id: 'brebo_benefits_block',
  admin_label: new TranslatableMarkup('BREBO – Voordelen'),
  category: new TranslatableMarkup('BREBO')
)]
final class BenefitsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#theme' => 'brebo_benefits',
      '#attached' => [
        'library' => [
          'brebo_benefits/benefits',
        ],
      ],
      '#cache' => [
        'max-age' => -1,
      ],
    ];
  }

}
