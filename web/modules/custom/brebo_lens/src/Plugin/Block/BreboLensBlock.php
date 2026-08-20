<?php

declare(strict_types=1);

namespace Drupal\brebo_lens\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Levert de interactieve BREBO Lens.
 */
#[Block(
  id: 'brebo_lens',
  admin_label: new TranslatableMarkup('BREBO Lens'),
  category: new TranslatableMarkup('BREBO'),
)]
final class BreboLensBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, private readonly ModuleExtensionList $moduleExtensionList) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self($configuration, $plugin_id, $plugin_definition, $container->get('extension.list.module'));
  }

  public function build(): array {
    $module_path = $this->moduleExtensionList->getPath('brebo_lens');

    $phases = [
      [
        'number' => '01',
        'title' => 'Inzicht',
        'subtitle' => 'Begrijpen + Analyseren',
        'text' => 'We beginnen bij het gebouw. We luisteren, onderzoeken de historie en technische staat en verbinden feiten, oorzaken, risico’s en kansen tot één helder beeld.',
        'points' => ['Vraag, doel en context begrijpen', 'Technische staat en historie analyseren', 'Oorzaken en risico’s zichtbaar maken', 'Scenario’s en prioriteiten onderbouwen'],
        'image' => Url::fromUri('base:' . $module_path . '/images/lens/01-begrijpen.webp')->toString(),
      ],
      [
        'number' => '02',
        'title' => 'Regie',
        'subtitle' => 'Adviseren + Organiseren',
        'text' => 'Inzicht wordt vertaald naar een onderbouwde koers. We maken keuzes inzichtelijk en organiseren mensen, disciplines, planning, communicatie en kwaliteit als één samenhangend proces.',
        'points' => ['Oplossingsrichtingen en gevolgen afwegen', 'Kosten en lange termijn meenemen', 'Verantwoordelijkheden en disciplines afstemmen', 'Planning, communicatie en kwaliteit organiseren'],
        'image' => Url::fromUri('base:' . $module_path . '/images/lens/03-adviseren.webp')->toString(),
      ],
      [
        'number' => '03',
        'title' => 'Realisatie',
        'subtitle' => 'Realiseren + Borgen',
        'text' => 'Pas wanneer duidelijk is wat nodig is, volgt de uitvoering. We bewaken kwaliteit en veiligheid, controleren het resultaat en leggen keuzes vast voor duurzaam en voorspelbaar gebouwbeheer.',
        'points' => ['Juiste werkzaamheden en specialisten inzetten', 'Kwaliteit, veiligheid en afstemming bewaken', 'Resultaat controleren en vastleggen', 'Onderhoud en continuïteit borgen'],
        'image' => Url::fromUri('base:' . $module_path . '/images/lens/05-realiseren.webp')->toString(),
      ],
    ];

    return [
      '#theme' => 'brebo_lens',
      '#initial_image_url' => $phases[0]['image'],
      '#phases' => $phases,
      '#attached' => ['library' => ['brebo_lens/lens']],
      '#cache' => ['max-age' => -1],
    ];
  }
}
