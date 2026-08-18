<?php

declare(strict_types=1);

namespace Drupal\brebo_projects\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\file\FileInterface;
use Drupal\node\NodeInterface;
use Drupal\views\Views;

/**
 * Displays the featured BREBO projects selected by Views.
 */
#[Block(
  id: 'brebo_featured_projects',
  admin_label: new TranslatableMarkup('BREBO – Uitgelichte projecten'),
  category: new TranslatableMarkup('BREBO')
)]
final class BreboFeaturedProjectsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $view = Views::getView('brebo_featured_projects');

    if ($view === NULL) {
      return [];
    }

    $view->setDisplay('block_1');
    $view->execute();

    $projects = [];
    $cacheability = CacheableMetadata::createFromObject($view->storage);

    foreach ($view->result as $row) {
      $node = $row->_entity ?? NULL;

      if (!$node instanceof NodeInterface || $node->bundle() !== 'project') {
        continue;
      }

      $cacheability = $cacheability->merge(CacheableMetadata::createFromObject($node));

      $image_url = '';
      $image_alt = $node->label();

      if (!$node->get('field_project_image')->isEmpty()) {
        $image_item = $node->get('field_project_image')->first();
        $file = $image_item?->entity;

        if ($file instanceof FileInterface) {
          $image_url = \Drupal::service('file_url_generator')
            ->generateString($file->getFileUri());
          $image_alt = $image_item->get('alt')->getString() ?: $node->label();
        }
      }

      $results = [];
      foreach ($node->get('field_project_results') as $item) {
        $value = trim((string) $item->value);
        if ($value !== '') {
          $results[] = $value;
        }
      }

      $projects[] = [
        'title' => $node->label(),
        'url' => $node->toUrl()->toString(),
        'image_url' => $image_url,
        'image_alt' => $image_alt,
        'question' => $node->get('field_client_question')->value ?? '',
        'approach' => $node->get('field_brebo_approach')->value ?? '',
        'results' => $results,
        'pillars' => [
          'insight' => (bool) ($node->get('field_pillar_insight')->value ?? FALSE),
          'regie' => (bool) ($node->get('field_pillar_regie')->value ?? FALSE),
          'realisatie' => (bool) ($node->get('field_pillar_realisation')->value ?? FALSE),
        ],
      ];
    }

    if ($projects === []) {
      return [];
    }

    $build = [
      '#theme' => 'brebo_projects_section',
      '#projects' => $projects,
      '#projects_url' => '/projecten',
      '#attached' => [
        'library' => [
          'brebo_projects/projects',
        ],
      ],
    ];

    $cacheability->applyTo($build);
    $build['#cache']['contexts'][] = 'url.path';
    $build['#cache']['tags'][] = 'node_list:project';

    return $build;
  }

}
