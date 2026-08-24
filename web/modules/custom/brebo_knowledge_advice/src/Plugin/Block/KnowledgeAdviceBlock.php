<?php

declare(strict_types=1);

namespace Drupal\brebo_knowledge_advice\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

#[Block(
  id: 'brebo_knowledge_advice_block',
  admin_label: new TranslatableMarkup('BREBO – Kennis & advies'),
  category: new TranslatableMarkup('BREBO')
)]
final class KnowledgeAdviceBlock extends BlockBase {

  public function build(): array {
    return [
      '#theme' => 'brebo_knowledge_advice',
      '#attached' => [
        'library' => ['brebo_knowledge_advice/knowledge_advice'],
      ],
      '#cache' => ['max-age' => -1],
    ];
  }

}
