# BREBO Context

Eerste basis voor contextgestuurde navigatie binnen het BREBO-platform.

## Wat de module doet

- Analyseert gepubliceerde content van het type `dienst`.
- Vergelijkt titel, gebouwvraagstuk, signalen, oorzaken, gevolgen, oplossingsrichtingen en resultaten.
- Geeft extra gewicht aan gelijke urgentie.
- Toont maximaal drie gerelateerde gebouwvraagstukken op de volledige Dienst-pagina.
- Valt bij weinig inhoud terug op de meest recent gewijzigde vraagstukken.

## Installatie

```bash
vendor/bin/drush en brebo_context -y
vendor/bin/drush cr
```

## Belangrijk

De kwaliteit van de aanbevelingen groeit mee met de inhoud. Vul vooral `Gebouwvraagstuk`, `Herkenbare signalen` en `Mogelijke oorzaken` zorgvuldig in.

Dit is de technische basis. In een volgende stap kan de resolver worden uitgebreid met expliciete metadata zoals gebouwdelen, gebouwtypen, doelgroepen en werkzaamheden.
