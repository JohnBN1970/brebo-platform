# Migratieregister documentatiestructuur

Gerelateerd issue: #29  
Status: actief

## Doel

Dit register bestuurt de overgang van de bestaande documentatiemappen naar de nieuwe hoofdstructuur. Er wordt niets verwijderd voordat inhoud, status en verwijzingen zijn gecontroleerd.

## Migratieregels

1. Verplaats per logisch samenhangende set.
2. Behoud Git-historie door verplaatsing zonder gelijktijdige inhoudelijke herschrijving waar mogelijk.
3. Werk interne links en de root-README in dezelfde Pull Request bij.
4. Controleer of een document normatief, implementerend, operationeel of historisch is.
5. Archiveer alleen wanneer een opvolgend document expliciet is aangewezen.
6. Verwijder oude mappen pas wanneer zij leeg zijn.
7. Wijzig geen runtimecode, configuratie, dependencies of deploymentgedrag als onderdeel van een documentatiemigratie.

## Voorlopige mapping

| Huidige locatie | Nieuwe hoofdlocatie | Status | Opmerking |
|---|---|---|---|
| `docs/adr/` | `docs/00-governance/adr/` | gestart | ADR-template verplaatst; inhoudelijke ADR's volgen per gecontroleerde set |
| `docs/rfc/` | `docs/00-governance/rfc/` | gestart | RFC-template verplaatst; inhoudelijke RFC's volgen per gecontroleerde set |
| `docs/decisions/` | `docs/00-governance/decisions/` | doelmap gereed | centrale registers eerst reconciliëren |
| `docs/architecture/` | `docs/01-enterprise/` of `docs/03-platform/` | in uitvoering | enterprise-kernmodellen verplaatst; Module Architecture-bronpad vastgesteld; overige Drupal-implementatiedocumenten nog classificeren |
| `docs/development/` | `docs/04-development/` | afgerond voor bekende bestanden | workflow en Git-scope verplaatst |
| `docs/deployment/` | `docs/06-operations/deployment/` | afgerond voor bekende bestanden | voorlopig Hostinger-model verplaatst met statusbehoud |

## Uitgevoerde migratieslice 1

| Oud pad | Nieuw pad |
|---|---|
| `docs/development/workflow.md` | `docs/04-development/workflow.md` |
| `docs/development/git-scope.md` | `docs/04-development/git-scope.md` |
| `docs/deployment/current-hostinger-model.md` | `docs/06-operations/deployment/current-hostinger-model.md` |
| `docs/adr/ADR-TEMPLATE.md` | `docs/00-governance/adr/ADR-TEMPLATE.md` |
| `docs/rfc/RFC-TEMPLATE.md` | `docs/00-governance/rfc/RFC-TEMPLATE.md` |

De inhoud van deze vijf bestanden is niet gewijzigd; alleen de locatie is aangepast.

## Uitgevoerde migratieslice 2

De definitieve plaatsingsgrenzen zijn vastgelegd voor:

- formele besluiten in `00-governance/decisions/`;
- het techniekonafhankelijke domeinmodel in `01-enterprise/domain-model/`;
- het Canonical Information Model in `01-enterprise/canonical-information-model/`;
- Drupal-architectuur en implementatiemappings in `03-platform/drupal/`.

Deze slice voegt uitsluitend mapdocumentatie toe en wijzigt geen bestaande normatieve inhoud.

## Uitgevoerde migratieslice 3

De resterende structurele doelgebieden zijn expliciet ingericht voor:

- API-contracten in `03-platform/api/`;
- externe integraties in `03-platform/integrations/`;
- technische beveiligingsarchitectuur in `03-platform/security/`;
- zelfstandige Python-services in `03-platform/python/`;
- aantoonbaar vervangen documentatie in `99-archive/`.

Iedere doelmap bevat een afbakening die voorkomt dat bedrijfsregels, governance, implementatie en operations opnieuw door elkaar gaan lopen. Deze slice voegt geen services, integraties, beveiligingsconfiguratie of runtimecode toe.

## Uitgevoerde migratieslice 4

| Oud pad | Nieuw pad | Controle |
|---|---|---|
| `docs/architecture/brebo-domain-model-1.0.md` | `docs/01-enterprise/domain-model/brebo-domain-model-1.0.md` | dezelfde Git-blob `e6e2ea123ba9b21764fecc10bb9c1f133242dfee`; geen inhoudswijziging |

Deze verplaatsing is uitgevoerd door dezelfde bestaande blob op het nieuwe pad te plaatsen en het oude pad in dezelfde commit te verwijderen. Daardoor is bytegelijkheid aantoonbaar en blijft de geschiedenis als rename herkenbaar.

## Uitgevoerde migratieslice 5

| Oud pad | Nieuw pad | Controle |
|---|---|---|
| `docs/architecture/brebo-canonical-information-model-1.0.md` | `docs/01-enterprise/canonical-information-model/brebo-canonical-information-model-1.0.md` | pure Git-rename; 0 toevoegingen en 0 verwijderingen |
| `docs/architecture/brebo-object-registry-1.0.md` | `docs/01-enterprise/object-registry/brebo-object-registry-1.0.md` | pure Git-rename; 0 toevoegingen en 0 verwijderingen |

De documenten zijn inhoudelijk ongewijzigd verplaatst. Hun status blijft `gereed voor architectuurreview`; deze migratie verleent geen nieuwe inhoudelijke goedkeuring.

## Voorbereide migratieslice 6

| Document | Vastgesteld bronpad | Beoogd doelpad | Controle |
|---|---|---|---|
| BREBO Module Architecture 1.0 | `docs/architecture/brebo-module-architecture-1.0.md` | `docs/03-platform/drupal/architecture/brebo-module-architecture-1.0.md` | broncommit `f298ab434734161ce840927f55a24b677adf4f1d`; Git-blob `b4fde6f3e944a32e30e027c792f93cf441ee7be8`; status `concept voor architectuurreview` |

Het bronpad, de bestemming en de inhoudelijke status zijn nu controleerbaar vastgesteld. De daadwerkelijke verplaatsing moet verliesvrij gebeuren zonder inhoudelijke herschrijving en zonder de status te verhogen.

## Eerste classificatie van bekende architectuurdocumenten

| Document | Voorgestelde bestemming | Status |
|---|---|---|
| BREBO Domain Model 1.0 | `01-enterprise/domain-model/` | verplaatst |
| BREBO Canonical Information Model 1.0 | `01-enterprise/canonical-information-model/` | verplaatst |
| BREBO Object Registry 1.0 | `01-enterprise/object-registry/` | verplaatst |
| BREBO Module Architecture 1.0 | `03-platform/drupal/architecture/` | bronpad en blob vastgesteld; gereed voor verliesvrije verplaatsing |
| KnowledgeItem Drupal Entity Mapping 1.0 | `03-platform/drupal/entity-mappings/` | bronpad vaststellen |
| Platform 1.0 Modulekaart | `03-platform/drupal/architecture/` | bronpad vaststellen |

## Veiligheidsgrenzen voor volgende slices

Nog niet automatisch migreren:

- inhoudelijke ADR's en RFC's waarvan status, nummering of onderlinge verwijzingen niet zijn vastgesteld;
- besluitregisters voordat de centrale registers zijn gereconcilieerd;
- architectuurdocumenten waarvan het bronpad of de canonieke status niet eenduidig is vastgesteld;
- runtimecode, Composerstructuur, Drupalconfiguratie of deploymentmechanismen;
- bestanden die inhoudelijk worden gewijzigd in een andere open Pull Request.

Een bestand buiten deze grenzen mag worden verplaatst zodra bronpad, bestemming en verwijzingen controleerbaar zijn.