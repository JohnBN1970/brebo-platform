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

- `01-enterprise/domain-model/`: bedrijfsobjecten, domeingrenzen en eigenaarschap;
- `01-enterprise/canonical-information-model/`: canonieke informatiebetekenis en gegevenscontracten;
- `01-enterprise/object-registry/`: objectdefinities, identiteit, levenscyclus en bron van waarheid.

## Platform-onderdelen

- `03-platform/drupal/`: Drupal-architectuur en implementatiemappings;
- `03-platform/python/`: toekomstige zelfstandige Python-services;
- `03-platform/api/`: versieerbare API-contracten;
- `03-platform/integrations/`: externe systeemintegraties;
- `03-platform/security/`: technische beveiligingsarchitectuur.

## Plaatsingsregel

Een document krijgt één primaire locatie op basis van zijn verantwoordelijkheid. Andere documenten verwijzen ernaar in plaats van dezelfde inhoud te kopiëren.

## Migratie

De overgang wordt per logisch samenhangende set uitgevoerd. Reeds gecontroleerd verplaatst zijn de ontwikkelworkflow, Git-scope, het voorlopige Hostinger-deploymentmodel en de ADR- en RFC-sjablonen.

Bestanden worden pas verplaatst nadat hun status, bestemming, inhoudsintegriteit en verwijzingen zijn gecontroleerd. Zie [`migration-register.md`](migration-register.md).

## Statussen

Gebruik waar relevant één van deze documentstatussen:

- `concept`
- `in review`
- `goedgekeurd`
- `vervangen`
- `gearchiveerd`

## Kernregel

Documentatie ondersteunt uitvoering. Alleen informatie die nodig is voor een besluit, implementatie, controle of beheerhandeling wordt normatief vastgelegd.
