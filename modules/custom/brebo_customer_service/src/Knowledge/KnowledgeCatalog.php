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
        self::item('rubbers-kozijnen-vervangen', 'Wanneer moeten kozijnrubbers of dichtingen worden vervangen?', 'Uitgedroogde, vervormde of losgeraakte dichtingen kunnen tocht, geluid en waterdichtheid beïnvloeden, maar ook de afstelling van het raam of de deur speelt mee.'),
        self::item('hang-en-sluitwerk-kozijnen', 'Wanneer is hang- en sluitwerk aan onderhoud of vervanging toe?', 'Klemmen, speling, zwaar sluiten of onvoldoende aandruk kunnen wijzen op slijtage, verkeerde afstelling of vervorming van raam, deur of kozijn.'),
        self::item('aluminium-kozijnen-corrosie', 'Wat betekent corrosie of aantasting op aluminium kozijnen?', 'Verkleuring, putvorming of aantasting kan samenhangen met oppervlaktebehandeling, vervuiling, vochtbelasting of contact met andere materialen.'),
        self::item('levensduur-kozijnen', 'Hoe bepaal je de resterende levensduur van kozijnen?', 'Leeftijd alleen zegt te weinig. Materiaal, detaillering, onderhoud, vochtbelasting, gebruik en feitelijke conditie bepalen samen hoeveel technische levensduur nog resteert.'),
      ],
      'glas' => [
        self::item('condens-tussen-glasbladen', 'Wat betekent condens tussen de glasbladen?', 'Vocht of waas in de afgesloten ruimte tussen glasbladen wijst doorgaans op een probleem met de afdichting van het isolatieglas.'),
        self::item('hrpp-bestaande-kozijnen', 'Heeft HR++ glas zin in bestaande kozijnen?', 'Dat kan, maar alleen wanneer het bestaande kozijn en beglazingssysteem geschikt zijn en de maatregel past bij ventilatie en de overige gebouwschil.'),
        self::item('triple-glas-bestaande-kozijnen', 'Kan triple glas in bestaande kozijnen?', 'Soms wel, maar de grotere dikte en massa maken dit nadrukkelijk een technische geschiktheidsvraag.'),
        self::item('veiligheidsglas-wanneer', 'Wanneer is veiligheidsglas relevant?', 'De benodigde glasopbouw hangt onder meer samen met positie, gebruik en het risico dat personen tegen of door het glas kunnen vallen.'),
        self::item('thermische-breuk-glas', 'Hoe ontstaat een thermische breuk in glas?', 'Grote temperatuurverschillen binnen één ruit kunnen spanningen veroorzaken. Zonbelasting, schaduw, folie, verwarming en glasopbouw kunnen daarbij een rol spelen.'),
        self::item('geluidswerend-glas', 'Wanneer helpt geluidswerend glas echt?', 'Geluidswering hangt niet alleen van het glas af. Ook kierdichting, ventilatieroosters, kozijnen en gevelaansluitingen bepalen hoeveel geluid binnenkomt.'),
        self::item('lekkage-beglazing', 'Kan lekkage via de beglazing ontstaan?', 'Water kan binnendringen via glasafdichtingen, glaslatten, sponningen of afwatering, maar de zichtbare plek binnen hoeft niet de werkelijke intredeplaats te zijn.'),
        self::item('glasdikte-en-afmetingen', 'Waarom zijn afmetingen belangrijk bij de keuze van glas?', 'Grotere ruiten krijgen andere belastingen en vervormingen te verwerken. Afmetingen, positie, windbelasting, ondersteuning en glasopbouw moeten daarom samen worden beoordeeld.'),
      ],
      'gevel-aansluitingen' => [
        self::item('lekkage-rond-kozijn', 'Waar kan lekkage rond een kozijn vandaan komen?', 'Water dat naast of onder een kozijn zichtbaar wordt kan via meerdere routes zijn binnengekomen. De zichtbare vochtplek is daarom een startpunt, geen diagnose.'),
        self::item('kitvoegen-vervangen', 'Wanneer moeten kitvoegen worden vervangen?', 'Scheuren, onthechting, verharding of verlies van vervormingsvermogen kunnen betekenen dat de voeg zijn functie niet meer goed vervult.'),
        self::item('koudebrug-herkennen', 'Hoe herken je een mogelijke koudebrug?', 'Een koudebrug kan leiden tot een kouder binnenoppervlak en onder bepaalde omstandigheden tot condens of schimmel.'),
        self::item('scheuren-gevel', 'Wat zegt een scheur in de gevel?', 'Een scheur kan oppervlakkig zijn, samenhangen met materiaalbeweging of wijzen op beweging in de constructie.'),
        self::item('voegwerk-gevel-herstellen', 'Wanneer is voegwerk aan herstel toe?', 'Open, uitgesleten of loszittende voegen kunnen de vochtbelasting van de gevel vergroten. Niet iedere beschadigde voeg vraagt echter om volledig opnieuw voegen.'),
        self::item('afspattende-bakstenen', 'Wat betekent afspattend of beschadigd metselwerk?', 'Afspatting kan samenhangen met vocht, vorst, zoutbelasting, materiaalkeuze of lokale detaillering. De oorzaak bepaalt of plaatselijk herstel voldoende is.'),
        self::item('vocht-in-spouwmuur', 'Hoe kan vocht via een spouwmuur binnenkomen?', 'Water kan via buitenspouwblad, vervuiling, spouwankers, isolatie, lateien of aansluitdetails naar binnen worden geleid. Een vochtplek bewijst daarom niet direct waar het probleem begint.'),
        self::item('dilatatievoegen-gevel', 'Waarom zijn dilatatievoegen in een gevel belangrijk?', 'Gevelmaterialen bewegen door temperatuur, vocht en vervorming. Dilataties moeten die beweging gecontroleerd opnemen en kunnen bij gebreken tot scheurvorming of lekkage leiden.'),
      ],
      'onderhoud-renovatie' => [
        self::item('onderhoud-of-renovatie', 'Wanneer wordt terugkerend onderhoud een renovatievraag?', 'Wanneer dezelfde problemen blijven terugkomen of verschillende bouwdelen tegelijk aandacht vragen, kan een samenhangende aanpak doelmatiger worden.'),
        self::item('schilderwerk-plannen', 'Hoe bepaal je wanneer buitenschilderwerk nodig is?', 'Oriëntatie, detaillering, materiaal, eerdere behandeling en feitelijke conditie bepalen wanneer onderhoud nodig wordt.'),
        self::item('planmatig-of-correctief', 'Wat is het verschil tussen planmatig en correctief onderhoud?', 'Correctief onderhoud reageert op een defect. Planmatig onderhoud organiseert werkzaamheden vooraf op basis van conditie, risico en verwachte levensduur.'),
        self::item('onderhoud-bundelen', 'Wanneer is het slim onderhoudswerkzaamheden te bundelen?', 'Bundelen kan voordeel geven wanneer werkzaamheden dezelfde bereikbaarheid, bouwdelen of uitvoeringsperiode delen.'),
        self::item('bouwbegeleiding-wanneer', 'Wanneer is onafhankelijke bouwbegeleiding zinvol?', 'Bouwbegeleiding is vooral waardevol wanneer kwaliteit, planning, kosten en technische keuzes tijdens de uitvoering actief bewaakt moeten worden namens de opdrachtgever.'),
        self::item('bouwbegeleiding-controlepunten', 'Wat controleert een bouwbegeleider tijdens de uitvoering?', 'De controle richt zich onder meer op afgesproken kwaliteit, technische details, voortgang, afwijkingen, meerwerk, vastlegging en tijdige besluitvorming.'),
        self::item('oplevering-restpunten', 'Hoe voorkom je discussie over restpunten bij oplevering?', 'Heldere eisen, tussentijdse controles, foto- en dossieropbouw en een eenduidige opname bij oplevering maken zichtbaar wat gereed is en wat nog moet worden hersteld.'),
        self::item('renovatie-bewoners-hinder', 'Hoe beperk je hinder tijdens onderhoud of renovatie?', 'Goede fasering, bereikbaarheid, communicatie, werkvolgorde en afspraken over veiligheid en gebruik zijn nodig om werkzaamheden uitvoerbaar te houden voor bewoners en gebruikers.'),
      ],
      'verduurzaming' => [
        self::item('glas-vervangen-verduurzamen', 'Is alleen het glas vervangen een goede verduurzamingsmaatregel?', 'Beter isolerend glas kan warmteverlies en comfort verbeteren, maar het resultaat hangt ook af van kozijnen, kierdichting, gevel en ventilatie.'),
        self::item('isoleren-en-ventileren', 'Waarom moet ventilatie mee bij isoleren?', 'Isolatie en kierdichting beperken warmteverlies, terwijl vocht en verontreinigde binnenlucht gecontroleerd moeten kunnen worden afgevoerd.'),
        self::item('gevelisolatie-aandachtspunten', 'Waar moet je op letten bij gevelisolatie?', 'Gevelisolatie verandert niet alleen de isolatiewaarde, maar ook aansluitingen, vochtgedrag, detaillering en het uiterlijk van de gevel.'),
        self::item('verduurzamen-in-volgorde', 'In welke volgorde kun je een gebouw verduurzamen?', 'De logische route volgt uit conditie, energieverlies, onderhoudsmomenten, technische afhankelijkheden en budget.'),
        self::item('kierdichting-verduurzamen', 'Hoe belangrijk is kierdichting bij verduurzaming?', 'Ongecontroleerde luchtlekken kunnen comfort en energieprestatie sterk beïnvloeden. Kierdichting moet wel samengaan met voldoende en beheerste ventilatie.'),
        self::item('spouwmuur-naisoleren', 'Wanneer is na-isolatie van een spouwmuur verstandig?', 'Geschiktheid hangt af van spouwbreedte, vervuiling, vochtbelasting, gevelconditie, blootstelling en aanwezige isolatie. Eerst onderzoeken voorkomt dat bestaande problemen worden opgesloten.'),
        self::item('zonwering-oververhitting', 'Wanneer hoort zonwering bij verduurzaming?', 'Een beter geïsoleerd gebouw kan warmte langer vasthouden. Zonbelasting, glasoppervlak, oriëntatie en gebruik bepalen of beperking van oververhitting onderdeel van het plan moet zijn.'),
        self::item('verduurzaming-combineren-onderhoud', 'Waarom verduurzaming combineren met gepland onderhoud?', 'Wanneer een bouwdeel toch wordt aangepakt, kunnen bereikbaarheid, afwerking en vervangingsmoment worden benut om energetische verbeteringen doelmatiger mee te nemen.'),
      ],
      'gebouwbeheer' => [
        self::item('mjop-wat-is-het', 'Wat hoort een MJOP eigenlijk te doen?', 'Een meerjarenonderhoudsplan helpt toekomstige onderhoudsbehoefte, timing en financiële reservering inzichtelijk te maken.'),
        self::item('gebrek-urgent', 'Wanneer is een gebrek urgent?', 'Urgentie wordt onder meer bepaald door veiligheid, kans op gevolgschade, snelheid van verslechtering en invloed op gebruik.'),
        self::item('inspectie-frequentie', 'Hoe vaak moet een gebouw worden geïnspecteerd?', 'Een zinvolle inspectiefrequentie hangt af van bouwdeel, conditie, risico, omgeving, gebruik en onderhoudsstrategie.'),
        self::item('onderhoud-prioriteren', 'Hoe prioriteer je onderhoud als niet alles tegelijk kan?', 'Prioriteren betekent gevolgen vergelijken. Veiligheid, waterdichtheid, verdere schade, continuïteit en financiële efficiency kunnen meewegen.'),
        self::item('conditiemeting-gebouw', 'Wat vertelt een conditiemeting wel en niet?', 'Een conditiescore helpt de technische staat gestructureerd vast te leggen, maar vervangt geen beoordeling van oorzaak, risico, functie en passende maatregel.'),
        self::item('onderhoudsbudget-reserveren', 'Hoe bepaal je een realistische onderhoudsreservering?', 'Een bruikbare reservering volgt uit verwachte maatregelen, prijsniveau, onzekerheid, planning en risico. Alleen historische uitgaven doortrekken geeft vaak een vertekend beeld.'),
        self::item('onderhoud-uitstellen-risico', 'Wat zijn de risico’s van onderhoud uitstellen?', 'Uitstel kan verantwoord zijn wanneer risico en ontwikkeling beperkt zijn, maar kan ook leiden tot gevolgschade, hogere kosten, veiligheidsproblemen of verlies van functionaliteit.'),
        self::item('gebouw-dossier-opbouwen', 'Welke informatie hoort in een goed gebouw- en onderhoudsdossier?', 'Inspecties, tekeningen, foto’s, garanties, onderhoudshistorie, besluiten, offertes, uitgevoerde maatregelen en revisiegegevens vormen samen de basis voor controleerbaar gebouwbeheer.'),
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
