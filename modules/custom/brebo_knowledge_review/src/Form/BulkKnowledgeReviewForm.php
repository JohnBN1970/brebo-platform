<?php

declare(strict_types=1);

namespace Drupal\brebo_knowledge_review\Form;

use Drupal\brebo_knowledge_review\Review\ReviewStatusStorage;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Bulk review cockpit for canonical BREBO KnowledgeItems.
 */
final class BulkKnowledgeReviewForm extends FormBase {

  private const REQUIRED_FIELDS = [
    'field_knowledge_observation',
    'field_knowledge_meaning',
    'field_knowledge_risk',
    'field_knowledge_next_step',
    'field_knowledge_basis',
  ];

  /**
   * Constructs the bulk review form.
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
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'brebo_knowledge_bulk_review_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $nodes = $this->loadKnowledgeItems();
    $snapshot = $form_state->get('revision_snapshot');
    if (!is_array($snapshot)) {
      $snapshot = [];
      foreach ($nodes as $node) {
        $snapshot[(int) $node->id()] = (int) $node->getRevisionId();
      }
      $form_state->set('revision_snapshot', $snapshot);
    }

    $topics = [];
    foreach ($nodes as $node) {
      $basis = (string) $node->get('field_knowledge_basis')->value;
      $topic = $this->lineValue($basis, 'Onderwerp:');
      if ($topic !== NULL) {
        $topics[$topic] = $topic;
      }
    }
    ksort($topics);

    $form['intro'] = [
      '#markup' => '<p><strong>Bulk-reviewcockpit.</strong> Werk per selectie of per kennisgebied. De automatische voorcontrole blokkeert publieke vrijgave als verplichte inhoud, bron of geldigheidscontrole ontbreekt. Goedkeuring is gebonden aan exact de revisie die op dit scherm is beoordeeld. AI-vrijgave blijft altijd uit.</p>',
    ];
    $form['bulk'] = ['#type' => 'details', '#title' => $this->t('Besluit voor KnowledgeItems'), '#open' => TRUE];
    $form['bulk']['selection_scope'] = ['#type' => 'select', '#title' => $this->t('Bereik'), '#options' => ['manual' => $this->t('Alleen handmatig aangevinkte items'), 'topic' => $this->t('Alle zichtbare items van één kennisgebied')], '#default_value' => 'manual', '#required' => TRUE];
    $form['bulk']['topic'] = ['#type' => 'select', '#title' => $this->t('Kennisgebied'), '#options' => ['' => $this->t('- Kies kennisgebied -')] + $topics, '#description' => $this->t('Wordt alleen gebruikt als het bereik op één kennisgebied staat.')];
    $form['bulk']['action'] = ['#type' => 'select', '#title' => $this->t('Bulkactie'), '#options' => ['in_review' => $this->t('Markeer als in beoordeling'), 'approve_publish' => $this->t('Goedkeuren en publiek vrijgeven'), 'unpublish' => $this->t('Publieke vrijgave intrekken')], '#required' => TRUE];
    $form['bulk']['sources'] = ['#type' => 'textfield', '#title' => $this->t('Bronnen voor deze batch'), '#description' => $this->t('Scheid meerdere bronnen met een puntkomma. Leeg laten behoudt reeds vastgelegde bronnen. Ingevulde bronnen worden ook bij In beoordeling of Intrekken opgeslagen.'), '#maxlength' => 512];
    $form['bulk']['validity_date'] = ['#type' => 'date', '#title' => $this->t('Geldigheid gecontroleerd op'), '#description' => $this->t('Leeg laten behoudt de bestaande geldigheidsdatum. Een ingevulde datum wordt bij iedere bulkactie opgeslagen.')];
    $form['bulk']['review_note'] = ['#type' => 'textfield', '#title' => $this->t('Reviewtoelichting'), '#maxlength' => 255, '#description' => $this->t('Wordt vastgelegd bij het bulkbesluit en de nieuwe revisie.')];
    $form['bulk']['confirmed'] = ['#type' => 'checkbox', '#title' => $this->t('Ik bevestig dat ik de inhoud van alle items binnen dit bereik inhoudelijk heb beoordeeld.')];

    $form['items'] = [
      '#type' => 'table',
      '#header' => [$this->t('Selecteer'), $this->t('Voorcontrole'), $this->t('KnowledgeItem'), $this->t('Onderwerp'), $this->t('Reviewstatus'), $this->t('Bron'), $this->t('Geldigheid'), $this->t('Publiek'), $this->t('Detail')],
      '#tree' => TRUE,
      '#empty' => $this->t('Geen KnowledgeItems gevonden.'),
      '#sticky' => TRUE,
    ];
    foreach ($nodes as $node) {
      $nid = (int) $node->id();
      $revisionId = $snapshot[$nid] ?? 0;
      $basis = (string) $node->get('field_knowledge_basis')->value;
      $topic = $this->lineValue($basis, 'Onderwerp:') ?? '';
      $check = $this->precheck($node);
      $effective = $this->statusStorage->getEffectiveStatus($nid, (int) $node->getRevisionId());
      $form['items'][$nid]['select'] = ['#type' => 'checkbox'];
      $form['items'][$nid]['revision_id'] = ['#type' => 'hidden', '#value' => $revisionId];
      $form['items'][$nid]['topic_key'] = ['#type' => 'hidden', '#value' => $topic];
      $form['items'][$nid]['signal'] = ['#markup' => $check['label']];
      $form['items'][$nid]['title'] = ['#plain_text' => (string) $node->label()];
      $form['items'][$nid]['topic'] = ['#plain_text' => $topic !== '' ? $topic : '—'];
      $form['items'][$nid]['review'] = ['#plain_text' => $this->statusLabel($effective)];
      $form['items'][$nid]['source'] = ['#plain_text' => $this->meaningfulValue($this->lineValue($basis, 'Bronnen:')) ?? '—'];
      $form['items'][$nid]['validity'] = ['#plain_text' => $this->meaningfulValue($this->lineValue($basis, 'Geldigheid:')) ?? '—'];
      $form['items'][$nid]['public'] = ['#plain_text' => $node->isPublished() ? 'Ja' : 'Nee'];
      $form['items'][$nid]['edit'] = ['#markup' => Link::fromTextAndUrl('Open', Url::fromRoute('brebo_knowledge_review.review', ['node' => $nid]))->toString()];
    }
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['apply'] = ['#type' => 'submit', '#value' => $this->t('Bulkbesluit toepassen'), '#button_type' => 'primary'];
    $form['#cache']['max-age'] = 0;
    return $form;
  }

  /** {@inheritdoc} */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if ((string) $form_state->getValue('selection_scope') === 'topic' && trim((string) $form_state->getValue('topic')) === '') {
      $form_state->setErrorByName('topic', $this->t('Kies een kennisgebied voor deze batch.'));
      return;
    }
    if ($this->selectedIds($form_state) === []) {
      $form_state->setErrorByName('items', $this->t('Dit bereik bevat geen KnowledgeItems.'));
      return;
    }
    if ((string) $form_state->getValue('action') === 'approve_publish' && !$form_state->getValue('confirmed')) {
      $form_state->setErrorByName('confirmed', $this->t('Bevestig eerst dat alle KnowledgeItems binnen dit bereik inhoudelijk zijn beoordeeld.'));
    }
  }

  /** {@inheritdoc} */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $selected = $this->selectedIds($form_state);
    $snapshot = $form_state->get('revision_snapshot');
    $snapshot = is_array($snapshot) ? $snapshot : [];
    $action = (string) $form_state->getValue('action');
    $bulkSources = trim((string) $form_state->getValue('sources'));
    $bulkValidity = trim((string) $form_state->getValue('validity_date'));
    $note = trim((string) $form_state->getValue('review_note'));
    $storage = $this->entityTypeManager->getStorage('node');
    $nodes = $storage->loadMultiple($selected);
    $updated = 0;
    $blocked = [];
    foreach ($nodes as $node) {
      if (!$node instanceof NodeInterface || $node->bundle() !== 'brebo_knowledge_item') {
        continue;
      }
      $nid = (int) $node->id();
      $expectedRevisionId = isset($snapshot[$nid]) ? (int) $snapshot[$nid] : 0;
      if ($action === 'approve_publish' && ($expectedRevisionId === 0 || $expectedRevisionId !== (int) $node->getRevisionId())) {
        $blocked[] = $node->label() . ': revisie is gewijzigd sinds deze cockpit is geopend; laad de pagina opnieuw en beoordeel de actuele revisie.';
        continue;
      }
      $basis = (string) $node->get('field_knowledge_basis')->value;
      $existingSources = $this->meaningfulValue($this->lineValue($basis, 'Bronnen:')) ?? '';
      $existingValidity = $this->meaningfulValue($this->lineValue($basis, 'Geldigheid:')) ?? '';
      $sources = $bulkSources !== '' ? ($this->meaningfulValue($bulkSources) ?? '') : $existingSources;
      $validity = $bulkValidity !== '' ? ($this->meaningfulValue($bulkValidity) ?? '') : $existingValidity;
      if ($bulkSources !== '') {
        $basis = $this->setLine($basis, 'Bronnen:', $sources);
      }
      if ($bulkValidity !== '') {
        $basis = $this->setLine($basis, 'Geldigheid:', $validity);
      }
      if ($action === 'approve_publish') {
        $check = $this->precheck($node, $sources, $validity);
        if (!$check['publishable']) {
          $blocked[] = $node->label() . ': ' . implode(', ', $check['reasons']);
          continue;
        }
        $basis = $this->setLine($basis, 'Status:', 'approved');
        $basis = $this->setLine($basis, 'Bronnen:', $sources);
        $basis = $this->setLine($basis, 'Geldigheid:', $validity);
        $basis = $this->setLine($basis, 'Deskundige controle:', $this->currentUser()->getDisplayName() . ' | ' . gmdate('Y-m-d\TH:i:s\Z'));
        $basis = $this->setLine($basis, 'Publieke vrijgave:', 'ja');
        $basis = $this->setLine($basis, 'AI-vrijgave:', 'nee');
        $node->setPublished(TRUE);
        $reviewStatus = 'approved';
      }
      elseif ($action === 'unpublish') {
        $basis = $this->setLine($basis, 'Publieke vrijgave:', 'nee');
        $basis = $this->setLine($basis, 'AI-vrijgave:', 'nee');
        $node->setUnpublished();
        $reviewStatus = $this->statusStorage->getEffectiveStatus($nid, (int) $node->getRevisionId());
      }
      else {
        $basis = $this->setLine($basis, 'Status:', 'review');
        $basis = $this->setLine($basis, 'Publieke vrijgave:', 'nee');
        $basis = $this->setLine($basis, 'AI-vrijgave:', 'nee');
        $node->setUnpublished();
        $reviewStatus = 'in_review';
      }
      $node->set('field_knowledge_basis', $basis);
      $node->setNewRevision(TRUE);
      $node->setRevisionUserId((int) $this->currentUser()->id());
      $node->setRevisionLogMessage($note !== '' ? $note : 'Bulkbesluit BREBO Knowledge Review.');
      $node->save();
      $this->statusStorage->save($nid, (int) $node->getRevisionId(), $reviewStatus, (int) $this->currentUser()->id(), time(), $note);
      $updated++;
    }
    Cache::invalidateTags(['brebo_public_knowledge']);
    if ($updated > 0) {
      $this->messenger()->addStatus($this->formatPlural($updated, '1 KnowledgeItem bijgewerkt.', '@count KnowledgeItems bijgewerkt.'));
    }
    foreach ($blocked as $message) {
      $this->messenger()->addWarning($message);
    }
    $form_state->setRedirect('brebo_knowledge_review.bulk');
  }

  /**
   * Loads all canonical KnowledgeItems for the cockpit.
   *
   * @return \Drupal\node\NodeInterface[]
   *   The KnowledgeItems sorted by title.
   */
  private function loadKnowledgeItems(): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'brebo_knowledge_item')->sort('title')->execute();
    return $ids === [] ? [] : $storage->loadMultiple($ids);
  }

  /**
   * Returns only node IDs that were actually rendered in this form instance.
   *
   * @return int[]
   *   The selected node IDs.
   */
  private function selectedIds(FormStateInterface $form_state): array {
    $rows = $form_state->getValue('items') ?? [];
    if ((string) $form_state->getValue('selection_scope') === 'topic') {
      $topic = trim((string) $form_state->getValue('topic'));
      if ($topic === '') {
        return [];
      }
      $ids = [];
      foreach ($rows as $nid => $row) {
        if (($row['topic_key'] ?? '') === $topic && !empty($row['revision_id'])) {
          $ids[] = (int) $nid;
        }
      }
      return $ids;
    }
    $ids = [];
    foreach ($rows as $nid => $row) {
      if (!empty($row['select']) && !empty($row['revision_id'])) {
        $ids[] = (int) $nid;
      }
    }
    return $ids;
  }

  /**
   * Performs the publication precheck for a KnowledgeItem.
   *
   * @return array{label:string,publishable:bool,reasons:string[]}
   *   The precheck label, publication flag and blocking reasons.
   */
  private function precheck(NodeInterface $node, ?string $sourceOverride = NULL, ?string $validityOverride = NULL): array {
    $reasons = [];
    foreach (self::REQUIRED_FIELDS as $fieldName) {
      if (!$node->hasField($fieldName) || trim((string) ($node->get($fieldName)->value ?? '')) === '') {
        $reasons[] = 'verplicht veld ontbreekt';
        break;
      }
    }
    $basis = (string) ($node->get('field_knowledge_basis')->value ?? '');
    if ($this->lineValue($basis, 'BREBO-WEB-SEED:') === NULL) {
      $reasons[] = 'seed-identiteit ontbreekt';
    }
    if ($this->lineValue($basis, 'Onderwerp:') === NULL) {
      $reasons[] = 'onderwerp ontbreekt';
    }
    $source = $sourceOverride !== NULL ? ($this->meaningfulValue($sourceOverride) ?? '') : ($this->meaningfulValue($this->lineValue($basis, 'Bronnen:')) ?? '');
    $validity = $validityOverride !== NULL ? ($this->meaningfulValue($validityOverride) ?? '') : ($this->meaningfulValue($this->lineValue($basis, 'Geldigheid:')) ?? '');
    if ($source === '') {
      $reasons[] = 'bron ontbreekt';
    }
    if ($validity === '') {
      $reasons[] = 'geldigheidscontrole ontbreekt';
    }
    if ($reasons === []) {
      return ['label' => '🟢 Publiceerbaar', 'publishable' => TRUE, 'reasons' => []];
    }
    if (in_array('verplicht veld ontbreekt', $reasons, TRUE) || in_array('seed-identiteit ontbreekt', $reasons, TRUE) || in_array('onderwerp ontbreekt', $reasons, TRUE)) {
      return ['label' => '🔴 Geblokkeerd', 'publishable' => FALSE, 'reasons' => $reasons];
    }
    return ['label' => '🟠 Controle nodig', 'publishable' => FALSE, 'reasons' => $reasons];
  }

  /** Returns the human-readable label for a review status. */
  private function statusLabel(string $status): string {
    return match ($status) {'approved' => 'Goedgekeurd', 'in_review' => 'In beoordeling', 'changes_required' => 'Herziening nodig', default => 'Te beoordelen'};
  }

  /** Reads a prefixed metadata line from the basis text. */
  private function lineValue(string $text, string $prefix): ?string {
    foreach (preg_split('/\R/', $text) ?: [] as $line) {
      $line = trim($line);
      if (str_starts_with($line, $prefix)) {
        $value = trim(substr($line, strlen($prefix)));
        return $value !== '' ? $value : NULL;
      }
    }
    return NULL;
  }

  /** Normalizes placeholder metadata to an empty value. */
  private function meaningfulValue(?string $value): ?string {
    if ($value === NULL) {
      return NULL;
    }
    $normalized = strtolower(trim($value));
    foreach (['nog niet', 'niet vastgesteld', 'niet gecontroleerd', 'niet uitgevoerd', 'geen'] as $empty) {
      if (str_contains($normalized, $empty)) {
        return NULL;
      }
    }
    return trim($value) !== '' ? trim($value) : NULL;
  }

  /** Replaces or appends a prefixed metadata line. */
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
