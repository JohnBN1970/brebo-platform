<?php

namespace Drupal\brebo_pillars\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;

/**
 * Provides the BREBO three pillars block.
 *
 * @Block(
 *   id = "brebo_pillars_block",
 *   admin_label = @Translation("BREBO - Drie pijlers"),
 *   category = @Translation("BREBO")
 * )
 */
final class BreboPillarsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $items = [
      [
        'number' => '01',
        'machine_name' => 'inzicht',
        'title' => $this->t('Inzicht'),
        'heading' => $this->t('Wat is er werkelijk aan de hand?'),
        'text' => $this->t("U wilt weten welke feiten vaststaan, wat de oorzaak is, welke risico's er zijn en welke onderhoudsbehoefte daaruit volgt. Zo kunt u een maatregel beoordelen voordat u geld en tijd vastlegt."),
        'url' => Url::fromUserInput('/inzicht')->toString(),
        'link_text' => $this->t('Krijg inzicht'),
      ],
      [
        'number' => '02',
        'machine_name' => 'regie',
        'title' => $this->t('Regie'),
        'heading' => $this->t('Hoe wordt de gekozen route beheersbaar?'),
        'text' => $this->t('Wanneer duidelijk is wat nodig is, wilt u grip op scope, prioriteiten, planning, kosten, verantwoordelijkheden en afstemming. Zo blijft de gekozen aanpak bestuurbaar voordat en tijdens de uitvoering.'),
        'url' => Url::fromUserInput('/regie')->toString(),
        'link_text' => $this->t('Houd grip'),
      ],
      [
        'number' => '03',
        'machine_name' => 'realisatie',
        'title' => $this->t('Realisatie'),
        'heading' => $this->t('Wordt uitgevoerd wat daadwerkelijk is afgesproken?'),
        'text' => $this->t('De uitvoering volgt uit de onderbouwde keuze. U wilt dat werkzaamheden aantoonbaar volgens scope, kwaliteit en planning worden gerealiseerd en dat afwijkingen tijdig zichtbaar en beheerst worden.'),
        'url' => Url::fromUserInput('/realisatie')->toString(),
        'link_text' => $this->t('Bekijk realisatie'),
      ],
    ];

    return [
      '#theme' => 'brebo_pillars',
      '#items' => $items,
      '#attached' => [
        'library' => [
          'brebo_pillars/pillars',
        ],
      ],
      '#cache' => [
        'max-age' => Cache::PERMANENT,
      ],
    ];
  }

}
