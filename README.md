# BREBO Platform

BREBO Platform is de centrale Drupal 11-codebase voor de publieke BREBO-website en toekomstige platformfuncties.

## Projectstructuur

- `composer.json` en `composer.lock`: reproduceerbare Drupal-build.
- `web/`: publieke documentroot.
- `web/modules/custom/`: BREBO-functionaliteit.
- `web/themes/custom/`: BREBO-presentatielaag.
- `docs/`: architectuur, besluiten en ontwikkelrichtlijnen.

De webserver moet uitsluitend naar `web/` wijzen. Runtimebestanden, uploads, lokale instellingen en geheimen horen niet in Git.

## Huidige fase

Eerst wordt het publieke websitekarkas opgebouwd. Alleen aantoonbaar benodigde modules worden geactiveerd. BREBO Office en verdere kennisarchitectuur volgen gefaseerd.
