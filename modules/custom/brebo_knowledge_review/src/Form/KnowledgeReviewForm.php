<?php

declare(strict_types=1);

namespace Drupal\brebo_knowledge_review\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a bounded editorial review form for BREBO KnowledgeItems.
 */
final class KnowledgeReviewForm extends FormBase {

  /**
   * Canonical KnowledgeItem fields owned by brebo_knowledge.
   *
   * @var array<string, array{title: string, description: string, required: bool, rows: int}>
   */
  private const FIELDS = [
    'field_knowledge_observation' => [
      'title' => 'Waarneming en afbakening',
      'description' => 'Beschrijf wat zichtbaar, merkbaar of gemeten is. Houd waarneming en verklaring uit elkaar.',
      'required' => TRUE,
      'rows' => 8,
    ],
    'field_knowledge_meaning' => [
      'title' => 'Mogelijke betekenis en oorzaken',
      'description' => 'Beschrijf mogelijke betekenissen en oorzaken, inclusief onzekerheden. Presenteer een mogelijkheid niet als vastgestelde diagnose.',
      'required' => TRUE,
      'rows' => 10,
    ],
    'field_knowledge_risk' => [
      'title' => 'Risico en urgentie',
      'description' => 'Beschrijf technische, functionele en waar relevant esthetische gevolgen, veiligheidsrisico\'s, vervolgschade en urgentie.',
      'required' => TRUE,
      'rows' => 8,
    ],
    'field_knowledge_next_step' => [
      'title' => 'Te verzamelen informatie en volgende stap',
      'description' => 'Beschrijf welke informatie of inspectie eerst nodig is en wat de eerstvolgende verantwoorde stap is.',
      'required' => TRUE,
      'rows' => 10,
    ],
    'field_knowledge_basis' => [
      'title' => 'Bron, geldigheid en deskundige controle',
      'description' => 'Leg vast waarop de bijdrage is gebaseerd, voor welke omstandigheden deze geldt en wanneer deskundige beoordeling nodig is.',
      'required' => TRUE,
      'rows' => 8,
    ],
    'field_knowledge_regie' => [
      'title' => 'Regie',
      'description' => 'Beschrijf indien relevant onderzoeksvolgorde, betrokken partijen, keuzes, verantwoordelijkheden en fasering.',
      'required' => FALSE,
      'rows' => 8,
    ],
    'field_knowledge_realization' => [
      'title' => 'Mogelijke oplossingsrichtingen',
      'description' => 'Beschrijf mogelijke oplossingsrichtingen en voorwaarden. Schrijf zonder voldoende inzicht geen definitieve maatregel voor.',
      'required' => FALSE,
      'rows' => 8,
    ],
  ];

  /**
   * The KnowledgeItem being reviewed.
   */
  private ?NodeInterface $knowledgeItem = NULL;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'brebo_knowledge_review_form';
  }

  /**
   * Builds the review form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?NodeInterface $node = NULL): array {
    if (!$node instanceof NodeInterface || $node->bundle() !== 'brebo_knowledge_item') {
      throw new AccessDeniedHttpException('Alleen BREBO KnowledgeItems kunnen via deze route worden beoordeeld.');
    }

    $this->knowledgeItem = $node;

    $form['context'] = [
      '#type' => 'details',
      '#title' => $this->t('Redactionele context'),
      '#open' => TRUE,
    ];
    $form['context']['title'] = [
      '#type' => 'item',
      '#title' => $this->t('KnowledgeItem'),
      '#markup' => $node->label(),
    ];
    $form['context']['guidance'] = [
      '#type' => 'item',
      '#title' => $this->t('Beoordelingsregel'),
      '#markup' => $this->t('Beoordeel waar relevant technische prestatie en comfort, functionele bruikbaarheid, esthetische kwaliteit, risico/urgentie en de passende volgende stap afzonderlijk. Goedkeuring en AI-vrijgave maken geen deel uit van deze eerste formulierstap.'),
    ];

    foreach (self::FIELDS as $fieldName => $definition) {
      if (!$node->hasField($fieldName)) {
        throw new AccessDeniedHttpException(sprintf('Canoniek veld %s ontbreekt.', $fieldName));
      }

      $form[$fieldName] = [
        '#type' => 'textarea',
        '#title' => $this->t($definition['title']),
        '#description' => $this->t($definition['description']),
        '#default_value' => (string) ($node->get($fieldName)->value ?? ''),
        '#required' => $definition['required'],
        '#rows' => $definition['rows'],
      ];
    }

    $form['revision_log'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Redactionele revisietoelichting'),
      '#description' => $this->t('Leg kort vast wat inhoudelijk is aangepast en waarom.'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Correcties opslaan als nieuwe revisie'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if (!$this->knowledgeItem instanceof NodeInterface || $this->knowledgeItem->bundle() !== 'brebo_knowledge_item') {
      throw new AccessDeniedHttpException('KnowledgeItem ontbreekt of is ongeldig.');
    }

    foreach (array_keys(self::FIELDS) as $fieldName) {
      $item = $this->knowledgeItem->get($fieldName);
      $format = $item->format ?? NULL;
      $value = (string) $form_state->getValue($fieldName);

      $this->knowledgeItem->set($fieldName, [
        'value' => $value,
        'format' => $format,
      ]);
    }

    $this->knowledgeItem->setNewRevision(TRUE);
    $this->knowledgeItem->setRevisionLogMessage((string) $form_state->getValue('revision_log'));
    $this->knowledgeItem->setRevisionUserId((int) $this->currentUser()->id());
    $this->knowledgeItem->save();

    $this->messenger()->addStatus($this->t('De KnowledgeItem-correcties zijn als nieuwe revisie opgeslagen.'));
    $form_state->setRedirect('brebo_knowledge_review.review', ['node' => $this->knowledgeItem->id()]);
  }

}
