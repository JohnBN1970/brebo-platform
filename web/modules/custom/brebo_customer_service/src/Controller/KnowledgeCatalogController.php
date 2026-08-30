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
    return [
      '#attached' => ['library' => ['brebo_customer_service/service']],
      '#markup' => '<main class="brebo-knowledge-index">'
        . '<a class="brebo-knowledge-article__back" href="/klantenservice">← Terug naar klantenservice</a>'
        . '<header><p class="brebo-knowledge-library__eyebrow">Kennisgebied</p><h1>' . $topics[$topic]['title'] . '</h1><p>' . $topics[$topic]['intro'] . '</p></header>'
        . '<div class="brebo-knowledge-index__grid">' . $cards . '</div></main>',
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

  private function guidance(string $slug): string {
    $specific = [
      'condens-tussen-glasbladen' => ['Bepaal eerst waar de condens zit', 'Condens aan de binnenzijde, buitenzijde en tussen de glasbladen heeft niet dezelfde betekenis. Vocht of waas tussen de glasbladen bevindt zich in de afgesloten spouw van het isolatieglas.', 'Kijk ook naar de beglazing als systeem', 'Bij vervanging is het verstandig ook randafdichting, glasoplegging, sponning en vochtbelasting rondom de ruit te beoordelen.'],
      'kozijnen-herstellen-of-vervangen' => ['Begin bij oorzaak en omvang', 'Afbladderende verf, open verbindingen of plaatselijke aantasting zijn niet automatisch een reden voor volledige vervanging. Eerst moet duidelijk zijn welk deel is aangetast en waarom.', 'Vergelijk herstel met resterende levensduur', 'Herstel is vooral logisch wanneer voldoende gezond materiaal aanwezig blijft en de oorzaak duurzaam kan worden weggenomen.'],
      'hrpp-bestaande-kozijnen' => ['Controleer eerst het bestaande kozijn', 'Sponning, glaslatten, ondersteuning, kierdichting en staat van het kozijn bepalen mede of een andere glasopbouw passend kan worden aangebracht.', 'Neem ventilatie mee', 'Een betere isolatie en luchtdichtheid veranderen het comfort en de vochtbalans. Gecontroleerde ventilatie blijft daarom onderdeel van de beoordeling.'],
      'onderhoud-of-renovatie' => ['Kijk naar herhaling en samenhang', 'Wanneer dezelfde reparaties terugkomen of meerdere bouwdelen elkaar beïnvloeden, wordt alleen incidentgericht herstellen steeds minder logisch.', 'Vergelijk scenario’s', 'Directe kosten zijn niet het enige criterium. Ook resterende levensduur, gevolgschade, bereikbaarheid, hinder en toekomstige onderhoudsbehoefte horen in de afweging.'],
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
