# BREBO Canonical Information Model 1.0

Status: concept voor architectuurreview  
Gerelateerd issue: #14  
Afhankelijk van: Issue #12 en PR #13

## 1. Doel

Dit document definieert de techniekonafhankelijke bedrijfsobjecten van BREBO Platform 1.0. Het vormt de stabiele semantische laag tussen het BREBO-domeinmodel en concrete implementaties zoals Drupal, API, zoekindex, MCP, GPT Actions en toekomstige applicaties.

Drupal implementeert BREBO-objecten, maar definieert ze niet.

## 2. Kernregels

1. Eén bedrijfsconcept heeft één canonieke Engelse naam.
2. Ieder canoniek object heeft één Nederlandse functionele benaming.
3. Namen, betekenis, identiteit en primaire eigenaar zijn techniekonafhankelijk.
4. Drupal-termen zijn uitsluitend implementatiemappings.
5. Externe contracten gebruiken canonieke resource-namen.
6. Een wijziging van canonieke naam, betekenis, identiteit of eigenaar vereist een ADR.
7. Zoekindexen, API-responses en presentatiemodellen zijn afgeleid en nooit bron van waarheid.

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

Deze termen mogen alleen voorkomen in technische implementatiedocumentatie.

## 4. Canonieke objecten Platform 1.0

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
- 0..n naar `BuildingType`
- 0..n naar `BuildingPart`
- 0..n naar `MaterialClassification`
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

Een afgebakende uitvoeringsactiviteit die binnen één of meer diensten kan worden toegepast.

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

Nederlandse naam: project  
Eigenaar: `brebo_project`

Een gerealiseerde of publiceerbare opdracht met context, scope, uitvoering en aantoonbaar resultaat.

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
- 0..n naar `MediaAsset`

Bron van waarheid: `brebo_project`.

Externe resource: `/projects`.

### 4.5 Reference

Nederlandse naam: referentie  
Eigenaar: `brebo_reference`

Een gecontroleerde, publiceerbare ervaring of aanbeveling waarvoor toestemming en redactionele controle zijn vastgelegd.

Kernattributen:

- stableId
- statement
- attributionName
- attributionRole
- consentStatus
- publicationStatus

Relaties:

- 0..1 naar `Project`
- 0..n naar `Service`

Bron van waarheid: `brebo_reference`.

Externe resource: `/references`.

### 4.6 LensStage

Nederlandse naam: Lens-fase  
Eigenaar: `brebo_lens`

Een gesloten, gecontroleerde methodische waarde binnen de BREBO Lens: Inzicht, Regie of Realisatie.

Kernattributen:

- stableCode
- name
- definition
- displayOrder
- status

Relaties:

- gerefereerd door `KnowledgeItem`
- gerefereerd door `Service`

Bron van waarheid: `brebo_lens`.

Externe representatie: ingebedde gecontroleerde waarde; geen zelfstandige publieke resource vereist in Platform 1.0.

### 4.7 BuildingType

Nederlandse naam: gebouwtype  
Eigenaar: definitief vast te stellen conform Domain Model 1.0

Een gecontroleerde contextclassificatie voor een type gebouw.

Platform 1.0-status: beperkt; uitsluitend invoeren wanneer een goedgekeurde usecase dit vereist.

### 4.8 BuildingPart

Nederlandse naam: gebouwdeel  
Eigenaar: definitief vast te stellen conform Domain Model 1.0

Een gecontroleerde contextclassificatie voor een fysiek deel van een gebouw.

Platform 1.0-status: beperkt; uitsluitend invoeren wanneer een goedgekeurde usecase dit vereist.

### 4.9 MaterialClassification

Nederlandse naam: materiaalclassificatie  
Eigenaar: definitief vast te stellen conform Domain Model 1.0

Een gecontroleerde classificatie van een relevant bouwmateriaal.

Platform 1.0-status: alleen indien direct nodig.

### 4.10 MediaAsset

Nederlandse naam: media-object  
Eigenaar: Drupal Media/File met technische ondersteuning door `brebo_media`

Een technisch herbruikbaar bestand met rechten- en toegankelijkheidsmetadata. De betekenis en domeinrelatie blijven eigendom van het verwijzende domein.

Kernattributen:

- stableId
- fileReference
- altText
- rightsStatus
- technicalMetadata

Bron van waarheid: Drupal Media/File.

Externe resource: alleen via een expliciet goedgekeurd mediacontract.

## 5. Identiteitsregels

1. Zelfstandige objecten krijgen een stabiele interne identifier die niet wijzigt bij titel- of naamwijziging.
2. Publieke URL's en slugs zijn geen primaire identiteit.
3. Drupal numeric IDs zijn implementatiedetails en worden niet als canonieke identiteit gepubliceerd.
4. Revisies wijzigen de identiteit van het object niet.
5. Verwijderde of gearchiveerde identifiers worden niet hergebruikt.

## 6. Levenscyclus

Voor zelfstandige publiceerbare objecten gelden minimaal:

- `concept`
- `published`
- `archived`

Kennisobjecten zijn revisioneerbaar. Afgeleide representaties moeten altijd opnieuw kunnen worden opgebouwd uit de bronobjecten.

## 7. Contractregels

1. API, MCP en toekomstige applicaties gebruiken dezelfde canonieke objectnamen.
2. Resource-namen zijn stabiel, meervoudig en domeingericht.
3. Interne Drupal-veldnamen worden niet rechtstreeks extern gepubliceerd.
4. Externe contracten krijgen een expliciete mapping naar canonieke attributen.
5. Een presentatieobject mag nooit een nieuw domeinobject introduceren.
6. Een zoekresultaat is een projectie van een bronobject en geen zelfstandig bedrijfsobject.

## 8. Synoniemen en voorkeursnamen

| Canonieke naam | Toegestane Nederlandse benaming | Niet als canonieke naam gebruiken |
|---|---|---|
| `KnowledgeItem` | probleemgerichte kennisbijdrage, kennisbijdrage | article, page, content, document |
| `Service` | dienst | product, page |
| `WorkActivity` | werkzaamheid | klus, task, job |
| `Project` | project | case, portfolio-item |
| `Reference` | referentie | testimonial, review |
| `LensStage` | Lens-fase | taxonomy term |
| `MediaAsset` | media-object | file, image node |

Synoniemen mogen redactioneel worden gebruikt, maar niet in technische contracten zonder expliciete mapping.

## 9. Implementatiemapping

De implementatiemapping wordt per object in het Object Registry vastgelegd. Deze mapping is vervangbaar en mag de canonieke definitie niet wijzigen.

Voorbeeld:

```text
KnowledgeItem
  -> Drupal node bundle: brebo_knowledge_item
  -> API resource: /knowledge-items
  -> Search document type: knowledge_item
  -> MCP representation: knowledgeItem
```

## 10. Buiten Platform 1.0

Niet opgenomen als definitief canoniek object:

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
- AIExecution
- AgentRun

Toevoeging vereist een afzonderlijke RFC of ADR.

## 11. Goedkeuringsvoorwaarde

Dit CIM kan alleen definitief worden vastgesteld wanneer het niet conflicteert met het goedgekeurde BREBO Domain Model 1.0. Bij afwijking is het domeinmodel leidend totdat een formeel wijzigingsbesluit is genomen.