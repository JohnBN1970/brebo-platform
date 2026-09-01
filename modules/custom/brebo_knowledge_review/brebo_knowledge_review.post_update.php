<?php

/**
 * @file
 * Post-update hooks for BREBO Knowledge Review.
 */

declare(strict_types=1);

/**
 * Reconciles status storage for modules enabled before schema introduction.
 */
function brebo_knowledge_review_post_update_reconcile_status_storage(array &$sandbox): void {
  $database = \Drupal::database();
  $schema = $database->schema();

  if (!$schema->tableExists('brebo_knowledge_review_status')) {
    $definitions = brebo_knowledge_review_schema();
    $schema->createTable(
      'brebo_knowledge_review_status',
      $definitions['brebo_knowledge_review_status'],
    );
  }

  $current = \Drupal::keyValue('system.schema')->get('brebo_knowledge_review');
  if ($current === NULL || (int) $current < 11001) {
    \Drupal::keyValue('system.schema')->set('brebo_knowledge_review', 11001);
  }
}
