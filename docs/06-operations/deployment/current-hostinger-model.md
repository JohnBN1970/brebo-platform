# Huidig Hostinger-deploymentmodel

## Status

Voorlopige vastlegging. De technische werkelijkheid moet per omgeving worden gevalideerd voordat dit document als volledig operationeel runbook geldt.

## Bevestigde uitgangspunten

- De repository gebruikt een Drupal legacy-rootstructuur met web-root `./`.
- De ontwikkelbranch is `develop`.
- De server communiceert via SSH met GitHub.
- Drupal core en `vendor/` staan momenteel in Git.
- `vendor/` blijft voorlopig in Git omdat het bestaande Hostinger-deploymentproces daarop is ingericht.
- Lokale secrets horen niet in Git.

## Voorlopige ontwikkelroute

```text
feature/fix/docs branch
  → Pull Request naar develop
  → review en controles
  → merge naar develop
  → synchronisatie naar dev-platform
  → Drupal-controles
  → functionele acceptatie
```

## Minimale veilige controles na synchronisatie

De exacte commando's en paden moeten vóór gebruik op de doelomgeving worden bevestigd.

```text
git status --short --branch
php -v
vendor/bin/drush status
vendor/bin/drush updatedb --no-interaction
vendor/bin/drush cache:rebuild
```

`config:import` mag alleen worden uitgevoerd wanneer de PR aantoonbaar een bedoelde configuratie-export bevat en de impact vooraf is beoordeeld.

## Productie

Productiedeployment is geen automatische vervolgstap van een merge naar `develop`.

Voor productie zijn minimaal vereist:

1. functionele acceptatie op dev of staging;
2. expliciete vrijgave;
3. vastgestelde back-up van database en relevante bestanden;
4. bekend rollbackpunt in Git;
5. gecontroleerde database- en configuratie-impact;
6. uitvoering door een geautoriseerde persoon of beperkte deploymentgateway.

## Rollback

Een code-rollback bestaat minimaal uit terugkeer naar een eerder goedgekeurd commit. Dat is niet automatisch voldoende wanneer een release ook database-updates, configuratie-imports of bestandswijzigingen bevat.

Voor zulke releases moet vóór productie apart zijn vastgelegd:

- welke databaseback-up wordt gebruikt;
- hoe configuratie wordt teruggezet;
- welke bestanden moeten worden hersteld;
- welke cache- en verificatiestappen volgen.

## Nog te valideren

- exacte serverpaden van dev, staging en productie;
- gebruikte SSH-account(s) en rechten;
- huidige pull- of deploycommando's;
- onderhoudsmodus tijdens risicovolle releases;
- locatie en retentie van back-ups;
- configuratie-exportmap en config split-strategie;
- mogelijkheid om Composer op Hostinger veilig en reproduceerbaar te draaien;
- branch of tag die productie vertegenwoordigt;
- procedure voor database-updates zonder terugwaartse compatibiliteit.

Zolang deze punten niet zijn bevestigd, is dit document een architectuur- en veiligheidskader en geen toestemming voor onbeheerde productiedeployment.
