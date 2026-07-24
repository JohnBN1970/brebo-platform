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
        'title' => 'Begrijpen',
        'short' => 'We luisteren, vragen door en brengen uw gebouw en omgeving in kaart.',
        'text' => 'Goed gebouwbeheer begint met begrijpen. Daarom brengen we eerst uw vraag, de historie en de huidige staat van het gebouw in kaart voordat keuzes worden gemaakt.',
        'points' => ['Uw vraag en doel helder', 'Historie en context in beeld', 'Huidige staat en risico’s', 'Belangen van gebruikers meegenomen'],
        'icon' => 'listen',
        'image' => Url::fromUri('base:' . $module_path . '/images/lens/01-begrijpen.webp')->toString(),
      ],
      [
        'number' => '02',
        'title' => 'Analyseren',
        'short' => 'Feiten, risico’s en kansen worden met elkaar verbonden.',
        'text' => 'U wilt beslissingen baseren op een volledig beeld. Daarom onderzoeken we de technische staat, de samenhang, de risico’s en de mogelijke scenario’s.',
        'points' => ['Technische staat onderzocht', 'Risico’s en oorzaken benoemd', 'Scenario’s vergeleken', 'Prioriteiten onderbouwd'],
        'icon' => 'search',
        'image' => Url::fromUri('base:' . $module_path . '/images/lens/02-analyseren.webp')->toString(),
      ],
      [
        'number' => '03',
        'title' => 'Adviseren',
        'short' => 'Inzicht wordt vertaald naar duidelijke en haalbare keuzes.',
        'text' => 'U krijgt geen los advies, maar een onderbouwde richting die past bij uw gebouw, uw doelen en de lange termijn.',
        'points' => ['Heldere oplossingsrichtingen', 'Kosten en gevolgen inzichtelijk', 'Lange termijn meegewogen', 'Besluitvorming vereenvoudigd'],
        'icon' => 'idea',
        'image' => Url::fromUri('base:' . $module_path . '/images/lens/03-adviseren.webp')->toString(),
      ],
      [
        'number' => '04',
        'title' => 'Organiseren',
        'short' => 'Mensen, disciplines en planning sluiten op elkaar aan.',
        'text' => 'U houdt overzicht doordat verantwoordelijkheden, communicatie, kwaliteit en planning vanuit één samenhangend proces worden georganiseerd.',
        'points' => ['Eén helder proces', 'Disciplines afgestemd', 'Planning en communicatie geborgd', 'Grip op kwaliteit en voortgang'],
        'icon' => 'organize',
        'image' => Url::fromUri('base:' . $module_path . '/images/lens/04-organiseren.webp')->toString(),
      ],
      [
        'number' => '05',
        'title' => 'Realiseren',
        'short' => 'De juiste werkzaamheden worden op het juiste moment uitgevoerd.',
        'text' => 'De uitvoering volgt pas wanneer duidelijk is wat nodig is en alles goed is voorbereid. Zo krijgt u kwaliteit zonder onnodige verrassingen.',
        'points' => ['Juiste specialisten ingezet', 'Kwaliteit en veiligheid bewaakt', 'Werkzaamheden goed afgestemd', 'Overlast zoveel mogelijk beperkt'],
        'icon' => 'build',
        'image' => Url::fromUri('base:' . $module_path . '/images/lens/05-realiseren.webp')->toString(),
      ],
      [
        'number' => '06',
        'title' => 'Borgen',
        'short' => 'Resultaten blijven werken, ook op de lange termijn.',
        'text' => 'Na de uitvoering wilt u zekerheid houden. Daarom evalueren we het resultaat, leggen we keuzes vast en bewaken we de continuïteit.',
        'points' => ['Resultaat gecontroleerd', 'Afspraken vastgelegd', 'Onderhoud voorspelbaar gemaakt', 'Continuïteit geborgd'],
        'icon' => 'shield',
        'image' => Url::fromUri('base:' . $module_path . '/images/lens/06-borgen.webp')->toString(),
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
