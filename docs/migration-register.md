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
| `docs/adr/` | `docs/00-governance/adr/` | te migreren | ADR-nummers en inhoud behouden |
| `docs/rfc/` | `docs/00-governance/rfc/` | te migreren | templates meeverplaatsen |
| `docs/decisions/` | `docs/00-governance/decisions/` | te inventariseren | centrale registers eerst reconciliëren |
| `docs/architecture/` | `docs/01-enterprise/` of `docs/03-platform/` | te classificeren | splits op techniekonafhankelijk model versus implementatiemapping |
| `docs/development/` | `docs/04-development/` | te migreren | workflow en Git-scope controleren |
| `docs/deployment/` | `docs/06-operations/deployment/` | te migreren | voorlopige status behouden |

## Eerste classificatie van bekende architectuurdocumenten

| Document | Voorgestelde bestemming |
|---|---|
| BREBO Domain Model 1.0 | `01-enterprise/domain-model/` |
| BREBO Canonical Information Model 1.0 | `01-enterprise/canonical-information-model/` |
| BREBO Object Registry 1.0 | `01-enterprise/object-registry/` |
| BREBO Module Architecture 1.0 | `03-platform/drupal/architecture/` |
| KnowledgeItem Drupal Entity Mapping 1.0 | `03-platform/drupal/entity-mappings/` |
| Platform 1.0 Modulekaart | `03-platform/drupal/architecture/` |

## Niet onderdeel van deze eerste slice

- bestaande bestanden verplaatsen;
- documentstatussen inhoudelijk wijzigen;
- ADR's of besluiten hernummeren;
- runtimecode, Composerstructuur of deployment wijzigen;
- de open projectimplementatie-PR aanpassen.
