# BREBO Runtime

`brebo_runtime` vormt de gedeelde technische fundering voor toekomstige BREBO-domeinmodules.

## Verantwoordelijkheid

De module levert kleine, expliciete contracten en helpers voor:

- stable-ID-generatie;
- lifecyclebeslissingen;
- revisiebeleid;
- permissionvalidatie;
- gedeelde testondersteuning.

## Architectuurgrens

Deze module bevat geen domeinobjecten en kent geen inhoudelijke regels van Knowledge, Reference, Project, Service of Lens.

`brebo_knowledge` en `brebo_reference` worden in deze sprint niet aangepast. Migratie naar gedeelde componenten gebeurt uitsluitend in afzonderlijke regressieveilige taken.

## Ontwerpregels

1. Interfaces blijven klein en domeinneutraal.
2. Domeinmodules leveren zelf prefix, bundle, veldnamen, lifecyclewaarden en permissionnamen.
3. Helpers mogen Drupalgedrag ondersteunen, maar nemen geen domeinbesluiten over.
4. Bestaand publiek gedrag mag niet impliciet wijzigen.
5. Iedere gedeelde component krijgt eigen tests en documentatie.

## Status

Runtime 1.0 is in ontwikkeling onder BREBO-2026-010.
