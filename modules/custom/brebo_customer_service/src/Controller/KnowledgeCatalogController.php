<?php

declare(strict_types=1);

namespace Drupal\brebo_customer_service\Controller;

use Drupal\brebo_customer_service\Knowledge\KnowledgeApproval;
use Drupal\brebo_customer_service\Knowledge\KnowledgeItemRepository;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class KnowledgeCatalogController extends ControllerBase {

  public function __construct(
    private readonly KnowledgeItemRepository $knowledgeItems,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static($container->get('brebo_customer_service.knowledge_item_repository'));
  }

  public function topic(string $topic): array {
    $topics = $this->topics();
    if (!isset($topics[$topic])) {
      throw new NotFoundHttpException();
    }
    $items = $this->knowledgeItems->itemsByTopic()[$topic] ?? [];
    $cards = '';
    foreach ($items as $item) {
      $cards .= '<details class="brebo-knowledge-index__card brebo-knowledge-index__accordion">'
        . '<summary><span>' . $item['title'] . '</span><span class="brebo-knowledge-index__accordion-icon" aria-hidden="true">+</span></summary>'
        . '<div class="brebo-knowledge-index__accordion-body"><p>' . $item['summary'] . '</p>'
        . '<a class="brebo-knowledge-index__more" href="/klantenservice/kennis/vraag/' . $item['slug'] . '">Lees verder <span aria-hidden="true">→</span></a></div>'
        . '</details>';
    }
    $landing = $this->landing($topic);
    return [
      '#attached' => ['library' => ['brebo_customer_service/service']],
      '#cache' => ['tags' => ['brebo_public_knowledge']],
      '#markup' => '<main class="brebo-knowledge-index brebo-knowledge-index--filled">'
        . '<a class="brebo-knowledge-article__back" href="/klantenservice">← Terug naar klantenservice</a>'
        . '<header><p class="brebo-knowledge-library__eyebrow">BREBO Kennis</p><h1>' . $topics[$topic]['title'] . '</h1><p>' . $topics[$topic]['intro'] . '</p></header>'
        . '<section class="brebo-knowledge-landing__intro"><div><p class="brebo-knowledge-library__eyebrow">Waar het om gaat</p><h2>' . $landing['heading'] . '</h2><p>' . $landing['lead'] . '</p></div><aside><strong>Eerst vaststellen</strong><p>' . $landing['first'] . '</p></aside></section>'
        . '<section class="brebo-knowledge-landing__signals"><div><p class="brebo-knowledge-library__eyebrow">Herkenning</p><h2>Wanneer is dit onderwerp relevant?</h2></div><div class="brebo-knowledge-landing__signal-grid">' . $this->landingList($this->landingSignals($topic)) . '</div></section>'
        . '<section class="brebo-knowledge-landing__points"><h2>Hoe BREBO dit beoordeelt</h2><div class="brebo-knowledge-landing__point-grid">' . $this->landingPoints($landing['points']) . '</div></section>'
        . '<section class="brebo-knowledge-landing__scenarios"><div><p class="brebo-knowledge-library__eyebrow">Mogelijke richtingen</p><h2>Niet ieder gebouw vraagt om dezelfde ingreep</h2><p>De juiste richting volgt pas nadat oorzaak, toestand, risico en samenhang voldoende duidelijk zijn.</p></div><div class="brebo-knowledge-landing__scenario-grid">' . $this->landingPoints($this->landingScenarios($topic)) . '</div></section>'
        . '<section class="brebo-knowledge-landing__research"><div><p class="brebo-knowledge-library__eyebrow">Wanneer nader onderzoek nodig is</p><h2>' . $this->landingResearch($topic)[0] . '</h2><p>' . $this->landingResearch($topic)[1] . '</p></div></section>'
        . ($cards !== '' ? '<section class="brebo-knowledge-landing__questions"><div><p class="brebo-knowledge-library__eyebrow">Verdieping</p><h2>Vragen die vaak bij ' . strtolower($topics[$topic]['title']) . ' horen</h2><p>Gebruik deze vragen om gericht verder te kijken. Een afzonderlijk antwoord is niet automatisch een diagnose voor uw gebouw.</p></div><div class="brebo-knowledge-index__grid">' . $cards . '</div></section>' : '')
        . '<section class="brebo-knowledge-landing__action"><div><p class="brebo-knowledge-library__eyebrow">Uw gebouw</p><h2>Wilt u weten wat in uw situatie verstandig is?</h2><p>' . $landing['action'] . '</p></div><a class="brebo-knowledge-library__button" href="' . $this->contactHref($topic) . '">Bespreek uw situatie <span aria-hidden="true">→</span></a></section>'
        . '</main>',
    ];
  }

  public function question(string $question): array {
    $item = $this->knowledgeItems->find($question);
    if ($item === NULL) {
      throw new NotFoundHttpException();
    }
    $topics = $this->topics();
    $topic = $topics[$item['topic']] ?? NULL;
    if ($topic === NULL) {
      throw new NotFoundHttpException();
    }
    $contactHref = $this->contactHref($item['topic'], $item['slug']);
    return [
      '#attached' => ['library' => ['brebo_customer_service/service']],
      '#cache' => ['tags' => ['brebo_public_knowledge', 'node:' . $item['nid']]],
      '#markup' => '<article class="brebo-knowledge-article">'
        . '<a class="brebo-knowledge-article__back" href="/klantenservice/kennis/' . $item['topic'] . '">← Terug naar ' . $topic['title'] . '</a>'
        . '<header><p class="brebo-knowledge-library__eyebrow">' . $topic['title'] . '</p><h1>' . $item['title'] . '</h1><p class="brebo-knowledge-article__lead">' . $item['summary'] . '</p></header>'
        . '<div class="brebo-knowledge-article__body">'
        . $this->guidance($item)
        . '<aside><strong>Wat BREBO hiervoor wil weten</strong><p>' . $this->needed($item['topic']) . '</p></aside>'
        . '<aside class="brebo-knowledge-article__cta"><div><strong>Gaat dit over uw gebouw?</strong><p>Leg uw situatie aan BREBO voor. We bekijken wat bekend is, wat nog moet worden vastgesteld en wat een logische volgende stap is.</p></div><a href="' . $contactHref . '">Situatie voorleggen <span aria-hidden="true">→</span></a></aside>'
        . '</div></article>',
    ];
  }

  private function contactHref(string $topic, ?string $slug = NULL): string {
    if ($slug !== NULL && $slug !== '') {
      $route = match ($topic) {
        'kozijnen', 'glas', 'onderhoud-renovatie', 'verduurzaming' => 'kozijnen-glas',
        'gevel-aansluitingen' => 'probleem',
        'gebouwbeheer' => 'orientatie',
        default => 'orientatie',
      };
      return '/contact/bericht?route=' . $route . '&amp;context=' . rawurlencode($slug);
    }

    return match ($topic) {
      'kozijnen' => '/contact/bericht?route=kozijnen-glas&amp;context=advies-nodig',
      'glas' => '/contact/bericht?route=kozijnen-glas&amp;context=glas-aanvragen',
      'gevel-aansluitingen' => '/contact/bericht?route=probleem&amp;context=lekkage-tocht',
      'onderhoud-renovatie' => '/contact/bericht?route=kozijnen-glas&amp;context=onderhoud-herstel',
      'verduurzaming' => '/contact/bericht?route=kozijnen-glas&amp;context=vervangen-verduurzamen',
      'gebouwbeheer' => '/contact/bericht?route=orientatie&amp;context=onderhoudsplanning',
      default => '/contact/bericht',
    };
  }

  private function landingList(array $items): string {
    $markup = '';
    foreach ($items as $item) {
      $markup .= '<div class="brebo-knowledge-landing__signal"><span aria-hidden="true">→</span><p>' . $item . '</p></div>';
    }
    return $markup;
  }

  private function landingSignals(string $topic): array {
    return match ($topic) {
      'kozijnen' => ['Zichtbare houtaantasting, corrosie of open verbindingen.', 'Ramen of deuren sluiten slecht, klemmen of tochten.', 'Schilderwerk verslechtert opvallend snel of plaatselijk.', 'U overweegt ander glas of vervanging maar weet niet wat het kozijn nog aankan.'],
      'glas' => ['Condens, waas of vocht rond of tussen glasbladen.', 'U wilt HR++, triple, veiligheidsglas of geluidswering toepassen.', 'Ruiten zijn groot, zwaar of liggen op een windbelaste positie.', 'Er is lekkage, thermische breuk, geluidsoverlast of comfortverlies.'],
      'gevel-aansluitingen' => ['Vocht of lekkage rond kozijnen, dorpels, dakranden of doorvoeren.', 'Scheuren, los voegwerk of afspattend metselwerk worden zichtbaar.', 'Binnenoppervlakken voelen koud aan of vertonen condens/schimmel.', 'Kitnaden of aansluitingen laten los of zijn meerdere keren hersteld.'],
      'onderhoud-renovatie' => ['Dezelfde reparaties blijven terugkomen.', 'Meerdere bouwdelen bereiken ongeveer tegelijk een onderhoudsmoment.', 'Steiger, bereikbaarheid of bewonershinder maken losse ingrepen inefficiënt.', 'U twijfelt tussen doorgaan met onderhoud, gedeeltelijk verbeteren of integraal renoveren.'],
      'verduurzaming' => ['U wilt energiegebruik of comfort verbeteren zonder losse maatregelen te stapelen.', 'Glas, kozijnen of gevels zijn toch aan onderhoud of vervanging toe.', 'Tocht, koudeval, oververhitting of ventilatieproblemen spelen mee.', 'U wilt investeringen faseren en combineren met toekomstig onderhoud.'],
      'gebouwbeheer' => ['Het MJOP bevat vooral jaartallen en bedragen maar weinig onderbouwing.', 'U wilt risico en urgentie beter prioriteren.', 'Onderhoudskosten zijn moeilijk voorspelbaar of lopen onverwacht op.', 'Inspecties, foto’s, garanties en besluiten staan verspreid of ontbreken.'],
      default => [],
    };
  }

  private function landingScenarios(string $topic): array {
    return match ($topic) {
      'kozijnen' => [['Plaatselijk herstellen', 'Logisch wanneer schade beperkt is, de oorzaak kan worden weggenomen en voldoende restlevensduur aanwezig blijft.'], ['Gericht verbeteren', 'Bijvoorbeeld kierdichting, beslag, beglazing of deelvervanging wanneer de basis nog goed is.'], ['Vervangen', 'In beeld wanneer schade omvangrijk of terugkerend is, prestaties tekortschieten en herstel niet meer in verhouding staat.']],
      'glas' => [['Alleen ruit vervangen', 'Kan passend zijn wanneer kozijn, sponning en beglazingssysteem technisch geschikt zijn.'], ['Glas en kozijn samen beoordelen', 'Nodig wanneer gewicht, sponning, veiligheid, kierdichting of ventilatie mee verandert.'], ['Onderzoek vóór keuze', 'Bij lekkage, thermische breuk, geluid of onbekende glasopbouw is eerst oorzaak en randvoorwaarde nodig.']],
      'gevel-aansluitingen' => [['Plaatselijk herstel', 'Mogelijk bij een duidelijk, afgebakend gebrek zonder bredere oorzaak.'], ['Detail of aansluiting verbeteren', 'Wanneer het probleem terugkomt door ontwerp, beweging, afwatering of materiaalaansluiting.'], ['Breder gevelonderzoek', 'Nodig wanneer vocht- of scheurpatronen niet lokaal verklaarbaar zijn of meerdere bouwdelen betrokken zijn.']],
      'onderhoud-renovatie' => [['Correctief blijven herstellen', 'Alleen logisch wanneer risico, omvang en terugkeer beperkt blijven.'], ['Planmatig bundelen', 'Werkzaamheden combineren op moment, gevel of bereikbaarheid om kosten en hinder te beperken.'], ['Renovatiescenario', 'Passend wanneer meerdere functies, bouwdelen en prestaties tegelijk structureel aandacht vragen.']],
      'verduurzaming' => [['Gerichte comfortmaatregel', 'Een beperkte ingreep kan logisch zijn wanneer doel en oorzaak helder zijn.'], ['Combineren met onderhoud', 'Vaak doelmatiger wanneer een bouwdeel toch open, bereikbaar of aan vervanging toe is.'], ['Gefaseerd verduurzamingsplan', 'Geschikt wanneer techniek, budget en onderhoudsmomenten over meerdere jaren moeten worden afgestemd.']],
      'gebouwbeheer' => [['Actualiseren bestaand MJOP', 'Passend wanneer basisdata bruikbaar zijn maar conditie, prijzen of prioriteiten verouderd zijn.'], ['Nieuwe nulmeting en risicoanalyse', 'Nodig wanneer betrouwbare conditiedata ontbreken of het gebouw onvoldoende in beeld is.'], ['Doorlopend onderhoudsmanagement', 'Geschikt wanneer inspectie, planning, budget, besluiten en uitvoering structureel in één regieproces moeten samenkomen.']],
      default => [],
    };
  }

  private function landingResearch(string $topic): array {
    return match ($topic) {
      'kozijnen' => ['Als oorzaak of restlevensduur niet duidelijk is', 'Bij verborgen houtaantasting, herhaalde lekkage, vervorming of twijfel over draagkracht en sponning is alleen visuele beoordeling vaak onvoldoende. Dan kan gerichte opname of meting nodig zijn.'],
      'glas' => ['Als glasopbouw, belasting of oorzaak onzeker is', 'Bij grote afmetingen, onbekende glasopbouw, veiligheidseisen, thermische breuk of windbelasting kan een projectspecifieke berekening of nadere technische controle nodig zijn.'],
      'gevel-aansluitingen' => ['Als vocht of scheuren niet lokaal verklaarbaar zijn', 'Bij terugkerende lekkage, onduidelijke waterroute of actieve scheurvorming kan aanvullend onderzoek nodig zijn, bijvoorbeeld destructief onderzoek, vochtmeting of specialistische beoordeling.'],
      'onderhoud-renovatie' => ['Als scenario’s financieel en technisch dicht bij elkaar liggen', 'Dan helpt een conditieopname, hoeveelhedenstaat, scenariovergelijking en raming om onderhoud, verbetering en renovatie op dezelfde uitgangspunten te vergelijken.'],
      'verduurzaming' => ['Als maatregelen elkaar technisch beïnvloeden', 'Bij wijzigingen aan isolatie, luchtdichtheid, ventilatie, zonbelasting of constructieve randvoorwaarden kan aanvullende berekening of specialistische toetsing nodig zijn.'],
      'gebouwbeheer' => ['Als de basisdata onvoldoende betrouwbaar zijn', 'Een planning kan niet beter zijn dan de onderliggende gebouwinformatie. Bij ontbrekende of verouderde gegevens is eerst een gerichte opname, conditiemeting en dossiercontrole nodig.'],
      default => ['', ''],
    };
  }

  private function landingPoints(array $points): string {
    $markup = '';
    foreach ($points as [$title, $text]) {
      $markup .= '<article><h3>' . $title . '</h3><p>' . $text . '</p></article>';
    }
    return $markup;
  }

  private function landing(string $topic): array {
    return match ($topic) {
      'kozijnen' => [
        'heading' => 'Niet ieder kozijnprobleem vraagt om vervanging.',
        'lead' => 'De technische staat van een kozijn wordt bepaald door meer dan leeftijd of uiterlijk. Materiaal, detaillering, vochtbelasting, onderhoud, beglazing, hang- en sluitwerk en de aansluiting op de gevel bepalen samen welke aanpak logisch is.',
        'first' => 'Waar zit de schade of klacht, hoe groot is de omvang, wat is de vermoedelijke oorzaak en hoe is de toestand van de rest van het kozijn?',
        'points' => [
          ['Herstellen of vervangen', 'Plaatselijke aantasting kan vaak anders worden benaderd dan schade die in meerdere verbindingen, stijlen of dorpels terugkomt. Resterende levensduur en oorzaak horen daarom mee in de keuze.'],
          ['Glas en kozijn horen bij elkaar', 'Ander glas verandert gewicht, sponningopbouw, glaslatten en soms ook ventilatie en comfort. Alleen naar de ruit kijken is daarom te beperkt.'],
          ['Water, tocht en bediening', 'Lekkage, tocht of een slecht sluitend raam kan uit het kozijn zelf komen, maar ook uit afdichtingen, beslag, beglazing of de gevelaansluiting.'],
        ],
        'action' => 'Met foto’s, materiaalsoort, locatie, omvang, eerdere reparaties en de klachten rondom het kozijn kan BREBO gerichter bepalen welke vervolgstap nodig is.',
      ],
      'glas' => [
        'heading' => 'Glas beoordelen begint bij functie, positie en bestaande situatie.',
        'lead' => 'Isolatie, veiligheid, geluid, zonbelasting en afmetingen stellen verschillende eisen aan glas. Tegelijk moet de gekozen ruit technisch passen bij kozijn, sponning, ondersteuning, ventilatie en gebruik van het gebouw.',
        'first' => 'Wat wilt u oplossen of verbeteren, welk glas zit er nu, wat zijn de afmetingen en waar bevindt de ruit zich in het gebouw?',
        'points' => [
          ['Condens is niet één probleem', 'Condens aan de binnenzijde, buitenzijde of tussen glasbladen heeft niet dezelfde betekenis. Eerst moet dus duidelijk zijn waar het vocht zich bevindt.'],
          ['Beter isoleren verandert de samenhang', 'HR++ of triple glas kan comfort en warmteverlies verbeteren, maar geschiktheid van het kozijn, kierdichting en ventilatie blijven onderdeel van de beoordeling.'],
          ['Afmetingen en belasting tellen mee', 'Grote ruiten, windbelasting, ondersteuning, veiligheid en positie kunnen invloed hebben op de benodigde glasopbouw en uitvoerbaarheid.'],
        ],
        'action' => 'Foto’s van ruit en glasrand, afmetingen, positie, kozijnsoort en huidig glastype geven een bruikbare basis voor een eerste beoordeling.',
      ],
      'gevel-aansluitingen' => [
        'heading' => 'De zichtbare plek van een gevelprobleem is niet automatisch de oorzaak.',
        'lead' => 'Water, scheuren, koude oppervlakken en beschadigde voegen kunnen via verschillende bouwdelen en aansluitingen ontstaan. Een goede beoordeling volgt daarom de route van belasting naar oorzaak in plaats van alleen het zichtbare symptoom te herstellen.',
        'first' => 'Waar is het gebrek zichtbaar, wanneer treedt het op, welke bouwdelen sluiten daar op elkaar aan en hoe ziet de situatie er buiten én binnen uit?',
        'points' => [
          ['Lekkage vraagt om een route', 'Water kan via voegen, kozijnen, beglazing, spouw, lateien of andere details verplaatsen voordat het binnen zichtbaar wordt.'],
          ['Scheuren eerst duiden', 'Een scheur kan oppervlakkig zijn of samenhangen met beweging. Vorm, plaats, verloop en ontwikkeling zijn nodig voordat herstel wordt gekozen.'],
          ['Aansluitingen zijn systeemdetails', 'Kit, voegwerk, isolatie, kozijn en gevel moeten beweging, water en temperatuurverschillen gezamenlijk kunnen opvangen.'],
        ],
        'action' => 'Overzichts- en detailfoto’s, exacte locatie en informatie over weersomstandigheden of ontwikkeling in de tijd helpen BREBO om gericht verder te onderzoeken.',
      ],
      'onderhoud-renovatie' => [
        'heading' => 'Onderhoud wordt een renovatievraag zodra losse ingrepen hun samenhang verliezen.',
        'lead' => 'Steeds opnieuw repareren kan logisch zijn, maar niet wanneer dezelfde gebreken terugkomen, meerdere bouwdelen tegelijk verouderen of bereikbaarheid telkens opnieuw moet worden georganiseerd. Dan hoort ook een samenhangend scenario op tafel.',
        'first' => 'Welke werkzaamheden keren terug, welke bouwdelen zijn betrokken, wat is hun conditie en levensduur en welke andere werkzaamheden staan al gepland?',
        'points' => [
          ['Kijk verder dan de directe reparatie', 'Kosten van bereikbaarheid, hinder, gevolgschade en toekomstig onderhoud kunnen de afweging tussen repareren en renoveren veranderen.'],
          ['Bundelen kan doelmatiger zijn', 'Werkzaamheden aan dezelfde gevel of in dezelfde uitvoeringsperiode kunnen technisch en organisatorisch voordeel opleveren wanneer ze goed worden gecombineerd.'],
          ['Regie tijdens uitvoering telt mee', 'Heldere eisen, controlepunten, dossieropbouw en tijdige besluitvorming verkleinen discussie en herstelwerk tijdens renovatie.'],
        ],
        'action' => 'Onderhoudshistorie, terugkerende klachten, conditie van de bouwdelen en reeds geplande werkzaamheden vormen de basis om scenario’s te vergelijken.',
      ],
      'verduurzaming' => [
        'heading' => 'Verduurzamen is geen verzameling losse producten.',
        'lead' => 'Glas, kozijnen, gevel, kierdichting, ventilatie en zonbelasting beïnvloeden elkaar. De beste maatregel volgt daarom uit de bestaande staat, het gewenste resultaat en logische onderhoudsmomenten van het gebouw.',
        'first' => 'Wat wilt u verbeteren: energiegebruik, comfort, onderhoud, geluid of een combinatie daarvan, en welke bouwdelen zijn binnenkort toch aan onderhoud of vervanging toe?',
        'points' => [
          ['Begin bij de bestaande staat', 'Een maatregel die technisch goed is op papier kan onlogisch zijn wanneer het onderliggende bouwdeel onvoldoende restlevensduur heeft.'],
          ['Ventilatie hoort erbij', 'Meer isolatie en kierdichting veranderen lucht- en vochtstromen. Gecontroleerde ventilatie moet daarom worden meegewogen.'],
          ['Gebruik onderhoudsmomenten', 'Wanneer gevel, glas of kozijnen toch worden aangepakt, kan verduurzaming vaak doelmatiger worden meegenomen dan als los project.'],
        ],
        'action' => 'Met gebouwtype, bestaande isolatie en beglazing, ventilatie, comfortklachten en onderhoudsplannen kan BREBO de logische combinaties in beeld brengen.',
      ],
      'gebouwbeheer' => [
        'heading' => 'Een onderhoudsplan is pas bruikbaar als duidelijk is waarom iets wanneer moet gebeuren.',
        'lead' => 'Een lijst met jaartallen en bedragen is onvoldoende. Conditie, risico, resterende levensduur, gevolgschade, gebruik en technische samenhang bepalen welke werkzaamheden prioriteit krijgen en welke verantwoord kunnen wachten.',
        'first' => 'Welke bouwdelen zijn aanwezig, wat is hun actuele conditie, welke gebreken en risico’s zijn bekend en welke informatie uit inspecties en onderhoudshistorie is beschikbaar?',
        'points' => [
          ['Prioriteit volgt uit risico', 'Veiligheid, waterdichtheid, kans op gevolgschade, continuïteit en snelheid van verslechtering kunnen belangrijker zijn dan leeftijd alleen.'],
          ['Planning en budget horen samen', 'Een realistische reservering volgt uit verwachte maatregelen, prijsniveau, onzekerheid en planning; niet alleen uit historische uitgaven.'],
          ['Het dossier moet meegroeien', 'Inspecties, foto’s, tekeningen, garanties, besluiten en uitgevoerde maatregelen maken toekomstige keuzes controleerbaar en beter onderbouwd.'],
        ],
        'action' => 'Een bouwdelenoverzicht, actuele conditie, bekende gebreken, onderhoudshistorie en een bestaand MJOP of planning zijn een goede basis voor verdere regie.',
      ],
      default => throw new NotFoundHttpException(),
    };
  }

  private function guidance(array $item): string {
    $guidance = $item['guidance'] ?? [];
    if ($guidance === []) {
      return '';
    }

    $markup = '';
    foreach ($guidance as [$heading, $text]) {
      $markup .= '<section class="brebo-knowledge-article__section"><h2>' . $heading . '</h2><p>' . $text . '</p></section>';
    }
    return $markup;
  }

  private function needed(string $topic): string {
    return match ($topic) {
      'kozijnen' => 'Foto’s van binnen- en buitenzijde, materiaalsoort, plaats en omvang van de schade, eerdere reparaties en informatie over lekkage, tocht of klemmende delen.',
      'glas' => 'Foto’s van het glas en de glasrand, afmetingen, type kozijn, positie in het gebouw en het huidige glastype indien bekend.',
      'gevel-aansluitingen' => 'Overzichts- en detailfoto’s, exacte locatie, weersomstandigheden waarbij het probleem optreedt en informatie over omliggende aansluitingen.',
      'onderhoud-renovatie' => 'Onderhoudshistorie, terugkerende klachten, leeftijd en conditie van relevante bouwdelen en reeds geplande werkzaamheden.',
      'verduurzaming' => 'Gebouwtype, bestaande isolatie en beglazing, ventilatievoorzieningen, comfortklachten en eventuele toekomstige onderhoudsplannen.',
      'gebouwbeheer' => 'Bouwdelenoverzicht, actuele conditie, bekende gebreken, onderhoudshistorie, risico’s en bestaande planning of MJOP.',
      default => 'Foto’s, locatie, omvang, historie en een korte beschrijving van wat u ziet of merkt.',
    };
  }

  private function topics(): array {
    return [
      'kozijnen' => ['title' => 'Kozijnen', 'intro' => 'Onderhoud, herstel, levensduur en de afweging tussen behouden, verbeteren en vervangen.'],
      'glas' => ['title' => 'Glas', 'intro' => 'Isolatieglas, veiligheid, condens, lekkage, comfort en de aansluiting op het kozijn.'],
      'gevel-aansluitingen' => ['title' => 'Gevel & aansluitingen', 'intro' => 'Kitwerk, aansluitdetails, lekkage, koudebruggen, isolatie en de samenhang tussen gevel, kozijn en glas.'],
      'onderhoud-renovatie' => ['title' => 'Onderhoud & renovatie', 'intro' => 'Van gericht herstel tot een samenhangende renovatieaanpak op het juiste moment.'],
      'verduurzaming' => ['title' => 'Verduurzaming', 'intro' => 'Comfort en energiegebruik bekeken in samenhang met glas, kozijnen, gevel en ventilatie.'],
      'gebouwbeheer' => ['title' => 'Gebouwbeheer', 'intro' => 'Inspecties, onderhoudsplanning, risico, conditie en onderbouwde keuzes voor de komende jaren.'],
    ];
  }

}
