<?php

declare(strict_types=1);

namespace Drupal\brebo_customer_service\Knowledge;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\NodeInterface;

/**
 * Applies explicit human review metadata to canonical KnowledgeItems.
 *
 * Review metadata is stored inside field_knowledge_basis so the canonical
 * sixteen-object Knowledge configuration remains unchanged.
 */
final class KnowledgeReviewManager {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly AccountProxyInterface $currentUser,
    private readonly TimeInterface $time,
  ) {}

  public function saveReview(
    string $slug,
    string $status,
    array $sources,
    string $validityDate,
    bool $aiApproved,
  ): NodeInterface {
    if (!in_array($status, [
      KnowledgeApproval::STATUS_EDITORIAL,
      KnowledgeApproval::STATUS_REVIEW,
      KnowledgeApproval::STATUS_APPROVED,
      KnowledgeApproval::STATUS_REJECTED,
    ], TRUE)) {
      throw new \InvalidArgumentException('Ongeldige kennisstatus.');
    }

    $sources = array_values(array_unique(array_filter(array_map('trim', $sources))));
    if ($status === KnowledgeApproval::STATUS_APPROVED && ($sources === [] || trim($validityDate) === '')) {
      throw new \InvalidArgumentException('Goedkeuring vereist minimaal één bron en een geldigheidscontrole.');
    }
    if ($aiApproved && $status !== KnowledgeApproval::STATUS_APPROVED) {
      throw new \InvalidArgumentException('AI-vrijgave is alleen mogelijk voor goedgekeurde kennis.');
    }
    if ($aiApproved && ($sources === [] || trim($validityDate) === '')) {
      throw new \InvalidArgumentException('AI-vrijgave vereist bron en geldigheidscontrole.');
    }

    $node = $this->loadBySlug($slug);
    if ($node === NULL) {
      throw new \RuntimeException('Kennisitem niet gevonden.');
    }

    $basis = (string) $node->get('field_knowledge_basis')->value;
    $reviewer = $this->currentUser->getDisplayName();
    $reviewedAt = gmdate('Y-m-d\TH:i:s\Z', $this->time->getRequestTime());

    $basis = $this->setLine($basis, 'Status:', $status);
    $basis = $this->setLine($basis, 'Bronnen:', implode('; ', $sources));
    $basis = $this->setLine($basis, 'Geldigheid:', $validityDate);
    $basis = $this->setLine($basis, 'Deskundige controle:', $reviewer . ' | ' . $reviewedAt);
    $basis = $this->setLine($basis, 'AI-vrijgave:', $aiApproved ? 'ja' : 'nee');

    $node->set('field_knowledge_basis', $basis);
    $node->setNewRevision(TRUE);
    $node->setRevisionLogMessage(sprintf('Kennisbeoordeling: %s; AI-vrijgave: %s.', $status, $aiApproved ? 'ja' : 'nee'));
    $node->save();

    return $node;
  }

  private function loadBySlug(string $slug): ?NodeInterface {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'brebo_knowledge_item')
      ->condition('field_knowledge_basis', 'BREBO-WEB-SEED:' . $slug, 'CONTAINS')
      ->range(0, 2)
      ->execute();

    if (count($ids) !== 1) {
      return NULL;
    }
    $node = $storage->load(reset($ids));
    return $node instanceof NodeInterface ? $node : NULL;
  }

  private function setLine(string $text, string $prefix, string $value): string {
    $lines = preg_split('/\R/', $text) ?: [];
    $replacement = $prefix . ' ' . trim($value);
    $found = FALSE;

    foreach ($lines as &$line) {
      if (str_starts_with(trim($line), $prefix)) {
        $line = $replacement;
        $found = TRUE;
        break;
      }
    }
    unset($line);

    if (!$found) {
      $lines[] = $replacement;
    }
    return implode("\n", $lines);
  }

}
