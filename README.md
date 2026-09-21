# BREBO Platform

Het BREBO Platform ondersteunt de ontwikkeling van een kennisgedreven platform voor gebouwbeheer, onderhoud en de complete buitenzijde van gebouwen.

Drupal is de huidige presentatie- en contentimplementatie. Bedrijfsmodellen, processen en toekomstige services worden technologie-onafhankelijk gedocumenteerd en via expliciete contracten gekoppeld.

## Uitgangspunten

- Het gebouw staat centraal.
- Kennis wordt gestructureerd en herbruikbaar vastgelegd.
- Functionaliteit wordt opgebouwd uit kleine componenten met één duidelijke verantwoordelijkheid.
- Eenvoud, onderhoudbaarheid en uitbreidbaarheid gaan vóór snelle oplossingen.
- Wijzigingen zijn herleidbaar via Issues, Pull Requests, RFC's, ADR's en besluiten.
- Software implementeert de vastgestelde bedrijfs- en platformarchitectuur; zij definieert deze niet zelfstandig.

## Branches

- `main`: stabiele productiegeschiedenis.
- `develop`: integratiebranch voor de ontwikkelomgeving.
- `feature/*`: afgebakende functionaliteit.
- `fix/*`: reguliere correcties.
- `hotfix/*`: urgente productiecorrecties.
- `docs/*`: documentatie zonder functionele codewijzigingen.

Nieuwe wijzigingen starten vanuit `develop` en keren via een Pull Request terug naar `develop`. Rechtstreeks committen op `main` of `develop` is niet de standaardwerkwijze.

## Standaardworkflow

```text
Ideeënbus
  → triage
  → Issue / RFC / ADR
  → featurebranch
  → ontwikkeling
  → Pull Request
  → review en tests
  → merge naar develop
  → deploy naar dev-platform
  → functionele acceptatie
  → release naar main
```

## Documentatie

De centrale documentatie-index staat in [`docs/README.md`](docs/README.md).

Hoofdsecties:

- `docs/00-governance/`: besluiten, ADR's, RFC's en bestuurlijke kaders;
- `docs/01-enterprise/`: techniekonafhankelijke bedrijfs- en informatiemodellen;
- `docs/02-business/`: processen, rollen, diensten en operationele werking;
- `docs/03-platform/`: Drupal, toekomstige Python-services, API's en integraties;
- `docs/04-development/`: ontwikkelworkflow, Git-scope en standaarden;
- `docs/05-quality/`: toetsing, traceability, audits en acceptatie;
- `docs/06-operations/`: deployment, beheer, incidenten en runbooks;
- `docs/99-archive/`: aantoonbaar vervangen of uitgefaseerde documentatie.

De gecontroleerde overgang van bestaande paden wordt bijgehouden in [`docs/migration-register.md`](docs/migration-register.md).

## Repositorymodel

De repository gebruikt momenteel een Drupal legacy-rootstructuur. Core, contrib en `vendor/` blijven voorlopig in Git conform het vastgestelde Platform 1.0-model. Een migratie naar een build-based projectstructuur wordt uitsluitend als afzonderlijk traject uitgevoerd nadat deployment en rollback reproduceerbaar zijn gevalideerd.

## Status

De default branch is `develop`. De repository wordt stapsgewijs ingericht voor een controleerbare Platform 1.0-ontwikkeling zonder bestaande kennis of runtimefunctionaliteit stilzwijgend te wijzigen.
