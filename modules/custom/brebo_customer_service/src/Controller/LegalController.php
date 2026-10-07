<?php

declare(strict_types=1);

namespace Drupal\brebo_customer_service\Controller;

use Drupal\Core\Controller\ControllerBase;

final class LegalController extends ControllerBase {

  public function privacy(): array {
    return $this->page(
      'Privacyverklaring',
      'Hoe BREBO omgaat met persoonsgegevens die u via de website of rechtstreeks aan ons verstrekt.',
      [
        ['Wie is verantwoordelijk?', 'BREBO Bouw en Advies B.V., Plaza 25A, 4782 SL Moerdijk, is verwerkingsverantwoordelijke voor persoonsgegevens die wij via de website, in klantcontact en bij onze dienstverlening verwerken. Voor vragen over privacy of voor het uitoefenen van uw rechten kunt u contact opnemen via info@brebobv.nl.'],
        ['Welke gegevens verwerken wij?', 'Afhankelijk van uw contact met BREBO verwerken wij bijvoorbeeld uw naam, e-mailadres, telefoonnummer, organisatie, project- of gebouwinformatie, correspondentie en andere informatie die u zelf aan ons verstrekt. Bij gebruik van onze website kunnen daarnaast technisch noodzakelijke gegevens worden verwerkt, zoals IP-adres en beveiligings- of loggegevens, voor het functioneren en beveiligen van de website. Verstrek via een algemeen contactformulier geen bijzondere persoonsgegevens als die niet nodig zijn voor uw vraag.'],
        ['Contact, aanvragen en offertes', 'Wanneer u contact met ons opneemt, een vraag stelt, een aanvraag doet of een offerte vraagt, gebruiken wij de gegevens die daarvoor nodig zijn om uw verzoek te behandelen, met u te communiceren en op uw verzoek stappen te zetten richting een mogelijke overeenkomst. De grondslag is in die gevallen het uitvoeren van een overeenkomst of het nemen van precontractuele maatregelen op uw verzoek. Voor algemene zakelijke communicatie die niet noodzakelijk onder die grondslag valt, kan BREBO een gerechtvaardigd belang hebben om normale bedrijfscommunicatie en opvolging mogelijk te maken.'],
        ['Opdrachten en administratie', 'Wanneer u opdrachtgever, leverancier of andere contractpartij bent, verwerken wij de gegevens die nodig zijn om afspraken en overeenkomsten uit te voeren, projecten te organiseren, werkzaamheden vast te leggen, te factureren en onze administratie te voeren. De grondslag is uitvoering van de overeenkomst en, waar van toepassing, het voldoen aan wettelijke verplichtingen, bijvoorbeeld fiscale en administratieve bewaarplichten.'],
        ['Beveiliging en misbruikpreventie', 'Wij verwerken beperkte technische gegevens om de website en formulieren te beveiligen, spam, fraude en ander misbruik tegen te gaan en storingen te onderzoeken. Daarvoor gebruiken wij onder meer tijdelijke beveiligings- en loggegevens en begrenzen wij het aantal formulierinzendingen. De grondslag is ons gerechtvaardigd belang om onze website, communicatiekanalen en bedrijfsvoering te beschermen.'],
        ['Hoe lang bewaren wij gegevens?', 'Gegevens uit een eerste contact of aanvraag bewaren wij zolang dat nodig is om de vraag of aanvraag af te handelen en voor een redelijke periode daarna voor opvolging en verantwoording. Ontstaat daaruit een overeenkomst of project, dan kunnen relevante gegevens onderdeel worden van het project- en administratiedossier en gelden de daarvoor toepasselijke wettelijke en zakelijke bewaartermijnen. Financiële en fiscale administratie bewaren wij gedurende de wettelijk voorgeschreven termijn. Technische beveiligings- en loggegevens bewaren wij niet langer dan noodzakelijk voor beveiliging, onderzoek en misbruikpreventie. Wanneer geen vaste wettelijke termijn geldt, bepalen wij de bewaartermijn aan de hand van het doel, de noodzaak van de gegevens en eventuele wettelijke verjarings- of verantwoordingsplichten.'],
        ['Met wie delen wij gegevens?', 'Wij verstrekken persoonsgegevens alleen wanneer dat nodig is voor onze dienstverlening, bedrijfsvoering of een wettelijke verplichting. Dit kan bijvoorbeeld gaan om hosting- en IT-dienstverleners, e-mail- en communicatiedienstverleners, administratieve of financiële dienstverleners en, wanneer dit voor een project nodig is, betrokken adviseurs, leveranciers of uitvoerende partijen. Zij ontvangen alleen de gegevens die voor hun taak nodig zijn. Met partijen die namens BREBO persoonsgegevens verwerken, maken wij waar vereist afspraken over gegevensbescherming. Wij verkopen geen persoonsgegevens.'],
        ['Verwerking buiten de EER', 'BREBO streeft ernaar persoonsgegevens binnen de Europese Economische Ruimte (EER) te verwerken. Wanneer een door ons gebruikte dienstverlener gegevens buiten de EER verwerkt of toegankelijk maakt, gebeurt dit alleen wanneer daarvoor een geldige doorgiftegrond bestaat, zoals een adequaatheidsbesluit of passende waarborgen volgens de AVG. Voor informatie over de waarborgen die in een concreet geval worden gebruikt, kunt u contact opnemen via info@brebobv.nl.'],
        ['Uw rechten', 'U kunt, voor zover de AVG dat in uw situatie bepaalt, vragen om inzage, correctie of verwijdering van uw persoonsgegevens, beperking van de verwerking of overdracht van uw gegevens. U kunt bezwaar maken tegen verwerkingen die op een gerechtvaardigd belang zijn gebaseerd. Als een verwerking op toestemming berust, kunt u die toestemming altijd intrekken; dit verandert niets aan de rechtmatigheid van de verwerking vóór de intrekking. Neem voor een verzoek contact op via info@brebobv.nl. U heeft daarnaast het recht een klacht in te dienen bij de Autoriteit Persoonsgegevens.'],
        ['Verplichte gegevens en geautomatiseerde besluiten', 'Voor een eerste contact vragen wij alleen de gegevens die nodig zijn om uw vraag te kunnen behandelen en u te kunnen bereiken. Zonder verplichte contactgegevens kunnen wij uw aanvraag mogelijk niet behandelen. BREBO neemt via het openbare contactformulier geen uitsluitend geautomatiseerde besluiten met rechtsgevolgen of vergelijkbare aanmerkelijke gevolgen voor u.'],
        ['Beveiliging en wijzigingen', 'BREBO neemt passende technische en organisatorische maatregelen om persoonsgegevens te beschermen. Wij kunnen deze privacyverklaring aanpassen wanneer onze website, dienstverlening, leveranciers of wettelijke verplichtingen veranderen. De actuele versie staat altijd op deze pagina.'],
      ],
    );
  }

  public function cookies(): array {
    return $this->page(
      'Cookieverklaring',
      'Welke cookies en vergelijkbare technieken de BREBO-website gebruikt en wanneer toestemming nodig is.',
      [
        ['Uitgangspunt', 'De publieke BREBO-website is ingericht zonder marketing- of trackingfunctionaliteit in de eigen websitecode. Wij plaatsen geen trackingcookies voordat daarvoor, wanneer wettelijk vereist, geldige toestemming is verkregen.'],
        ['Noodzakelijke technieken', 'Voor het technisch functioneren en beveiligen van de website kunnen strikt noodzakelijke cookies of vergelijkbare opslag worden gebruikt. Voor dergelijke noodzakelijke technieken is geen marketingtoestemming bedoeld.'],
        ['Analytics en externe inhoud', 'Op dit moment is in de BREBO-websitecode geen Google Analytics, Google Tag Manager, Meta Pixel, Matomo, YouTube- of Vimeo-embed aangetroffen. Als later analytische, tracking- of externe mediatechnieken worden toegevoegd, wordt deze verklaring aangepast en wordt vooraf een passende toestemmingsoplossing ingericht wanneer dat verplicht is.'],
        ['Uw keuze', 'Wanneer BREBO in de toekomst toestemming vraagt voor niet-noodzakelijke cookies, moet u die toestemming ook weer eenvoudig kunnen intrekken. De website moet normaal bruikbaar blijven wanneer u trackingcookies weigert.'],
        ['Vragen', 'Heeft u vragen over cookies of privacy op deze website? Neem dan contact op via info@brebobv.nl.'],
      ],
    );
  }

  public function disclaimer(): array {
    return $this->page(
      'Disclaimer',
      'De website geeft inzicht in onze werkwijze en kennis, maar vervangt geen beoordeling van een concreet gebouw of project.',
      [
        ['Algemene informatie', 'BREBO besteedt zorg aan de inhoud van deze website. De informatie is algemeen van aard en kan niet zonder nadere beoordeling worden toegepast op ieder gebouw, bouwdeel of project.'],
        ['Geen projectspecifiek advies', 'Technische informatie, voorbeelden, indicaties en kennisartikelen op de website vervangen geen inspectie, opname, berekening, constructieve beoordeling, deskundigenonderzoek of projectspecifiek advies wanneer dat voor een situatie nodig is.'],
        ['Actualiteit en volledigheid', 'Bouwkundige omstandigheden, regelgeving, normen, producten en inzichten kunnen veranderen. Hoewel wij informatie zo zorgvuldig mogelijk beheren, garandeert BREBO niet dat iedere webpagina op ieder moment volledig, foutloos of voor een specifieke situatie actueel is.'],
        ['Offertes en overeenkomsten', 'Een opdracht aan BREBO ontstaat uitsluitend op basis van een afzonderlijke offerte, opdrachtbevestiging of overeenkomst. Website-informatie, globale kostenindicaties, planningen of voorbeelden vormen op zichzelf geen aanbod of garantie.'],
        ['Externe informatie', 'De website kan verwijzen naar informatie van derden. BREBO is niet verantwoordelijk voor de inhoud, beschikbaarheid of verwerking van persoonsgegevens op externe websites.'],
        ['Aansprakelijkheid', 'Voor zover wettelijk toegestaan is BREBO niet aansprakelijk voor schade die uitsluitend ontstaat door het zonder nadere beoordeling gebruiken van algemene informatie van deze website. Deze bepaling beperkt geen aansprakelijkheid die op grond van dwingend recht niet kan worden uitgesloten of beperkt.'],
        ['Intellectueel eigendom', 'Teksten, afbeeldingen, modellen, methodieken en andere inhoud van deze website mogen niet zonder toestemming van BREBO worden gekopieerd of commercieel hergebruikt, tenzij een wettelijke uitzondering van toepassing is.'],
      ],
    );
  }

  private function page(string $title, string $lead, array $sections): array {
    $content = '';
    foreach ($sections as [$heading, $text]) {
      $content .= '<section class="brebo-legal__section"><h2>' . $heading . '</h2><p>' . $text . '</p></section>';
    }

    return [
      '#attached' => ['library' => ['brebo_customer_service/service']],
      '#markup' => '<main class="brebo-legal"><header class="brebo-legal__header"><p class="brebo-knowledge-library__eyebrow">BREBO</p><h1>' . $title . '</h1><p class="brebo-legal__lead">' . $lead . '</p></header><div class="brebo-legal__content">' . $content . '</div><p class="brebo-legal__updated">Laatst bijgewerkt: 7 oktober 2026</p></main>',
    ];
  }

}
