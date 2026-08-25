<?php

declare(strict_types=1);

namespace Drupal\brebo_contact\Form;

use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class ContactMessageForm extends FormBase {

  public function __construct(
    private readonly MailManagerInterface $mailManager,
    private readonly FloodInterface $flood,
    private readonly RequestStack $contactRequestStack,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('plugin.manager.mail'),
      $container->get('flood'),
      $container->get('request_stack'),
    );
  }

  public function getFormId(): string {
    return 'brebo_contact_message_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attached']['library'][] = 'brebo_contact/contact';
    $form['#attributes']['class'][] = 'brebo-contact-message';

    $form['intro'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['brebo-contact-message__intro']],
      'eyebrow' => ['#markup' => '<p class="brebo-contact__eyebrow">Contact</p>'],
      'title' => ['#markup' => '<h2>Stuur ons een bericht.</h2>'],
      'lead' => ['#markup' => '<p>Vertel kort wat er speelt of wat u wilt bereiken. U hoeft de oplossing nog niet te kennen.</p>'],
    ];

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Waar gaat uw vraag over?'),
      '#required' => TRUE,
      '#options' => [
        'issue' => $this->t('Er speelt nu iets'),
        'planning' => $this->t('Ik wil vooruitkijken'),
        'plans' => $this->t('Er zijn plannen'),
        'consult' => $this->t('Ik wil eerst overleggen'),
        'other' => $this->t('Anders'),
      ],
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Naam'),
      '#required' => TRUE,
      '#maxlength' => 120,
    ];

    $form['organisation'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Organisatie'),
      '#required' => FALSE,
      '#maxlength' => 160,
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

    $form['location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Locatie of adres'),
      '#description' => $this->t('Optioneel, maar vaak handig om uw vraag sneller te plaatsen.'),
      '#required' => FALSE,
      '#maxlength' => 180,
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Uw bericht'),
      '#required' => TRUE,
      '#rows' => 8,
      '#maxlength' => 5000,
    ];

    $form['company_website'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Website'),
      '#attributes' => [
        'autocomplete' => 'off',
        'tabindex' => '-1',
      ],
      '#wrapper_attributes' => ['class' => ['brebo-contact-message__trap']],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Verstuur bericht'),
      '#button_type' => 'primary',
    ];

    $form['note'] = [
      '#markup' => '<p class="brebo-contact-message__note">Heeft u al een lopend project bij BREBO? Gebruik dan de <a href="/klantenservice">Klantenservice</a>.</p>',
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if (trim((string) $form_state->getValue('company_website')) !== '') {
      $form_state->setErrorByName('message', $this->t('Uw bericht kon niet worden verzonden.'));
    }

    $identifier = $this->contactRequestStack->getCurrentRequest()?->getClientIp() ?? 'unknown';
    if (!$this->flood->isAllowed('brebo_contact.submit', 5, 3600, $identifier)) {
      $form_state->setErrorByName('message', $this->t('Er zijn te veel berichten verzonden. Probeer het later opnieuw of bel BREBO.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $request = $this->contactRequestStack->getCurrentRequest();
    $identifier = $request?->getClientIp() ?? 'unknown';
    $this->flood->register('brebo_contact.submit', 3600, $identifier);

    $reference = strtoupper(substr(hash('sha256', microtime(TRUE) . random_int(1000, 999999)), 0, 10));
    $tracking = 'BREBO-WEB-' . date('Ymd') . '-' . $reference;

    $categories = [
      'issue' => 'Er speelt nu iets',
      'planning' => 'Vooruitkijken',
      'plans' => 'Er zijn plannen',
      'consult' => 'Eerst overleggen',
      'other' => 'Andere vraag',
    ];

    $categoryKey = (string) $form_state->getValue('category');
    $category = $categories[$categoryKey] ?? 'Contactvraag';
    $name = trim((string) $form_state->getValue('name'));
    $organisation = trim((string) $form_state->getValue('organisation'));
    $email = trim((string) $form_state->getValue('email'));
    $phone = trim((string) $form_state->getValue('phone'));
    $location = trim((string) $form_state->getValue('location'));
    $text = trim((string) $form_state->getValue('message'));
    $sourcePath = $request?->getPathInfo() ?? '/contact/bericht';
    $referer = $request?->headers->get('referer') ?? '-';

    $subject = sprintf('[BREBO-WEB][Contact][%s] %s – %s', $tracking, $category, $name);
    $body = implode("\n", [
      'Bron: BREBO website / contact',
      'Kenmerk: ' . $tracking,
      'Route: ' . $sourcePath,
      'Verwijzer: ' . $referer,
      'Categorie: ' . $category,
      'Naam: ' . $name,
      'Organisatie: ' . ($organisation !== '' ? $organisation : '-'),
      'E-mail: ' . $email,
      'Telefoon: ' . ($phone !== '' ? $phone : '-'),
      'Locatie/adres: ' . ($location !== '' ? $location : '-'),
      '',
      'Bericht:',
      $text,
    ]);

    $result = $this->mailManager->mail(
      'brebo_contact',
      'website_contact_request',
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
      $this->messenger()->addStatus($this->t('Dank u. Uw bericht is verzonden. Kenmerk: @tracking', ['@tracking' => $tracking]));
      $form_state->setRedirect('brebo_contact.message');
      return;
    }

    $this->messenger()->addError($this->t('Het bericht kon niet worden verzonden. Bel BREBO via 085-5003838.'));
  }

}
