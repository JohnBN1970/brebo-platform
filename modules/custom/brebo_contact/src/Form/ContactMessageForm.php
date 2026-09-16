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

    $request = $this->contactRequestStack->getCurrentRequest();
    $routeName = (string) ($request?->attributes->get('_route') ?? '');

    if ($routeName === 'brebo_contact.confirmation') {
      $tracking = trim((string) ($request?->query->get('kenmerk') ?? ''));
      $safeTracking = htmlspecialchars($tracking, ENT_QUOTES, 'UTF-8');

      $form['#attributes']['class'][] = 'brebo-contact-message--confirmation';
      $form['confirmation'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['brebo-contact-message__confirmation']],
        'icon' => ['#markup' => '<div class="brebo-contact-message__check" aria-hidden="true"><span></span></div>'],
        'eyebrow' => ['#markup' => '<p class="brebo-contact__eyebrow">Bericht ontvangen</p>'],
        'title' => ['#markup' => '<h1>Bedankt. Uw bericht is ontvangen.</h1>'],
        'lead' => ['#markup' => '<p class="brebo-contact-message__confirmation-lead">We bekijken eerst wat er speelt en nemen van daaruit contact met u op.<br>Als aanvullende informatie nodig is, vragen we daar gericht om.</p>'],
        'reference' => [
          '#markup' => $safeTracking !== '' ? '<div class="brebo-contact-message__reference">Kenmerk: <strong>' . $safeTracking . '</strong></div>' : '',
        ],
        'actions' => [
          '#markup' => '<div class="brebo-contact-message__confirmation-actions"><a class="brebo-contact-message__back" href="/"><span aria-hidden="true">→</span> Terug naar BREBO</a><p>Liever direct contact? Bel BREBO: <a href="tel:+31855003838">085-5003838</a></p></div>',
        ],
      ];

      return $form;
    }

    $journeyRoute = trim((string) ($request?->query->get('route') ?? ''));
    $journeyContext = trim((string) ($request?->query->get('context') ?? ''));
    $routeLabels = [
      'orientatie' => 'Ik weet nog niet wat er nodig is',
      'probleem' => 'Ik heb een concreet onderhoudsprobleem',
      'kozijnen-glas' => 'Ik wil kozijnen of glas aanpakken',
      'documenten' => 'Ik heb al plannen of documenten',
      'bouwbegeleiding' => 'Ik zoek begeleiding bij een project',
    ];
    $contextLabels = [
      'staat-inzicht' => 'Ik wil eerst weten wat de staat van het gebouw is',
      'onderhoudsplanning' => 'Ik wil onderhoud of investeringen beter kunnen plannen',
      'keuze-onduidelijk' => 'Ik twijfel tussen meerdere technische oplossingen',
      'risico-kosten' => 'Ik wil risico, kosten en prioriteiten eerst helder krijgen',
      'lekkage-tocht' => 'Lekkage, tocht of vocht',
      'schade-slijtage' => 'Schade, slijtage of houtrot',
      'glas-condens' => 'Glas, condens of doorzicht',
      'functioneren' => 'Ramen, deuren of onderdelen functioneren niet goed',
      'kozijnen' => 'Kozijnen',
      'glas' => 'Glas',
      'kozijnen-glas' => 'Kozijnen én glas in samenhang',
      'ventilatie' => 'Kozijnen, glas en ventilatie samen',
      'mjop-rapport' => 'MJOP, inspectie of technisch rapport',
      'tekening-kozijnstaat' => 'Tekeningen of kozijnstaat',
      'offerte-bestek' => 'Offerte, bestek of aanvraagstukken',
      'fotos-overig' => 'Foto’s of andere projectinformatie',
      'voorbereiding' => 'Planvorming en voorbereiding',
      'inkoop-aanbesteding' => 'Inkoop, aanbesteding of contractvorming',
      'uitvoering-toezicht' => 'Uitvoering, toezicht en kwaliteitsbewaking',
      'oplevering-nazorg' => 'Oplevering, restpunten en nazorg',
    ];
    $journeyActive = isset($routeLabels[$journeyRoute]) && isset($contextLabels[$journeyContext]);

    $form['intro'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['brebo-contact-message__intro']],
      'eyebrow' => ['#markup' => '<p class="brebo-contact__eyebrow">Contact</p>'],
      'title' => ['#markup' => $journeyActive ? '<h2>Vertel ons om welk gebouw het gaat.</h2>' : '<h2>Vertel kort wat er speelt.</h2>'],
      'lead' => ['#markup' => $journeyActive ? '<p>Uw gebouwvraag is al meegenomen. Vul het gebouw en uw contactgegevens aan; daarna bekijken wij welke volgende stap logisch is.</p>' : '<p>Meer hoeft voor een eerste contact niet. We luisteren eerst naar uw vraag en bepalen van daaruit wat een logische volgende stap is.</p>'],
    ];

    if ($journeyActive) {
      $safeRoute = htmlspecialchars($routeLabels[$journeyRoute], ENT_QUOTES, 'UTF-8');
      $safeContext = htmlspecialchars($contextLabels[$journeyContext], ENT_QUOTES, 'UTF-8');
      $form['journey_summary'] = [
        '#markup' => '<div class="brebo-contact-message__journey"><span>Uw route</span><strong>' . $safeRoute . '</strong><small>' . $safeContext . '</small></div>',
      ];
    }

    $form['journey_route'] = [
      '#type' => 'hidden',
      '#value' => $journeyActive ? $journeyRoute : '',
    ];
    $form['journey_context'] = [
      '#type' => 'hidden',
      '#value' => $journeyActive ? $journeyContext : '',
    ];

    $form['building'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Adres of naam van het gebouw'),
      '#description' => $this->t('Bijvoorbeeld straat + huisnummer en plaats. Als het adres nog niet bekend is, kunt u ook een project- of gebouwnaam invullen.'),
      '#required' => $journeyActive,
      '#maxlength' => 240,
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
      '#title' => $journeyActive ? $this->t('Aanvulling (optioneel)') : $this->t('Uw bericht'),
      '#description' => $journeyActive ? $this->t('Alleen als u nog iets wilt meegeven. Uw gekozen gebouwvraag is al bekend.') : NULL,
      '#required' => !$journeyActive,
      '#rows' => $journeyActive ? 5 : 7,
      '#maxlength' => 5000,
    ];

    $form['company_website'] = [
      '#type' => 'hidden',
      '#value' => '',
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $journeyActive ? $this->t('Stuur mijn gebouwvraag') : $this->t('Stuur mijn bericht'),
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
    $building = trim((string) $form_state->getValue('building'));
    $text = trim((string) $form_state->getValue('message'));
    $journeyRoute = trim((string) $form_state->getValue('journey_route'));
    $journeyContext = trim((string) $form_state->getValue('journey_context'));
    $sourcePath = $request?->getPathInfo() ?? '/contact/bericht';
    $referer = $request?->headers->get('referer') ?? '-';
    $replyTo = filter_var($contact, FILTER_VALIDATE_EMAIL) ? $contact : NULL;

    $subject = sprintf('[BREBO-WEB][Contact][%s] Eerste contact – %s', $tracking, $name);
    $body = implode("\n", [
      'Bron: BREBO website / eerste contact',
      'Kenmerk: ' . $tracking,
      'Route: ' . $sourcePath,
      'Verwijzer: ' . $referer,
      'Gebouw: ' . ($building !== '' ? $building : '-'),
      'Klantreis: ' . ($journeyRoute !== '' ? $journeyRoute : '-'),
      'Situatie: ' . ($journeyContext !== '' ? $journeyContext : '-'),
      'Naam: ' . $name,
      'Bereikbaar via: ' . $contact,
      '',
      'Bericht:',
      $text !== '' ? $text : '-',
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
      $form_state->clearErrors();
      $form_state->setRedirect('brebo_contact.confirmation', [], ['query' => ['kenmerk' => $tracking]]);
      return;
    }

    $this->messenger()->addError($this->t('Het bericht kon niet worden verzonden. Bel BREBO via 085-5003838.'));
  }

}
