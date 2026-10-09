# BREBO Module Architecture 1.0

Status: concept voor architectuurreview  
Gerelateerd issue: #17  
Besluitbasis: BREBO Domain Model 1.0, Canonical Information Model 1.0 en Object Registry 1.0

## 1. Doel

Dit document legt de verantwoordelijkheden, eigendomsgrenzen, afhankelijkheden en samenwerkingsregels van de custom modules van BREBO Platform 1.0 vast.

Het vormt de bindende implementatielaag tussen het techniekonafhankelijke BREBO-model en Drupal. Modules implementeren de vastgestelde bedrijfsobjecten, maar mogen hun betekenis, identiteit of eigenaarschap niet zelfstandig wijzigen.

## 2. Architectuurprincipes

1. Ieder zelfstandig bedrijfsobject heeft precies één primaire module-eigenaar.
2. Alleen de eigenaar schrijft de canonieke domeingegevens van een object.
3. Andere modules lezen via een expliciete referentie, service of contract.
4. Ondersteunende modules bezitten geen bedrijfsobjecten.
5. Presentatie, zoeken, API en integraties zijn afgeleid en nooit bron van waarheid.
6. Moduleafhankelijkheden zijn expliciet, minimaal en acyclisch.
7. Een module mag niet als algemene verzamelplaats voor gedeelde functionaliteit ontstaan.
8. `brebo_core` bevat uitsluitend stabiele infrastructuur die aantoonbaar door meerdere domeinen wordt gebruikt.
9. Drupal-entiteiten, bundles en velden zijn implementatiemappings en geen nieuwe domeinobjecten.
10. Nieuwe afhankelijkheden worden uitsluitend toegevoegd voor een concrete, goedgekeurde usecase.

## 3. Modulecategorieën

### 3.1 Domeinmodules

Domeinmodules bezitten canonieke bedrijfsobjecten en hun levenscyclus:

- `brebo_knowledge`
- `brebo_service`
- `brebo_project`
- `brebo_reference`
- `brebo_lens`

### 3.2 Ondersteunende platformmodules

Ondersteunende modules leveren technische functies, maar bezitten geen canonieke bedrijfsobjecten:

- `brebo_core`
- `brebo_search`
- `brebo_media`
- `brebo_api`

### 3.3 Presentatiemodules

Presentatiemodules bepalen weergave en gebruikersinteractie, maar bezitten geen domeindata:

- `brebo_theme`
- `brebo_hero`
- `brebo_page_banner`
- `brebo_cta`

### 3.4 Toekomstige modules

De volgende namen zijn gereserveerd als mogelijke toekomstige domeinen, maar vallen buiten Platform 1.0 en mogen nog geen actieve domeinverantwoordelijkheid krijgen:

- `brebo_building`
- `brebo_asset`
- `brebo_inspection`
- `brebo_maintenance`
- `brebo_customer`
- `brebo_commerce`
- `brebo_ai`

Invoering vereist een afzonderlijke RFC of ADR en aanvulling van het Object Registry.

## 4. Bindend eigenaarschap

| Object-ID / waarde | Canonieke naam | Primaire module | Schrijfbevoegdheid | Lezen door andere modules |
|---|---|---|---|---|
| `OBJ-001` | `KnowledgeItem` | `brebo_knowledge` | uitsluitend `brebo_knowledge` | via entity reference, queryservice of goedgekeurd contract |
| `OBJ-002` | `Service` | `brebo_service` | uitsluitend `brebo_service` | via entity reference, queryservice of goedgekeurd contract |
| `OBJ-003` | `WorkActivity` | `brebo_service` | uitsluitend `brebo_service` | via entity reference, queryservice of goedgekeurd contract |
| `OBJ-004` | `Project` | `brebo_project` | uitsluitend `brebo_project` | via entity reference, queryservice of goedgekeurd contract |
| `OBJ-005` | `Reference` | `brebo_reference` | uitsluitend `brebo_reference` | via entity reference, queryservice of goedgekeurd contract |
| gecontroleerde waarde | `LensStage` | `brebo_lens` | uitsluitend `brebo_lens` beheert waarden en definities | inbedden of refereren volgens contract |

Een module mag redactionele schermen aanbieden voor gegevens uit een andere module, maar de opslag, validatie en levenscyclus blijven onder verantwoordelijkheid van de eigenaar.

## 5. Moduleverantwoordelijkheden

### 5.1 `brebo_core`

Verantwoordelijk voor:

- stabiele gedeelde interfaces en value objects;
- generieke infrastructuur die aantoonbaar door meerdere domeinmodules wordt gebruikt;
- technische conventies voor identifiers, statuswaarden en contractversionering;
- gedeelde fout- en resultaattypen wanneer hiervoor een concrete usecase bestaat.

Niet verantwoordelijk voor:

- bedrijfsobjecten;
- generieke contenttypes;
- gedeelde taxonomieën zonder eigenaar;
- domeinregels;
- presentatiecomponenten;
- project-, dienst-, kennis- of referentiegegevens.

`brebo_core` mag geen verplichte afhankelijkheid worden zonder dat minimaal twee domeinmodules dezelfde stabiele infrastructuur werkelijk gebruiken.

### 5.2 `brebo_knowledge`

Bezit:

- `OBJ-001 KnowledgeItem`;
- revisies en publicatiestatus van kennisbijdragen;
- domeinvalidatie voor waarneming, betekenis, urgentie, eerste stap en risico's;
- lokale kennisclassificaties voor zover nodig voor een goedgekeurde usecase.

Mag refereren aan:

- `Service`;
- `WorkActivity`;
- `LensStage`;
- technische mediarecords.

Mag niet:

- diensten of werkzaamheden beheren;
- Lens-waarden definiëren;
- projectcases als kennisobject opslaan;
- zoekindexdata als bron gebruiken.

### 5.3 `brebo_service`

Bezit:

- `OBJ-002 Service`;
- `OBJ-003 WorkActivity`;
- scope, voorwaarden en uitsluitingen van diensten;
- toepasselijkheid en verwacht resultaat van werkzaamheden.

Mag refereren aan:

- `KnowledgeItem`;
- `Project`;
- `Reference`;
- `LensStage`;
- technische mediarecords.

Mag niet:

- kennisinhoud dupliceren;
- projectresultaten beheren;
- klantreferenties redactioneel bezitten;
- generieke product- of commercefunctionaliteit introduceren.

### 5.4 `brebo_project`

Bezit:

- `OBJ-004 Project` als publiceerbare projectcase;
- publiceerbare projectcontext, scope, periode en resultaten;
- relaties vanuit de projectcase naar diensten, werkzaamheden, referenties en media.

Mag refereren aan:

- `Service`;
- `WorkActivity`;
- `Reference`;
- technische mediarecords.

Mag niet:

- interne projectadministratie, planning, werkvoorbereiding of financiën beheren;
- diensten of werkzaamheden dupliceren;
- referentietoestemming beheren;
- media-inhoudelijke betekenis naar `brebo_media` verschuiven.

### 5.5 `brebo_reference`

Bezit:

- `OBJ-005 Reference`;
- herkomst, toestemming en publicatiestatus;
- redactionele controle van referentieteksten.

Mag refereren aan:

- optioneel één `Project`;
- nul of meer `Service`-objecten.

Mag niet:

- projectresultaten beheren;
- diensten beheren;
- oncontroleerbare reviews of externe beoordelingen als canonieke referentie opslaan;
- toestemming impliciet uit een projectrelatie afleiden.

### 5.6 `brebo_lens`

Bezit:

- de gesloten waarden en definities Inzicht, Regie en Realisatie;
- volgorde en semantiek van `LensStage`.

Mag niet:

- bedrijfsobjecten uit andere domeinen bezitten;
- een generieke taxonomiemodule worden;
- vrije of ongecontroleerde Lens-fasen toestaan;
- procesuitvoering of workflowstatus van andere modules beheren.

### 5.7 `brebo_search`

Verantwoordelijk voor:

- indexeren van gepubliceerde bronobjecten;
- zoekprojecties, ranking en facetten;
- opnieuw opbouwbare zoekdocumenten.

Mag niet:

- canonieke data beheren;
- ontbrekende domeinvelden in de index aanvullen als brondata;
- bedrijfsobjecten zelfstandig publiceren of archiveren;
- directe domeinschrijfacties uitvoeren.

### 5.8 `brebo_media`

Verantwoordelijk voor:

- technische ondersteuning van Drupal Media/File;
- herbruikbare mediaconfiguratie;
- technische metadata, rechtenstatus en toegankelijkheidsvoorzieningen.

Mag niet:

- media als zelfstandig BREBO-bedrijfsobject definiëren;
- de domeinbetekenis of inhoudelijke relatie van een afbeelding bezitten;
- publicatie van een domeinobject bepalen.

### 5.9 `brebo_api`

Verantwoordelijk voor:

- ontsluiting van goedgekeurde canonieke contracten;
- resourceversionering en serialisatie;
- autorisatie en technische API-validatie;
- mapping tussen domeinobjecten en externe representaties.

Mag niet:

- brondata bezitten;
- eigen domeinregels introduceren;
- interne Drupal-veldnamen ongecontroleerd publiceren;
- write-endpoints aanbieden zonder expliciete domeinservice van de eigenaar.

### 5.10 `brebo_theme`

Verantwoordelijk voor:

- visuele presentatie;
- templates, componentcompositie en toegankelijke frontendweergave;
- presentatie van view models of render arrays.

Mag niet:

- domeinlogica bevatten;
- rechtstreeks gegevens wijzigen;
- eigen brondata of bedrijfsobjecten definiëren;
- moduleafhankelijkheden omzeilen via directe databasequeries.

### 5.11 `brebo_hero`, `brebo_page_banner` en `brebo_cta`

Deze modules bezitten uitsluitend hun eigen presentatieconfiguratie en herbruikbare UI-componenten.

Zij mogen:

- configureerbare presentatie-inhoud leveren;
- renderbare componenten aanbieden;
- door het thema worden geplaatst en gestyled.

Zij mogen niet:

- canonieke domeinobjecten simuleren;
- kennis, diensten, projecten of referenties beheren;
- een afhankelijkheid op een domeinmodule afdwingen tenzij een expliciete usecase dit vereist;
- bedrijfsregels bevatten.

## 6. Afhankelijkheidsrichting

De toegestane richting is:

```text
Presentatie
    ↓
Query- en ontsluitingslagen
    ↓
Domeinmodules
    ↓
Gedeelde technische infrastructuur
    ↓
Drupal core/contrib
```

Concreet:

- `brebo_theme` mag diensten of querylagen van domeinmodules gebruiken;
- `brebo_api` en `brebo_search` mogen domeinmodules lezen via stabiele contracten;
- domeinmodules mogen gedeelde infrastructuur uit `brebo_core` gebruiken;
- een domeinmodule mag niet afhankelijk zijn van `brebo_theme`, `brebo_search` of `brebo_api`;
- domeinmodules mogen onderling alleen afhankelijk zijn wanneer een noodzakelijke write-time validatie niet via een stabiel contract kan worden opgelost.

De voorkeur voor relaties tussen domeinmodules is een losse entity reference of identifier plus validatie door de eigenaar, niet een wederzijdse harde moduleafhankelijkheid.

## 7. Voorlopige dependency matrix

Legenda:

- `T`: toegestaan;
- `V`: verboden;
- `C`: uitsluitend na concrete usecase en contractbesluit;
- `—`: niet van toepassing of zelfrelatie.

| Van \ Naar | core | knowledge | service | project | reference | lens | search | media | api | theme |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| `brebo_core` | — | V | V | V | V | V | V | V | V | V |
| `brebo_knowledge` | C | — | C | V | V | C | V | C | V | V |
| `brebo_service` | C | C | — | V | V | C | V | C | V | V |
| `brebo_project` | C | V | C | — | C | V | V | C | V | V |
| `brebo_reference` | C | V | C | C | — | V | V | C | V | V |
| `brebo_lens` | C | V | V | V | V | — | V | V | V | V |
| `brebo_search` | C | T | T | T | T | T | — | C | V | V |
| `brebo_media` | C | V | V | V | V | V | V | — | V | V |
| `brebo_api` | C | T | T | T | T | T | V | C | — | V |
| `brebo_theme` | C | C | C | C | C | C | C | C | V | — |

Een `C` wordt pas `T` wanneer de concrete implementatie-PR de usecase, het gebruikte contract en de afhankelijkheidsrichting vermeldt.

## 8. Schrijf- en leesregels

1. Directe schrijftoegang tot opslag van een andere domeinmodule is verboden.
2. Cross-domain writes lopen via een commandservice of expliciet write-contract van de eigenaar.
3. Lezen mag via entity references, repository/queryservices of gepubliceerde read-contracten.
4. Directe databasequeries buiten de eigenaar zijn verboden.
5. Afgeleide data moet opnieuw kunnen worden opgebouwd.
6. Validatie van een object blijft bij de eigenaar, ook wanneer invoer via een ander scherm of API-kanaal plaatsvindt.
7. Verwijderen van een object met inkomende relaties vereist gecontroleerde impactafhandeling.
8. Een module mag geen fallback-kopie van andermans domeindata als permanente bron opslaan.

## 9. Publieke contracten en uitbreidpunten

Een domeinmodule mag de volgende contracttypen publiceren:

- read/queryservice;
- commandservice voor gecontroleerde wijzigingen;
- events na succesvolle domeinwijziging;
- typed value objects;
- validation interfaces;
- access-policy interfaces.

Regels:

1. Contracten gebruiken canonieke namen uit het CIM.
2. Interfaces staan bij voorkeur in de eigenaarmodule; alleen werkelijk gedeelde infrastructuur hoort in `brebo_core`.
3. Events melden een reeds voltooide wijziging en dragen geen verantwoordelijkheid voor de primaire transactie over.
4. Event consumers mogen de bronmodule niet synchroon terugroepen in een lus.
5. Contractbrekende wijzigingen vereisen versionering en een ADR.
6. Drupal hooks zijn technische uitbreidpunten en vervangen geen expliciet domeincontract wanneer betekenis of eigenaarschap relevant is.

## 10. Verboden architectuurpatronen

De volgende patronen zijn niet toegestaan:

- een generieke `brebo_content`-module die meerdere domeinobjecten bezit;
- een `brebo_taxonomy`- of `brebo_context`-module zonder expliciete levenscycluseigenaar;
- domeinlogica in theme preprocess, Twig of frontend-JavaScript;
- directe writes vanuit `brebo_api`, `brebo_search` of `brebo_theme` naar domeinopslag;
- wederzijdse moduleafhankelijkheden;
- duplicatie van canonieke velden om queries eenvoudiger te maken;
- API-responses of zoekdocumenten als bron van waarheid;
- `brebo_core` als opslagplaats voor willekeurige helpers;
- een domeinmodule die afhankelijk is van een presentatiecomponent;
- introductie van een nieuw zelfstandig object zonder Object-ID en architectuurbesluit.

## 11. Drupal-implementatieregels

1. Iedere canonieke objectsoort krijgt maximaal één primaire Drupal-opslagmapping.
2. Bundle- en veldnamen worden per object geregistreerd voordat implementatie wordt gemerged.
3. Configuratie van een object blijft in de eigenaarmodule.
4. Views en displays zijn afgeleid en mogen in de eigenaarmodule of een expliciete presentatielaag staan, maar bezitten geen data.
5. Revisies, status en toegang worden ingericht volgens de levenscyclus van het canonieke object.
6. Entity references wijzen naar de primaire opslagmapping en worden niet vervangen door vrije tekstkopieën.
7. Uninstall-gedrag mag geen gegevens van andere modules verwijderen.
8. Een module-installatie mag geen configuratie van een andere domeinmodule muteren zonder expliciet migratiebesluit.

## 12. Implementatievolgorde na goedkeuring

1. Leg de definitieve Drupal entity mapping voor `KnowledgeItem` vast.
2. Verifieer en voltooi de minimale `brebo_knowledge`-implementatie.
3. Leg de entity mapping voor `Service` en `WorkActivity` vast.
4. Implementeer `brebo_service` zonder verplichte koppelingen naar andere domeinen.
5. Leg de entity mapping voor de publiceerbare `Project`-case vast.
6. Implementeer `brebo_project` met uitsluitend aantoonbaar benodigde relaties.
7. Leg de entity mapping en toestemmingsregels voor `Reference` vast.
8. Implementeer `brebo_reference`.
9. Leg daarna pas zoek-, API- en MCP-contracten vast.
10. Voeg presentatie-integraties uitsluitend per concrete usecase toe.

Iedere implementatiestap krijgt een afzonderlijk issue, branch en toetsbare PR.

## 13. Reviewvragen

Voor definitieve goedkeuring moeten reviewers expliciet bevestigen:

1. of de vijf domeinmodules en hun eigenaarschap volledig overeenkomen met het Object Registry;
2. of de dependency matrix voldoende streng is om cyclische koppelingen te voorkomen;
3. of `brebo_core` voldoende beperkt blijft;
4. of presentatiecomponenten terecht buiten het domeinmodel blijven;
5. of cross-domain writes uitsluitend via eigenaarcontracten mogen plaatsvinden;
6. of de voorgestelde implementatievolgorde uitvoerbaar is voor Platform 1.0.

## 14. Goedkeuringsvoorwaarde

Deze modulearchitectuur kan definitief worden vastgesteld wanneer:

- zij niet conflicteert met het Domain Model, CIM of Object Registry;
- iedere module één heldere verantwoordelijkheid heeft;
- geen ondersteunende module bron van domeindata wordt;
- de afhankelijkheidsrichting acyclisch blijft;
- de eerste Drupal-implementatieslices zonder verdere eigenaarschapsbesluiten kunnen worden gepland.
