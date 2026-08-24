<?php

declare(strict_types=1);

namespace Drupal\brebo_contact\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

#[Block(
  id: 'brebo_contact_block',
  admin_label: new TranslatableMarkup('BREBO – Contact'),
  category: new TranslatableMarkup('BREBO')
)]
final class ContactBlock extends BlockBase {

  public function build(): array {
    return [
      '#theme' => 'brebo_contact',
      '#attached' => [
        'library' => ['brebo_contact/contact'],
      ],
      '#cache' => ['max-age' => -1],
    ];
  }

}
