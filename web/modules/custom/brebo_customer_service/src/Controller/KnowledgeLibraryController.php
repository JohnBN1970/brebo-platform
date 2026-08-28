<?php

declare(strict_types=1);

namespace Drupal\brebo_customer_service\Controller;

use Drupal\Core\Controller\ControllerBase;

final class KnowledgeLibraryController extends ControllerBase {

  public function page(): array {
    $topics = [
      ['icon' => '▦', 'slug' => 'kozijnen', 'title' => 'Kozijnen', 'text' => 'Onderhoud, herstel, levensduur en de afweging tussen behouden, verbeteren en vervangen.'],
      ['icon' => '◩', 'slug' => 'glas', 'title' => 'Glas', 'text' => 'Isolatieglas, veiligheid, condens, lekkage, geluid, zonbelasting en de aansluiting op het kozijn.'],
      ['icon' => '⌗', 'slug' => 'gevel-aansluitingen', 'title' => 'Gevel & aansluitingen', 'text' => 'Kitwerk, aansluitdetails, lekkage, koudebruggen, isolatie en de samenhang tussen gevel, kozijn en glas.'],
      ['icon' => '⚒', 'slug' => 'onderhoud-renovatie', 'title' => 'Onderhoud & renovatie', 'text' => 'Wanneer plaatselijk onderhoud nog logisch is en wanneer een samenhangende renovatie verstandiger wordt.'],
      ['icon' => '◒', 'slug' => 'verduurzaming', 'title' => 'Verduurzaming', 'text' => 'Comfort, energiegebruik en de technische samenhang tussen glas, kozijnen, gevel, ventilatie en gebruik.'],
      ['icon' => '▤', 'slug' => 'gebouwbeheer', 'title' => 'Gebouwbeheer', 'text' => 'Inspecties, onderhoudsplanning, risico, conditie en onderbouwde keuzes voor de komende jaren.'],
    ];

    $questions = [
      ['slug' => 'kozijnen-herstellen-of-vervangen', 'title' => 'Wanneer is een bestaand kozijn nog goed te herstellen?'],
      ['slug' => 'condens-tussen-glasbladen', 'title' => 'Wat betekent condens tussen de glasbladen?'],
      ['slug' => 'hrpp-bestaande-kozijnen', 'title' => 'Heeft HR++ glas zin in bestaande kozijnen?'],
      ['slug' => 'onderhoud-of-renovatie', 'title' => 'Wanneer wordt terugkerend onderhoud een renovatievraag?'],
    ];

    $topic_markup = '';
    foreach ($topics as $topic) {
      $topic_markup .= '<article class="brebo-knowledge-card"><span class="brebo-knowledge-card__icon" aria-hidden="true">' . $topic['icon'] . '</span><div><h3>' . $topic['title'] . '</h3><p>' . $topic['text'] . '</p><a href="/klantenservice/kennis/' . $topic['slug'] . '">Bekijk kennis <span aria-hidden="true">→</span></a></div></article>';
    }

    $question_markup = '';
    foreach ($questions as $question) {
      $question_markup .= '<li><a href="/klantenservice/kennis/vraag/' . $question['slug'] . '">' . $question['title'] . '<span aria-hidden="true">＋</span></a></li>';
    }

    return [
      '#attached' => ['library' => ['brebo_customer_service/service']],
      '#markup' => '
        <section class="brebo-knowledge-library">
          <div class="brebo-knowledge-library__hero"><div class="brebo-knowledge-library__hero-inner">
            <div class="brebo-knowledge-library__hero-copy"><p class="brebo-knowledge-library__eyebrow">BREBO Kennisbibliotheek</p><h1>Waar kunnen we u mee helpen?</h1><p class="brebo-knowledge-library__lead">Beschrijf wat u aan uw gebouw merkt of waar u meer over wilt weten. Zoek in onze kennis over kozijnen, glas, gevels, onderhoud, renovatie en gebouwbeheer.</p></div>
            <div class="brebo-knowledge-library__ask" aria-label="BREBO kennisassistent in voorbereiding"><div class="brebo-knowledge-library__ask-title"><span aria-hidden="true">▣</span><label for="brebo-knowledge-question">Stel uw vraag aan BREBO</label></div><textarea id="brebo-knowledge-question" rows="4" placeholder="Beschrijf hier wat u ziet, merkt of wilt weten over uw gebouw..." disabled></textarea><div class="brebo-knowledge-library__ask-action"><button type="button" disabled><span aria-hidden="true">➤</span> Vraag BREBO AI <span aria-hidden="true">→</span></button></div><p class="brebo-knowledge-library__assurance"><span aria-hidden="true">◇</span> Antwoorden worden gebaseerd op door BREBO beoordeelde bronnen.<br>Is betrouwbare informatie onvoldoende beschikbaar, dan zeggen we dat.</p></div>
          </div></div>
          <div class="brebo-knowledge-library__section"><div class="brebo-knowledge-library__section-head"><p class="brebo-knowledge-library__eyebrow">Onderwerpen</p><h2>Begin bij wat er aan uw gebouw speelt.</h2></div><div class="brebo-knowledge-library__grid">' . $topic_markup . '</div></div>
          <div class="brebo-knowledge-library__questions"><div><p class="brebo-knowledge-library__eyebrow">Veelgestelde vragen</p><h2>Herkenbare vragen uit de praktijk.</h2><p>Geen technisch jargon nodig. Begin bij wat u ziet, merkt of wilt weten en zoek van daaruit verder.</p></div><ul>' . $question_markup . '</ul></div>
          <div class="brebo-knowledge-library__promise"><div class="brebo-knowledge-library__promise-mark" aria-hidden="true">✓</div><div class="brebo-knowledge-library__promise-title"><p class="brebo-knowledge-library__eyebrow">Onze kwaliteitsbelofte</p><h2>Geen antwoord is beter dan een verzonnen antwoord.</h2></div><p>BREBO combineert eigen kennis met relevante externe bronnen. Informatie wordt eerst beoordeeld voordat deze als BREBO-kennis wordt gebruikt. Is een situatie te specifiek of ontbreekt voldoende onderbouwing, dan geven we dat duidelijk aan en vragen we gericht om meer informatie.</p></div>
          <div class="brebo-knowledge-library__routes"><article><div class="brebo-route-icon" aria-hidden="true">◉</div><div><p class="brebo-knowledge-library__eyebrow">Nieuwe vraag</p><h2>Komt u er niet uit?</h2><p>Leg kort uit wat er speelt. U hoeft de technische oorzaak of oplossing nog niet te kennen.</p><a class="brebo-knowledge-library__button" href="/contact/bericht">Neem contact op <span aria-hidden="true">→</span></a></div></article><article><div class="brebo-route-icon" aria-hidden="true">▦</div><div><p class="brebo-knowledge-library__eyebrow">Lopend project</p><h2>Heeft BREBO al een project bij u?</h2><p>Bekijk de projectcockpit voor uw vraag, melding of afspraak over een lopend project.</p><a class="brebo-knowledge-library__button brebo-knowledge-library__button--secondary" href="/klantenservice/projectvraag">Naar projectservice <span aria-hidden="true">→</span></a></div></article></div>
        </section>',
    ];
  }

  public function topic(string $topic): array {
    $content = $this->topicContent()[$topic] ?? NULL;
    if ($content === NULL) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
    return $this->knowledgePage($content['eyebrow'], $content['title'], $content['intro'], $content['sections']);
  }

  public function question(string $question): array {
    $content = $this->questionContent()[$question] ?? NULL;
    if ($content === NULL) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
    return $this->knowledgePage('Veelgestelde vraag', $content['title'], $content['intro'], $content['sections']);
  }

  private function knowledgePage(string $eyebrow, string $title, string $intro, array $sections): array {
    $body = '';
    foreach ($sections as $section) {
      $body .= '<section class="brebo-knowledge-article__section"><h2>' . $section['title'] . '</h2><p>' . $section['text'] . '</p></section>';
    }
    return ['#attached' => ['library' => ['brebo_customer_service/service']], '#markup' => '<article class="brebo-knowledge-article"><a class="brebo-knowledge-article__back" href="/klantenservice">← Terug naar klantenservice</a><header><p class="brebo-knowledge-library__eyebrow">' . $eyebrow . '</p><h1>' . $title . '</h1><p class="brebo-knowledge-article__lead">' . $intro . '</p></header><div class="brebo-knowledge-article__body">' . $body . '<aside><strong>Goed om te weten</strong><p>Dit is algemene BREBO-kennis. De juiste maatregel hangt altijd af van de werkelijke situatie, detaillering, materiaalstaat en het gebruik van het gebouw. Bij twijfel beoordelen we eerst wat er daadwerkelijk aan de hand is.</p></aside></div></article>'];
  }

  private function topicContent(): array {
    return [
      'kozijnen' => ['eyebrow' => 'Kozijnen', 'title' => 'Kozijnen beoordelen: behouden, herstellen of vervangen?', 'intro' => 'Een kozijn hoeft niet automatisch vervangen te worden zodra er schade of slijtage zichtbaar is. Eerst moet duidelijk zijn waar het probleem zit en of het technisch nog verantwoord te herstellen is.', 'sections' => [
        ['title' => 'Begin bij de oorzaak', 'text' => 'Loszittende verf, open verbindingen, houtaantasting, corrosie, slechte afdichtingen of tocht kunnen verschillende oorzaken hebben. Alleen het zichtbare gebrek repareren is onvoldoende wanneer vochtbelasting, detaillering of aansluitingen de werkelijke oorzaak zijn.'],
        ['title' => 'Herstellen kan zinvol zijn', 'text' => 'Plaatselijk herstel is vooral logisch wanneer de dragende en vormvaste delen voldoende gezond zijn en het gebrek duurzaam kan worden weggenomen. Daarbij kijken we ook naar glas, beslag, kierdichting en aansluitingen op de gevel.'],
        ['title' => 'Wanneer vervangen in beeld komt', 'text' => 'Vervanging wordt logischer wanneer schade omvangrijk of terugkerend is, meerdere functies tegelijk tekortschieten of herstel technisch en financieel niet meer in verhouding staat tot de resterende levensduur.'],
      ]],
      'glas' => ['eyebrow' => 'Glas', 'title' => 'Glas: comfort, isolatie en veiligheid hangen samen.', 'intro' => 'De keuze voor glas begint niet bij een productnaam maar bij de bestaande situatie: kozijn, sponning, afmetingen, belasting, gebruik en gewenste prestatie.', 'sections' => [
        ['title' => 'Condens vertelt waar het probleem zit', 'text' => 'Condens aan de kamerzijde, buitenzijde of tussen de glasbladen heeft niet dezelfde betekenis. Vocht tussen de glasbladen wijst doorgaans op verlies van de afdichting van isolatieglas.'],
        ['title' => 'Beter isolerend glas vraagt een systeemcontrole', 'text' => 'Bij vervanging door beter isolerend glas moet worden gecontroleerd of kozijn, sponning, glaslatten, beglazingssysteem en ventilatie geschikt zijn. Alleen het glas vervangen kan anders nieuwe problemen veroorzaken.'],
        ['title' => 'Veiligheid en belasting horen erbij', 'text' => 'Afmetingen, positie, doorvalrisico, windbelasting, thermische belasting en gebruik kunnen bepalend zijn voor de benodigde glasopbouw. Daarom beoordelen we glas nooit uitsluitend op isolatiewaarde.'],
      ]],
      'gevel-aansluitingen' => ['eyebrow' => 'Gevel & aansluitingen', 'title' => 'Veel gevelproblemen ontstaan juist op de aansluiting.', 'intro' => 'Een gevel bestaat uit materialen en bouwdelen die samen moeten functioneren. Juist overgangen rond kozijnen, dakranden, dorpels, kitnaden en doorvoeren verdienen aandacht.', 'sections' => [
        ['title' => 'Water volgt details', 'text' => 'Lekkage wordt niet altijd veroorzaakt op de plaats waar binnen vocht zichtbaar is. Water kan via naden, aansluitingen en achterliggende constructies een andere route afleggen.'],
        ['title' => 'Kit is geen oplossing voor ieder detail', 'text' => 'Kitvoegen hebben een functie en een beperkte levensduur. Een slechte bouwkundige aansluiting structureel dichtzetten met kit kan het werkelijke probleem maskeren in plaats van oplossen.'],
        ['title' => 'Isoleren verandert de bouwfysica', 'text' => 'Bij gevelisolatie moeten koudebruggen, vochttransport, ventilatie en aansluitdetails in samenhang worden bekeken. Een lokale verbetering kan anders een zwakke plek verplaatsen.'],
      ]],
      'onderhoud-renovatie' => ['eyebrow' => 'Onderhoud & renovatie', 'title' => 'Wanneer is onderhoud nog onderhoud en wanneer wordt het renovatie?', 'intro' => 'Onderhoud houdt een bouwdeel bruikbaar en beheersbaar. Wanneer dezelfde problemen blijven terugkomen of meerdere onderdelen tegelijk het einde van hun functionele levensduur naderen, verandert de vraag.', 'sections' => [
        ['title' => 'Niet ieder gebrek vraagt een groot project', 'text' => 'Gericht onderhoud blijft verstandig wanneer oorzaken duidelijk zijn, schade beperkt is en de resterende levensduur voldoende is. Daarmee voorkomt u onnodige vervanging.'],
        ['title' => 'Terugkerende kosten zijn een signaal', 'text' => 'Wanneer reparaties elkaar snel opvolgen, storingen toenemen of verschillende bouwdelen elkaar beïnvloeden, is het verstandig om niet langer per incident te beslissen maar het geheel te beoordelen.'],
        ['title' => 'Renovatie vraagt samenhang', 'text' => 'Bij renovatie worden prestaties, planning, bereikbaarheid, bewoners of gebruikers, veiligheid, kosten en toekomstige onderhoudsbehoefte gezamenlijk afgewogen.'],
      ]],
      'verduurzaming' => ['eyebrow' => 'Verduurzaming', 'title' => 'Verduurzamen begint met begrijpen hoe het gebouw nu werkt.', 'intro' => 'Meer isolatie of beter glas is niet automatisch de beste eerste maatregel. Comfort, ventilatie, vocht, aansluitingen en gebruik bepalen samen het resultaat.', 'sections' => [
        ['title' => 'Kijk naar het geheel', 'text' => 'Glas, kozijnen, gevel, dak, vloer, kierdichting, ventilatie en installaties beïnvloeden elkaar. Een maatregel moet daarom passen binnen de technische samenhang van het gebouw.'],
        ['title' => 'Comfort is meer dan energiegebruik', 'text' => 'Koudeval, tocht, oppervlaktetemperaturen, zoninstraling en ventilatie bepalen sterk hoe een ruimte wordt ervaren. Een energiemaatregel kan juist op deze punten veel waarde toevoegen.'],
        ['title' => 'Voorkom ongewenste neveneffecten', 'text' => 'Een gebouw luchtdichter of beter geïsoleerd maken verandert warmte- en vochtstromen. Daarom moet vooraf worden bekeken of ventilatie en details na de ingreep nog passend functioneren.'],
      ]],
      'gebouwbeheer' => ['eyebrow' => 'Gebouwbeheer', 'title' => 'Goed gebouwbeheer maakt onderhoud voorspelbaar.', 'intro' => 'Niet wachten tot iets uitvalt, maar weten wat de toestand is, welke risico’s ontstaan en wanneer ingrijpen technisch en financieel verstandig wordt.', 'sections' => [
        ['title' => 'Inspecteren is meer dan gebreken opsommen', 'text' => 'Een bruikbare inspectie koppelt waarnemingen aan oorzaak, gevolg, urgentie en een passende maatregel. Daarmee wordt informatie geschikt voor besluitvorming.'],
        ['title' => 'Plan op risico én samenhang', 'text' => 'Niet ieder gebrek heeft dezelfde prioriteit. Veiligheid, vervolgschade, functioneren, comfort, bereikbaarheid en kosten bepalen samen wanneer een maatregel moet worden uitgevoerd.'],
        ['title' => 'Bundelen kan veel verschil maken', 'text' => 'Werkzaamheden aan dezelfde gevel of hetzelfde bouwdeel kunnen vaak efficiënter worden gecombineerd. Dat beperkt dubbele bereikbaarheid, overlast en uitvoeringskosten.'],
      ]],
    ];
  }

  private function questionContent(): array {
    return [
      'kozijnen-herstellen-of-vervangen' => ['title' => 'Wanneer is een bestaand kozijn nog goed te herstellen?', 'intro' => 'Dat hangt vooral af van de omvang en oorzaak van de schade, de staat van de dragende delen en de resterende functie van het kozijn.', 'sections' => [
        ['title' => 'Herstel is geen cosmetische beoordeling', 'text' => 'Een verweerde afwerking kan relatief eenvoudig zijn, terwijl verborgen vocht of aantasting bij verbindingen veel belangrijker is. Eerst moet de technische staat worden vastgesteld.'],
        ['title' => 'Kijk ook naar de rest van het kozijn', 'text' => 'Glas, beslag, kierdichting, ventilatie en gevelaansluitingen bepalen mede of herstel nog een duurzame keuze is.'],
      ]],
      'condens-tussen-glasbladen' => ['title' => 'Wat betekent condens tussen de glasbladen?', 'intro' => 'Vocht of waas tussen de bladen van isolatieglas betekent meestal dat de afgesloten spouw niet meer intact is.', 'sections' => [
        ['title' => 'De isolatieglaseenheid is niet meer gesloten', 'text' => 'Wanneer de randafdichting zijn functie verliest kan vocht in de spouw komen. Dit is niet hetzelfde als condens aan de binnen- of buitenzijde van het glas.'],
        ['title' => 'Beoordeel meteen het omliggende systeem', 'text' => 'Bij vervanging is het verstandig ook sponning, glaslatten, ontwatering en beglazingsrubbers of kit te controleren zodat een onderliggende oorzaak niet wordt gemist.'],
      ]],
      'hrpp-bestaande-kozijnen' => ['title' => 'Heeft HR++ glas zin in bestaande kozijnen?', 'intro' => 'Dat kan zeker, maar alleen wanneer het bestaande kozijn en beglazingssysteem geschikt zijn voor de nieuwe glasopbouw.', 'sections' => [
        ['title' => 'Controleer maat, gewicht en sponning', 'text' => 'Dikker en zwaarder isolatieglas moet veilig kunnen worden opgenomen. Ook glaslatten, stelruimte, ondersteuning en afdichting moeten passen.'],
        ['title' => 'Bekijk comfort en ventilatie samen', 'text' => 'Beter glas vermindert warmteverlies via het raam, maar kan de vocht- en ventilatiebalans in een ruimte veranderen. Daarom hoort ventilatie bij de beoordeling.'],
      ]],
      'onderhoud-of-renovatie' => ['title' => 'Wanneer wordt terugkerend onderhoud een renovatievraag?', 'intro' => 'Wanneer onderhoud vooral symptomen blijft bestrijden, kosten zich opstapelen of meerdere onderdelen tegelijk structureel aandacht vragen.', 'sections' => [
        ['title' => 'Let op het patroon', 'text' => 'Steeds dezelfde lekkage, terugkerend schilderwerk door vochtbelasting of herhaald beslag- en kierprobleem kan aangeven dat de oorzaak breder ligt dan het afzonderlijke gebrek.'],
        ['title' => 'Vergelijk scenario’s', 'text' => 'Een goede keuze vergelijkt doorgaan met onderhoud, gedeeltelijk verbeteren en integraal renoveren op investering, risico, levensduur, overlast en toekomstige onderhoudskosten.'],
      ]],
    ];
  }

}
