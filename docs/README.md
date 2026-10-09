# BREBO-documentatie

Deze map is de centrale, versiebeheerbare documentatiebron van het BREBO Platform.

## Hoofdstructuur

| Map | Verantwoordelijkheid |
|---|---|
| `00-governance/` | formele besluiten, principes, ADR's, RFC's en bestuurlijke registers |
| `01-enterprise/` | techniekonafhankelijke domeinmodellen, canonieke objecten en bedrijfsregels |
| `02-business/` | processen, rollen, werkafspraken en operationele blueprints |
| `03-platform/` | technische architectuur en implementatiemappings voor Drupal, Python, API en integraties |
| `04-development/` | ontwikkelworkflow, Git-scope, coding standards, testen, branches en releases |
| `05-quality/` | validatie, traceerbaarheid, audits, conformiteit en kwaliteitscriteria |
| `06-operations/` | deployment, beheer, monitoring, back-up, incidenten en runbooks |
| `99-archive/` | vervangen of historisch materiaal dat niet meer normatief is |

## Enterprise-onderdelen

- [`01-enterprise/domain-model/`](01-enterprise/domain-model/) — bedrijfsobjecten, domeingrenzen en eigenaarschap;
- [`01-enterprise/canonical-information-model/`](01-enterprise/canonical-information-model/) — canonieke begrippen, waarden en uitwisselingsbetekenis;
- [`01-enterprise/object-registry/`](01-enterprise/object-registry/) — gecontroleerd register van canonieke objecten.

## Platformonderdelen

- [`03-platform/drupal/`](03-platform/drupal/) — Drupal-architectuur en implementatiemappings;
- [`03-platform/api/`](03-platform/api/) — versieerbare API-contracten;
- [`03-platform/integrations/`](03-platform/integrations/) — externe systeemintegraties;
- [`03-platform/security/`](03-platform/security/) — technische beveiligingsarchitectuur;
- [`03-platform/python/`](03-platform/python/) — toekomstige zelfstandige Python-services.

## Plaatsingsregel

Een document krijgt één primaire locatie op basis van zijn verantwoordelijkheid. Andere documenten verwijzen ernaar in plaats van dezelfde inhoud te kopiëren.

## Migratie

De overgang wordt per logisch samenhangende set uitgevoerd. De ontwikkelworkflow, Git-scope, het voorlopige Hostinger-deploymentmodel, de ADR- en RFC-sjablonen en het BREBO Domain Model zijn verliesvrij naar hun nieuwe locaties verplaatst.

Bestanden worden pas verplaatst nadat hun status, bestemming en verwijzingen zijn gecontroleerd. Zie [`migration-register.md`](migration-register.md).

## Statussen

Gebruik waar relevant één van deze documentstatussen:

- `concept`
- `in review`
- `goedgekeurd`
- `vervangen`
- `gearchiveerd`

## Kernregel

Documentatie ondersteunt uitvoering. Alleen informatie die nodig is voor een besluit, implementatie, controle of beheerhandeling wordt normatief vastgelegd.