# BREBO Canonical Information Model 1.0

Status: gereed voor architectuurreview  
Gerelateerd issue: #14  
Besluitbasis: BREBO Domain Model 1.0, PR #13

## 1. Doel

Dit document definieert de techniekonafhankelijke bedrijfsobjecten en gecontroleerde waarden van BREBO Platform 1.0. Het vormt de semantische laag tussen het BREBO-domeinmodel en implementaties zoals Drupal, API, zoekindex, MCP en toekomstige applicaties.

Drupal implementeert BREBO-objecten, maar definieert ze niet.

## 2. Kernregels

1. Eén bedrijfsconcept heeft één canonieke Engelse naam.
2. Ieder zelfstandig object heeft één Nederlandse functionele benaming, één primaire eigenaar en een stabiele identiteit.
3. Drupal-termen zijn uitsluitend implementatiemappings.
4. Externe contracten gebruiken canonieke object- en attribuutnamen.
5. Zoekindexen, API-responses en presentatiemodellen zijn afgeleid en nooit bron van waarheid.
6. Een wijziging van canonieke naam, betekenis, identiteit, zelfstandigheid of eigenaar vereist een ADR.
7. Alleen concepten met een zelfstandige identiteit en levenscyclus krijgen een Object-ID.

## 3. Verboden technische naamlekkage

De volgende termen mogen niet als canonieke bedrijfsobjectnaam of externe resource-naam worden gebruikt:

- `Node`
- `Content`
- `Article`
- `Page`
- `View`
- `Paragraph`
- `TaxonomyTerm`
- `Bundle`
- `EntityReference`

Deze termen mogen alleen voorkomen in technische implementatiedocumentatie en mappings.

## 4. Zelfstandige bedrijfsobjecten Platform 1.0

### 4.1 KnowledgeItem

Nederlandse naam: probleemgerichte kennisbijdrage  
Eigenaar: `brebo_knowledge`

Een zelfstandig, revisioneerbaar kennisobject dat een waarneming duidt en richting geeft aan betekenis, urgentie, risico en eerste onderzoek.

Kernattributen:

- stableId
- title
- observation
- meaning
- urgency
- firstStep
- risks
- lensStages
- publicationStatus
- revision

Relaties:

- 0..n naar `Service`
- 0..n naar `WorkActivity`
- 0..n naar lokale gebouw- en materiaalclassificaties
- 0..n naar `LensStage`

Bron van waarheid: `brebo_knowledge`.  
Externe resource: `/knowledge-items`.

### 4.2 Service

Nederlandse naam: dienst  
Eigenaar: `brebo_service`

Een door BREBO aangeboden samenhangende oplossing voor een onderhoudsvraag.

Kernattributen:

- stableId
- name
- purpose
- scope
- conditions
- exclusions
- publicationStatus

Relaties:

- 0..n naar `WorkActivity`
- 0..n naar `KnowledgeItem`
- 0..n naar `Project`
- 0..n naar `Reference`
- 0..n naar `LensStage`

Bron van waarheid: `brebo_service`.  
Externe resource: `/services`.

### 4.3 WorkActivity

Nederlandse naam: werkzaamheid  
Eigenaar: `brebo_service`

Een zelfstandig, minimaal uitvoeringsobject voor een afgebakende activiteit die binnen één of meer diensten kan worden toegepast.

Kernattributen:

- stableId
- name
- description
- applicability
- expectedResult
- publicationStatus

Relaties:

- 0..n naar `Service`
- 0..n naar `Project`
- 0..n naar `KnowledgeItem`

Bron van waarheid: `brebo_service`.  
Externe resource: `/work-activities`.

### 4.4 Project

Nederlandse naam: projectcase  
Eigenaar: `brebo_project`

Een publiceerbare weergave van een gerealiseerde opdracht met context, scope, uitvoering en aantoonbaar resultaat. Interne projectregistratie, planning, werkvoorbereiding en financiële administratie vallen buiten Platform 1.0.

Kernattributen:

- stableId
- title
- summary
- publicationLocation
- period
- publicationStatus
- results

Relaties:

- 0..n naar `Service`
- 0..n naar `WorkActivity`
- 0..n naar `Reference`
- 0..n naar technische mediarepresentaties

Bron van waarheid: `brebo_project`.  
Externe resource: `/projects`.

### 4.5 Reference

Nederlandse naam: referentie  
Eigenaar: `brebo_reference`

Een gecontroleerde, publiceerbare ervaring of aanbeveling waarvoor herkomst, toestemming en redactionele controle zijn vastgelegd. Een referentie mag zonder projectcase bestaan.

Kernattributen:

- stableId
- statement
- attributionName
- attributionRole
- sourceContext
- consentStatus
- publicationStatus

Relaties:

- 0..1 naar `Project`
- 0..n naar `Service`

Bron van waarheid: `brebo_reference`.  
Externe resource: `/references`.

## 5. Gecontroleerde waarde: LensStage

Nederlandse naam: Lens-fase  
Eigenaar: `brebo_lens`

`LensStage` is geen zelfstandig bedrijfsobject en krijgt geen Object-ID. Het is een gesloten waardencontract met uitsluitend:

- `insight` — Inzicht
- `direction` — Regie
- `realisation` — Realisatie

De waarde mag worden ingebed in `KnowledgeItem`, `Service` en later expliciet goedgekeurde objecten. Er is geen zelfstandige publieke resource vereist.

Wijziging van waarden of betekenis vereist een ADR.

## 6. Lokale classificaties

`BuildingType`, `BuildingPart` en `MaterialClassification` zijn in Platform 1.0 geen platformbrede zelfstandige objecten en krijgen geen Object-ID.

Regels:

1. Een classificatie blijft lokaal bij het domein dat haar nodig heeft.
2. Zij wordt alleen ingevoerd voor een goedgekeurde usecase.
3. Een gedeelde classificatie vereist vooraf één expliciete levenscycluseigenaar en een afzonderlijk architectuurbesluit.
4. Er komt geen generieke eigenaarloze module voor taxonomie of context.
5. Lokale classificaties mogen extern alleen via een expliciete mapping worden ontsloten.

## 7. Technische mediarepresentatie

Een mediarecord of bestand is geen zelfstandig BREBO-bedrijfsobject in Platform 1.0 en krijgt geen Object-ID.

Drupal Media/File kan bestanden, alt-tekst, rechtenstatus en technische metadata beheren. De inhoudelijke betekenis en relatie blijven bij het verwijzende domeinobject. Een extern mediacontract vereist afzonderlijke goedkeuring.

## 8. Identiteits- en levenscyclusregels

1. Zelfstandige objecten krijgen een stabiele interne identifier die niet wijzigt bij titel- of naamwijziging.
2. Publieke URL's, slugs, Drupal numeric IDs en bundle-ID's zijn geen canonieke identiteit.
3. Revisies wijzigen de identiteit van het object niet.
4. Verwijderde of gearchiveerde identifiers worden niet hergebruikt.
5. Zelfstandige publiceerbare objecten ondersteunen minimaal `concept`, `published` en `archived`.
6. Afgeleide representaties moeten opnieuw kunnen worden opgebouwd uit de bronobjecten.

## 9. Contractregels

1. API, MCP en toekomstige applicaties gebruiken dezelfde canonieke objectnamen.
2. Resource-namen zijn stabiel, meervoudig en domeingericht.
3. Interne Drupal-veldnamen worden niet rechtstreeks extern gepubliceerd.
4. Externe contracten krijgen een expliciete mapping naar canonieke attributen.
5. Een presentatieobject, zoekresultaat of mediarepresentatie introduceert geen nieuw domeinobject.
6. Implementatiemappings zijn vervangbaar en mogen de canonieke betekenis niet wijzigen.

## 10. Synoniemen en voorkeursnamen

| Canonieke naam | Nederlandse voorkeursnaam | Niet als canonieke naam gebruiken |
|---|---|---|
| `KnowledgeItem` | probleemgerichte kennisbijdrage | article, page, content, document |
| `Service` | dienst | product, service page |
| `WorkActivity` | werkzaamheid | klus, task, job |
| `Project` | projectcase | case, portfolio item, intern projectrecord |
| `Reference` | referentie | testimonial, review |
| `LensStage` | Lens-fase | taxonomy term, zelfstandig bedrijfsobject |

Synoniemen mogen redactioneel worden gebruikt, maar niet in technische contracten zonder expliciete mapping.

## 11. Implementatiemapping

De implementatiemapping wordt per zelfstandig object in het Object Registry vastgelegd.

Voorbeeld:

```text
KnowledgeItem
  -> Drupal node bundle: brebo_knowledge_item
  -> API resource: /knowledge-items
  -> Search document type: knowledge_item
  -> MCP representation: knowledgeItem
```

## 12. Buiten Platform 1.0

Niet opgenomen als actief zelfstandig canoniek object:

- Building
- Asset
- Inspection
- InspectionFinding
- ConditionMeasurement
- MaintenancePlan
- MaintenanceAction
- Customer
- ContactPerson
- Supplier
- Quote
- Order
- Invoice
- InternalProjectRecord
- AIExecution
- AgentRun

Toevoeging vereist een afzonderlijke RFC of ADR.

## 13. Architectuurtoets

Dit CIM voldoet wanneer:

- uitsluitend zelfstandige bedrijfsobjecten een Object-ID krijgen;
- `Project` uitsluitend de publiceerbare projectcase betekent;
- `Reference` zonder project kan bestaan onder expliciete herkomst- en toestemmingsvoorwaarden;
- `LensStage` een gesloten waardencontract blijft;
- gebouw- en materiaalclassificaties lokaal blijven totdat gedeeld eigenaarschap formeel is besloten;
- technische mediarecords geen domeinobject worden;
- Drupal-termen alleen als implementatiemapping voorkomen.
