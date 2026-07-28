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

## Voorlopige mapping

| Huidige locatie | Nieuwe hoofdlocatie | Status | Opmerking |
|---|---|---|---|
| `docs/adr/` | `docs/00-governance/adr/` | gestart | ADR-template verplaatst; inhoudelijke ADR's volgen per set |
| `docs/rfc/` | `docs/00-governance/rfc/` | gestart | RFC-template verplaatst; inhoudelijke RFC's volgen per set |
| `docs/decisions/` | `docs/00-governance/decisions/` | te inventariseren | centrale registers eerst reconciliëren |
| `docs/architecture/` | `docs/01-enterprise/` of `docs/03-platform/` | te classificeren | splits op techniekonafhankelijk model versus implementatiemapping |
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

## Eerste classificatie van bekende architectuurdocumenten

| Document | Voorgestelde bestemming |
|---|---|
| BREBO Domain Model 1.0 | `01-enterprise/domain-model/` |
| BREBO Canonical Information Model 1.0 | `01-enterprise/canonical-information-model/` |
| BREBO Object Registry 1.0 | `01-enterprise/object-registry/` |
| BREBO Module Architecture 1.0 | `03-platform/drupal/architecture/` |
| KnowledgeItem Drupal Entity Mapping 1.0 | `03-platform/drupal/entity-mappings/` |
| Platform 1.0 Modulekaart | `03-platform/drupal/architecture/` |

## Nog niet migreren

- inhoudelijke ADR's en RFC's zonder classificatiecontrole;
- besluitregisters voordat de centrale registers zijn gereconcilieerd;
- architectuurdocumenten voordat techniekonafhankelijke modellen en Drupal-mappings definitief zijn gescheiden;
- runtimecode, Composerstructuur of deploymentmechanismen;
- bestanden uit de open projectimplementatie-PR.
