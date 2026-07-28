# BREBO Object Registry 1.0

Status: gereed voor architectuurreview  
Gerelateerd issue: #14  
Besluitbasis: BREBO Domain Model 1.0 en BREBO Canonical Information Model 1.0

## 1. Doel

Dit register kent ieder zelfstandig BREBO-bedrijfsobject een vaste Object-ID, canonieke naam, primaire eigenaar, status en implementatiemapping toe.

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
| `OBJ-003` | `WorkActivity` | werkzaamheid | `brebo_service` | concept | 1.0 minimaal | nog vast te stellen | `/work-activities` | zelfstandigheid, betekenis of eigenaar vereist ADR |
| `OBJ-004` | `Project` | projectcase | `brebo_project` | concept | 1.0 | nog vast te stellen | `/projects` | mag geen interne projectadministratie omvatten; wijziging vereist ADR |
| `OBJ-005` | `Reference` | referentie | `brebo_reference` | concept | 1.0 | nog vast te stellen | `/references` | naam, betekenis, identiteit of eigenaar vereist ADR |

## 4. Bindende definities

### OBJ-001 — KnowledgeItem

Een zelfstandig, revisioneerbaar kennisobject dat een waarneming duidt en richting geeft aan betekenis, urgentie, risico en eerste onderzoek.

Synoniem: kennisbijdrage.  
Verboden canonieke alternatieven: article, page, content, document.

### OBJ-002 — Service

Een door BREBO aangeboden samenhangende oplossing voor een onderhoudsvraag.

Synoniem: dienst.  
Verboden canonieke alternatieven: product, service page.

### OBJ-003 — WorkActivity

Een zelfstandig, minimaal uitvoeringsobject voor een afgebakende activiteit die binnen één of meer diensten kan worden toegepast.

Synoniem: werkzaamheid.  
Verboden canonieke alternatieven: klus, job, task.

### OBJ-004 — Project

Een publiceerbare weergave van een gerealiseerde opdracht met context, scope, uitvoering en aantoonbaar resultaat.

Synoniem: projectcase.  
Verboden canonieke alternatieven: interne projectregistratie, planningrecord, werkvoorbereidingsrecord, portfolio item.

### OBJ-005 — Reference

Een gecontroleerde, publiceerbare ervaring of aanbeveling waarvoor herkomst, toestemming en redactionele controle zijn vastgelegd. Een referentie mag zonder projectcase bestaan.

Synoniem: referentie.  
Verboden canonieke alternatieven: testimonial, review.

## 5. Gecontroleerde waarden zonder Object-ID

De volgende begrippen zijn wel canoniek gedefinieerd, maar zijn geen zelfstandige bedrijfsobjecten en krijgen daarom geen Object-ID:

### LensStage

Een gesloten waardencontract binnen de BREBO Lens met uitsluitend:

- `insight` — Inzicht
- `direction` — Regie
- `realisation` — Realisatie

Eigenaar: `brebo_lens`.  
Wijziging van waarden of betekenis vereist een ADR.

## 6. Lokale classificaties zonder Object-ID

De volgende classificaties blijven lokaal bij het gebruikende domein en krijgen in Platform 1.0 geen Object-ID:

- `BuildingType`
- `BuildingPart`
- `MaterialClassification`

Een platformbrede gedeelde variant vereist vooraf:

1. een aantoonbare goedgekeurde usecase;
2. één expliciete levenscycluseigenaar;
3. een afzonderlijk architectuurbesluit;
4. een nieuwe beoordeling of zelfstandige identiteit werkelijk nodig is.

Er komt geen generieke eigenaarloze taxonomie- of contextmodule.

## 7. Technische representaties zonder Object-ID

Drupal Media/File, zoekdocumenten, API-responses, presentatieobjecten en MCP-representaties zijn technische of afgeleide representaties en krijgen geen Object-ID.

De inhoudelijke betekenis en relaties blijven eigendom van het betreffende bronobject. Een technische mapping mag nooit stil een nieuw bedrijfsobject introduceren.

## 8. Identificatieregels

1. Object-ID's worden nooit hergebruikt.
2. Verwijderde objecten blijven als `deprecated` in het register staan.
3. Nieuwe zelfstandige objecten krijgen het eerstvolgende vrije nummer.
4. Subtypen en gecontroleerde waarden krijgen niet automatisch een Object-ID.
5. Drupal bundle-ID's, database-ID's, UUID's en URL-aliases zijn geen Object-ID.
6. Een Object-ID vertegenwoordigt een bedrijfsconcept, niet een technische implementatie.

## 9. Implementatiemappings

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

## 10. Wijzigingsbeheer

Een ADR is verplicht bij wijziging van:

- canonieke naam;
- bindende definitie;
- primaire eigenaar;
- identiteit;
- zelfstandigheid van het object;
- Object-ID;
- betekenis van een bestaande relatie;
- overgang van gecontroleerde waarde of classificatie naar zelfstandig object.

Een normale documentatie- of implementatie-PR volstaat voor:

- aanvullen van een goedgekeurde technische mapping;
- toevoegen van een niet-bindend voorbeeld;
- corrigeren van een taal- of typefout zonder betekeniswijziging;
- registreren van een nieuwe compatibele representatie.

## 11. Toekomstige objecten — erkend maar niet genummerd

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
- InternalProjectRecord
- AIExecution
- AgentRun

Het ontbreken van een Object-ID betekent dat deze begrippen niet als actief zelfstandig BREBO Platform 1.0-object mogen worden geïmplementeerd.

## 12. Goedkeuringsvoorwaarde

De vijf geregistreerde objecten blijven `concept` totdat:

1. het CIM inhoudelijk is goedgekeurd;
2. de Object-ID's formeel zijn bevestigd;
3. de documenten als samenhangend geheel zijn samengevoegd naar `develop`.

Na goedkeuring worden `OBJ-001` tot en met `OBJ-005` op `definitief` gezet zonder hernummering.

## 13. Architectuurtoets

Dit register voldoet wanneer:

- uitsluitend zelfstandige bedrijfsobjecten zijn genummerd;
- de reeks zonder gaten eindigt bij `OBJ-005`;
- `LensStage` als gesloten waardencontract is geregistreerd zonder Object-ID;
- gebouw- en materiaalclassificaties lokaal blijven zonder Object-ID;
- technische media- en platformrepresentaties geen Object-ID krijgen;
- `Project` uitsluitend de publiceerbare projectcase betekent;
- iedere betekenis- of eigenaarswijziging via ADR wordt beheerst.
