<?php

declare(strict_types=1);

namespace Drupal\brebo_customer_service\Knowledge;

/**
 * Curated public knowledge seed.
 *
 * Public presentation and AI authority are deliberately separate. Every item
 * starts as editorial knowledge and requires explicit source validation and
 * human approval before KnowledgeApproval can expose it to BREBO AI.
 */
final class KnowledgeCatalog {

  public static function items(): array {
    return [
      'kozijnen' => [
        self::item('kozijnen-herstellen-of-vervangen', 'Wanneer is een bestaand kozijn nog goed te herstellen?', 'Zichtbare slijtage betekent niet automatisch dat een kozijn vervangen moet worden. De omvang, oorzaak en positie van de schade bepalen of duurzaam herstel nog logisch is.'),
        self::item('houtrot-kozijnen', 'Wat betekent houtrot in een kozijn?', 'Houtaantasting ontstaat niet zonder vocht. De belangrijkste vraag is daarom waardoor het hout langdurig vochtig kon worden.'),
        self::item('tocht-langs-kozijnen', 'Waarom voel ik tocht langs mijn kozijnen?', 'Tocht kan via draaiende delen, aansluitnaden, beglazing of de aansluiting tussen kozijn en gevel binnenkomen.'),
        self::item('schilderwerk-kozijnen', 'Wanneer vraagt schilderwerk meer dan een nieuwe verflaag?', 'Schilderwerk beschermt houten kozijnen, maar een nieuwe afwerklaag lost onderliggende vocht- of houtproblemen niet op.'),
      ],
      'glas' => [
        self::item('condens-tussen-glasbladen', 'Wat betekent condens tussen de glasbladen?', 'Vocht of waas in de afgesloten ruimte tussen glasbladen wijst doorgaans op een probleem met de afdichting van het isolatieglas.'),
        self::item('hrpp-bestaande-kozijnen', 'Heeft HR++ glas zin in bestaande kozijnen?', 'Dat kan, maar alleen wanneer het bestaande kozijn en beglazingssysteem geschikt zijn en de maatregel past bij ventilatie en de overige gebouwschil.'),
        self::item('triple-glas-bestaande-kozijnen', 'Kan triple glas in bestaande kozijnen?', 'Soms wel, maar de grotere dikte en massa maken dit nadrukkelijk een technische geschiktheidsvraag.'),
        self::item('veiligheidsglas-wanneer', 'Wanneer is veiligheidsglas relevant?', 'De benodigde glasopbouw hangt onder meer samen met positie, gebruik en het risico dat personen tegen of door het glas kunnen vallen.'),
      ],
      'gevel-aansluitingen' => [
        self::item('lekkage-rond-kozijn', 'Waar kan lekkage rond een kozijn vandaan komen?', 'Water dat naast of onder een kozijn zichtbaar wordt kan via meerdere routes zijn binnengekomen. De zichtbare vochtplek is daarom een startpunt, geen diagnose.'),
        self::item('kitvoegen-vervangen', 'Wanneer moeten kitvoegen worden vervangen?', 'Scheuren, onthechting, verharding of verlies van vervormingsvermogen kunnen betekenen dat de voeg zijn functie niet meer goed vervult.'),
        self::item('koudebrug-herkennen', 'Hoe herken je een mogelijke koudebrug?', 'Een koudebrug kan leiden tot een kouder binnenoppervlak en onder bepaalde omstandigheden tot condens of schimmel.'),
        self::item('scheuren-gevel', 'Wat zegt een scheur in de gevel?', 'Een scheur kan oppervlakkig zijn, samenhangen met materiaalbeweging of wijzen op beweging in de constructie.'),
      ],
      'onderhoud-renovatie' => [
        self::item('onderhoud-of-renovatie', 'Wanneer wordt terugkerend onderhoud een renovatievraag?', 'Wanneer dezelfde problemen blijven terugkomen of verschillende bouwdelen tegelijk aandacht vragen, kan een samenhangende aanpak doelmatiger worden.'),
        self::item('schilderwerk-plannen', 'Hoe bepaal je wanneer buitenschilderwerk nodig is?', 'Oriëntatie, detaillering, materiaal, eerdere behandeling en feitelijke conditie bepalen wanneer onderhoud nodig wordt.'),
        self::item('planmatig-of-correctief', 'Wat is het verschil tussen planmatig en correctief onderhoud?', 'Correctief onderhoud reageert op een defect. Planmatig onderhoud organiseert werkzaamheden vooraf op basis van conditie, risico en verwachte levensduur.'),
        self::item('onderhoud-bundelen', 'Wanneer is het slim onderhoudswerkzaamheden te bundelen?', 'Bundelen kan voordeel geven wanneer werkzaamheden dezelfde bereikbaarheid, bouwdelen of uitvoeringsperiode delen.'),
      ],
      'verduurzaming' => [
        self::item('glas-vervangen-verduurzamen', 'Is alleen het glas vervangen een goede verduurzamingsmaatregel?', 'Beter isolerend glas kan warmteverlies en comfort verbeteren, maar het resultaat hangt ook af van kozijnen, kierdichting, gevel en ventilatie.'),
        self::item('isoleren-en-ventileren', 'Waarom moet ventilatie mee bij isoleren?', 'Isolatie en kierdichting beperken warmteverlies, terwijl vocht en verontreinigde binnenlucht gecontroleerd moeten kunnen worden afgevoerd.'),
        self::item('gevelisolatie-aandachtspunten', 'Waar moet je op letten bij gevelisolatie?', 'Gevelisolatie verandert niet alleen de isolatiewaarde, maar ook aansluitingen, vochtgedrag, detaillering en het uiterlijk van de gevel.'),
        self::item('verduurzamen-in-volgorde', 'In welke volgorde kun je een gebouw verduurzamen?', 'De logische route volgt uit conditie, energieverlies, onderhoudsmomenten, technische afhankelijkheden en budget.'),
      ],
      'gebouwbeheer' => [
        self::item('mjop-wat-is-het', 'Wat hoort een MJOP eigenlijk te doen?', 'Een meerjarenonderhoudsplan helpt toekomstige onderhoudsbehoefte, timing en financiële reservering inzichtelijk te maken.'),
        self::item('gebrek-urgent', 'Wanneer is een gebrek urgent?', 'Urgentie wordt onder meer bepaald door veiligheid, kans op gevolgschade, snelheid van verslechtering en invloed op gebruik.'),
        self::item('inspectie-frequentie', 'Hoe vaak moet een gebouw worden geïnspecteerd?', 'Een zinvolle inspectiefrequentie hangt af van bouwdeel, conditie, risico, omgeving, gebruik en onderhoudsstrategie.'),
        self::item('onderhoud-prioriteren', 'Hoe prioriteer je onderhoud als niet alles tegelijk kan?', 'Prioriteren betekent gevolgen vergelijken. Veiligheid, waterdichtheid, verdere schade, continuïteit en financiële efficiency kunnen meewegen.'),
      ],
    ];
  }

  public static function find(string $slug): ?array {
    foreach (self::items() as $topic => $items) {
      foreach ($items as $item) {
        if ($item['slug'] === $slug) {
          return $item + ['topic' => $topic];
        }
      }
    }
    return NULL;
  }

  public static function aiItems(): array {
    $approved = [];
    foreach (self::items() as $topic => $items) {
      foreach ($items as $item) {
        $item += ['topic' => $topic];
        if (KnowledgeApproval::isAiApproved($item)) {
          $approved[] = $item;
        }
      }
    }
    return $approved;
  }

  private static function item(string $slug, string $title, string $summary): array {
    return [
      'slug' => $slug,
      'title' => $title,
      'summary' => $summary,
      'public' => TRUE,
      'status' => KnowledgeApproval::STATUS_EDITORIAL,
      'ai_approved' => FALSE,
      'reviewed_by' => NULL,
      'reviewed_at' => NULL,
      'basis' => [
        'sources' => [],
        'validity_checked_at' => NULL,
        'notes' => 'Nog te voorzien van aantoonbare bron, geldigheidscontrole en deskundige beoordeling.',
      ],
    ];
  }

}
