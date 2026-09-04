<?php

declare(strict_types=1);

namespace Drupal\brebo_projects\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\file\FileInterface;
use Drupal\node\NodeInterface;

/**
 * Builds the public BREBO projects overview.
 */
final class ProjectsOverviewController extends ControllerBase {

  /**
   * Returns the projects overview render array.
   */
  public function build(): array {
    $storage = $this->entityTypeManager()->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'project')
      ->condition('status', 1)
      ->sort('field_sort_order', 'ASC')
      ->sort('created', 'DESC')
      ->execute();

    $projects = [];
    $cacheability = new CacheableMetadata();

    foreach ($storage->loadMultiple($ids) as $node) {
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
          $image_url = \Drupal::service('file_url_generator')->generateString($file->getFileUri());
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
        'question' => (string) ($node->get('field_client_question')->value ?? ''),
        'approach' => (string) ($node->get('field_brebo_approach')->value ?? ''),
        'results' => $results,
        'pillars' => [
          'insight' => (bool) ($node->get('field_pillar_insight')->value ?? FALSE),
          'regie' => (bool) ($node->get('field_pillar_regie')->value ?? FALSE),
          'realisatie' => (bool) ($node->get('field_pillar_realisation')->value ?? FALSE),
        ],
      ];
    }

    $build = [
      '#theme' => 'brebo_projects_overview',
      '#projects' => $projects,
      '#attached' => [
        'library' => [
          'brebo_projects/projects',
        ],
      ],
      '#cache' => [
        'contexts' => ['user.permissions'],
        'tags' => ['node_list:project'],
      ],
    ];

    $cacheability->applyTo($build);

    return $build;
  }

}
