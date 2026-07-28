# BREBO KnowledgeItem Drupal Entity Mapping 1.0

Status: concept voor architectuurreview  
Gerelateerd issue: #19  
Object: `OBJ-001 KnowledgeItem`  
Primaire eigenaar: `brebo_knowledge`

## 1. Doel

Dit document legt de definitieve Drupal-implementatiemapping voor `OBJ-001 KnowledgeItem` vast.

`KnowledgeItem` is het canonieke BREBO-bedrijfsobject. Drupal levert uitsluitend de technische opslag, revisie, redactie en presentatie. Drupal-termen wijzigen de betekenis, identiteit of levenscyclus van het bedrijfsobject niet.

## 2. Primaire opslagmapping

| Onderdeel | Definitieve mapping |
|---|---|
| Canoniek object | `KnowledgeItem` |
| Object-ID | `OBJ-001` |
| Eigenaarmodule | `brebo_knowledge` |
| Drupal entity type | `node` |
| Drupal bundle | `brebo_knowledge_item` |
| Bundlelabel | Kennisbijdrage |
| Revisies | verplicht ingeschakeld |
| Vertaalbaar | voorbereid, maar niet verplicht voor Platform 1.0 |
| Primaire publieke resource | buiten scope van deze mapping |

Er is voor Platform 1.0 maximaal één primaire Drupal-opslagmapping voor `KnowledgeItem`.

Een tweede bundle, custom content entity of parallelle tabel voor hetzelfde canonieke object is niet toegestaan zonder ADR.

## 3. Identiteit

### 3.1 Canonieke identiteit

Ieder `KnowledgeItem` krijgt een stabiele functionele identifier in:

```text
field_brebo_stable_id
```

Formaat:

```text
KI-000001
```

Regels:

1. de identifier is uniek en onveranderlijk;
2. de identifier wordt bij eerste opslag gegenereerd door `brebo_knowledge`;
3. handmatige wijziging via het redactionele formulier is niet toegestaan;
4. verwijderde identifiers worden niet hergebruikt;
5. revisies behouden dezelfde identifier;
6. node-ID, UUID, titel, URL-alias en revisie-ID zijn geen canonieke identiteit.

### 3.2 Technische identifiers

Drupal `nid`, UUID en revision ID blijven beschikbaar voor technische verwerking, maar worden niet gebruikt als functionele Object-ID in externe of domeincontracten.

## 4. Canonieke veldmapping

| Canoniek attribuut | Drupal-mapping | Veldtype | Cardinaliteit | Verplicht | Revisioneerbaar | Opmerking |
|---|---|---|---:|---:|---:|---|
| `stableId` | `field_brebo_stable_id` | string | 1 | ja | ja | systeembeheerd en uniek |
| `title` | node `title` | string | 1 | ja | ja | redactionele titel |
| `observation` | `field_brebo_observation` | text_long | 1 | ja | ja | feitelijke waarneming of vraag |
| `meaning` | `field_brebo_meaning` | text_long | 1 | ja | ja | betekenis en duiding |
| `urgency` | `field_brebo_urgency` | list_string | 1 | ja | ja | gesloten waardencontract |
| `firstStep` | `field_brebo_first_step` | text_long | 1 | ja | ja | aanbevolen eerste onderzoek of handeling |
| `risks` | `field_brebo_risks` | text_long | 1 | nee | ja | relevante risico’s en gevolgen |
| `lensStages` | `field_brebo_lens_stages` | list_string | onbeperkt | nee | ja | gesloten waarden uit `LensStage` |
| `publicationStatus` | node `status` plus moderatiestatus | boolean/workflow | 1 | ja | ja | zie lifecycle |
| `revision` | node revision metadata | revision | n.v.t. | ja | n.v.t. | technisch revisiemechanisme |

## 5. Gesloten waarden

### 5.1 Urgency

`field_brebo_urgency` gebruikt uitsluitend:

| Machinewaarde | Label |
|---|---|
| `low` | Laag |
| `normal` | Normaal |
| `high` | Hoog |
| `critical` | Kritiek |

Wijziging van deze waarden of betekenis vereist een ADR, omdat zij onderdeel worden van het canonieke contract.

### 5.2 LensStage

`field_brebo_lens_stages` gebruikt uitsluitend:

| Machinewaarde | Label |
|---|---|
| `insight` | Inzicht |
| `direction` | Regie |
| `realisation` | Realisatie |

Het veld is in Platform 1.0 een gesloten lijstveld. Er wordt geen taxonomy vocabulary voor Lens-fasen aangemaakt.

`brebo_lens` beheert de semantiek van de waarden. `brebo_knowledge` bezit het veld en de opslag op `KnowledgeItem`.

## 6. Relaties

Relatievelden worden alleen toegevoegd wanneer de doelmodule en het doelobject werkelijk beschikbaar zijn.

| Canonieke relatie | Drupal-veld | Type | Cardinaliteit | Eigendom veldconfiguratie | Regel |
|---|---|---|---:|---|---|
| naar `Service` | `field_brebo_services` | entity_reference | onbeperkt | `brebo_knowledge` | target bundle wordt door `brebo_service` geleverd |
| naar `WorkActivity` | `field_brebo_work_activities` | entity_reference | onbeperkt | `brebo_knowledge` | target bundle wordt door `brebo_service` geleverd |
| naar media | `field_brebo_media` | entity_reference | onbeperkt | `brebo_knowledge` | technische mediarecords; betekenis blijft bij KnowledgeItem |

Regels:

1. relaties worden niet als vrije tekst of gekopieerde labels opgeslagen;
2. een cross-domain relatie verplicht niet automatisch tot een wederzijdse harde moduleafhankelijkheid;
3. relatievelden mogen conditioneel worden geleverd via afzonderlijke configuratieslice zodra de doelmodule bestaat;
4. verwijdering van een doelobject mag geen stil verlies van betekenis veroorzaken;
5. bidirectionele navigatie wordt afgeleid; dezelfde relatie wordt niet dubbel opgeslagen.

## 7. Lokale classificaties

Platform 1.0 kent nog geen verplichte classificatievelden voor gebouwtype, bouwdeel of materiaal.

Toevoeging is alleen toegestaan wanneer:

1. een concrete kennis-usecase is goedgekeurd;
2. de waardenlijst en lifecycle zijn vastgelegd;
3. `brebo_knowledge` eigenaar blijft van de lokale classificatie;
4. geen generieke `brebo_taxonomy`- of `brebo_context`-module ontstaat;
5. de classificatie geen nieuw zelfstandig bedrijfsobject simuleert.

## 8. Lifecycle en publicatie

### 8.1 Minimale toestanden

De functionele lifecycle bestaat uit:

```text
concept -> published -> archived
```

Drupal-mapping:

| Functionele status | Drupalstatus | Moderatiestatus | Publiek zichtbaar |
|---|---:|---|---:|
| `concept` | 0 | `draft` | nee |
| `published` | 1 | `published` | ja |
| `archived` | 0 | `archived` | nee |

Wanneer Content Moderation niet in de eerste implementatieslice wordt gebruikt, moet `brebo_knowledge` minimaal een gelijkwaardig expliciet statusveld leveren. Stilzwijgend reduceren tot alleen node `status` is niet toegestaan zonder vastgelegd tijdelijk besluit.

### 8.2 Revisies

1. iedere inhoudelijke wijziging maakt een nieuwe revisie;
2. publicatie en archivering zijn revisiegebaseerd;
3. revisielog is verplicht bij redactionele wijzigingen;
4. terugzetten van een revisie verandert `stableId` niet;
5. zoek- en API-projecties gebruiken alleen de actieve gepubliceerde revisie.

## 9. Redactionele validatie

Voor publicatie gelden minimaal:

- titel aanwezig;
- observation aanwezig;
- meaning aanwezig;
- urgency bevat een geldige waarde;
- firstStep aanwezig;
- LensStage-waarden zijn geldig;
- alle entity references verwijzen naar bestaande toegankelijke objecten;
- geen ongepubliceerde cross-domain inhoud wordt onbedoeld publiek ontsloten.

`risks` is optioneel, maar wanneer risico’s bekend en relevant zijn, mogen zij niet uitsluitend impliciet in andere velden worden verstopt.

## 10. Toegang

Minimale rollen en bevoegdheden:

| Rol/capability | Concept maken | Eigen concept wijzigen | Alle concepten wijzigen | Publiceren | Archiveren | Revisies bekijken |
|---|---:|---:|---:|---:|---:|---:|
| kennisredacteur | ja | ja | nee | nee | nee | eigen |
| kennisbeheerder | ja | ja | ja | ja | ja | alle |
| anonieme bezoeker | nee | nee | nee | nee | nee | nee |

De concrete Drupal-permissions worden door `brebo_knowledge` geleverd en mogen niet als algemene sitebrede contentpermissions worden gemodelleerd.

## 11. Configuratie-eigendom

`brebo_knowledge` bezit minimaal:

- node type `brebo_knowledge_item`;
- alle `field.storage.node.*` die exclusief voor KnowledgeItem zijn;
- alle `field.field.node.brebo_knowledge_item.*`;
- form displays;
- view displays;
- lifecycle- en workflowconfiguratie voor KnowledgeItem;
- module-eigen permissions;
- eventuele conditionele configuratie voor relaties.

Andere modules mogen deze configuratie niet tijdens installatie muteren.

Gedeelde technische storage wordt alleen gebruikt wanneer daadwerkelijk meerdere bundles dezelfde semantiek en lifecycle delen. Gelijke veldtypes alleen zijn onvoldoende reden voor gedeelde storage.

## 12. Installatie, updates en uninstall

### Installatie

- installatie is herhaalbaar en faalt gecontroleerd bij conflicterende bestaande configuratie;
- installatie overschrijft geen bestaande veldconfiguratie met afwijkende semantiek;
- de module maakt geen content aan als verborgen installatieside-effect.

### Updates

- veldwijzigingen die data kunnen verliezen vereisen een expliciet updatepad;
- machine-namen worden na eerste release niet gewijzigd zonder migratie en ADR;
- gewijzigde gesloten waarden vereisen dat bestaande content valideerbaar wordt gemigreerd.

### Uninstall

- uninstall verwijdert nooit gegevens of configuratie van andere domeinmodules;
- bij bestaande KnowledgeItem-content wordt destructieve uninstall geblokkeerd of vooraf expliciet afgehandeld;
- cross-domain relaties worden gecontroleerd op impact;
- stable IDs worden na export of verwijdering niet hergebruikt.

## 13. Niet opgenomen in deze slice

Buiten scope:

- API-resources;
- MCP-tools;
- zoekindexmapping;
- frontendcomponenten;
- gebouw-, bouwdeel- en materiaalclassificaties;
- automatische AI-verrijking;
- import uit externe kennisbronnen;
- entity mapping voor Service, WorkActivity, Project of Reference.

## 14. Implementatieacceptatiecriteria

Een latere implementatie-PR voor `brebo_knowledge` is architectuurconform wanneer:

1. de primaire mapping `node:brebo_knowledge_item` wordt gebruikt;
2. alle verplichte velden exact of via een expliciet gemotiveerde equivalente mapping aanwezig zijn;
3. stable ID uniek, onveranderlijk en systeembeheerd is;
4. revisies en revisielogs aantoonbaar werken;
5. concept, published en archived toetsbaar zijn;
6. permissions de redactionele scheiding afdwingen;
7. er geen directe afhankelijkheid op presentatie-, zoek- of API-modules bestaat;
8. cross-domain relaties conditioneel en zonder duplicatie zijn ingericht;
9. installatie en uninstall geen configuratie van andere modules muteren;
10. geautomatiseerde tests identiteit, validatie, lifecycle en toegang afdekken.

## 15. Reviewvragen

Voor definitieve goedkeuring moeten reviewers bevestigen:

1. of `node:brebo_knowledge_item` de juiste primaire opslagmapping is;
2. of de canonieke attributen volledig en zonder technische naamlekkage zijn gemapt;
3. of urgency als gesloten waardencontract voldoende stabiel is;
4. of Content Moderation verplicht moet zijn in de eerste implementatieslice;
5. of de stable-ID-vorm `KI-000001` bestuurlijk wordt geaccepteerd;
6. of relatievelden direct of pas per doelmodule moeten worden geleverd.
