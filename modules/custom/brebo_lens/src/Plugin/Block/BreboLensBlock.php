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

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly ModuleExtensionList $moduleExtensionList,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('extension.list.module'),
    );
  }

  public function build(): array {
    $module_path = $this->moduleExtensionList->getPath('brebo_lens');

    $steps = [
      [
        'number' => '01',
        'title' => 'Inzicht',
        'short' => 'We begrijpen het gebouw, onderzoeken de situatie en maken keuzes onderbouwd.',
        'text' => 'Goed gebouwbeheer begint met inzicht. We brengen uw vraag, de historie, de technische staat, risico’s en kansen samen tot één helder beeld waarop u kunt beslissen.',
        'points' => ['Gebouw en vraag in beeld', 'Technische staat en oorzaken onderzocht', 'Risico’s en kansen benoemd', 'Keuzes en prioriteiten onderbouwd'],
        'icon' => 'search',
        'image' => Url::fromUri('base:' . $module_path . '/images/lens/02-analyseren.webp')->toString(),
      ],
      [
        'number' => '02',
        'title' => 'Regie',
        'short' => 'We vertalen inzicht naar een haalbare aanpak en bewaken het proces namens u.',
        'text' => 'Van advies tot voorbereiding en bouwbegeleiding: BREBO organiseert de samenhang. Afspraken, kosten, planning, kwaliteit en betrokken partijen worden vanuit één proces bewaakt.',
        'points' => ['Aanpak en voorbereiding georganiseerd', 'Partijen en disciplines afgestemd', 'Kosten en planning bewaakt', 'Kwaliteit en voortgang gecontroleerd'],
        'icon' => 'organize',
        'image' => Url::fromUri('base:' . $module_path . '/images/lens/04-organiseren.webp')->toString(),
      ],
      [
        'number' => '03',
        'title' => 'Realisatie',
        'short' => 'De gekozen aanpak wordt uitgevoerd, opgeleverd en duurzaam geborgd.',
        'text' => 'Wanneer uitvoering nodig is, zorgen we dat de juiste werkzaamheden op het juiste moment plaatsvinden. Daarna controleren we het resultaat en leggen we de basis voor voorspelbaar beheer.',
        'points' => ['Juiste werkzaamheden uitgevoerd', 'Kwaliteit en veiligheid bewaakt', 'Oplevering en restpunten gecontroleerd', 'Resultaat en vervolg geborgd'],
        'icon' => 'build',
        'image' => Url::fromUri('base:' . $module_path . '/images/lens/05-realiseren.webp')->toString(),
      ],
    ];

    return [
      '#theme' => 'brebo_lens',
      '#image_url' => Url::fromUri('base:' . $module_path . '/images/hero-home-desktop.webp')->toString(),
      '#initial_image_url' => $steps[0]['image'],
      '#steps' => $steps,
      '#attached' => [
        'library' => ['brebo_lens/lens'],
      ],
      '#cache' => ['max-age' => -1],
    ];
  }

}
