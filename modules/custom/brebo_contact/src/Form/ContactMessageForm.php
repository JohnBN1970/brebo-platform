<?php

declare(strict_types=1);

namespace Drupal\brebo_contact\Form;

use Drupal\brebo_contact\Service\OfficeWebsiteDocumentClient;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
    private readonly OfficeWebsiteDocumentClient $officeDocumentClient,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('plugin.manager.mail'),
      $container->get('flood'),
      $container->get('request_stack'),
      $container->get('brebo_contact.office_website_document_client'),
      $container->get('entity_type.manager'),
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
      'vervangen-verduurzamen' => 'Vervangen of verduurzamen',
      'probleem-oplossen' => 'Een probleem of gebrek oplossen',
      'onderhoud-herstel' => 'Onderhoud of herstel uitvoeren',
      'advies-nodig' => 'Weten wat verstandig of technisch nodig is',
      'doel-onduidelijk' => 'Ik weet het nog niet',
      'mjop-rapport' => 'MJOP, inspectie of technisch rapport',
      'tekening-kozijnstaat' => 'Tekeningen of kozijnstaat',
      'offerte-bestek' => 'Offerte, bestek of aanvraagstukken',
      'fotos-overig' => 'Foto’s of andere projectinformatie',
      'voorbereiding' => 'Planvorming en voorbereiding',
      'inkoop-aanbesteding' => 'Inkoop, aanbesteding of contractvorming',
      'uitvoering-toezicht' => 'Uitvoering, toezicht en kwaliteitsbewaking',
      'oplevering-nazorg' => 'Oplevering, restpunten en nazorg',
    ];
    $routeContexts = [
      'orientatie' => ['staat-inzicht', 'onderhoudsplanning', 'keuze-onduidelijk', 'risico-kosten'],
      'probleem' => ['lekkage-tocht', 'schade-slijtage', 'glas-condens', 'functioneren'],
      'kozijnen-glas' => ['vervangen-verduurzamen', 'probleem-oplossen', 'onderhoud-herstel', 'advies-nodig', 'doel-onduidelijk'],
      'documenten' => ['mjop-rapport', 'tekening-kozijnstaat', 'offerte-bestek', 'fotos-overig'],
      'bouwbegeleiding' => ['voorbereiding', 'inkoop-aanbesteding', 'uitvoering-toezicht', 'oplevering-nazorg'],
    ];

    // The second journey choice describes where the visitor actually is.
    // Route concrete defects and execution-ready information to realisation,
    // uncertainty/assessment to knowledge, and project control to supervision.
    $contextDestination = [
      'staat-inzicht' => 'knowledge',
      'onderhoudsplanning' => 'knowledge',
      'keuze-onduidelijk' => 'knowledge',
      'risico-kosten' => 'knowledge',
      'lekkage-tocht' => 'realisation',
      'schade-slijtage' => 'realisation',
      'glas-condens' => 'realisation',
      'functioneren' => 'realisation',
      'vervangen-verduurzamen' => 'realisation',
      'probleem-oplossen' => 'realisation',
      'onderhoud-herstel' => 'realisation',
      'advies-nodig' => 'knowledge',
      'doel-onduidelijk' => 'knowledge',
      'mjop-rapport' => 'knowledge',
      'tekening-kozijnstaat' => 'realisation',
      'offerte-bestek' => 'supervision',
      'fotos-overig' => 'knowledge',
      'voorbereiding' => 'supervision',
      'inkoop-aanbesteding' => 'supervision',
      'uitvoering-toezicht' => 'supervision',
      'oplevering-nazorg' => 'supervision',
    ];
    $destinationInfo = [
      'knowledge' => [
        'title' => 'Eerst begrijpen wat er speelt en wat verstandig is.',
        'summary' => 'Kennis & advies helpt om de technische situatie, oorzaak, risico’s, keuzes en prioriteiten helder te krijgen voordat een maatregel wordt gekozen.',
        'url' => '/kennis-advies',
        'link' => 'Bekijk Kennis & advies',
      ],
      'supervision' => [
        'title' => 'Grip houden op voorbereiding, afspraken en uitvoering.',
        'summary' => 'Bouwbegeleiding sluit aan wanneer voorbereiding, inkoop, contractvorming, toezicht, kwaliteit of oplevering moet worden bewaakt.',
        'url' => '/bouwbegeleiding',
        'link' => 'Bekijk Bouwbegeleiding',
      ],
      'realisation' => [
        'title' => 'Van duidelijke opgave naar herstel, vervanging of renovatie.',
        'summary' => 'Onderhoud & renovatie past wanneer er een concreet gebrek, herstelvraag of uitvoeringsopgave ligt.',
        'url' => '/onderhoud-renovatie',
        'link' => 'Bekijk Onderhoud & renovatie',
      ],
    ];
    $journeyDestination = $contextDestination[$journeyContext] ?? '';
    $journeyActive = isset(
      $routeLabels[$journeyRoute],
      $contextLabels[$journeyContext],
      $destinationInfo[$journeyDestination],
    ) && in_array($journeyContext, $routeContexts[$journeyRoute] ?? [], TRUE);

    $form['intro'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['brebo-contact-message__intro']],
      'eyebrow' => ['#markup' => '<p class="brebo-contact__eyebrow">Contact</p>'],
      'title' => ['#markup' => $journeyActive ? '<h2>Vertel ons om welk gebouw het gaat.</h2>' : '<h2>Vertel kort wat er speelt.</h2>'],
      'lead' => ['#markup' => $journeyActive ? '<p>We weten waar uw vraag over gaat. Vertel ons alleen nog om welk gebouw het gaat en hoe we u kunnen bereiken.</p>' : '<p>Meer hoeft voor een eerste contact niet. We luisteren eerst naar uw vraag en bepalen van daaruit wat een logische volgende stap is.</p>'],
    ];

    if ($journeyActive) {
      $safeRoute = htmlspecialchars($routeLabels[$journeyRoute], ENT_QUOTES, 'UTF-8');
      $safeContext = htmlspecialchars($contextLabels[$journeyContext], ENT_QUOTES, 'UTF-8');
      $info = $destinationInfo[$journeyDestination];
      $safeInfoTitle = htmlspecialchars($info['title'], ENT_QUOTES, 'UTF-8');
      $safeInfoSummary = htmlspecialchars($info['summary'], ENT_QUOTES, 'UTF-8');
      $safeInfoUrl = htmlspecialchars($info['url'], ENT_QUOTES, 'UTF-8');
      $safeInfoLink = htmlspecialchars($info['link'], ENT_QUOTES, 'UTF-8');

      $form['journey_context_info'] = [
        '#markup' => '<div class="brebo-contact-message__context"><span>Waar ging dit over?</span><strong>' . $safeInfoTitle . '</strong><p>' . $safeInfoSummary . '</p><a href="' . $safeInfoUrl . '">Nog even terugkijken? ' . $safeInfoLink . ' →</a></div>',
      ];
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

    if ($journeyActive && $journeyRoute === 'documenten') {
      $form['documents'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Documenten toevoegen (optioneel)'),
        '#upload_location' => 'temporary://brebo-contact/',
        '#multiple' => TRUE,
        '#upload_validators' => [
          'FileExtension' => ['extensions' => 'pdf doc docx xls xlsx jpg jpeg png webp heic heif zip'],
          'FileSizeLimit' => ['fileLimit' => 26214400],
        ],
        '#description' => $this->t('Maximaal 5 bestanden van 25 MB per bestand. BREBO Office verwerkt de bestanden na verzending.'),
      ];
    }

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $journeyActive ? $this->t('Aanvulling (optioneel)') : $this->t('Uw bericht'),
      '#description' => $journeyActive ? $this->t('Alleen als u nog iets wilt meegeven.') : NULL,
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

    if (!$journeyActive) {
      $form['aftercare'] = [
        '#markup' => '<p class="brebo-contact-message__note">Na uw eerste bericht kunnen we gericht aangeven welke aanvullende informatie eventueel nuttig is. Heeft u al een lopend project bij BREBO? Gebruik dan de <a href="/klantenservice">Klantenservice</a>.</p>',
      ];
    }

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

    $documents = array_values(array_filter((array) $form_state->getValue('documents')));
    if (count($documents) > 5) {
      $form_state->setErrorByName('documents', $this->t('Voeg maximaal 5 bestanden toe.'));
    }

    $identifier = $this->contactRequestStack->getCurrentRequest()?->getClientIp() ?? 'unknown';
    if (!$this->flood->isAllowed('brebo_contact.submit', 5, 3600, $identifier)) {
      $form_state->setErrorByName('message', $this->t('Er zijn te veel berichten verzonden. Probeer het later opnieuw of bel BREBO.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $request = $this->contactRequestStack->getCurrentRequest();
    $identifier = $request?->getClientIp() ?? 'unknown';

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

    $documentIds = array_values(array_filter(array_map('intval', (array) $form_state->getValue('documents'))));
    if ($documentIds !== []) {
      $fileStorage = $this->entityTypeManager->getStorage('file');
      $metadata = [
        'schema_version' => '1.0',
        'source' => 'brebo-platform.contact',
        'tracking' => $tracking,
        'building' => $building,
        'name' => $name,
        'contact' => $contact,
        'message' => $text,
        'journey_route' => $journeyRoute,
        'journey_context' => $journeyContext,
        'source_path' => $sourcePath,
      ];

      foreach ($documentIds as $fileId) {
        $file = $fileStorage->load($fileId);
        if ($file === NULL) {
          $this->messenger()->addError($this->t('Een toegevoegd bestand kon niet worden gelezen. Probeer het opnieuw.'));
          return;
        }
        $handoff = $this->officeDocumentClient->send(
          (string) $file->getFileUri(),
          (string) $file->getFilename(),
          $this->requestUuid(),
          $metadata,
        );
        if (empty($handoff['ok'])) {
          $this->messenger()->addError($this->t('Uw document(en) konden nog niet veilig aan BREBO Office worden overgedragen. Probeer het opnieuw of neem contact met ons op.'));
          return;
        }
      }
    }

    $this->flood->register('brebo_contact.submit', 3600, $identifier);

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

  private function requestUuid(): string {
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    $hex = bin2hex($bytes);
    return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
  }

}
