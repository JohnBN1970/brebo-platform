# BREBO Software Architecture Guide

**Status:** concept  
**Versie:** 1.0  
**Toepassing:** alle nieuwe en gewijzigde BREBO-maatwerkcode  
**Normatieve basis:** BREBO Platformhandboek, goedgekeurde besluiten, ADR's en RFC's

## 1. Doel

Deze guide beschrijft hoe BREBO-software wordt ontworpen, gebouwd, getest en onderhouden. Het document vult het Platformhandboek aan: het Platformhandboek bepaalt richting en uitgangspunten; deze guide vertaalt die naar uitvoerbare ontwikkelafspraken.

De guide voorkomt dat functionaliteit versnipperd raakt over Drupal-configuratie, thema's en willekeurige helpers. Nieuwe code moet eenvoudig, onderhoudbaar, toetsbaar en uitbreidbaar blijven.

## 2. Architectuurlagen

BREBO onderscheidt drie hoofdlagen.

### 2.1 Presentatielaag

Voorbeelden:

- `brebo_theme`;
- `brebo_hero`;
- `brebo_page_banner`;
- `brebo_cta`.

De presentatielaag verzorgt rendering, vormgeving en gebruikersinteractie. Zij bevat geen domeinregels, beslislogica of integratiebeleid.

### 2.2 Domeinlaag

Voorbeelden:

- `brebo_knowledge`;
- toekomstige modules voor diensten, projecten, referenties en de BREBO Lens.

De domeinlaag bevat bedrijfsbetekenis, validaties en toepassingsregels. Iedere domeinmodule heeft één duidelijke verantwoordelijkheid en beheert haar eigen concepten.

### 2.3 Integratielaag

Voorbeelden:

- `brebo_api`;
- externe adapters;
- toekomstige MCP- en Python-services.

De integratielaag vertaalt tussen BREBO en externe systemen. Zij mag domeinlogica aanroepen, maar neemt die niet over.

## 3. Modulegrenzen

Voor iedere module gelden de volgende regels:

1. Eén module heeft één primaire verantwoordelijkheid.
2. Een module bevat alleen functionaliteit die binnen haar vastgelegde domeingrens valt.
3. Nieuwe afhankelijkheden worden alleen toegevoegd wanneer zij aantoonbaar nodig zijn.
4. Directe cyclische afhankelijkheden tussen custom modules zijn verboden.
5. Gedeelde infrastructuur wordt pas centraal geplaatst wanneer minimaal twee concrete toepassingen dezelfde stabiele behoefte hebben.
6. Een lege of speculatieve `brebo_core` is niet toegestaan. Centrale abstrahering volgt bewezen hergebruik; zij gaat er niet aan vooraf.

## 4. Standaard moduleopbouw

Een module bevat alleen mappen en bestanden die daadwerkelijk nodig zijn.

```text
brebo_example/
├── brebo_example.info.yml
├── brebo_example.services.yml       # alleen wanneer services bestaan
├── brebo_example.install            # alleen voor install/update/uninstall-logica
├── brebo_example.module             # alleen voor hooks die niet anders kunnen
├── README.md
├── config/
├── src/
└── tests/
```

Binnen `src/` worden Drupal- en PSR-conventies gevolgd. Er wordt geen kunstmatige mapstructuur aangemaakt voor nog niet bestaande klassen.

## 5. Verantwoordelijkheden per component

### 5.1 Controllers

Controllers mogen:

- een request ontvangen;
- invoer normaliseren;
- een toepassingsservice aanroepen;
- een response of render array teruggeven.

Controllers bevatten geen domeinbeslissingen, databasequeries of integratielogica.

### 5.2 Forms

Forms bouwen en valideren gebruikersinvoer. Complexe validatie en verwerking worden gedelegeerd aan services. Een form is geen domeinservice.

### 5.3 Services

Services bevatten herbruikbare toepassings- of infrastructuurlogica. Services hebben een beperkte, benoembare verantwoordelijkheid en worden via dependency injection gebruikt.

### 5.4 Repositories en storage

Opslagtoegang wordt alleen achter een eigen repository- of gatewaylaag geplaatst wanneer dit de domeingrens, testbaarheid of vervangbaarheid werkelijk verbetert. Eenvoudige Drupal entity queries hoeven niet automatisch in extra abstracties te worden verpakt.

### 5.5 Events

Events worden gebruikt wanneer meerdere onafhankelijke onderdelen op een afgeronde gebeurtenis moeten kunnen reageren. Events zijn niet bedoeld om een eenvoudige, directe serviceaanroep te verbergen.

### 5.6 Value objects

Value objects worden gebruikt voor concepten met eigen invarianten of betekenis, zoals urgentie, observatie of beoordelingsresultaat. Voor eenvoudige gegevensoverdracht volstaat een typed DTO of een bestaande Drupal-waarde.

## 6. Dependency injection

Constructor injection is de standaard voor services, controllers, plugins en andere container-managed classes.

Statische service-opvraging via `\Drupal::service()`, `\Drupal::entityTypeManager()` of vergelijkbare calls is niet toegestaan in nieuwe objectgeoriënteerde code, behalve waar Drupal zelf geen dependency-injectionpunt biedt. Een uitzondering wordt lokaal gemotiveerd.

## 7. Drupal-specifieke regels

1. Gebruik Drupal core API's en conventies voordat custom infrastructuur wordt toegevoegd.
2. Configuratie die bij installatie beschikbaar moet zijn, staat in `config/install` of `config/optional` volgens het vastgestelde activatiemodel.
3. Wijzigingen voor bestaande installaties lopen via genummerde update hooks of post-updates.
4. Update hooks zijn idempotent waar praktisch mogelijk en stoppen veilig bij conflicterende bestaande configuratie.
5. Machine names worden na vrijgave niet gewijzigd zonder migratie- en rollbackplan.
6. Thema's en preprocessfuncties bevatten uitsluitend presentatielogica.
7. Hooks blijven dun en delegeren naar services zodra de logica meer dan triviaal is.
8. Cache metadata, toegangscontrole en vertaalbaarheid worden expliciet behandeld.

## 8. API- en integratieontwerp

BREBO bouwt service-first, niet endpoint-first. Een REST-, JSON:API-, MCP- of andere adapter ontsluit bestaande toepassingsservices en bevat geen eigen bedrijfsregels.

Voor externe integraties gelden aanvullend:

- expliciete authenticatie en autorisatie;
- minimale rechten;
- geen token-passthrough zonder goedgekeurd ontwerp;
- auditbare acties;
- time-outs, foutafhandeling en herhaalbeleid;
- geen directe productie-SSH, SQL of algemene filesystemtoegang vanuit AI-integraties.

## 9. Foutafhandeling en logging

- Verwachte domeinfouten krijgen een specifieke exception of resultaatstatus.
- Technische details worden gelogd zonder secrets of persoonsgegevens onnodig vast te leggen.
- Gebruikersmeldingen zijn begrijpelijk en lekken geen interne implementatiedetails.
- Fouten worden niet stil genegeerd.

## 10. Teststrategie

De minimale testset wordt bepaald door risico en gedrag.

### Unit tests

Voor zuivere domeinlogica, value objects en services zonder Drupal-kernelafhankelijkheid.

### Kernel tests

Voor entity-, configuratie-, servicecontainer- en update-hookgedrag.

### Functional tests

Voor routes, formulieren, toegangscontrole en gebruikersstromen.

### Acceptatiecontrole

Iedere wijziging bevat reproduceerbare verificatiestappen. Voor configuratiewijzigingen worden ten minste installatie, updatepad en rollback/uninstall beoordeeld.

## 11. Coding standards

- PHP-code volgt Drupal Coding Standards en de ondersteunde PHP-versie van het project.
- Nieuwe code gebruikt strict typing waar dit binnen Drupal veilig toepasbaar is.
- Publieke classes en niet-triviale methoden hebben doelgerichte documentatie.
- Namen beschrijven gedrag en domeinbetekenis; generieke namen als `Helper`, `Manager` en `Utils` worden vermeden.
- Dode code, ongebruikte services en speculatieve extensiepunten worden niet toegevoegd.
- Comments verklaren waarom; de code zelf laat zien wat er gebeurt.

## 12. Git- en reviewregels

1. Werk vanaf `develop` in een taak- of featurebranch.
2. Eén commit bevat één logisch samenhangende wijziging.
3. Geen directe runtimewijzigingen op productie.
4. Pull requests vermelden doel, scope, verificatie, risico en rollback.
5. Architectuurimpact wordt vóór implementatie beoordeeld.
6. Afwijkingen van deze guide worden in de PR gemotiveerd en zo nodig via ADR of RFC vastgelegd.

## 13. Definition of Done

Een wijziging is pas gereed wanneer:

- de domeingrens is gerespecteerd;
- dependencies minimaal en expliciet zijn;
- update- en installatiegedrag zijn gecontroleerd;
- relevante tests en statische controles slagen;
- toegangscontrole, caching en logging zijn beoordeeld;
- README en technische documentatie zijn bijgewerkt;
- verificatie en rollback reproduceerbaar zijn;
- de wijziging via een pull request is beoordeeld.

## 14. Voorkeursvolgorde bij ontwerp

Bij twijfel geldt deze volgorde:

1. gebruik een bestaande Drupal-conventie;
2. houd de oplossing lokaal binnen de verantwoordelijke module;
3. introduceer een kleine service wanneer gedrag herbruikbaar of testbaar moet zijn;
4. voeg pas een gedeeld contract, event of centrale service toe wanneer een concrete tweede toepassing bestaat;
5. kies eenvoud en onderhoudbaarheid boven theoretische flexibiliteit.

## 15. Wijzigingsbeheer

Deze guide is normatief nadat zij formeel is goedgekeurd. Grote wijzigingen in modulegrenzen, dependencyregels, integratiebeleid of testverplichtingen vereisen een geregistreerd besluit, ADR of RFC. Kleine verduidelijkingen kunnen via een reguliere documentatie-PR worden verwerkt.
