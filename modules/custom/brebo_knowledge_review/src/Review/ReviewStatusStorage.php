<?php

declare(strict_types=1);

namespace Drupal\brebo_knowledge_review\Review;

use Drupal\Core\Database\Connection;

/**
 * Stores and resolves lightweight editorial review decisions.
 */
final class ReviewStatusStorage {

  /**
   * Creates the storage service.
   */
  public function __construct(
    private readonly Connection $database,
  ) {}

  /**
   * Loads the stored decision for a KnowledgeItem.
   *
   * @return array{node_id:int, revision_id:int, status:string, reviewer_uid:int, changed:int, note:string}|null
   *   The decision record, or NULL when the item has not been reviewed yet.
   */
  public function load(int $nodeId): ?array {
    $record = $this->database->select('brebo_knowledge_review_status', 'r')
      ->fields('r')
      ->condition('node_id', $nodeId)
      ->execute()
      ->fetchAssoc();

    if (!$record) {
      return NULL;
    }

    return [
      'node_id' => (int) $record['node_id'],
      'revision_id' => (int) $record['revision_id'],
      'status' => (string) $record['status'],
      'reviewer_uid' => (int) $record['reviewer_uid'],
      'changed' => (int) $record['changed'],
      'note' => (string) $record['note'],
    ];
  }

  /**
   * Saves the current decision for a KnowledgeItem revision.
   */
  public function save(
    int $nodeId,
    int $revisionId,
    string $status,
    int $reviewerUid,
    int $changed,
    string $note,
  ): void {
    $this->database->merge('brebo_knowledge_review_status')
      ->key('node_id', $nodeId)
      ->fields([
        'revision_id' => $revisionId,
        'status' => $status,
        'reviewer_uid' => $reviewerUid,
        'changed' => $changed,
        'note' => $note,
      ])
      ->execute();
  }

  /**
   * Returns the effective status for the current revision.
   */
  public function getEffectiveStatus(int $nodeId, int $revisionId): string {
    $record = $this->load($nodeId);
    if ($record === NULL) {
      return 'to_review';
    }

    if ($record['revision_id'] !== $revisionId) {
      return 'changes_required';
    }

    return $record['status'];
  }

}
