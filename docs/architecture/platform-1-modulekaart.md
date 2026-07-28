# BREBO Platform 1.0 — Modulekaart

## Doel

Deze modulekaart vertaalt ADR-0002 naar een praktisch toetsingskader voor Issues, Pull Requests en toekomstige ontwikkeling.

## Platform 1.0

### Verplicht of reeds bevestigd

- `brebo_knowledge` — gestructureerde kennisobjecten en kennisdomein;
- `brebo_project` — projecten en projectrelaties;
- `brebo_service` — dienstenaanbod en dienstgegevens;
- `brebo_hero` — zelfstandig herbruikbaar hero-component, indien bestaande implementatie dit bevestigt;
- `brebo_page_banner` — zelfstandig herbruikbaar bannercomponent, indien bestaande implementatie dit bevestigt;
- `brebo_cta` — zelfstandig beheerbare CTA-componenten;
- `brebo_lens` — alleen voor herbruikbare BREBO Lens-domeinlogica;
- `brebo_core` — alleen wanneer generieke platformcontracten of diagnose aantoonbaar niet in een specifiek domein thuishoren.

### Later binnen of na Platform 1.0

- `brebo_search` — na stabilisatie van kennis-, service- en projectmodellen;
- `brebo_api` — na beveiligings- en integratiebesluit;
- `brebo_media` — alleen bij BREBO-specifieke behoefte boven Drupal Media;
- `brebo_reference` — na functionele afbakening tegenover projecten.

### Geen doelmodule

- `brebo_content_model`;
- `brebo_taxonomy`;
- `brebo_context` in de huidige vorm;
- `brebo_pillars`;
- `brebo_benefits`;
- `brebo_projects` als meervoudige machinenaam.

## Eigenaarschap

| Onderwerp | Eigenaar |
|---|---|
| Kennisobjecten, kennisvelden, kennisrevisies | `brebo_knowledge` |
| Kennisclassificatie en kennisrelaties | `brebo_knowledge` |
| Dienstenaanbod | `brebo_service` |
| Projectcases en uitvoering | `brebo_project` |
| BREBO Lens-domeinlogica | `brebo_lens` |
| Zoekindex, filters en ranking | `brebo_search` |
| Externe API-contracten | `brebo_api` |
| Hero, banner en CTA-presentatie | respectieve componentmodule en theme |
| Generieke platformstatus | beperkt `brebo_core` |

## Toetsvragen voor nieuwe functionaliteit

1. Heeft de wijziging een zelfstandig domeinobject, eigen regels en een eigen lifecycle?
2. Welk domein is eigenaar van de data?
3. Is een nieuwe module noodzakelijk, of volstaat configuratie, een service in een bestaande module of themepresentatie?
4. Introduceert de wijziging een afhankelijkheid terug naar presentatie, zoeken of API?
5. Wordt `brebo_core` gebruikt omdat eigenaarschap onduidelijk is? Dan moet de wijziging opnieuw worden afgebakend.
6. Kan installatie, update en rollback veilig en herhaalbaar worden uitgevoerd?

## Migratierichting PR #1–#4

### PR #1 — `brebo_content_model`

Niet rechtstreeks mergen. Eerst splitsen in:

- kennisgerichte modelwijzigingen onder `brebo_knowledge`;
- dienstgerichte wijzigingen onder `brebo_service`;
- theme-aanpassingen in een afzonderlijke presentatie-PR.

### PR #2 — `brebo_context`

Niet rechtstreeks mergen. Eerst:

- resolverlogica afbakenen als kennisrelatie of zoekfunctionaliteit;
- directe Twig/preprocess-koppeling scheiden van domeinlogica;
- cacheability en toegangscontrole opnieuw toetsen;
- afhankelijkheid op het technische bundle-id `dienst` expliciet maken.

### PR #3 — `brebo_taxonomy`

Niet rechtstreeks mergen. Taxonomieën en velden moeten per domein worden toegewezen. Automatische creatie van termen en wijziging van displays vereist een expliciet configuratie- en rollbackplan.

### PR #4 — `brebo_core`

Niet rechtstreeks mergen. De diagnose mag niet hardcoded afhankelijk zijn van alle modules, kennisvelden, vocabularies en bundles. Ontwerp diagnostiek later op basis van expliciete contracten per module.

## Beheerregel

Een modulekaart is geen opdracht om lege modules aan te maken. Een module wordt pas geïmplementeerd wanneer er een goedgekeurde, concrete verantwoordelijkheid en acceptatiecriteria bestaan.