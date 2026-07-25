# BREBO Core

`brebo_core` is de centrale, alleen-lezen diagnosebasis van het BREBO-platform.
De eerste versie wijzigt geen content, velden, taxonomieën of configuratie.

## Commando's

```bash
vendor/bin/drush brebo:doctor
vendor/bin/drush brebo:integrity
vendor/bin/drush brebo:statistics
vendor/bin/drush brebo:readiness
```

## Doctor

Controleert onder meer:

- Drupal- en PHP-versie;
- databaseverbinding;
- aanwezigheid en status van BREBO-modules;
- verwachte vocabularies;
- verwachte velden op contenttype `dienst`;
- een procentuele platformscore.

## Integrity

Controleert uitsluitend of de verwachte vocabularies en veldinstanties aanwezig zijn. Ontbrekende onderdelen worden gemeld, nooit automatisch aangemaakt of overschreven.

## Statistics

Toont aantallen Dienstpagina's, Projecten en termen per BREBO-vocabulary.

## Readiness

Toont hoeveel Dienstpagina's nog geen FAQ, resultaten, metadata of gekoppelde projecten hebben.

## Installatie

```bash
git fetch origin
git checkout feature/brebo-platform-doctor
git pull origin feature/brebo-platform-doctor
vendor/bin/drush en brebo_core -y
vendor/bin/drush cr
vendor/bin/drush brebo:doctor
```

## Veiligheidsdoctrine

- alleen lezen en rapporteren;
- geen bestaande configuratie overschrijven;
- geen content wijzigen of verwijderen;
- ontbrekende onderdelen expliciet melden;
- herstelacties pas toevoegen nadat de diagnose op de productieomgeving is gevalideerd.

De toekomstige `brebo:install`- en `brebo:update`-commando's worden bewust pas toegevoegd nadat deze diagnostische basis op de echte omgeving correct werkt. Daarmee voorkomen we dat een automatisch herstel- of updatecommando op aannames wordt gebouwd.
