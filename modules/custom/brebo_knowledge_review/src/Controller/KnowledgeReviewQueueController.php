<?php

declare(strict_types=1);

namespace Drupal\brebo_knowledge_review\Controller;

use Drupal\brebo_knowledge_review\Review\ReviewStatusStorage;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Shows the editorial review queue for BREBO KnowledgeItems.
 */
final class KnowledgeReviewQueueController extends ControllerBase {

  /**
   * Creates the controller.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ReviewStatusStorage $statusStorage,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('brebo_knowledge_review.status_storage'),
    );
  }

  /**
   * Builds the review queue.
   */
  public function queue(): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'brebo_knowledge_item')
      ->sort('title')
      ->execute();
    $nodes = $storage->loadMultiple($ids);

    $labels = [
      'to_review' => 'Te beoordelen',
      'in_review' => 'In beoordeling',
      'approved' => 'Goedgekeurd',
      'changes_required' => 'Herziening nodig',
    ];
    $counts = array_fill_keys(array_keys($labels), 0);
    $rows = [];

    foreach ($nodes as $node) {
      $status = $this->statusStorage->getEffectiveStatus((int) $node->id(), (int) $node->getRevisionId());
      $counts[$status] = ($counts[$status] ?? 0) + 1;
      $rows[] = [
        'title' => $node->label(),
        'status' => $labels[$status] ?? $status,
        'published' => $node->isPublished() ? 'Ja' : 'Nee',
        'review' => Link::fromTextAndUrl('Beoordelen', Url::fromRoute('brebo_knowledge_review.review', ['node' => $node->id()]))->toString(),
      ];
    }

    $summary = [];
    foreach ($labels as $key => $label) {
      $summary[] = ['#markup' => '<strong>' . $label . ':</strong> ' . ($counts[$key] ?? 0)];
    }

    return [
      'intro' => [
        '#markup' => '<p>Werk KnowledgeItems hier gecontroleerd af. Inhoudelijke wijzigingen trekken publieke en AI-vrijgave automatisch in; vrijgave gebeurt daarna expliciet via de daarvoor bedoelde reviewstap.</p>',
      ],
      'summary' => [
        '#theme' => 'item_list',
        '#items' => $summary,
      ],
      'table' => [
        '#type' => 'table',
        '#header' => ['KnowledgeItem', 'Reviewstatus', 'Publiek', 'Actie'],
        '#rows' => $rows,
        '#empty' => 'Geen KnowledgeItems gevonden.',
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

}
