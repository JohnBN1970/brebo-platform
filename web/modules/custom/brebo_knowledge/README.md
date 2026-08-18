# BREBO Knowledge

## Doel

`brebo_knowledge` beheert het zelfstandige, revisioneerbare nodeobject
`brebo_knowledge_item` met het beheerlabel **Probleemgerichte
kennisbijdrage**. Het object begeleidt een gebouwsignaal via betekenis, risico
en informatieverzameling naar een verantwoorde volgende stap.

## Velden

De vijf verplichte kernvelden zijn:

- `field_knowledge_observation` — Waarneming en afbakening;
- `field_knowledge_meaning` — Mogelijke betekenis en oorzaken;
- `field_knowledge_risk` — Risico en urgentie;
- `field_knowledge_next_step` — Te verzamelen informatie en volgende stap;
- `field_knowledge_basis` — Bron, geldigheid en deskundige controle.

De twee optionele BREBO Lens-velden zijn:

- `field_knowledge_regie` — Regie;
- `field_knowledge_realization` — Mogelijke oplossingsrichtingen.

## Configuratie-eigenaarschap en dependencies

De zestien configuratieobjecten in `config/install` zijn de enige inhoudelijke
configuratiebron: één nodebundle, zeven field storages, zeven field instances
en één default entity form display. De module heeft uitsluitend directe
dependencies op `drupal:node` en `drupal:text`.

## Installatie en update

Nieuwe installaties installeren de zestien objecten rechtstreeks vanuit
`config/install`. Bestaande installaties gebruiken
`brebo_knowledge_update_11001()`.

De update controleert alle zestien objecten vóór iedere schrijfactie. Als geen
object bestaat, installeert Drupal de module-eigen defaultconfiguratie en volgt
een volledige nacontrole. Als alle objecten bestaan en inhoudelijk gelijk zijn,
is de update een geldige no-op. Installatiemetadata die Drupal zelf aan actieve
configuratie toevoegt, wordt bij de inhoudelijke vergelijking genegeerd.

De update stopt vóór schrijven wanneer de bronconfiguratie incompleet is,
slechts een deel van de objecten bestaat, een object inhoudelijk afwijkt of de
configuratie niet volledig kan worden gevalideerd. Conflicten worden niet
automatisch hersteld, verwijderd of overschreven.

Maak vóór de updateroute een volledige databaseback-up en stel de actieve
configuratie veilig. Alleen code terugzetten is geen volledige rollback:
herstel bij een mislukte update ook de database vanuit de pre-updateback-up.
Nadat echte kennisnodes bestaan, is uitsluitend een afzonderlijk goedgekeurd
migratie- of herstelplan een geldige rollbackroute.

## Expliciete uitsluitingen

De module levert geen publieke presentatie, theming, Views, zoekfunctionaliteit,
SEO, media, AI, projecten, taxonomie, relaties, workflows, moderatie,
permissions, routes, services, controllers, maatwerkformulieren,
preprocesslogica, themelogica, voorbeeldcontent, migraties of contentimport.
De redactionele beleidspunten rond publieke publicatie worden niet technisch
afgedwongen.
