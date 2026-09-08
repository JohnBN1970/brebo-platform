<?php

declare(strict_types=1);

namespace Drupal\brebo_customer_service\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Builds the public customer-service landing page.
 */
final class CustomerServiceLandingController extends ControllerBase {

  public function page(): array {
    $topics = [
      ['icon' => '▦', 'slug' => 'kozijnen', 'title' => 'Kozijnen', 'text' => 'Onderhoud, herstel, levensduur en de afweging tussen behouden, verbeteren en vervangen.'],
      ['icon' => '◩', 'slug' => 'glas', 'title' => 'Glas', 'text' => 'Isolatieglas, veiligheid, condens, lekkage, geluid, zonbelasting en de aansluiting op het kozijn.'],
      ['icon' => '⌗', 'slug' => 'gevel-aansluitingen', 'title' => 'Gevel & aansluitingen', 'text' => 'Kitwerk, aansluitdetails, lekkage, koudebruggen, isolatie en de samenhang tussen gevel, kozijn en glas.'],
      ['icon' => '⚒', 'slug' => 'onderhoud-renovatie', 'title' => 'Onderhoud & renovatie', 'text' => 'Wanneer plaatselijk onderhoud nog logisch is en wanneer een samenhangende renovatie verstandiger wordt.'],
      ['icon' => '◒', 'slug' => 'verduurzaming', 'title' => 'Verduurzaming', 'text' => 'Comfort, energiegebruik en de technische samenhang tussen glas, kozijnen, gevel, ventilatie en gebruik.'],
      ['icon' => '▤', 'slug' => 'gebouwbeheer', 'title' => 'Inspectie & onderhoudsplanning', 'text' => 'Inspecties, conditie, risico, MJOP en onderbouwde onderhoudskeuzes voor de komende jaren.'],
    ];

    $topicMarkup = '';
    foreach ($topics as $topic) {
      $topicMarkup .= '<article class="brebo-knowledge-card"><span class="brebo-knowledge-card__icon" aria-hidden="true">' . $topic['icon'] . '</span><div><h3>' . $topic['title'] . '</h3><p>' . $topic['text'] . '</p><a href="/klantenservice/kennis/' . $topic['slug'] . '">Bekijk kennis <span aria-hidden="true">→</span></a></div></article>';
    }

    return [
      '#attached' => ['library' => ['brebo_customer_service/service']],
      '#markup' => '
        <section class="brebo-knowledge-library">
          <div class="brebo-knowledge-library__hero"><div class="brebo-knowledge-library__hero-inner">
            <div class="brebo-knowledge-library__hero-copy"><p class="brebo-knowledge-library__eyebrow">BREBO Klantenservice</p><h1>Waar kunnen we u mee helpen?</h1><p class="brebo-knowledge-library__lead">Kies wat bij uw situatie past. Voor een lopend project, een servicevraag, technische informatie of een nieuwe opgave komt u direct op de juiste plek.</p></div>
          </div></div>
          <div class="brebo-knowledge-library__routes">
            <article><div class="brebo-route-icon" aria-hidden="true">▦</div><div><p class="brebo-knowledge-library__eyebrow">Lopend project</p><h2>Vraag over uw project?</h2><p>Stel een vraag, geef een wijziging door of leg een afspraak over een lopend BREBO-project vast.</p><a class="brebo-knowledge-library__button" href="/klantenservice/projectvraag">Naar projectservice <span aria-hidden="true">→</span></a></div></article>
            <article><div class="brebo-route-icon" aria-hidden="true">!</div><div><p class="brebo-knowledge-library__eyebrow">Service & melding</p><h2>Iets melden over uitgevoerd werk?</h2><p>Meld een servicepunt, gebrek of andere vraag die hoort bij een bestaand of uitgevoerd BREBO-project.</p><a class="brebo-knowledge-library__button brebo-knowledge-library__button--secondary" href="/klantenservice/projectvraag">Service melden <span aria-hidden="true">→</span></a></div></article>
            <article><div class="brebo-route-icon" aria-hidden="true">◇</div><div><p class="brebo-knowledge-library__eyebrow">Kennis</p><h2>Technische informatie zoeken?</h2><p>Bekijk BREBO-kennis over kozijnen, glas, gevels, onderhoud, renovatie, verduurzaming, inspecties en onderhoudsplanning.</p><a class="brebo-knowledge-library__button brebo-knowledge-library__button--secondary" href="#brebo-kennis">Bekijk kennis <span aria-hidden="true">→</span></a></div></article>
            <article><div class="brebo-route-icon" aria-hidden="true">◉</div><div><p class="brebo-knowledge-library__eyebrow">Nieuwe opgave</p><h2>Nog geen BREBO-project?</h2><p>Wilt u kozijnen of glas vervangen, renoveren, verduurzamen of advies? Vertel ons kort wat u wilt aanpakken.</p><a class="brebo-knowledge-library__button brebo-knowledge-library__button--secondary" href="/contact/bericht">Bespreek uw opgave <span aria-hidden="true">→</span></a></div></article>
          </div>
          <div class="brebo-knowledge-library__section" id="brebo-kennis"><div class="brebo-knowledge-library__section-head"><p class="brebo-knowledge-library__eyebrow">BREBO Kennis</p><h2>Begin bij wat er aan uw gebouw speelt.</h2><p>Onze kennis ondersteunt de keuze en de realisatie. U hoeft de technische oorzaak of oplossing nog niet te kennen.</p></div><div class="brebo-knowledge-library__grid">' . $topicMarkup . '</div></div>
          <div class="brebo-knowledge-library__promise"><div class="brebo-knowledge-library__promise-mark" aria-hidden="true">✓</div><div class="brebo-knowledge-library__promise-title"><p class="brebo-knowledge-library__eyebrow">Onze kwaliteitsbelofte</p><h2>Geen antwoord is beter dan een verzonnen antwoord.</h2></div><p>BREBO combineert eigen kennis met relevante externe bronnen. Informatie wordt beoordeeld voordat deze als BREBO-kennis wordt gebruikt. Is een situatie te specifiek of ontbreekt voldoende onderbouwing, dan geven we dat duidelijk aan.</p></div>
        </section>',
    ];
  }

}
