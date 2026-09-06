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
        ['Wie is verantwoordelijk?', 'BREBO Bouw en Advies B.V., Plaza 25A, 4782 SL Moerdijk, is verantwoordelijk voor de verwerking van persoonsgegevens die in het kader van de website, klantcontact en dienstverlening worden verwerkt. Voor privacyvragen kunt u contact opnemen via info@brebobv.nl.'],
        ['Welke gegevens verwerken wij?', 'Afhankelijk van uw contact met BREBO kan het gaan om naam, contactgegevens, organisatie, project- of gebouwinformatie, correspondentie en informatie die u zelf via een formulier, e-mail of andere contactroute verstrekt.'],
        ['Waarvoor gebruiken wij die gegevens?', 'Wij gebruiken persoonsgegevens om vragen te beantwoorden, contact op te nemen, aanvragen te beoordelen, offertes of opdrachten uit te voeren, afspraken vast te leggen, onze dienstverlening te administreren en te voldoen aan wettelijke verplichtingen.'],
        ['Grondslag en bewaartermijn', 'Wij verwerken gegevens alleen wanneer daarvoor een geldige grondslag bestaat, bijvoorbeeld omdat dit nodig is voor een aanvraag of overeenkomst, vanwege een wettelijke verplichting, op basis van een gerechtvaardigd belang of met uw toestemming. Gegevens worden niet langer bewaard dan nodig voor het doel waarvoor zij zijn verzameld en de toepasselijke wettelijke bewaartermijnen.'],
        ['Delen met anderen', 'Wij delen persoonsgegevens alleen met partijen wanneer dat nodig is voor onze dienstverlening of wettelijke verplichtingen. Waar nodig maken wij afspraken over de bescherming van persoonsgegevens. Wij verkopen geen persoonsgegevens.'],
        ['Uw rechten', 'U kunt vragen om inzage, correctie of verwijdering van uw persoonsgegevens en u kunt in de daarvoor geldende gevallen bezwaar maken, de verwerking laten beperken, gegevens laten overdragen of gegeven toestemming intrekken. Neem daarvoor contact op via info@brebobv.nl. U heeft daarnaast het recht een klacht in te dienen bij de Autoriteit Persoonsgegevens.'],
        ['Beveiliging en wijzigingen', 'BREBO neemt passende technische en organisatorische maatregelen om persoonsgegevens te beschermen. Deze privacyverklaring kan worden aangepast wanneer onze website, dienstverlening of wettelijke verplichtingen veranderen.'],
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
      '#markup' => '<main class="brebo-legal"><header class="brebo-legal__header"><p class="brebo-knowledge-library__eyebrow">BREBO</p><h1>' . $title . '</h1><p class="brebo-legal__lead">' . $lead . '</p></header><div class="brebo-legal__content">' . $content . '</div><p class="brebo-legal__updated">Laatst bijgewerkt: 6 september 2026</p></main>',
    ];
  }

}
