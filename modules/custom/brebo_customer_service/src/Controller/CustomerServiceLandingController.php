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
      ['icon' => '!', 'id' => 'schade', 'title' => 'Ik zie schade of slijtage', 'text' => 'Bijvoorbeeld houtrot, scheuren, corrosie, beschadigingen of onderdelen die zichtbaar achteruitgaan.', 'question' => 'Waar ziet u de schade?', 'choices' => [
        ['label' => 'Aan kozijnen, ramen of deuren', 'href' => '/klantenservice/kennis/kozijnen'],
        ['label' => 'Aan metselwerk, voegen of gevelaansluitingen', 'href' => '/klantenservice/kennis/gevel-aansluitingen'],
        ['label' => 'Aan glas of beglazing', 'href' => '/klantenservice/kennis/glas'],
        ['label' => 'Op meerdere plekken of bouwdelen', 'href' => '/klantenservice/kennis/onderhoud-renovatie'],
      ], 'contact' => 'Laat BREBO de schade beoordelen', 'contact_href' => '/contact/bericht?route=probleem&context=schade-slijtage'],
      ['icon' => '≈', 'id' => 'vocht', 'title' => 'Ik heb vocht, condens of lekkage', 'text' => 'Vocht is zichtbaar, glas beslaat, er lekt water of een aansluiting lijkt niet waterdicht.', 'question' => 'Waar merkt u het probleem?', 'choices' => [
        ['label' => 'Tussen de glasbladen', 'href' => '/klantenservice/kennis/glas'],
        ['label' => 'Aan het glas of bij de glasrand', 'href' => '/klantenservice/kennis/glas'],
        ['label' => 'Rond het kozijn of de gevelaansluiting', 'href' => '/klantenservice/kennis/gevel-aansluitingen'],
        ['label' => 'Ik kan niet bepalen waar het vandaan komt', 'href' => '/klantenservice/kennis/gevel-aansluitingen'],
      ], 'contact' => 'Laat BREBO de situatie beoordelen', 'contact_href' => '/contact/bericht?route=probleem&context=lekkage-tocht'],
      ['icon' => '↔', 'id' => 'comfort', 'title' => 'Ik heb last van tocht of comfortproblemen', 'text' => 'Bijvoorbeeld tocht, koudeval, geluid, warmte of slecht sluitende ramen en deuren.', 'question' => 'Wat merkt u vooral?', 'choices' => [
        ['label' => 'Tocht langs ramen, deuren of kozijnen', 'href' => '/klantenservice/kennis/kozijnen'],
        ['label' => 'Kou of onvoldoende isolatie bij glas', 'href' => '/klantenservice/kennis/glas'],
        ['label' => 'Geluid van buiten', 'href' => '/klantenservice/kennis/glas'],
        ['label' => 'Warmte of oververhitting', 'href' => '/klantenservice/kennis/verduurzaming'],
        ['label' => 'Een raam of deur sluit slecht', 'href' => '/klantenservice/kennis/kozijnen'],
      ], 'contact' => 'Bespreek uw comfortprobleem', 'contact_href' => '/contact/bericht?route=probleem&context=functioneren'],
      ['icon' => '◒', 'id' => 'verbeteren', 'title' => 'Ik wil mijn gebouw verbeteren of verduurzamen', 'text' => 'U wilt comfort, energieprestatie of kwaliteit verbeteren, maar de juiste maatregel staat nog niet vast.', 'question' => 'Wat wilt u vooral bereiken?', 'choices' => [
        ['label' => 'Minder energieverlies', 'href' => '/klantenservice/kennis/verduurzaming'],
        ['label' => 'Beter comfort', 'href' => '/klantenservice/kennis/verduurzaming'],
        ['label' => 'Glas of kozijnen verbeteren', 'href' => '/klantenservice/kennis/verduurzaming'],
        ['label' => 'Verduurzaming combineren met gepland onderhoud', 'href' => '/klantenservice/kennis/verduurzaming'],
      ], 'contact' => 'Bespreek wat u wilt verbeteren', 'contact_href' => '/contact/bericht?route=kozijnen-glas&context=vervangen-verduurzamen'],
      ['icon' => '↻', 'id' => 'terugkerend', 'title' => 'Onderhoud of gebreken blijven terugkomen', 'text' => 'Reparaties volgen elkaar op, dezelfde klacht keert terug of meerdere onderdelen vragen tegelijk aandacht.', 'question' => 'Wat komt steeds terug?', 'choices' => [
        ['label' => 'Lekkage of vocht', 'href' => '/klantenservice/kennis/gevel-aansluitingen'],
        ['label' => 'Schilderwerk of kozijnschade', 'href' => '/klantenservice/kennis/kozijnen'],
        ['label' => 'Losse reparaties aan meerdere onderdelen', 'href' => '/klantenservice/kennis/onderhoud-renovatie'],
        ['label' => 'Onderhoudsmomenten lopen door elkaar', 'href' => '/klantenservice/kennis/gebouwbeheer'],
      ], 'contact' => 'Laat BREBO naar de samenhang kijken', 'contact_href' => '/contact/bericht?route=kozijnen-glas&context=advies-nodig'],
      ['icon' => '▤', 'id' => 'plannen', 'title' => 'Ik wil onderhoud en investeringen vooruit plannen', 'text' => 'U wilt weten wat wanneer nodig is, welke risico’s prioriteit hebben en welke werkzaamheden logisch gecombineerd kunnen worden.', 'question' => 'Waar zoekt u vooral inzicht in?', 'choices' => [
        ['label' => 'De technische staat van het gebouw', 'href' => '/klantenservice/kennis/gebouwbeheer'],
        ['label' => 'Een MJOP of onderhoudsplanning', 'href' => '/klantenservice/kennis/gebouwbeheer'],
        ['label' => 'Prioriteiten en urgente gebreken', 'href' => '/klantenservice/kennis/gebouwbeheer'],
        ['label' => 'Een realistisch onderhoudsbudget', 'href' => '/klantenservice/kennis/gebouwbeheer'],
        ['label' => 'Werkzaamheden logisch combineren', 'href' => '/klantenservice/kennis/gebouwbeheer'],
      ], 'contact' => 'Bespreek uw onderhoudsplanning', 'contact_href' => '/contact/bericht?route=orientatie&context=onderhoudsplanning'],
    ];

    $topicMarkup = '';
    foreach ($situations as $situation) {
      $choices = '';
      foreach ($situation['choices'] as $choice) {
        $choices .= '<a class="brebo-knowledge-card__choice" href="' . $choice['href'] . '"><span>' . $choice['label'] . '</span><span aria-hidden="true">→</span></a>';
      }
      $topicMarkup .= '<details class="brebo-knowledge-card brebo-knowledge-card--situation" id="situatie-' . $situation['id'] . '"><summary><span class="brebo-knowledge-card__icon" aria-hidden="true">' . $situation['icon'] . '</span><div><h3>' . $situation['title'] . '</h3><p>' . $situation['text'] . '</p><span class="brebo-knowledge-card__action">Kies wat het beste past <span aria-hidden="true">↓</span></span></div></summary><div class="brebo-knowledge-card__followup"><h4>' . $situation['question'] . '</h4><div class="brebo-knowledge-card__choices">' . $choices . '</div><a class="brebo-knowledge-card__contact" href="' . $situation['contact_href'] . '">' . $situation['contact'] . ' <span aria-hidden="true">→</span></a></div></details>';
    }

    return [
      '#attached' => ['library' => ['brebo_customer_service/service']],
      '#markup' => '
        <section class="brebo-knowledge-library">
          <div class="brebo-knowledge-library__hero"><div class="brebo-knowledge-library__hero-inner">
            <div class="brebo-knowledge-library__hero-copy brebo-knowledge-library__hero-copy--full"><p class="brebo-knowledge-library__eyebrow">BREBO Klantenservice</p><h1>Waar kunnen we u mee helpen?</h1><p class="brebo-knowledge-library__lead">Kies wat bij uw situatie past. Voor een lopend project, een servicevraag, technische informatie of een nieuwe opgave komt u direct op de juiste plek.</p></div>
          </div></div>
          <div class="brebo-knowledge-library__primary-routes">
            <article class="brebo-primary-route">
              <div class="brebo-route-icon" aria-hidden="true">▦</div>
              <div><p class="brebo-knowledge-library__eyebrow">BREBO is al betrokken</p><h2>Gaat uw vraag over een project of uitgevoerd werk?</h2><p>Kies deze route voor een lopend project, een wijziging of afspraak, of voor een servicepunt na uitvoering.</p>
                <div class="brebo-primary-route__actions">
                  <a class="brebo-knowledge-library__button" href="/klantenservice/projectvraag">Lopend project <span aria-hidden="true">→</span></a>
                  <a class="brebo-knowledge-library__button brebo-knowledge-library__button--secondary" href="/contact/bericht?route=bouwbegeleiding&amp;context=oplevering-nazorg">Service na uitvoering <span aria-hidden="true">→</span></a>
                </div>
              </div>
            </article>
            <article class="brebo-primary-route">
              <div class="brebo-route-icon" aria-hidden="true">◉</div>
              <div><p class="brebo-knowledge-library__eyebrow">Nog geen BREBO-project</p><h2>Wilt u iets onderzoeken, verbeteren of laten uitvoeren?</h2><p>Gebruik de kennisroute als u eerst wilt begrijpen wat er aan de hand kan zijn. Heeft u al een concrete opgave, dan kunt u die direct aan BREBO voorleggen.</p>
                <div class="brebo-primary-route__actions">
                  <a class="brebo-knowledge-library__button" href="#brebo-kennis">Eerst situatie verkennen <span aria-hidden="true">↓</span></a>
                  <a class="brebo-knowledge-library__button brebo-knowledge-library__button--secondary" href="/contact/bericht?route=kozijnen-glas&amp;context=doel-onduidelijk">Opgave voorleggen <span aria-hidden="true">→</span></a>
                </div>
              </div>
            </article>
          </div>
          <div class="brebo-knowledge-library__section" id="brebo-kennis"><div class="brebo-knowledge-library__section-head"><p class="brebo-knowledge-library__eyebrow">BREBO Kennis</p><h2>Wat wilt u aan uw gebouw aanpakken?</h2><p>U hoeft de technische oorzaak of oplossing niet te kennen. Kies wat u ziet, merkt of wilt bereiken. Van daaruit helpen we u verder.</p></div><div class="brebo-knowledge-library__grid">' . $topicMarkup . '</div></div>
          <div class="brebo-knowledge-library__promise"><div class="brebo-knowledge-library__promise-mark" aria-hidden="true">✓</div><div class="brebo-knowledge-library__promise-title"><p class="brebo-knowledge-library__eyebrow">Onze kwaliteitsbelofte</p><h2>We maken duidelijk wat we weten en wat we aannemen.</h2></div><p>BREBO gebruikt eigen vakkennis, beschikbare projectinformatie en relevante externe bronnen. Ontbreekt informatie, dan kunnen we werken met een aanname. Die benoemen we als zodanig. Is er onvoldoende informatie om een verantwoorde conclusie te trekken, dan geven we dat aan. We presenteren een aanname nooit als een feit.</p></div>
        </section>',
    ];
  }

}
