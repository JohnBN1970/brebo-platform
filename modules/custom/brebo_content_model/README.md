# BREBO Contentmodel

Deze module breidt het bestaande contenttype `dienst` uit zonder het machine-id te wijzigen.

## Doel

De bezoeker wordt geleid vanuit het gebouwvraagstuk in plaats van vanuit het dienstenaanbod.

## Toegevoegde velden

- Gebouwvraagstuk
- Herkenbare signalen
- Mogelijke oorzaken
- Mogelijke gevolgen
- Oplossingsrichtingen
- Resultaten
- Urgentie
- Veelgestelde vragen

## Ingebruikname

```bash
vendor/bin/drush en brebo_content_model -y
vendor/bin/drush cr
```

Controleer daarna bij **Structuur > Inhoudstypen > Dienst** de formulier- en weergavevolgorde. Bestaande velden en bestaande content blijven behouden.

De bijbehorende presentatie staat in:

```text
themes/custom/brebo_theme/templates/content/node--dienst--full.html.twig
```
