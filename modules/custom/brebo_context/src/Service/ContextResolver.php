<?php

namespace Drupal\brebo_context\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Resolves related building questions from the available Dienst content.
 */
final class ContextResolver {

  /**
   * Fields that contribute to contextual similarity and their relative weight.
   */
  private const CONTEXT_FIELDS = [
    'title' => 5,
    'field_building_issue' => 4,
    'field_signals' => 3,
    'field_causes' => 3,
    'field_consequences' => 2,
    'field_solution_directions' => 2,
    'field_results' => 1,
  ];

  /**
   * Common words that add little contextual meaning.
   */
  private const STOP_WORDS = [
    'aan', 'als', 'bij', 'dan', 'dat', 'de', 'door', 'een', 'en', 'het', 'in',
    'is', 'kan', 'met', 'naar', 'niet', 'of', 'om', 'op', 'ook', 'te', 'tot',
    'uit', 'van', 'voor', 'wat', 'wordt', 'zijn', 'zich', 'uw', 'gebouw',
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Returns contextually related published Dienst nodes.
   *
   * @return \Drupal\node\NodeInterface[]
   *   Related nodes, ordered from most to least relevant.
   */
  public function resolveRelated(NodeInterface $current, int $limit = 3): array {
    if ($current->bundle() !== 'dienst' || $limit < 1) {
      return [];
    }

    $query = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'dienst')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('nid', $current->id(), '<>')
      ->sort('changed', 'DESC')
      ->range(0, 100);

    $ids = $query->execute();
    if (!$ids) {
      return [];
    }

    $candidates = $this->entityTypeManager->getStorage('node')->loadMultiple($ids);
    $current_profile = $this->buildProfile($current);
    $current_urgency = $this->getUrgency($current);
    $ranked = [];

    foreach ($candidates as $candidate) {
      if (!$candidate instanceof NodeInterface) {
        continue;
      }

      $candidate_profile = $this->buildProfile($candidate);
      $score = 0;
      $reasons = [];

      foreach ($current_profile as $field_name => $current_tokens) {
        if (!$current_tokens || empty($candidate_profile[$field_name])) {
          continue;
        }

        $overlap = array_intersect_key($current_tokens, $candidate_profile[$field_name]);
        if ($overlap) {
          $field_score = array_sum($overlap) * self::CONTEXT_FIELDS[$field_name];
          $score += $field_score;
          $reasons[] = $field_name;
        }
      }

      $candidate_urgency = $this->getUrgency($candidate);
      if ($current_urgency !== NULL && $current_urgency === $candidate_urgency) {
        $score += 3;
        $reasons[] = 'field_urgency';
      }

      $ranked[] = [
        'node' => $candidate,
        'score' => $score,
        'changed' => (int) $candidate->getChangedTime(),
        'reasons' => array_values(array_unique($reasons)),
      ];
    }

    usort($ranked, static function (array $left, array $right): int {
      return [$right['score'], $right['changed']] <=> [$left['score'], $left['changed']];
    });

    return array_map(
      static fn(array $item): NodeInterface => $item['node'],
      array_slice($ranked, 0, $limit),
    );
  }

  /**
   * Builds a weighted token profile for a node.
   */
  private function buildProfile(NodeInterface $node): array {
    $profile = [];

    foreach (self::CONTEXT_FIELDS as $field_name => $weight) {
      $text = $field_name === 'title'
        ? $node->label()
        : $this->getFieldText($node, $field_name);

      $profile[$field_name] = $this->tokenize($text);
    }

    return $profile;
  }

  /**
   * Extracts plain values from an optional field.
   */
  private function getFieldText(NodeInterface $node, string $field_name): string {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return '';
    }

    $values = [];
    foreach ($node->get($field_name) as $item) {
      if (isset($item->value) && is_scalar($item->value)) {
        $values[] = strip_tags((string) $item->value);
      }
    }

    return implode(' ', $values);
  }

  /**
   * Returns the urgency machine value when available.
   */
  private function getUrgency(NodeInterface $node): ?string {
    if (!$node->hasField('field_urgency') || $node->get('field_urgency')->isEmpty()) {
      return NULL;
    }

    return (string) $node->get('field_urgency')->value;
  }

  /**
   * Converts text into a normalized token-frequency map.
   */
  private function tokenize(string $text): array {
    $text = mb_strtolower($text);
    $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text) ?? '';
    $tokens = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $frequencies = [];

    foreach ($tokens as $token) {
      if (mb_strlen($token) < 4 || in_array($token, self::STOP_WORDS, TRUE)) {
        continue;
      }
      $frequencies[$token] = min(($frequencies[$token] ?? 0) + 1, 3);
    }

    return $frequencies;
  }

}
