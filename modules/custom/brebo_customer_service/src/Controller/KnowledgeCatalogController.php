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
      $cards .= '<article class="brebo-knowledge-index__card">'
        . '<p class="brebo-knowledge-library__eyebrow">' . $topics[$topic]['title'] . '</p>'
        . '<h2><a href="/klantenservice/kennis/vraag/' . $item['slug'] . '">' . $item['title'] . '</a></h2>'
        . '<p>' . $item['summary'] . '</p>'
        . '<a class="brebo-knowledge-index__more" href="/klantenservice/kennis/vraag/' . $item['slug'] . '">Lees verder <span aria-hidden="true">→</span></a>'
        . '</article>';
    }
    $landing = $this->landing($topic);
    return [
      '#attached' => ['library' => ['brebo_customer_service/service']],
      '#cache' => ['tags' => ['brebo_public_knowledge']],
      '#markup' => '<main class="brebo-knowledge-index brebo-knowledge-index--filled">'
        . '<a class="brebo-knowledge-article__back" href="/klantenservice">← Terug naar klantenservice</a>'
        . '<header><p class="brebo-knowledge-library__eyebrow">BREBO Kennis</p><h1>' . $topics[$topic]['title'] . '</h1><p>' . $topics[$topic]['intro'] . '</p></header>'
        . '<section class="brebo-knowledge-landing__intro"><div><p class="brebo-knowledge-library__eyebrow">Waar het om gaat</p><h2>' . $landing['heading'] . '</h2><p>' . $landing['lead'] . '</p></div><aside><strong>Eerst vaststellen</strong><p>' . $landing['first'] . '</p></aside></section>'
        . '<section class="brebo-knowledge-landing__points"><h2>Belangrijke afwegingen</h2><div class="brebo-knowledge-landing__point-grid">' . $this->landingPoints($landing['points']) . '</div></section>'
        . '<section class="brebo-knowledge-landing__questions"><div><p class="brebo-knowledge-library__eyebrow">Verdieping</p><h2>Vragen die vaak bij ' . strtolower($topics[$topic]['title']) . ' horen</h2><p>Gebruik deze vragen om gericht verder te kijken. Een afzonderlijk antwoord is niet automatisch een diagnose voor uw gebouw.</p></div><div class="brebo-knowledge-index__grid">' . $cards . '</div></section>'
        . '<section class="brebo-knowledge-landing__action"><div><p class="brebo-knowledge-library__eyebrow">Uw gebouw</p><h2>Wilt u weten wat in uw situatie verstandig is?</h2><p>' . $landing['action'] . '</p></div><a class="brebo-knowledge-library__button" href="/contact/bericht">Bespreek uw situatie <span aria-hidden="true">→</span></a></section>'
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
    $status = KnowledgeApproval::statusLabel($item);
    $ai = KnowledgeApproval::aiReason($item);
    return [
      '#attached' => ['library' => ['brebo_customer_service/service']],
      '#cache' => ['tags' => ['brebo_public_knowledge', 'node:' . $item['nid']]],
      '#markup' => '<article class="brebo-knowledge-article">'
        . '<a class="brebo-knowledge-article__back" href="/klantenservice/kennis/' . $item['topic'] . '">← Terug naar ' . $topic['title'] . '</a>'
        . '<header><p class="brebo-knowledge-library__eyebrow">' . $topic['title'] . '</p><h1>' . $item['title'] . '</h1><p class="brebo-knowledge-article__lead">' . $item['summary'] . '</p></header>'
        . '<div class="brebo-knowledge-article__body">'
        . $this->guidance($question)
        . '<aside><strong>Wat BREBO hiervoor wil weten</strong><p>' . $this->needed($item['topic']) . '</p></aside>'
        . '<aside class="brebo-knowledge-article__quality"><strong>Kennisstatus: ' . $status . '</strong><p>' . $ai . '</p></aside>'
        . '</div></article>',
    ];
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

  private function guidance(string $slug): string {
    $specific = [
      'condens-tussen-glasbladen' => ['Bepaal eerst waar de condens zit', 'Condens aan de binnenzijde, buitenzijde en tussen de glasbladen heeft niet dezelfde betekenis. Vocht of waas tussen de glasbladen bevindt zich in de afgesloten spouw van het isolatieglas.', 'Kijk ook naar de beglazing als systeem', 'Bij vervanging is het verstandig ook randafdichting, glasoplegging, sponning en vochtbelasting rondom de ruit te beoordelen.'],
      'kozijnen-herstellen-of-vervangen' => ['Begin bij oorzaak en omvang', 'Afbladderende verf, open verbindingen of plaatselijke aantasting zijn niet automatisch een reden voor volledige vervanging. Eerst moet duidelijk zijn welk deel is aangetast en waarom.', 'Vergelijk herstel met resterende levensduur', 'Herstel is vooral logisch wanneer voldoende gezond materiaal aanwezig blijft en de oorzaak duurzaam kan worden weggenomen.'],
      'hrpp-bestaande-kozijnen' => ['Controleer eerst het bestaande kozijn', 'Sponning, glaslatten, ondersteuning, kierdichting en staat van het kozijn bepalen mede of een andere glasopbouw passend kan worden aangebracht.', 'Neem ventilatie mee', 'Een betere isolatie en luchtdichtheid veranderen het comfort en de vochtbalans. Gecontroleerde ventilatie blijft daarom onderdeel van de beoordeling.'],
      'onderhoud-of-renovatie' => ['Kijk naar herhaling en samenhang', 'Wanneer dezelfde reparaties terugkomen of meerdere bouwdelen elkaar beïnvloeden, wordt alleen incidentgericht herstellen steeds minder logisch.', 'Vergelijk scenario’s', 'Directe kosten zijn niet het enige criterium. Ook resterende levensduur, gevolgschade, bereikbaarheid, hinder en toekomstige onderhoudsbehoefte horen in de afweging.'],
      'bouwbegeleiding-wanneer' => ['Zorg voor regie vóórdat de uitvoering begint', 'Onafhankelijke bouwbegeleiding is vooral waardevol wanneer u als opdrachtgever grip wilt houden op kwaliteit, planning, kosten en technische keuzes zonder zelf dagelijks op het werk aanwezig te zijn.', 'Controleer tijdens het werk, niet alleen achteraf', 'Door afspraken, details, afwijkingen en voortgang tijdens de uitvoering vast te leggen, kunnen problemen worden bijgestuurd voordat ze bij oplevering tot discussie, herstelwerk of extra kosten leiden.'],
      'bouwbegeleiding-controlepunten' => ['Maak vooraf duidelijk wat gecontroleerd wordt', 'De controlepunten volgen uit contractstukken, tekeningen, technische eisen, planning en kritieke uitvoeringsmomenten. Zo is vooraf duidelijk waarop de uitvoering wordt beoordeeld.', 'Leg afwijkingen en besluiten aantoonbaar vast', 'Foto’s, bevindingen, afspraken, meer- en minderwerk en openstaande acties horen in één dossier. Daarmee blijft voor opdrachtgever en uitvoerende partijen zichtbaar wat is afgesproken en wat nog moet gebeuren.'],
      'oplevering-restpunten' => ['Begin de oplevering al tijdens de uitvoering', 'Een goede oplevering ontstaat niet op de laatste dag. Tussentijdse controles maken gebreken en onafgemaakte onderdelen eerder zichtbaar, zodat herstel kan worden ingepland voordat het werk als gereed wordt beschouwd.', 'Maak ieder restpunt concreet en controleerbaar', 'Leg per punt vast wat niet voldoet, waar het zich bevindt, wie actie neemt en wanneer nacontrole plaatsvindt. Foto’s en een eenduidige status voorkomen discussie over wat wel of niet is afgehandeld.'],
      'renovatie-bewoners-hinder' => ['Plan de uitvoering vanuit het gebruik van het gebouw', 'Bewoners en gebruikers merken vooral bereikbaarheid, geluid, stof, tijdelijke afsluitingen en veranderingen in voorzieningen. De werkvolgorde moet daarom niet alleen technisch kloppen, maar ook praktisch uitvoerbaar zijn.', 'Communiceer vóórdat hinder ontstaat', 'Duidelijke informatie over planning, toegang, veiligheidsmaatregelen, contactpersonen en wijzigingen geeft bewoners en gebruikers handelingsperspectief en voorkomt onnodige verstoring en misverstanden.'],
    ];
    if (isset($specific[$slug])) {
      [$h1, $p1, $h2, $p2] = $specific[$slug];
    }
    else {
      $h1 = 'Begin bij wat u daadwerkelijk waarneemt';
      $p1 = 'Dezelfde zichtbare klacht kan verschillende oorzaken hebben. Leg daarom eerst plaats, omvang, omstandigheden en ontwikkeling in de tijd vast voordat een maatregel wordt gekozen.';
      $h2 = 'Beoordeel het bouwdeel in samenhang';
      $p2 = 'Kozijn, glas, gevel, afdichtingen, vocht, ventilatie, gebruik en onderhoud kunnen elkaar beïnvloeden. Een goede oplossing pakt niet alleen het zichtbare symptoom aan.';
    }
    return '<section class="brebo-knowledge-article__section"><h2>' . $h1 . '</h2><p>' . $p1 . '</p></section><section class="brebo-knowledge-article__section"><h2>' . $h2 . '</h2><p>' . $p2 . '</p></section>';
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
