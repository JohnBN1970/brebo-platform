# ADR-0002 — Modulearchitectuur BREBO Platform 1.0

## Status

Voorgesteld

## Datum

28 juli 2026

## Context

De repository bevat bestaande BREBO-modules en vier open, gestapelde Pull Requests met aanvullende modules. Daarbij zijn verantwoordelijkheden versnipperd of juist te breed geworden. Voorbeelden:

- `brebo_content_model` beheert kennisvelden op het technische contenttype `dienst`;
- `brebo_taxonomy` beheert metadata die inhoudelijk bij hetzelfde kennisdomein hoort;
- `brebo_context` bevat zowel vergelijkingslogica als presentatie-integratie;
- `brebo_core` uit PR #4 kent concrete kennisvelden, vocabularies en contenttypes en dreigt daardoor een verzamelmodule te worden;
- modules als `brebo_pillars` en `brebo_benefits` modelleren vooral presentatiesecties, niet zelfstandige domeinen.

BREBO wil kleine modules met één duidelijke verantwoordelijkheid, waarbij kennis centraal staat en presentatie, domeinlogica en integraties gescheiden blijven.

## Besluit

Voor Platform 1.0 geldt de volgende doelarchitectuur.

### Platformbasis

| Module | Verantwoordelijkheid | Platform 1.0 |
|---|---|---|
| `brebo_core` | Alleen generieke BREBO-platformdiagnostiek, statuscontracten en gedeelde technische interfaces die aantoonbaar door meerdere BREBO-modules worden gebruikt. Geen kennisvelden, vocabularies, contenttypekennis of presentatielogica. | Ja, beperkt |
| `brebo_media` | BREBO-specifieke mediaconventies en herbruikbare media-integratie, alleen wanneer core/contrib dit niet afdoende beheert. | Uitgesteld tenzij concrete noodzaak |
| `brebo_search` | Zoeken, indexering, filters en zoekresultaatlogica over meerdere domeinen. | Later in 1.0 na stabiele contentmodellen |
| `brebo_api` | Externe, beveiligde en versieerbare ontsluiting van BREBO-data en acties. | Later; afhankelijk van goedgekeurde integratiearchitectuur |

### Kennisdomein

| Module | Verantwoordelijkheid | Platform 1.0 |
|---|---|---|
| `brebo_knowledge` | Eigenaar van gestructureerde kennisobjecten, kennisvelden, revisies, kennisrelaties en domeinspecifieke metadata. Geen algemene presentatie-, zoek-, SEO-, media- of AI-verantwoordelijkheid. | Ja |
| `brebo_lens` | Domeinmodel en herbruikbare logica voor Inzicht – Regie – Realisatie. Geen vaste pagina-opmaak. | Ja, alleen bij aantoonbaar hergebruik |

### Bedrijfsdomeinen

| Module | Verantwoordelijkheid | Platform 1.0 |
|---|---|---|
| `brebo_project` | Projectcases, projectgegevens en relaties vanuit projecten naar diensten, kennis en referenties. | Ja |
| `brebo_service` | Dienstenaanbod en servicegerichte domeingegevens. Het technische bestaande bundle-id `dienst` mag tijdens migratie behouden blijven. | Ja |
| `brebo_reference` | Referenties, bewijsvoering, resultaten en klant-/gebouwcases die niet hetzelfde zijn als uitvoeringsprojecten. | Uitgesteld totdat onderscheid met project functioneel bevestigd is |

### Presentatiecomponenten

| Module | Verantwoordelijkheid | Platform 1.0 |
|---|---|---|
| `brebo_hero` | Herbruikbare hero-component en configuratie. | Behouden indien bestaand en zelfstandig |
| `brebo_page_banner` | Herbruikbare paginabanner. | Behouden indien bestaand en zelfstandig |
| `brebo_cta` | Herbruikbare call-to-actioncomponenten en beheerbare CTA-data. | Behouden indien bestaand en zelfstandig |
| `brebo_pillars` | Geen zelfstandig domein; migreren naar component/configuratie of themepresentatie. | Niet als doelmodule |
| `brebo_benefits` | Geen zelfstandig domein; migreren naar component/configuratie of themepresentatie. | Niet als doelmodule |

## Migratie van bestaande en voorgestelde modules

| Huidige of voorgestelde module | Besluit |
|---|---|
| `brebo_content_model` | Niet als definitieve module behouden. Kennisgerichte onderdelen migreren naar `brebo_knowledge`; dienstgerichte onderdelen naar `brebo_service`. |
| `brebo_taxonomy` | Niet zelfstandig behouden. Taxonomie en metadata worden eigendom van het domein dat ze gebruikt, primair `brebo_knowledge`; gedeelde classificaties vereisen een apart besluit. |
| `brebo_context` | Hernoemen of opnieuw ontwerpen binnen `brebo_knowledge` of later `brebo_search`, afhankelijk van functie. Presentatie-integratie hoort in theme/componentlaag, niet in de resolver. |
| `brebo_projects` | Normaliseren naar enkelvoud `brebo_project`; migratie pas na inventarisatie van bestaande configuratie en machine-namen. |
| `brebo_core` uit PR #4 | Niet in huidige vorm accepteren. Diagnostiek moet contractgestuurd zijn en mag geen hardcoded kennisvelden, vocabularies of lijst van alle modules bezitten. |
| `brebo_pillars` | Samenvoegen met presentatie-/componentbeheer; geen zelfstandig domein. |
| `brebo_benefits` | Samenvoegen met presentatie-/componentbeheer; geen zelfstandig domein. |

## Afhankelijkheidsregels

1. Domeinmodules mogen afhankelijk zijn van Drupal core/contrib en van expliciet benodigde kleine BREBO-modules.
2. Domeinmodules zijn niet standaard afhankelijk van `brebo_core`.
3. `brebo_core` mag geen omgekeerde kennis hebben van alle domeinmodules.
4. Presentatiemodules mogen domeindata renderen via stabiele Drupal-interfaces, maar bezitten het domeinmodel niet.
5. `brebo_search` en `brebo_api` lezen domeinen via expliciete contracten en mogen geen velden stilzwijgend veronderstellen.
6. Module-installatie mag bestaande configuratie niet opportunistisch aanpassen zonder update-, conflict- en rollbackstrategie.

## Afhankelijkheidsrichting

```text
Drupal core/contrib
        ↑
kleine technische contracten (optioneel brebo_core)
        ↑
  domeinmodules
  ├── brebo_knowledge
  ├── brebo_service
  ├── brebo_project
  ├── brebo_lens
  └── later brebo_reference
        ↑
consumenten
  ├── brebo_search
  ├── brebo_api
  └── theme/presentatiecomponenten
```

Afhankelijkheden lopen omhoog vanuit consumenten naar domeincontracten. Domeinmodules worden niet afhankelijk van zoek-, API- of themelogica.

## Gevolgen

### Positief

- kennis krijgt één duidelijke eigenaar;
- minder overlap tussen contentmodel, taxonomie en context;
- `brebo_core` blijft klein en vervangbaar;
- zoek- en API-functionaliteit kunnen later meerdere domeinen bedienen;
- presentatiemodules vervuilen het domeinmodel niet.

### Negatief of aandachtspunt

- PR #1 tot en met #4 kunnen niet ongewijzigd worden gemerged;
- bestaande machine-namen kunnen tijdelijk afwijken van de doelarchitectuur;
- migratie vereist inventarisatie van actieve configuratie en content;
- niet iedere gewenste doelmodule hoeft direct als lege module te worden aangemaakt.

## Implementatie

1. Normaliseer eerst de PR-keten #1–#4 onder Issue #8.
2. Behoud bestaande content en machine-namen totdat een afzonderlijk migratieplan is goedgekeurd.
3. Implementeer geen lege doelmodules alleen om de kaart compleet te maken.
4. Iedere functionele uitbreiding krijgt een eigen Issue en toetst expliciet eigenaarschap, afhankelijkheden en migratie.
5. Werk de modulekaart bij wanneer een nieuw domein aantoonbaar zelfstandig gedrag, data en lifecycle bezit.

## Herziening

Herbeoordeling is nodig wanneer:

- een classificatie aantoonbaar door drie of meer domeinen gedeeld wordt;
- zoek- of API-contracten stabiele cross-domeininterfaces vereisen;
- `brebo_reference` functioneel duidelijk van `brebo_project` kan worden onderscheiden;
- een componentmodule eigen data, rechten en lifecycle krijgt en daardoor een zelfstandig domein wordt.