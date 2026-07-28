# BREBO Object Registry 1.0

Status: concept voor architectuurreview  
Gerelateerd issue: #14  
Afhankelijk van: BREBO Domain Model 1.0 en BREBO Canonical Information Model 1.0

## 1. Doel

Dit register kent ieder BREBO-bedrijfsobject een vaste Object-ID, canonieke naam, primaire eigenaar, status en implementatiemapping toe.

Object-ID's zijn onveranderlijk. Een object wordt niet hernummerd wanneer naam, status of implementatie verandert.

## 2. Statuswaarden

- `concept`: nog niet bindend vastgesteld;
- `definitief`: bindend onderdeel van het platformmodel;
- `deprecated`: niet meer gebruiken voor nieuwe implementaties;
- `toekomstig`: erkend toekomstig object, nog buiten de actieve platformscope.

## 3. Register Platform 1.0

| Object-ID | Canonieke naam | Nederlandse naam | Primaire eigenaar | Status | Eerste platformversie | Implementatiemapping | Externe resource | Wijzigingsregel |
|---|---|---|---|---|---|---|---|---|
| `OBJ-001` | `KnowledgeItem` | probleemgerichte kennisbijdrage | `brebo_knowledge` | concept | 1.0 | Drupal node bundle `brebo_knowledge_item` | `/knowledge-items` | naam, betekenis, identiteit of eigenaar vereist ADR |
| `OBJ-002` | `Service` | dienst | `brebo_service` | concept | 1.0 | nog vast te stellen | `/services` | naam, betekenis, identiteit of eigenaar vereist ADR |
| `OBJ-003` | `WorkActivity` | werkzaamheid | `brebo_service` | concept | 1.0 minimaal | nog vast te stellen | `/work-activities` | zelfstandigheid en eigenaar vereist architectuurbesluit |
| `OBJ-004` | `Project` | project | `brebo_project` | concept | 1.0 | nog vast te stellen | `/projects` | naam, betekenis, identiteit of eigenaar vereist ADR |
| `OBJ-005` | `Reference` | referentie | `brebo_reference` | concept | 1.0 | nog vast te stellen | `/references` | naam, betekenis, identiteit of eigenaar vereist ADR |
| `OBJ-006` | `LensStage` | Lens-fase | `brebo_lens` | concept | 1.0 | gesloten waardenlijst; mapping nog vast te stellen | geen zelfstandige publieke resource vereist | waarden of betekenis wijzigen vereist ADR |
| `OBJ-007` | `BuildingType` | gebouwtype | nog definitief vast te stellen | concept | 1.0 beperkt | gecontroleerde classificatie; mapping nog vast te stellen | geen zelfstandige resource in 1.0 | eigenaar en gedeeld gebruik vereisen besluit |
| `OBJ-008` | `BuildingPart` | gebouwdeel | nog definitief vast te stellen | concept | 1.0 beperkt | gecontroleerde classificatie; mapping nog vast te stellen | geen zelfstandige resource in 1.0 | eigenaar en gedeeld gebruik vereisen besluit |
| `OBJ-009` | `MaterialClassification` | materiaalclassificatie | nog definitief vast te stellen | concept | 1.0 indien nodig | gecontroleerde classificatie; mapping nog vast te stellen | geen zelfstandige resource in 1.0 | invoering vereist concrete usecase en eigenaar |
| `OBJ-010` | `MediaAsset` | media-object | Drupal Media/File; ondersteuning `brebo_media` | concept | 1.0 ondersteunend | Drupal media entity | alleen via goedgekeurd mediacontract | domeinbetekenis mag niet naar `brebo_media` verschuiven |

## 4. Bindende definities

### OBJ-001 — KnowledgeItem

Een zelfstandig, revisioneerbaar kennisobject dat een waarneming duidt en richting geeft aan betekenis, urgentie, risico en eerste onderzoek.

Synoniemen: kennisbijdrage.  
Verboden canonieke alternatieven: article, page, content, document.

### OBJ-002 — Service

Een door BREBO aangeboden samenhangende oplossing voor een onderhoudsvraag.

Synoniemen: dienst.  
Verboden canonieke alternatieven: product, service page.

### OBJ-003 — WorkActivity

Een afgebakende uitvoeringsactiviteit die binnen één of meer diensten kan worden toegepast.

Synoniemen: werkzaamheid.  
Verboden canonieke alternatieven: klus, job, task.

### OBJ-004 — Project

Een gerealiseerde of publiceerbare opdracht met context, scope, uitvoering en aantoonbaar resultaat.

Synoniemen: project.  
Verboden canonieke alternatieven: case, portfolio item.

### OBJ-005 — Reference

Een gecontroleerde, publiceerbare ervaring of aanbeveling waarvoor toestemming en redactionele controle zijn vastgelegd.

Synoniemen: referentie.  
Verboden canonieke alternatieven: testimonial, review.

### OBJ-006 — LensStage

Een gesloten methodische waarde binnen de BREBO Lens: Inzicht, Regie of Realisatie.

Synoniemen: Lens-fase.  
Verboden canonieke alternatieven: taxonomy term.

### OBJ-007 — BuildingType

Een gecontroleerde contextclassificatie voor een type gebouw.

Synoniemen: gebouwtype.  
Statusbeperking: geen zelfstandig gebouwobject.

### OBJ-008 — BuildingPart

Een gecontroleerde contextclassificatie voor een fysiek deel van een gebouw.

Synoniemen: gebouwdeel.

### OBJ-009 — MaterialClassification

Een gecontroleerde classificatie van een relevant bouwmateriaal.

Synoniemen: materiaalclassificatie, materiaaltype.

### OBJ-010 — MediaAsset

Een technisch herbruikbaar bestand met rechten- en toegankelijkheidsmetadata. Domeinbetekenis blijft bij het verwijzende bronobject.

Synoniemen: media-object.  
Verboden canonieke alternatieven: file node, image node.

## 5. Identificatieregels

1. Object-ID's worden nooit hergebruikt.
2. Verwijderde objecten blijven als `deprecated` in het register staan.
3. Nieuwe objecten krijgen het eerstvolgende vrije nummer.
4. Subtypen krijgen niet automatisch een eigen Object-ID; daarvoor is zelfstandige identiteit en levenscyclus vereist.
5. Drupal bundle IDs, database-ID's, UUID's en URL-aliases zijn geen Object-ID.

## 6. Implementatiemappings

Een implementatiemapping mag worden gewijzigd zonder het object te hernoemen wanneer:

- de bindende betekenis gelijk blijft;
- de identiteit behouden blijft;
- de primaire eigenaar niet verandert;
- externe contracten compatibel blijven of formeel worden geversioneerd.

Voor iedere runtime-implementatie moet later minimaal worden geregistreerd:

- technologie;
- entiteits- of recordtype;
- interne machine name;
- canonieke attribuutmapping;
- externe resource- of contractnaam;
- migratie- en compatibiliteitsregel.

## 7. Wijzigingsbeheer

Een ADR is verplicht bij wijziging van:

- canonieke naam;
- bindende definitie;
- primaire eigenaar;
- identiteit;
- zelfstandigheid van het object;
- Object-ID;
- betekenis van een bestaande relatie.

Een normale documentatie- of implementatie-PR volstaat voor:

- aanvullen van een goedgekeurde technische mapping;
- toevoegen van een niet-bindend voorbeeld;
- corrigeren van een taal- of typefout zonder betekeniswijziging;
- registreren van een nieuwe compatibele representatie.

## 8. Toekomstige objecten — gereserveerd maar niet genummerd

De volgende begrippen zijn erkend, maar krijgen pas een Object-ID na afzonderlijke goedkeuring:

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

Het ontbreken van een Object-ID betekent dat deze begrippen niet als actief BREBO Platform 1.0-object mogen worden geïmplementeerd.

## 9. Goedkeuringsvoorwaarde

Alle objecten blijven `concept` totdat:

1. het BREBO Domain Model 1.0 is goedgekeurd;
2. het CIM inhoudelijk is getoetst;
3. open eigenaarschapsvragen zijn opgelost;
4. de Object-ID's formeel zijn bevestigd.

Na goedkeuring worden de toepasselijke regels op `definitief` gezet zonder hernummering.