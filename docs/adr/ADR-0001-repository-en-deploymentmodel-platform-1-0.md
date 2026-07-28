# ADR-0001 — Repository- en deploymentmodel Platform 1.0

## Status

Voorgesteld

## Datum

28 juli 2026

## Context

De BREBO-repository gebruikt momenteel `drupal/legacy-project` met de Drupal web-root in de repositoryroot. Drupal core, Composer-afhankelijkheden en `vendor/` worden momenteel in Git beheerd. De `.gitignore` vermeldt expliciet dat `vendor/` voorlopig in Git blijft vanwege het bestaande Hostinger-deploymentproces.

Het volledige deploymentproces is nog niet reproduceerbaar gedocumenteerd. Een directe migratie naar `drupal/recommended-project`, een aparte `web/`-directory of een build-based deployment zou daarom tegelijk de repositorystructuur, serverpaden en uitrolwerkwijze wijzigen.

## Besluit

Voor BREBO Platform 1.0 wordt voorlopig het bestaande legacy-rootmodel behouden.

Daarbij gelden de volgende afspraken:

1. `composer.json` en `composer.lock` zijn leidend voor afhankelijkheden.
2. Drupal core, contrib en `vendor/` blijven tijdelijk in Git zolang Hostinger-deployment hiervan afhankelijk is.
3. Custom modules, custom themes, configuratie en projectdocumentatie blijven in Git.
4. Uploads, private files, lokale instellingen, secrets, logs, caches, database-exports en back-ups horen niet in Git.
5. `sites/*/settings.php` mag in Git blijven, mits secrets uitsluitend via lokale of omgevingsspecifieke configuratie worden geladen.
6. Nieuwe secrets mogen nooit aan de repository worden toegevoegd.
7. Migratie naar `drupal/recommended-project` wordt niet gecombineerd met functionele platformontwikkeling, maar later als afzonderlijk migratietraject uitgevoerd.

## Reden

Deze keuze beperkt risico tijdens Platform 1.0. De huidige serverstructuur blijft bruikbaar, terwijl governance en reproduceerbaarheid eerst kunnen worden verbeterd. Een toekomstige migratie blijft mogelijk, maar gebeurt pas nadat deployment, rollback, serverpaden en afhankelijkheden aantoonbaar zijn vastgelegd en getest.

## Gevolgen

### Positief

- geen onmiddellijke verstoring van de Hostinger-omgeving;
- geen gecombineerde migratie van code, paden en deployment;
- bestaande ontwikkelomgeving blijft herkenbaar;
- repositoryprofessionalisering kan gecontroleerd doorgaan.

### Negatief

- de repository blijft groter dan noodzakelijk;
- core- en vendorwijzigingen kunnen omvangrijke diffs veroorzaken;
- Composer-herleidbaarheid en Git-inhoud bestaan tijdelijk naast elkaar;
- het model wijkt af van de aanbevolen Drupal-projectstructuur.

## Herzieningsvoorwaarden

Dit besluit moet opnieuw worden beoordeeld zodra:

- een reproduceerbare build en deployment beschikbaar is;
- Hostinger `composer install` betrouwbaar in het deploymentpad kan uitvoeren;
- staging en rollback aantoonbaar werken;
- serverdocumentroot en paden gecontroleerd kunnen worden aangepast;
- CI Composer- en Drupal-validatie uitvoert.

## Niet besloten

Dit ADR legt nog niet de exacte deploymentcommando's, serveraccounts, secretsopslag of productievrijgave vast. Die onderdelen moeten afzonderlijk worden gevalideerd en gedocumenteerd.
