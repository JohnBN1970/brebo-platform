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
            <p class="brebo-knowledge-library__lead">Beschrijf wat u aan uw gebouw merkt of waar u meer over wilt weten. Zoek in onze kennis over kozijnen, glas, gevels, onderhoud, renovatie en gebouwbeheer.</p>

            <div class="brebo-knowledge-library__ask" aria-label="BREBO kennisassistent in voorbereiding">
              <label for="brebo-knowledge-question">Stel uw vraag aan BREBO</label>
              <div class="brebo-knowledge-library__ask-row">
                <input id="brebo-knowledge-question" type="text" placeholder="Bijvoorbeeld: er zit condens tussen mijn dubbele beglazing. Wat betekent dat?" disabled>
                <button type="button" disabled>Vraag BREBO AI <span aria-hidden="true">→</span></button>
              </div>
              <p class="brebo-knowledge-library__assurance"><span aria-hidden="true">✓</span> Antwoorden worden gebaseerd op door BREBO beoordeelde bronnen. Is betrouwbare informatie onvoldoende beschikbaar, dan zeggen we dat.</p>
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
              <p>Geen technisch jargon nodig. Begin bij wat u ziet, merkt of wilt weten en zoek van daaruit verder.</p>
            </div>
            <ul>' . $question_markup . '</ul>
          </div>

          <div class="brebo-knowledge-library__promise">
            <div class="brebo-knowledge-library__promise-mark" aria-hidden="true">✓</div>
            <div>
              <p class="brebo-knowledge-library__eyebrow">Onze kwaliteitsbelofte</p>
              <h2>Geen antwoord is beter dan een verzonnen antwoord.</h2>
              <p>BREBO combineert eigen kennis met relevante externe bronnen. Informatie wordt eerst beoordeeld voordat deze als BREBO-kennis wordt gebruikt. Is een situatie te specifiek of ontbreekt voldoende onderbouwing, dan geven we dat duidelijk aan en vragen we gericht om meer informatie.</p>
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
