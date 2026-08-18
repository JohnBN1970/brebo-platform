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
        'heading' => $this->t('Goede beslissingen beginnen met een volledig beeld.'),
        'text' => $this->t("U wilt investeren op basis van feiten, niet op aannames. Daarom brengen we eerst de technische staat van uw gebouw, de risico's en de onderhoudsbehoefte in kaart. Zo ontstaat een helder overzicht waarop u met vertrouwen keuzes kunt maken."),
        'url' => Url::fromUserInput('/inzicht')->toString(),
        'link_text' => $this->t('Ontdek Inzicht'),
      ],
      [
        'number' => '02',
        'machine_name' => 'regie',
        'title' => $this->t('Regie'),
        'heading' => $this->t('Overzicht ontstaat wanneer alles op elkaar aansluit.'),
        'text' => $this->t('Bij vastgoedonderhoud zijn vaak meerdere disciplines betrokken. Zonder goede afstemming ontstaan vertragingen, extra kosten en onnodige overlast. Door planning, communicatie en uitvoering op elkaar af te stemmen, houdt u grip op het volledige proces.'),
        'url' => Url::fromUserInput('/regie')->toString(),
        'link_text' => $this->t('Ontdek Regie'),
      ],
      [
        'number' => '03',
        'machine_name' => 'realisatie',
        'title' => $this->t('Realisatie'),
        'heading' => $this->t('De juiste werkzaamheden, op het juiste moment.'),
        'text' => $this->t('Wanneer duidelijk is wat nodig is en het proces goed is georganiseerd, volgt de uitvoering. De werkzaamheden worden uitgevoerd door de juiste specialisten, met aandacht voor kwaliteit, duurzaamheid en de lange termijn van uw gebouw.'),
        'url' => Url::fromUserInput('/realisatie')->toString(),
        'link_text' => $this->t('Ontdek Realisatie'),
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
