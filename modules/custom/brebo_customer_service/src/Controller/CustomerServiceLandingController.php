<?php

declare(strict_types=1);

namespace Drupal\brebo_customer_service\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Builds the public customer-service landing page.
 */
final class CustomerServiceLandingController extends ControllerBase {

  public function page(): array {
    $situations = [
      ['icon' => '!', 'id' => 'schade', 'title' => 'Ik zie schade of slijtage', 'text' => 'Bijvoorbeeld houtrot, scheuren, corrosie, beschadigingen of onderdelen die zichtbaar achteruitgaan.', 'next' => 'Bekijk eerst waar de schade zit en of deze terugkomt. BREBO helpt daarna onderscheid maken tussen plaatselijk herstel, nader onderzoek en vervanging.', 'links' => [['label' => 'Kennis over kozijnen', 'href' => '/klantenservice/kennis/kozijnen'], ['label' => 'Kennis over gevels', 'href' => '/klantenservice/kennis/gevel-aansluitingen']]],
      ['icon' => '≈', 'id' => 'vocht', 'title' => 'Ik heb vocht, condens of lekkage', 'text' => 'Vocht is zichtbaar, glas beslaat, er lekt water of een aansluiting lijkt niet waterdicht.', 'next' => 'De plek waar vocht zichtbaar wordt is niet altijd de oorzaak. Begin daarom bij waar en wanneer het probleem optreedt.', 'links' => [['label' => 'Kennis over glas', 'href' => '/klantenservice/kennis/glas'], ['label' => 'Kennis over aansluitingen', 'href' => '/klantenservice/kennis/gevel-aansluitingen']]],
      ['icon' => '↔', 'id' => 'comfort', 'title' => 'Ik heb last van tocht of comfortproblemen', 'text' => 'Bijvoorbeeld tocht, koudeval, geluid, warmte of slecht sluitende ramen en deuren.', 'next' => 'Comfortproblemen kunnen uit glas, kozijnen, kierdichting, aansluitingen of ventilatie komen. We kijken daarom eerst naar de samenhang.', 'links' => [['label' => 'Kennis over kozijnen', 'href' => '/klantenservice/kennis/kozijnen'], ['label' => 'Kennis over glas', 'href' => '/klantenservice/kennis/glas']]],
      ['icon' => '◒', 'id' => 'verbeteren', 'title' => 'Ik wil mijn gebouw verbeteren of verduurzamen', 'text' => 'U wilt comfort, energieprestatie of kwaliteit verbeteren, maar de juiste maatregel staat nog niet vast.', 'next' => 'Begin niet automatisch bij een product. Eerst bepalen we welke verbetering past bij het gebouw en welke bouwdelen elkaar beïnvloeden.', 'links' => [['label' => 'Kennis over verduurzaming', 'href' => '/klantenservice/kennis/verduurzaming'], ['label' => 'Kennis over onderhoud & renovatie', 'href' => '/klantenservice/kennis/onderhoud-renovatie']]],
      ['icon' => '↻', 'id' => 'terugkerend', 'title' => 'Onderhoud of gebreken blijven terugkomen', 'text' => 'Reparaties volgen elkaar op, dezelfde klacht keert terug of meerdere onderdelen vragen tegelijk aandacht.', 'next' => 'Terugkerende problemen zijn een reden om niet alleen het afzonderlijke gebrek, maar oorzaak, samenhang en resterende levensduur te beoordelen.', 'links' => [['label' => 'Kennis over onderhoud & renovatie', 'href' => '/klantenservice/kennis/onderhoud-renovatie'], ['label' => 'Kennis over onderhoudsplanning', 'href' => '/klantenservice/kennis/gebouwbeheer']]],
      ['icon' => '▤', 'id' => 'plannen', 'title' => 'Ik wil onderhoud en investeringen vooruit plannen', 'text' => 'U wilt weten wat wanneer nodig is, welke risico’s prioriteit hebben en welke werkzaamheden logisch gecombineerd kunnen worden.', 'next' => 'Een bruikbare planning begint bij conditie, risico, resterende levensduur en samenhang. Daarna kunnen maatregelen in tijd en budget worden gezet.', 'links' => [['label' => 'Kennis over onderhoudsplanning', 'href' => '/klantenservice/kennis/gebouwbeheer'], ['label' => 'Kennis over onderhoud & renovatie', 'href' => '/klantenservice/kennis/onderhoud-renovatie']]],
    ];

    $topicMarkup = '';
    foreach ($situations as $situation) {
      $links = '';
      foreach ($situation['links'] as $link) {
        $links .= '<a href="' . $link['href'] . '">' . $link['label'] . ' <span aria-hidden="true">→</span></a>';
      }
      $topicMarkup .= '<details class="brebo-knowledge-card brebo-knowledge-card--situation" id="situatie-' . $situation['id'] . '"><summary><span class="brebo-knowledge-card__icon" aria-hidden="true">' . $situation['icon'] . '</span><div><h3>' . $situation['title'] . '</h3><p>' . $situation['text'] . '</p><span class="brebo-knowledge-card__action">Bekijk wat dit kan betekenen <span aria-hidden="true">↓</span></span></div></summary><div class="brebo-knowledge-card__followup"><p>' . $situation['next'] . '</p><div class="brebo-knowledge-card__links">' . $links . '</div><a class="brebo-knowledge-card__contact" href="/contact/bericht">Vraag BREBO om mee te kijken <span aria-hidden="true">→</span></a></div></details>';
    }

    return [
      '#attached' => ['library' => ['brebo_customer_service/service']],
      '#markup' => '
        <section class="brebo-knowledge-library">
          <div class="brebo-knowledge-library__hero"><div class="brebo-knowledge-library__hero-inner">
            <div class="brebo-knowledge-library__hero-copy brebo-knowledge-library__hero-copy--full"><p class="brebo-knowledge-library__eyebrow">BREBO Klantenservice</p><h1>Waar kunnen we u mee helpen?</h1><p class="brebo-knowledge-library__lead">Kies wat bij uw situatie past. Voor een lopend project, een servicevraag, technische informatie of een nieuwe opgave komt u direct op de juiste plek.</p></div>
          </div></div>
          <div class="brebo-knowledge-library__routes">
            <article><div class="brebo-route-icon" aria-hidden="true">▦</div><div><p class="brebo-knowledge-library__eyebrow">Lopend project</p><h2>Vraag over uw project?</h2><p>Stel een vraag, geef een wijziging door of leg een afspraak over een lopend BREBO-project vast.</p><a class="brebo-knowledge-library__button" href="/klantenservice/projectvraag">Naar projectservice <span aria-hidden="true">→</span></a></div></article>
            <article><div class="brebo-route-icon" aria-hidden="true">!</div><div><p class="brebo-knowledge-library__eyebrow">Service & melding</p><h2>Iets melden over uitgevoerd werk?</h2><p>Meld een servicepunt, gebrek of andere vraag die hoort bij eerder door BREBO uitgevoerd werk.</p><a class="brebo-knowledge-library__button brebo-knowledge-library__button--secondary" href="/contact/bericht">Service melden <span aria-hidden="true">→</span></a></div></article>
            <article><div class="brebo-route-icon" aria-hidden="true">◇</div><div><p class="brebo-knowledge-library__eyebrow">Kennis</p><h2>Technische informatie zoeken?</h2><p>Bekijk BREBO-kennis over kozijnen, glas, gevels, onderhoud, renovatie, verduurzaming, inspecties en onderhoudsplanning.</p><a class="brebo-knowledge-library__button brebo-knowledge-library__button--secondary" href="#brebo-kennis">Bekijk kennis <span aria-hidden="true">→</span></a></div></article>
            <article><div class="brebo-route-icon" aria-hidden="true">◉</div><div><p class="brebo-knowledge-library__eyebrow">Nieuwe opgave</p><h2>Nog geen BREBO-project?</h2><p>Wilt u kozijnen of glas vervangen, renoveren, verduurzamen of advies? Vertel ons kort wat u wilt aanpakken.</p><a class="brebo-knowledge-library__button brebo-knowledge-library__button--secondary" href="/contact/bericht">Bespreek uw opgave <span aria-hidden="true">→</span></a></div></article>
          </div>
          <div class="brebo-knowledge-library__section" id="brebo-kennis"><div class="brebo-knowledge-library__section-head"><p class="brebo-knowledge-library__eyebrow">BREBO Kennis</p><h2>Wat wilt u aan uw gebouw aanpakken?</h2><p>U hoeft de technische oorzaak of oplossing niet te kennen. Kies wat u ziet, merkt of wilt bereiken. Van daaruit helpen we u verder.</p></div><div class="brebo-knowledge-library__grid">' . $topicMarkup . '</div></div>
          <div class="brebo-knowledge-library__promise"><div class="brebo-knowledge-library__promise-mark" aria-hidden="true">✓</div><div class="brebo-knowledge-library__promise-title"><p class="brebo-knowledge-library__eyebrow">Onze kwaliteitsbelofte</p><h2>We maken duidelijk wat we weten en wat we aannemen.</h2></div><p>BREBO gebruikt eigen vakkennis, beschikbare projectinformatie en relevante externe bronnen. Ontbreekt informatie, dan kunnen we werken met een aanname. Die benoemen we als zodanig. Is er onvoldoende informatie om een verantwoorde conclusie te trekken, dan geven we dat aan. We presenteren een aanname nooit als een feit.</p></div>
        </section>',
    ];
  }

}
