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

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ReviewStatusStorage $statusStorage,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('brebo_knowledge_review.status_storage'),
    );
  }

  public function getFormId(): string {
    return 'brebo_knowledge_bulk_review_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $nodes = $this->loadKnowledgeItems();

    $form['intro'] = [
      '#markup' => '<p><strong>Bulk-reviewcockpit.</strong> Selecteer alleen items die u inhoudelijk hebt beoordeeld. De automatische voorcontrole voorkomt vrijgave als verplichte inhoud, bron of geldigheidscontrole ontbreekt. AI-vrijgave blijft altijd uit.</p>',
    ];

    $form['bulk'] = [
      '#type' => 'details',
      '#title' => $this->t('Besluit voor geselecteerde items'),
      '#open' => TRUE,
    ];
    $form['bulk']['action'] = [
      '#type' => 'select',
      '#title' => $this->t('Bulkactie'),
      '#options' => [
        'in_review' => $this->t('Markeer als in beoordeling'),
        'approve_publish' => $this->t('Goedkeuren en publiek vrijgeven'),
        'unpublish' => $this->t('Publieke vrijgave intrekken'),
      ],
      '#required' => TRUE,
    ];
    $form['bulk']['sources'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bronnen voor deze selectie'),
      '#description' => $this->t('Scheid meerdere bronnen met een puntkomma. Leeg laten behoudt reeds vastgelegde bronnen. Bij publieke vrijgave moet per item minimaal één betekenisvolle bron aanwezig zijn.'),
      '#maxlength' => 512,
    ];
    $form['bulk']['validity_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Geldigheid gecontroleerd op'),
      '#description' => $this->t('Leeg laten behoudt de bestaande geldigheidsdatum. Bij publieke vrijgave is een datum verplicht.'),
    ];
    $form['bulk']['review_note'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Reviewtoelichting'),
      '#maxlength' => 255,
      '#description' => $this->t('Wordt vastgelegd bij het bulkbesluit.'),
    ];
    $form['bulk']['confirmed'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Ik bevestig dat ik de geselecteerde items inhoudelijk heb beoordeeld.'),
    ];

    $form['items'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Selecteer'),
        $this->t('Voorcontrole'),
        $this->t('KnowledgeItem'),
        $this->t('Onderwerp'),
        $this->t('Reviewstatus'),
        $this->t('Bron'),
        $this->t('Geldigheid'),
        $this->t('Publiek'),
        $this->t('Detail'),
      ],
      '#tree' => TRUE,
      '#empty' => $this->t('Geen KnowledgeItems gevonden.'),
      '#sticky' => TRUE,
    ];

    foreach ($nodes as $node) {
      $nid = (int) $node->id();
      $basis = (string) $node->get('field_knowledge_basis')->value;
      $check = $this->precheck($node);
      $effective = $this->statusStorage->getEffectiveStatus($nid, (int) $node->getRevisionId());

      $form['items'][$nid]['select'] = ['#type' => 'checkbox'];
      $form['items'][$nid]['signal'] = ['#markup' => $check['label']];
      $form['items'][$nid]['title'] = ['#plain_text' => (string) $node->label()];
      $form['items'][$nid]['topic'] = ['#plain_text' => $this->lineValue($basis, 'Onderwerp:') ?? '—'];
      $form['items'][$nid]['review'] = ['#plain_text' => $this->statusLabel($effective)];
      $form['items'][$nid]['source'] = ['#plain_text' => $this->meaningfulValue($this->lineValue($basis, 'Bronnen:')) ?? '—'];
      $form['items'][$nid]['validity'] = ['#plain_text' => $this->meaningfulValue($this->lineValue($basis, 'Geldigheid:')) ?? '—'];
      $form['items'][$nid]['public'] = ['#plain_text' => $node->isPublished() ? 'Ja' : 'Nee'];
      $form['items'][$nid]['edit'] = [
        '#markup' => Link::fromTextAndUrl('Open', Url::fromRoute('brebo_knowledge_review.review', ['node' => $nid]))->toString(),
      ];
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['apply'] = [
      '#type' => 'submit',
      '#value' => $this->t('Bulkbesluit toepassen'),
      '#button_type' => 'primary',
    ];

    $form['#cache']['max-age'] = 0;
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if ($this->selectedIds($form_state) === []) {
      $form_state->setErrorByName('items', $this->t('Selecteer minimaal één KnowledgeItem.'));
      return;
    }

    if ((string) $form_state->getValue('action') === 'approve_publish' && !$form_state->getValue('confirmed')) {
      $form_state->setErrorByName('confirmed', $this->t('Bevestig eerst dat de geselecteerde inhoud inhoudelijk is beoordeeld.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $selected = $this->selectedIds($form_state);
    $action = (string) $form_state->getValue('action');
    $bulkSources = trim((string) $form_state->getValue('sources'));
    $bulkValidity = trim((string) $form_state->getValue('validity_date'));
    $note = trim((string) $form_state->getValue('review_note'));
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($selected);
    $updated = 0;
    $blocked = [];

    foreach ($nodes as $node) {
      if (!$node instanceof NodeInterface || $node->bundle() !== 'brebo_knowledge_item') {
        continue;
      }

      $basis = (string) $node->get('field_knowledge_basis')->value;
      $sources = $bulkSources !== '' ? $bulkSources : ($this->meaningfulValue($this->lineValue($basis, 'Bronnen:')) ?? '');
      $validity = $bulkValidity !== '' ? $bulkValidity : ($this->meaningfulValue($this->lineValue($basis, 'Geldigheid:')) ?? '');

      if ($action === 'approve_publish') {
        $check = $this->precheck($node, $sources, $validity);
        if (!$check['publishable']) {
          $blocked[] = $node->label() . ': ' . implode(', ', $check['reasons']);
          continue;
        }
        $basis = $this->setLine($basis, 'Status:', 'approved');
        $basis = $this->setLine($basis, 'Bronnen:', $sources);
        $basis = $this->setLine($basis, 'Geldigheid:', $validity);
        $basis = $this->setLine($basis, 'Deskundige controle:', $this->currentUser()->getDisplayName() . ' | ' . gmdate('Y-m-d\\TH:i:s\\Z'));
        $basis = $this->setLine($basis, 'Publieke vrijgave:', 'ja');
        $basis = $this->setLine($basis, 'AI-vrijgave:', 'nee');
        $node->setPublished(TRUE);
        $reviewStatus = 'approved';
      }
      elseif ($action === 'unpublish') {
        $basis = $this->setLine($basis, 'Publieke vrijgave:', 'nee');
        $basis = $this->setLine($basis, 'AI-vrijgave:', 'nee');
        $node->setUnpublished();
        $reviewStatus = $this->statusStorage->getEffectiveStatus((int) $node->id(), (int) $node->getRevisionId());
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

      $this->statusStorage->save(
        (int) $node->id(),
        (int) $node->getRevisionId(),
        $reviewStatus,
        (int) $this->currentUser()->id(),
        time(),
        $note,
      );
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
   * @return \Drupal\node\NodeInterface[]
   */
  private function loadKnowledgeItems(): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'brebo_knowledge_item')
      ->sort('title')
      ->execute();
    return $ids === [] ? [] : $storage->loadMultiple($ids);
  }

  /**
   * @return int[]
   */
  private function selectedIds(FormStateInterface $form_state): array {
    $rows = $form_state->getValue('items') ?? [];
    $ids = [];
    foreach ($rows as $nid => $row) {
      if (!empty($row['select'])) {
        $ids[] = (int) $nid;
      }
    }
    return $ids;
  }

  /**
   * @return array{label:string,publishable:bool,reasons:string[]}
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

    $source = $sourceOverride ?? ($this->meaningfulValue($this->lineValue($basis, 'Bronnen:')) ?? '');
    $validity = $validityOverride ?? ($this->meaningfulValue($this->lineValue($basis, 'Geldigheid:')) ?? '');
    if (trim($source) === '') {
      $reasons[] = 'bron ontbreekt';
    }
    if (trim($validity) === '') {
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

  private function statusLabel(string $status): string {
    return match ($status) {
      'approved' => 'Goedgekeurd',
      'in_review' => 'In beoordeling',
      'changes_required' => 'Herziening nodig',
      default => 'Te beoordelen',
    };
  }

  private function lineValue(string $text, string $prefix): ?string {
    foreach (preg_split('/\\R/', $text) ?: [] as $line) {
      $line = trim($line);
      if (str_starts_with($line, $prefix)) {
        $value = trim(substr($line, strlen($prefix)));
        return $value !== '' ? $value : NULL;
      }
    }
    return NULL;
  }

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

  private function setLine(string $text, string $prefix, string $value): string {
    $lines = preg_split('/\\R/', $text) ?: [];
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
    return implode("\\n", $lines);
  }

}
