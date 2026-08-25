<?php

declare(strict_types=1);

namespace Drupal\brebo_customer_service\Controller;

use Drupal\Core\Controller\ControllerBase;

final class KnowledgeLibraryController extends ControllerBase {

  public function page(): array {
    $topics = [
      ['title' => 'Kozijnen', 'text' => 'Onderhoud, herstel, levensduur en de afweging tussen behouden, verbeteren en vervangen.'],
      ['title' => 'Glas', 'text' => 'HR++, triple glas, veiligheid, condens, lekkage, geluid en zonbelasting.'],
      ['title' => 'Gevel & aansluitingen', 'text' => 'Kitwerk, aansluitdetails, lekkage, koudebruggen, isolatie en samenhang met kozijn en glas.'],
      ['title' => 'Onderhoud & renovatie', 'text' => 'Wanneer ingrijpen verstandig is en hoe maatregelen projectmatig en in samenhang worden georganiseerd.'],
      ['title' => 'Verduurzaming', 'text' => 'Comfort, energie en de technische samenhang tussen glas, kozijnen, gevel en gebruik.'],
      ['title' => 'Gebouwbeheer', 'text' => 'Inspecties, onderhoudsplanning, risico, conditie en onderbouwde besluitvorming.'],
    ];

    $questions = [
      'Wanneer is een bestaand kozijn nog goed te herstellen?',
      'Wat betekent condens tussen de glasbladen?',
      'Heeft HR++ glas zin in bestaande kozijnen?',
      'Wanneer wordt terugkerend onderhoud een renovatievraag?',
    ];

    $topic_markup = '';
    foreach ($topics as $index => $topic) {
      $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
      $topic_markup .= '<article class="brebo-knowledge-card"><span>' . $number . '</span><h3>' . $topic['title'] . '</h3><p>' . $topic['text'] . '</p><a href="/kennis">Bekijk kennis <span aria-hidden="true">→</span></a></article>';
    }

    $question_markup = '';
    foreach ($questions as $question) {
      $question_markup .= '<li><a href="/kennis">' . $question . '<span aria-hidden="true">→</span></a></li>';
    }

    return [
      '#attached' => [
        'library' => ['brebo_customer_service/service'],
      ],
      '#markup' => '
        <section class="brebo-knowledge-library">
          <div class="brebo-knowledge-library__hero">
            <p class="brebo-knowledge-library__eyebrow">BREBO Kennisbibliotheek</p>
            <h1>Waar kunnen we u mee helpen?</h1>
            <p class="brebo-knowledge-library__lead">Stel uw vraag over uw gebouw, kozijnen, glas, onderhoud of renovatie. BREBO geeft alleen antwoorden die voldoende door beschikbare en beoordeelde kennis worden ondersteund.</p>

            <div class="brebo-knowledge-library__ask" aria-label="BREBO kennisassistent in voorbereiding">
              <label for="brebo-knowledge-question">Uw vraag</label>
              <div class="brebo-knowledge-library__ask-row">
                <input id="brebo-knowledge-question" type="text" placeholder="Bijvoorbeeld: er zit condens tussen mijn dubbele beglazing. Wat betekent dat?" disabled>
                <button type="button" disabled>Vraag BREBO AI</button>
              </div>
              <p><strong>AI-koppeling in voorbereiding.</strong> We tonen hier pas antwoorden zodra de bron- en goedkeuringslaag gereed is. Geen voldoende onderbouwing betekent geen verzonnen antwoord.</p>
            </div>
          </div>

          <div class="brebo-knowledge-library__section">
            <div class="brebo-knowledge-library__section-head">
              <p class="brebo-knowledge-library__eyebrow">Onderwerpen</p>
              <h2>Begin bij wat er aan uw gebouw speelt.</h2>
            </div>
            <div class="brebo-knowledge-library__grid">' . $topic_markup . '</div>
          </div>

          <div class="brebo-knowledge-library__questions">
            <div>
              <p class="brebo-knowledge-library__eyebrow">Veelgestelde vragen</p>
              <h2>Herkenbare vragen uit de praktijk.</h2>
              <p>De bibliotheek groeit vanuit echte gebouwvragen. Nieuwe kennis wordt pas publiek gebruikt nadat BREBO de inhoud en bronnen heeft beoordeeld.</p>
            </div>
            <ul>' . $question_markup . '</ul>
          </div>

          <div class="brebo-knowledge-library__trust">
            <p class="brebo-knowledge-library__eyebrow">Hoe BREBO met kennis omgaat</p>
            <h2>Goed gevonden. Goed beoordeeld. Pas dan gebruikt.</h2>
            <div class="brebo-knowledge-library__trust-grid">
              <article><strong>01</strong><h3>Bron vinden</h3><p>BREBO AI mag informatie verzamelen uit eigen kennis, fabrikanten, overheden, brancheorganisaties en andere relevante bronnen.</p></article>
              <article><strong>02</strong><h3>Beoordelen</h3><p>Een internetbron wordt nooit automatisch BREBO-kennis. De bron, actualiteit en toepasbaarheid worden eerst beoordeeld.</p></article>
              <article><strong>03</strong><h3>Goedkeuren</h3><p>Alleen goedgekeurde kennis mag worden gebruikt voor publieke antwoorden. Bij onvoldoende bewijs wordt geen technisch antwoord verzonnen.</p></article>
            </div>
          </div>

          <div class="brebo-knowledge-library__routes">
            <article><p class="brebo-knowledge-library__eyebrow">Nieuwe vraag</p><h2>Komt u er niet uit?</h2><p>Leg kort uit wat er speelt. U hoeft de technische oorzaak of oplossing nog niet te kennen.</p><a class="brebo-knowledge-library__button" href="/contact/bericht">Neem contact op <span aria-hidden="true">→</span></a></article>
            <article><p class="brebo-knowledge-library__eyebrow">Lopend project</p><h2>Heeft BREBO al een project bij u?</h2><p>Gebruik de projectroute voor een vraag, melding of afspraak over een lopend project.</p><a class="brebo-knowledge-library__button brebo-knowledge-library__button--secondary" href="/klantenservice/projectvraag">Naar projectservice <span aria-hidden="true">→</span></a></article>
          </div>
        </section>',
    ];
  }

}
