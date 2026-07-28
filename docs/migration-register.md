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
8. Verplaats geen bestand wanneer de connector de volledige inhoud niet verliesvrij kan lezen of schrijven.

## Voorlopige mapping

| Huidige locatie | Nieuwe hoofdlocatie | Status | Opmerking |
|---|---|---|---|
| `docs/adr/` | `docs/00-governance/adr/` | gestart | ADR-template verplaatst; inhoudelijke ADR's volgen per gecontroleerde set |
| `docs/rfc/` | `docs/00-governance/rfc/` | gestart | RFC-template verplaatst; inhoudelijke RFC's volgen per gecontroleerde set |
| `docs/decisions/` | `docs/00-governance/decisions/` | doelmap gereed | centrale registers eerst reconciliëren |
| `docs/architecture/` | `docs/01-enterprise/` of `docs/03-platform/` | classificatie gestart | splits op techniekonafhankelijk model versus implementatiemapping |
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

- doelmap `01-enterprise/object-registry/` ingericht;
- exact bronpad van BREBO Domain Model 1.0 vastgesteld als `docs/architecture/brebo-domain-model-1.0.md`;
- definitieve bestemming vastgesteld als `docs/01-enterprise/domain-model/brebo-domain-model-1.0.md`;
- bestand nog niet verplaatst omdat verliesvrije volledige inhoudsoverdracht via de connector eerst aantoonbaar moet zijn.

## Eerste classificatie van bekende architectuurdocumenten

| Document | Bronpad | Voorgestelde bestemming | Status |
|---|---|---|---|
| BREBO Domain Model 1.0 | `docs/architecture/brebo-domain-model-1.0.md` | `docs/01-enterprise/domain-model/brebo-domain-model-1.0.md` | bron en bestemming vastgesteld; technische verplaatsing nog uitstaand |
| BREBO Canonical Information Model 1.0 | nog vast te stellen | `docs/01-enterprise/canonical-information-model/` | te inventariseren |
| BREBO Object Registry 1.0 | nog vast te stellen | `docs/01-enterprise/object-registry/` | doelmap gereed |
| BREBO Module Architecture 1.0 | nog vast te stellen | `docs/03-platform/drupal/architecture/` | te inventariseren |
| KnowledgeItem Drupal Entity Mapping 1.0 | nog vast te stellen | `docs/03-platform/drupal/entity-mappings/` | te inventariseren |
| Platform 1.0 Modulekaart | nog vast te stellen | `docs/03-platform/drupal/architecture/` | te inventariseren |

## Veiligheidsgrenzen voor volgende slices

Nog niet automatisch migreren:

- inhoudelijke ADR's en RFC's waarvan status, nummering of onderlinge verwijzingen niet zijn vastgesteld;
- besluitregisters voordat de centrale registers zijn gereconcilieerd;
- architectuurdocumenten waarvan het bronpad of de canonieke status niet eenduidig is vastgesteld;
- bestanden waarvan de connector de volledige inhoud niet verliesvrij kan overzetten;
- runtimecode, Composerstructuur, Drupalconfiguratie of deploymentmechanismen;
- bestanden die inhoudelijk worden gewijzigd in een andere open Pull Request.

Een bestand buiten deze grenzen mag worden verplaatst zodra bronpad, bestemming, inhoudsintegriteit en verwijzingen controleerbaar zijn.
