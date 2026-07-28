# ADR-010-01 – Gedeelde runtime als domeinneutrale fundering

## Status

Voorgesteld binnen BREBO-2026-010.

## Context

`brebo_knowledge` en `brebo_reference` bevatten vergelijkbare technische patronen voor stable IDs, lifecycle, revisies en permissions. Directe migratie van beide modules tegelijk zou onnodig regressierisico creëren.

## Besluit

BREBO introduceert `brebo_runtime` als kleine, domeinneutrale technische fundering.

De module levert uitsluitend:

- contracten voor stable-ID-generatie en lifecyclebeleid;
- een configureerbare abstracte stable-ID-generator;
- helpers voor revisies en lifecyclepermissions;
- eigen tests en CI-validatie.

Domeinmodules blijven eigenaar van:

- prefixes en tellers;
- bundle- en veldnamen;
- lifecyclewaarden;
- publicatiestatussen;
- permissionnamen;
- inhoudelijke validatie.

## Gevolgen

- nieuwe domeinmodules kunnen het gedeelde patroon direct gebruiken;
- bestaande modules blijven voorlopig ongewijzigd;
- migratie van Knowledge en Reference gebeurt per afzonderlijke taak met regressietests;
- `brebo_runtime` mag geen afhankelijkheid krijgen op een concrete BREBO-domeinmodule.

## Niet besloten

Deze ADR besluit niet over migratietiming, contentmodellen, zoekindexering, API's, MCP of frontendgedrag.
