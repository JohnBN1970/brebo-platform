# BREBO Reference

`brebo_reference` beheert zelfstandige, revisioneerbare bronnen en onderbouwingen binnen het BREBO-platform.

## Domeinregels

- iedere referentie krijgt automatisch een onveranderlijke ID in formaat `REF-000001`;
- dubbele Reference IDs zijn niet toegestaan;
- iedere opslag maakt een nieuwe revisie;
- alleen lifecycle-status `approved` resulteert in een gepubliceerd Drupal-nodeobject;
- `concept`, `verified`, `expired` en `archived` blijven ongepubliceerd;
- goedkeuren/controleren en vervallen/archiveren vereisen afzonderlijke permissions.

## Lifecycle

- `concept` – inhoud in voorbereiding;
- `verified` – inhoudelijk gecontroleerd, nog niet publiek;
- `approved` – formeel goedgekeurd en gepubliceerd;
- `expired` – inhoudelijk niet meer actueel;
- `archived` – administratief afgesloten.

## Installatie

```bash
vendor/bin/drush en brebo_reference -y
vendor/bin/drush cr
```

De installatie stopt bewust wanneer een bestaand contenttype of veldopslag dezelfde machine name heeft maar technisch onverenigbaar is.

## Tests

```bash
vendor/bin/phpunit modules/custom/brebo_reference/tests/src/Kernel/ReferenceRuntimeTest.php
```

## Rollback

1. stop nieuwe invoer van referenties;
2. exporteer of behoud bestaande Reference-content wanneer nodig;
3. draai de featurecommit of merge terug;
4. uninstall `brebo_reference` uitsluitend wanneer verlies van moduleconfiguratie en gekoppelde velddata expliciet is aanvaard.

## Buiten scope 1.0

- relaties met KnowledgeItem, Project, Service of Lens;
- zoekindexering;
- API- of MCP-exposure;
- frontendtemplates;
- generieke runtime-extractie.
