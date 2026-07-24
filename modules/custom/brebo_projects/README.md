# BREBO Projecten

Dynamische projectmodule voor Drupal 10.3/11.

## Installatie

1. Plaats de map `brebo_projects` in:
   `platform/modules/custom/`
2. Activeer **BREBO Projecten** via **Uitbreiden**.
3. Leeg de Drupal-cache.
4. Voeg projecten toe via **Inhoud → Inhoud toevoegen → Project**.
5. Zet bij maximaal drie projecten **Uitgelicht op homepage** aan.
6. Ga naar **Structuur → Blokindeling**.
7. Plaats het blok **BREBO – Uitgelichte projecten** op de homepage.
8. Schakel de standaard bloktitel uit.

## Dynamiek

De View **BREBO uitgelichte projecten** bepaalt:
- welke gepubliceerde projecten zichtbaar zijn;
- dat alleen projecten met `Uitgelicht op homepage = Ja` worden getoond;
- de volgorde via `Sorteervolgorde`;
- een maximum van drie projecten.

De selectie kan later via de Views-interface worden aangepast.

## Belangrijk

De knop **Bekijk alle projecten** verwijst alvast naar `/projecten`.
Maak later de overzichtspagina op die URL.
