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
      'lead' => ['#markup' => '<p>Vertel kort wat er speelt of wat u wilt bereiken. Meer hoeft voor een eerste contact niet.</p>'],
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Naam'),
      '#required' => TRUE,
      '#maxlength' => 120,
      '#autocomplete_route_name' => FALSE,
    ];

    $form['contact'] = [
      '#type' => 'textfield',
      '#title' => $this->t('E-mail of telefoon'),
      '#description' => $this->t('Vul in hoe we u het makkelijkst kunnen bereiken.'),
      '#required' => TRUE,
      '#maxlength' => 160,
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Uw bericht'),
      '#required' => TRUE,
      '#rows' => 7,
      '#maxlength' => 5000,
    ];

    $form['company_website'] = [
      '#type' => 'hidden',
      '#value' => '',
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Stuur mijn bericht'),
      '#button_type' => 'primary',
    ];

    $form['aftercare'] = [
      '#markup' => '<p class="brebo-contact-message__note">Na uw eerste bericht kunnen we gericht aangeven welke aanvullende informatie eventueel nuttig is. Heeft u al een lopend project bij BREBO? Gebruik dan de <a href="/klantenservice">Klantenservice</a>.</p>',
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if (trim((string) $form_state->getValue('company_website')) !== '') {
      $form_state->setErrorByName('message', $this->t('Uw bericht kon niet worden verzonden.'));
    }

    $contact = trim((string) $form_state->getValue('contact'));
    if ($contact === '') {
      $form_state->setErrorByName('contact', $this->t('Vul uw e-mailadres of telefoonnummer in.'));
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
    $name = trim((string) $form_state->getValue('name'));
    $contact = trim((string) $form_state->getValue('contact'));
    $text = trim((string) $form_state->getValue('message'));
    $sourcePath = $request?->getPathInfo() ?? '/contact/bericht';
    $referer = $request?->headers->get('referer') ?? '-';
    $replyTo = filter_var($contact, FILTER_VALIDATE_EMAIL) ? $contact : NULL;

    $subject = sprintf('[BREBO-WEB][Contact][%s] Eerste contact – %s', $tracking, $name);
    $body = implode("\n", [
      'Bron: BREBO website / eerste contact',
      'Kenmerk: ' . $tracking,
      'Route: ' . $sourcePath,
      'Verwijzer: ' . $referer,
      'Naam: ' . $name,
      'Bereikbaar via: ' . $contact,
      '',
      'Bericht:',
      $text,
      '',
      'Vervolgprincipe: eerste contact ontvangen; aanvullende scope/informatie pas gericht uitvragen in fase 2.',
    ]);

    $result = $this->mailManager->mail(
      'brebo_contact',
      'website_contact_request',
      'info@brebobv.nl',
      'nl',
      ['subject' => $subject, 'body' => $body, 'reply_to' => $replyTo],
      NULL,
      TRUE,
    );

    if (!empty($result['result'])) {
      $this->messenger()->addStatus($this->t('Bedankt. We hebben uw bericht ontvangen. We gebruiken uw toelichting om de vraag eerst goed te begrijpen en bepalen daarna welke aanvullende informatie eventueel nodig is. Kenmerk: @tracking', ['@tracking' => $tracking]));
      $form_state->setRedirect('brebo_contact.message');
      return;
    }

    $this->messenger()->addError($this->t('Het bericht kon niet worden verzonden. Bel BREBO via 085-5003838.'));
  }

}
