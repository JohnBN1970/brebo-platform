# BREBO Domain Model 1.0

Status: concept voor architectuurreview  
Gerelateerd issue: #12  
Besluitbasis: ADR-0001 en ADR-0002

## 1. Doel

Dit document definieert de bedrijfsobjecten, eigenaarschap, relaties en grenzen van BREBO Platform 1.0.

Het model is leidend vóór Drupal-implementatie. Contenttypes, velden, taxonomieën, views, templates, zoekindexen en API-contracten worden pas afgeleid nadat dit model is goedgekeurd.

## 2. Kernprincipes

1. Ieder domeinobject heeft precies één primaire eigenaar.
2. Een module beheert uitsluitend haar eigen domeingegevens.
3. Relaties tussen domeinen worden gelegd via expliciete referenties of contracten.
4. Presentatie, zoeken, media en API bezitten geen domeingegevens.
5. Het gebouw en de onderhoudsvraag staan centraal; diensten zijn oplossingen, geen primaire kennisbron.
6. Platform 1.0 kiest eenvoud, onderhoudbaarheid en uitbreidbaarheid boven vroegtijdige modellering.
7. Een zelfstandig object wordt alleen ingevoerd wanneer het een eigen levenscyclus, identiteit of hergebruik heeft.

## 3. Domeingrenzen

### 3.1 Kennis

Eigenaar: `brebo_knowledge`

Verantwoordelijk voor probleemgerichte, revisioneerbare kennis waarmee een waarneming wordt geduid en een veilige eerste handelingsrichting wordt gegeven.

### 3.2 Diensten en werkzaamheden

Eigenaar: `brebo_service`

Verantwoordelijk voor de oplossings- en uitvoeringsdomeinen van BREBO, waaronder diensten, werkzaamheden, toepassingsvoorwaarden en afbakening.

### 3.3 Projecten

Eigenaar: `brebo_project`

Verantwoordelijk voor gerealiseerde opdrachten, projectcontext, uitgevoerde werkzaamheden en aantoonbare resultaten.

### 3.4 Referenties

Eigenaar: `brebo_reference`

Verantwoordelijk voor publiceerbare klant- of projectervaringen en hun gecontroleerde koppeling aan projecten.

### 3.5 BREBO Lens

Eigenaar: `brebo_lens`

Verantwoordelijk voor de vaste methodische duiding Inzicht, Regie en Realisatie. De Lens is geen generieke taxonomie en bezit geen objecten uit andere domeinen.

### 3.6 Ondersteunende platformfuncties

- `brebo_search`: indexeert en ontsluit, maar bezit geen brondata.
- `brebo_media`: beheert technische mediafunctionaliteit, niet de betekenis of domeinrelatie van media.
- `brebo_api`: ontsluit contracten, maar is geen bron van waarheid.
- `brebo_theme`: presenteert, maar definieert geen domeinmodel.
- `brebo_core`: bevat uitsluitend gedeelde infrastructuur en stabiele contracten.

## 4. Domeinobjectenmatrix

| Object | Definitie | Primaire eigenaar | Identiteit | Kernattributen | Toegestane relaties | Bron van waarheid | Platform 1.0 |
|---|---|---|---|---|---|---|---|
| Probleemgerichte kennisbijdrage | Revisioneerbare kennisbijdrage die een waarneming duidt en richting geeft aan urgentie, risico en eerste onderzoek | `brebo_knowledge` | Eigen stabiele identifier | titel, waarneming, betekenis, urgentie, eerste stap, risico's, Lens-duiding, status, revisie | optioneel naar dienst, werkzaamheid en gebouwclassificaties | `brebo_knowledge` | In scope |
| Dienst | Door BREBO aangeboden samenhangende oplossing voor een onderhoudsvraag | `brebo_service` | Eigen stabiele identifier | naam, doel, scope, voorwaarden, uitsluitingen, status | naar werkzaamheden, kennisbijdragen, projecten en referenties | `brebo_service` | In scope |
| Werkzaamheid | Afgebakende uitvoeringsactiviteit binnen één of meer diensten | `brebo_service` | Eigen stabiele identifier | naam, beschrijving, toepassingsvoorwaarden, resultaatsoort | naar diensten, projecten en kennisbijdragen | `brebo_service` | In scope, minimaal |
| Project | Gerealiseerde of publiceerbare opdracht met context, scope en resultaat | `brebo_project` | Eigen stabiele identifier | titel, samenvatting, locatie op publicatieniveau, periode, status, resultaat | naar diensten, werkzaamheden, referenties en media | `brebo_project` | In scope |
| Projectresultaat | Beschrijving van het aantoonbare resultaat van een project | `brebo_project` | Onderdeel van project; geen zelfstandige identiteit in 1.0 | resultaatbeschrijving, bewijs, toelichting | uitsluitend binnen project; publiceerbaar via project | `brebo_project` | In scope als attribuut/onderdeel |
| Referentie | Gecontroleerde publiceerbare ervaring of aanbeveling gekoppeld aan een opdrachtgever of project | `brebo_reference` | Eigen stabiele identifier | tekst, naam/rol indien toegestaan, publicatiestatus, toestemming | optioneel naar project; eventueel naar dienst | `brebo_reference` | In scope |
| Lens-duiding | Methodische classificatie volgens Inzicht, Regie en Realisatie | `brebo_lens` | Vaste gecontroleerde waarden | fase, toelichting, toepassingscontext | toepasbaar op kennis, diensten en later andere domeinen | `brebo_lens` | In scope, gesloten waardenlijst |
| Gebouwtype | Gecontroleerde contextclassificatie, bijvoorbeeld portiekflat of grondgebonden woning | eigenaar bepaald door gebruikend domein; geen zelfstandige generieke module | Gecontroleerde term | naam, definitie, status | naar kennis en projecten | vastgelegde classificatieconfiguratie bij het domein dat de levenscyclus beheert | Beperkt in scope |
| Gebouwdeel | Gecontroleerde contextclassificatie, bijvoorbeeld gevel, dak of kozijn | eigenaar bepaald door gebruikend domein; geen zelfstandige generieke module | Gecontroleerde term | naam, definitie, status | naar kennis, diensten en projecten | domeineigen configuratie | Beperkt in scope |
| Materiaal | Gecontroleerde contextclassificatie voor relevante bouwmaterialen | eigenaar bepaald door gebruikend domein; geen zelfstandige generieke module | Gecontroleerde term | naam, definitie, status | naar kennis, werkzaamheden en projecten | domeineigen configuratie | Alleen indien direct nodig |
| Media-item | Technisch herbruikbaar bestand met metadata | Drupal core/media met ondersteuning door `brebo_media` | Eigen media-identifier | bestand, alt-tekst, rechten, technische metadata | gerefereerd door project, kennis of dienst; betekenis blijft bij dat domein | Drupal media-opslag | Ondersteunend |

## 5. Besluit over gebouwcontext en classificaties

Platform 1.0 introduceert geen generieke module `brebo_taxonomy`, `brebo_context` of vergelijkbare eigenaarloze verzamelmodule.

Een classificatie mag gedeeld worden wanneer:

1. de definitie domeinoverstijgend stabiel is;
2. één expliciete eigenaar de levenscyclus beheert;
3. afnemende modules alleen refereren;
4. verwijdering of wijziging gecontroleerd plaatsvindt;
5. de classificatie aantoonbaar nodig is voor een goedgekeurde Platform 1.0-usecase.

Zonder deze voorwaarden blijft de classificatie lokaal bij het domein dat haar nodig heeft.

## 6. Relaties en cardinaliteit

| Bron | Relatie | Doel | Cardinaliteit | Verplicht in 1.0 | Eigenaarschap relatie |
|---|---|---|---|---|---|
| Kennisbijdrage | behandelt | gebouwdeel/classificatie | 0..n | Nee | `brebo_knowledge` |
| Kennisbijdrage | ondersteunt | dienst | 0..n | Nee | `brebo_knowledge` |
| Kennisbijdrage | leidt mogelijk tot | werkzaamheid | 0..n | Nee | `brebo_knowledge` |
| Dienst | bestaat uit | werkzaamheid | 0..n | Nee bij eerste minimale versie | `brebo_service` |
| Project | omvat | dienst | 0..n | Nee | `brebo_project` |
| Project | omvat | werkzaamheid | 0..n | Nee | `brebo_project` |
| Project | heeft | projectresultaat | 0..n | Nee | `brebo_project` |
| Project | gebruikt | media-item | 0..n | Nee | `brebo_project` |
| Referentie | hoort bij | project | 0..1 | Nee | `brebo_reference` |
| Referentie | betreft | dienst | 0..n | Nee | `brebo_reference` |
| Kennisbijdrage | gebruikt | Lens-duiding | 0..n | Nee | `brebo_knowledge` verwijst; `brebo_lens` beheert waarden |
| Dienst | gebruikt | Lens-duiding | 0..n | Nee | `brebo_service` verwijst; `brebo_lens` beheert waarden |

Relaties zijn standaard eenrichtingsverwijzingen vanuit het object dat de context nodig heeft. Afgeleide terugkoppelingen worden via queries of views berekend en niet dubbel opgeslagen.

## 7. Bron van waarheid

- Kennisinhoud: `brebo_knowledge`.
- Diensten en werkzaamheden: `brebo_service`.
- Projectgegevens en resultaten: `brebo_project`.
- Referentieteksten en publicatietoestemming: `brebo_reference`.
- Lens-waarden en definities: `brebo_lens`.
- Media-binaries en technische metadata: Drupal Media/File.
- Zoekindex: afgeleid en nooit leidend.
- API-respons: afgeleid en nooit leidend.
- Themaweergave: afgeleid en nooit leidend.

## 8. Levenscyclusregels

1. Domeinobjecten hebben een expliciete concept-, gepubliceerd- en eventueel gearchiveerdstatus.
2. Kennisbijdragen zijn revisioneerbaar; historische revisies blijven herleidbaar.
3. Verwijdering van een object met inkomende relaties wordt niet stil uitgevoerd.
4. Publicatie van referenties vereist aantoonbare toestemming en redactionele controle.
5. Media mag technisch hergebruikt worden, maar de domeinbetekenis wordt niet in `brebo_media` opgeslagen.
6. Afgeleide zoek- of API-data moet opnieuw opgebouwd kunnen worden uit de bronobjecten.

## 9. Aansluiting op BREBO-2026-004

De eerder vastgestelde eerste uitbreiding van `brebo_knowledge` blijft geldig:

- één zelfstandig, revisioneerbaar kennisobject;
- probleemgerichte kennisbijdrage;
- vijf verplichte kernvelden voor waarneming, betekenis, urgentie, eerste stap en risico's;
- twee optionele Lens-velden;
- geen presentatielaag;
- geen verplichte relaties of taxonomieën in de eerste implementatie.

Daarmee is de bestaande minimale architectuur een geldige eerste implementatieslice van dit domeinmodel.

## 10. Buiten Platform 1.0

De volgende objecten en domeinen worden bewust uitgesteld:

- zelfstandig gebouw- of vastgoedobject;
- assetregistratie en gebouwinventaris;
- inspectieobjecten en inspectiebevindingen;
- conditiemetingen;
- MJOP en onderhoudsmaatregelen;
- offertes, opdrachten en financiële transacties;
- CRM-relaties en contactpersonen;
- leveranciers- en partneradministratie;
- planning en werkvoorbereiding;
- AI-uitvoeringsobjecten, prompts en agentstatus;
- Moneybird- en Microsoft 365-brondata.

Deze onderdelen mogen later via een afzonderlijke RFC of ADR worden toegevoegd en mogen de huidige domeineigenaars niet impliciet wijzigen.

## 11. Implementatievolgorde na goedkeuring

1. Bevestig en implementeer de eerste kennisbijdrage conform BREBO-2026-004.
2. Definieer het minimale dienstobject in `brebo_service`.
3. Definieer het minimale projectobject in `brebo_project`.
4. Definieer het minimale referentieobject in `brebo_reference`.
5. Voeg uitsluitend aantoonbaar benodigde relaties toe.
6. Ontwerp daarna pas zoeken, presentatielaag en API-contracten.

Iedere stap krijgt een afzonderlijk GitHub Issue, featurebranch en toetsbare pull request.

## 12. Architectuurtoets

Dit model voldoet wanneer:

- ieder zelfstandig object één primaire eigenaar heeft;
- ondersteunende modules geen domeinbron worden;
- geen eigenaarloze generieke contextmodule ontstaat;
- relaties niet dubbel worden opgeslagen;
- BREBO-2026-004 zonder conflict kan worden geïmplementeerd;
- toekomstige inspectie-, MJOP- en AI-domeinen kunnen worden toegevoegd zonder de 1.0-objecten te vermengen.

## 13. Open reviewpunten

Voor definitieve goedkeuring moeten reviewers expliciet bevestigen:

1. of Werkzaamheid in Platform 1.0 een zelfstandig object moet zijn of voorlopig onderdeel van Dienst blijft;
2. welke module de levenscyclus van gedeelde gebouwclassificaties beheert zodra hergebruik werkelijk nodig is;
3. of `brebo_lens` uitsluitend vaste waarden beheert of later ook zelfstandige methodische content;
4. of Referentie zonder Project mag bestaan;
5. of Project in 1.0 alleen publiceerbare cases bevat of ook interne projectregistratie.
