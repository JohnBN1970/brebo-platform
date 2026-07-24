<?php

declare(strict_types=1);

namespace Drupal\brebo_hero\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Levert de vaste hero voor de BREBO-homepage.
 */
#[Block(
  id: 'brebo_hero',
  admin_label: new TranslatableMarkup('BREBO Hero'),
  category: new TranslatableMarkup('BREBO'),
)]
final class HeroBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Maakt een nieuwe HeroBlock-instantie.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly ModuleExtensionList $moduleExtensionList,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
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

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $module_path = $this->moduleExtensionList->getPath('brebo_hero');

    return [
      '#theme' => 'brebo_hero',
      '#desktop_image_url' => Url::fromUri('base:' . $module_path . '/images/hero-home-desktop.webp')->toString(),
      '#mobile_image_url' => Url::fromUri('base:' . $module_path . '/images/hero-home-mobile.webp')->toString(),
      '#attached' => [
        'library' => [
          'brebo_hero/hero',
        ],
      ],
      '#cache' => [
        'max-age' => -1,
      ],
    ];
  }

}
