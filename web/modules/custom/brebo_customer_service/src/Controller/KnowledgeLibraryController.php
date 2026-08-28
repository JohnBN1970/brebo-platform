<?php

declare(strict_types=1);

namespace Drupal\brebo_customer_service\Controller;

use Drupal\Core\Controller\ControllerBase;

final class KnowledgeLibraryController extends ControllerBase {

  public function page(): array {
    $topics = [
      ['icon' => '▦', 'title' => 'Kozijnen', 'text' => 'Onderhoud, herstel, levensduur en de afweging tussen behouden, verbeteren en vervangen.'],
      ['icon' => '◩', 'title' => 'Glas', 'text' => 'HR++, triple glas, veiligheid, condens, lekkage, geluid en zonbelasting.'],
      ['icon' => '⌗', 'title' => 'Gevel & aansluitingen', 'text' => 'Kitwerk, aansluitdetails, lekkage, koudebruggen, isolatie en samenhang met kozijn en glas.'],
      ['icon' => '⚒', 'title' => 'Onderhoud & renovatie', 'text' => 'Wanneer ingrijpen verstandig is en hoe maatregelen projectmatig en in samenhang worden georganiseerd.'],
      ['icon' => '◒', 'title' => 'Verduurzaming', 'text' => 'Comfort, energie en de technische samenhang tussen glas, kozijnen, gevel en gebruik.'],
      ['icon' => '▤', 'title' => 'Gebouwbeheer', 'text' => 'Inspecties, onderhoudsplanning, risico, conditie en onderbouwde besluitvorming.'],
    ];

    $questions = [
      'Wanneer is een bestaand kozijn nog goed te herstellen?',
      'Wat betekent condens tussen de glasbladen?',
      'Heeft HR++ glas zin in bestaande kozijnen?',
      'Wanneer wordt terugkerend onderhoud een renovatievraag?',
    ];

    $topic_markup = '';
    foreach ($topics as $topic) {
      $topic_markup .= '<article class="brebo-knowledge-card"><span class="brebo-knowledge-card__icon" aria-hidden="true">' . $topic['icon'] . '</span><div><h3>' . $topic['title'] . '</h3><p>' . $topic['text'] . '</p><a href="/kennis">Bekijk kennis <span aria-hidden="true">→</span></a></div></article>';
    }

    $question_markup = '';
    foreach ($questions as $question) {
      $question_markup .= '<li><a href="/kennis">' . $question . '<span aria-hidden="true">＋</span></a></li>';
    }

    return [
      '#attached' => [
        'library' => ['brebo_customer_service/service'],
      ],
      '#markup' => '
        <section class="brebo-knowledge-library">
          <div class="brebo-knowledge-library__hero">
            <div class="brebo-knowledge-library__hero-inner">
              <div class="brebo-knowledge-library__hero-copy">
                <p class="brebo-knowledge-library__eyebrow">BREBO Kennisbibliotheek</p>
                <h1>Waar kunnen we u mee helpen?</h1>
                <p class="brebo-knowledge-library__lead">Beschrijf wat u aan uw gebouw merkt of waar u meer over wilt weten. Zoek in onze kennis over kozijnen, glas, gevels, onderhoud, renovatie en gebouwbeheer.</p>
              </div>
              <div class="brebo-knowledge-library__ask" aria-label="BREBO kennisassistent in voorbereiding">
                <div class="brebo-knowledge-library__ask-title"><span aria-hidden="true">▣</span><label for="brebo-knowledge-question">Stel uw vraag aan BREBO</label></div>
                <textarea id="brebo-knowledge-question" rows="4" placeholder="Beschrijf hier wat u ziet, merkt of wilt weten over uw gebouw..." disabled></textarea>
                <div class="brebo-knowledge-library__ask-action"><button type="button" disabled><span aria-hidden="true">➤</span> Vraag BREBO AI <span aria-hidden="true">→</span></button></div>
                <p class="brebo-knowledge-library__assurance"><span aria-hidden="true">◇</span> Antwoorden worden gebaseerd op door BREBO beoordeelde bronnen.<br>Is betrouwbare informatie onvoldoende beschikbaar, dan zeggen we dat.</p>
              </div>
            </div>
          </div>

          <div class="brebo-knowledge-library__section">
            <div class="brebo-knowledge-library__section-head"><p class="brebo-knowledge-library__eyebrow">Onderwerpen</p><h2>Begin bij wat er aan uw gebouw speelt.</h2></div>
            <div class="brebo-knowledge-library__grid">' . $topic_markup . '</div>
          </div>

          <div class="brebo-knowledge-library__questions">
            <div><p class="brebo-knowledge-library__eyebrow">Veelgestelde vragen</p><h2>Herkenbare vragen uit de praktijk.</h2><p>Geen technisch jargon nodig. Begin bij wat u ziet, merkt of wilt weten en zoek van daaruit verder.</p></div>
            <ul>' . $question_markup . '</ul>
          </div>

          <div class="brebo-knowledge-library__promise">
            <div class="brebo-knowledge-library__promise-mark" aria-hidden="true">✓</div>
            <div class="brebo-knowledge-library__promise-title"><p class="brebo-knowledge-library__eyebrow">Onze kwaliteitsbelofte</p><h2>Geen antwoord is beter dan een verzonnen antwoord.</h2></div>
            <p>BREBO combineert eigen kennis met relevante externe bronnen. Informatie wordt eerst beoordeeld voordat deze als BREBO-kennis wordt gebruikt. Is een situatie te specifiek of ontbreekt voldoende onderbouwing, dan geven we dat duidelijk aan en vragen we gericht om meer informatie.</p>
          </div>

          <div class="brebo-knowledge-library__routes">
            <article><div class="brebo-route-icon" aria-hidden="true">◉</div><div><p class="brebo-knowledge-library__eyebrow">Nieuwe vraag</p><h2>Komt u er niet uit?</h2><p>Leg kort uit wat er speelt. U hoeft de technische oorzaak of oplossing nog niet te kennen.</p><a class="brebo-knowledge-library__button" href="/contact/bericht">Neem contact op <span aria-hidden="true">→</span></a></div></article>
            <article><div class="brebo-route-icon" aria-hidden="true">▦</div><div><p class="brebo-knowledge-library__eyebrow">Lopend project</p><h2>Heeft BREBO al een project bij u?</h2><p>Bekijk de projectcockpit voor uw vraag, melding of afspraak over een lopend project.</p><a class="brebo-knowledge-library__button brebo-knowledge-library__button--secondary" href="/klantenservice/projectvraag">Naar projectservice <span aria-hidden="true">→</span></a></div></article>
          </div>
        </section>',
    ];
  }

}
