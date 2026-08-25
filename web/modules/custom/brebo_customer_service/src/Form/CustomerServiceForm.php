<?php

declare(strict_types=1);

namespace Drupal\brebo_customer_service\Form;

use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class CustomerServiceForm extends FormBase {

  public function __construct(
    private readonly MailManagerInterface $mailManager,
    private readonly FloodInterface $flood,
    private readonly RequestStack $customerServiceRequestStack,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('plugin.manager.mail'),
      $container->get('flood'),
      $container->get('request_stack'),
    );
  }

  public function getFormId(): string {
    return 'brebo_customer_service_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attached']['library'][] = 'brebo_customer_service/service';
    $form['#attributes']['class'][] = 'brebo-customer-service';

    $form['intro'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['brebo-customer-service__intro']],
      'eyebrow' => ['#markup' => '<p class="brebo-customer-service__eyebrow">Lopende projecten</p>'],
      'title' => ['#markup' => '<h2>Waar kunnen we u bij helpen?</h2>'],
      'lead' => ['#markup' => '<p>Deze service is bedoeld voor vragen en meldingen over een project dat al bij BREBO in uitvoering of voorbereiding is.</p>'],
    ];

    $form['project_reference'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Projectnummer, adres of projectnaam'),
      '#required' => TRUE,
      '#maxlength' => 160,
    ];

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Waar gaat uw vraag over?'),
      '#required' => TRUE,
      '#options' => [
        'planning' => $this->t('Planning of afspraak'),
        'execution' => $this->t('Vraag tijdens de uitvoering'),
        'issue' => $this->t('Melding of restpunt'),
        'damage' => $this->t('Schade of incident'),
        'documents' => $this->t('Documenten of informatie'),
        'other' => $this->t('Andere projectvraag'),
      ],
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Naam'),
      '#required' => TRUE,
      '#maxlength' => 120,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('E-mailadres'),
      '#required' => TRUE,
      '#maxlength' => 160,
    ];

    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Telefoonnummer'),
      '#required' => FALSE,
      '#maxlength' => 40,
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Uw bericht'),
      '#required' => TRUE,
      '#rows' => 7,
      '#maxlength' => 5000,
    ];

    $form['company_website'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Website'),
      '#attributes' => [
        'autocomplete' => 'off',
        'tabindex' => '-1',
      ],
      '#wrapper_attributes' => ['class' => ['brebo-customer-service__trap']],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Verstuur projectvraag'),
      '#button_type' => 'primary',
    ];

    $form['note'] = [
      '#markup' => '<p class="brebo-customer-service__note">Voor een algemene nieuwe vraag gebruikt u Contact. Deze route is uitsluitend voor lopende projecten.</p>',
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if (trim((string) $form_state->getValue('company_website')) !== '') {
      $form_state->setErrorByName('message', $this->t('Uw bericht kon niet worden verzonden.'));
    }

    $identifier = $this->customerServiceRequestStack->getCurrentRequest()?->getClientIp() ?? 'unknown';
    if (!$this->flood->isAllowed('brebo_customer_service.submit', 5, 3600, $identifier)) {
      $form_state->setErrorByName('message', $this->t('Er zijn te veel berichten verzonden. Probeer het later opnieuw of bel BREBO.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $request = $this->customerServiceRequestStack->getCurrentRequest();
    $identifier = $request?->getClientIp() ?? 'unknown';
    $this->flood->register('brebo_customer_service.submit', 3600, $identifier);

    $reference = strtoupper(substr(hash('sha256', microtime(TRUE) . random_int(1000, 999999)), 0, 10));
    $tracking = 'BREBO-WEB-' . date('Ymd') . '-' . $reference;

    $categories = [
      'planning' => 'Planning of afspraak',
      'execution' => 'Vraag tijdens de uitvoering',
      'issue' => 'Melding of restpunt',
      'damage' => 'Schade of incident',
      'documents' => 'Documenten of informatie',
      'other' => 'Andere projectvraag',
    ];

    $category_key = (string) $form_state->getValue('category');
    $category = $categories[$category_key] ?? 'Projectvraag';
    $project = trim((string) $form_state->getValue('project_reference'));
    $name = trim((string) $form_state->getValue('name'));
    $email = trim((string) $form_state->getValue('email'));
    $phone = trim((string) $form_state->getValue('phone'));
    $text = trim((string) $form_state->getValue('message'));

    $subject = sprintf('[BREBO-WEB][Klantenservice][%s] %s – %s', $tracking, $category, $project);
    $body = implode("\n", [
      'Bron: BREBO website / klantenservice',
      'Kenmerk: ' . $tracking,
      'Project: ' . $project,
      'Categorie: ' . $category,
      'Naam: ' . $name,
      'E-mail: ' . $email,
      'Telefoon: ' . ($phone !== '' ? $phone : '-'),
      '',
      'Bericht:',
      $text,
    ]);

    $result = $this->mailManager->mail(
      'brebo_customer_service',
      'project_service_request',
      'info@brebobv.nl',
      'nl',
      [
        'subject' => $subject,
        'body' => $body,
        'reply_to' => $email,
      ],
      NULL,
      TRUE,
    );

    if (!empty($result['result'])) {
      $this->messenger()->addStatus($this->t('Dank u. Uw projectvraag is verzonden. Kenmerk: @tracking', ['@tracking' => $tracking]));
      $form_state->setRedirect('brebo_customer_service.form');
      return;
    }

    $this->messenger()->addError($this->t('Het bericht kon niet worden verzonden. Bel BREBO via 085-5003838.'));
  }

}
