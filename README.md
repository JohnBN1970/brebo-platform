# BREBO Platform

Het BREBO Platform is een kennisgedreven Drupal-platform voor gebouwbeheer, onderhoud en de complete buitenzijde van gebouwen.

## Uitgangspunten

- Het gebouw staat centraal.
- Kennis wordt gestructureerd en herbruikbaar vastgelegd.
- Functionaliteit wordt opgebouwd uit kleine modules met één duidelijke verantwoordelijkheid.
- Eenvoud, onderhoudbaarheid en uitbreidbaarheid gaan vóór snelle oplossingen.
- Wijzigingen zijn herleidbaar via Issues, Pull Requests, RFC's, ADR's en besluiten.

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

- `CONTRIBUTING.md`: bijdrage- en reviewproces.
- `docs/development/workflow.md`: branch-, commit- en Pull Request-werkwijze.
- `docs/architecture/`: architectuurdocumentatie.
- `docs/adr/`: Architecture Decision Records.
- `docs/rfc/`: voorstellen die inhoudelijke of technische beoordeling vereisen.
- `docs/deployment/`: vastgelegd deploymentproces.
- `docs/decisions/`: formele besluiten en registers.

## Lokale installatie

De repository gebruikt momenteel een Drupal legacy-rootstructuur. Het definitieve Git- en deploymentmodel wordt afzonderlijk vastgesteld in GitHub Issue #6. Tot dat besluit worden core-, vendor- en deploymentkeuzes niet stilzwijgend gewijzigd.

## Status

De default branch is `develop`. De repository wordt stapsgewijs ingericht voor een controleerbare Platform 1.0-ontwikkeling.
