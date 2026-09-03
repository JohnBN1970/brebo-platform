<?php

declare(strict_types=1);

namespace Drupal\brebo_customer_service\Form;

use Drupal\brebo_customer_service\Knowledge\KnowledgeApproval;
use Drupal\brebo_customer_service\Knowledge\KnowledgeItemRepository;
use Drupal\brebo_customer_service\Knowledge\KnowledgeReviewManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class KnowledgeReviewForm extends FormBase {

  public function __construct(
    private readonly KnowledgeItemRepository $repository,
    private readonly KnowledgeReviewManager $reviewManager,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('brebo_customer_service.knowledge_item_repository'),
      $container->get('brebo_customer_service.knowledge_review_manager'),
    );
  }

  public function getFormId(): string {
    return 'brebo_customer_service_knowledge_review';
  }

  public function buildForm(array $form, FormStateInterface $form_state, ?string $slug = NULL): array {
    $item = $slug ? $this->repository->find($slug) : NULL;
    if ($item === NULL) {
      throw new NotFoundHttpException();
    }

    $form['slug'] = ['#type' => 'hidden', '#value' => $slug];
    $form['title'] = ['#markup' => '<h2>' . $item['title'] . '</h2><p>' . $item['summary'] . '</p>'];
    $form['status'] = [
      '#type' => 'select',
      '#title' => $this->t('Beoordelingsstatus'),
      '#default_value' => $item['status'] ?? KnowledgeApproval::STATUS_EDITORIAL,
      '#options' => [
        KnowledgeApproval::STATUS_EDITORIAL => $this->t('Redactioneel'),
        KnowledgeApproval::STATUS_REVIEW => $this->t('In beoordeling'),
        KnowledgeApproval::STATUS_APPROVED => $this->t('Goedgekeurd'),
        KnowledgeApproval::STATUS_REJECTED => $this->t('Niet vrijgegeven'),
      ],
      '#required' => TRUE,
    ];
    $form['sources'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Bronnen'),
      '#description' => $this->t('Één bron per regel. Gebruik bij voorkeur een concrete URL, norm, richtlijn, fabrikantdocumentatie of interne BREBO-bron.'),
      '#default_value' => implode("\n", $item['basis']['sources'] ?? []),
      '#rows' => 6,
    ];
    $form['validity_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Geldigheid gecontroleerd op'),
      '#default_value' => $item['basis']['validity_checked_at'] ?? '',
    ];
    $form['ai_approved'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Vrijgeven als bron voor BREBO AI'),
      '#default_value' => !empty($item['ai_approved']),
      '#description' => $this->t('Alleen toegestaan bij status Goedgekeurd, minimaal één bron en ingevulde geldigheidscontrole.'),
    ];
    $form['publication_note'] = [
      '#markup' => '<p><strong>Publicatie:</strong> dit formulier wijzigt de Drupal-publicatiestatus niet. Publieke publicatie en AI-vrijgave blijven bewust gescheiden.</p>',
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Beoordeling opslaan'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $status = (string) $form_state->getValue('status');
    $sources = $this->sources((string) $form_state->getValue('sources'));
    $validity = (string) $form_state->getValue('validity_date');
    $aiApproved = (bool) $form_state->getValue('ai_approved');

    if ($status === KnowledgeApproval::STATUS_APPROVED && ($sources === [] || $validity === '')) {
      $form_state->setErrorByName('sources', $this->t('Goedkeuring vereist minimaal één bron en een datum waarop de geldigheid is gecontroleerd.'));
    }
    if ($aiApproved && $status !== KnowledgeApproval::STATUS_APPROVED) {
      $form_state->setErrorByName('ai_approved', $this->t('AI-vrijgave is alleen mogelijk voor goedgekeurde kennis.'));
    }
    if ($aiApproved && ($sources === [] || $validity === '')) {
      $form_state->setErrorByName('ai_approved', $this->t('AI-vrijgave vereist minimaal één bron en een geldigheidscontrole.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $slug = (string) $form_state->getValue('slug');
    $status = (string) $form_state->getValue('status');
    $sources = $this->sources((string) $form_state->getValue('sources'));
    $validity = (string) $form_state->getValue('validity_date');
    $aiApproved = (bool) $form_state->getValue('ai_approved');

    $this->reviewManager->saveReview($slug, $status, $sources, $validity, $aiApproved);
    $this->messenger()->addStatus($this->t('Kennisbeoordeling opgeslagen.'));
    $form_state->setRedirect('brebo_customer_service.knowledge_review', ['slug' => $slug]);
  }

  private function sources(string $value): array {
    return array_values(array_filter(array_map('trim', preg_split('/\R/', $value) ?: [])));
  }

}
