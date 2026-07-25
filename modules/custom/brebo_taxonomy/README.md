# BREBO Taxonomy

Deze module voegt de eerste gestructureerde metadata voor gebouwvraagstukken toe aan het bestaande contenttype `dienst`.

## Veilig uitbreidingsprincipe

De module werkt uitsluitend aanvullend:

- bestaande vocabularies worden niet opnieuw aangemaakt;
- bestaande termen worden niet gewijzigd of verwijderd;
- bestaande veldopslag wordt niet overschreven;
- bestaande veldinstanties worden niet gewijzigd;
- bestaande formulier- en weergavecomponenten behouden hun instellingen;
- alleen ontbrekende onderdelen worden toegevoegd.

Wanneer een bestaand veld dezelfde machinenaam heeft maar een onverenigbaar veldtype gebruikt, slaat de module dit veld over en schrijft zij een waarschuwing naar het Drupal-logboek.

## Toegevoegde vocabularies

- Gebouwtypen
- Gebouwdelen
- Vraagstukken
- Werkzaamheden
- Materialen
- Doelgroepen
- Resultaattypen

Iedere vocabulary krijgt een beperkte eerste set BREBO-termen. Bestaande termen met dezelfde naam blijven onaangetast.

## Velden op Dienst

- `field_building_types`
- `field_building_parts`
- `field_issue_types`
- `field_activities`
- `field_materials`
- `field_audiences`
- `field_result_types`

Alle velden zijn meervoudige taxonomiereferenties.

## Installeren

```bash
vendor/bin/drush en brebo_taxonomy -y
vendor/bin/drush cr
```

## Controleren

Open daarna een bestaand Dienst-item. Onderaan het bewerkingsformulier moeten de nieuwe metadata-velden zichtbaar zijn. Controleer daarnaast via **Structuur → Taxonomie** of de vocabularies en termen zijn toegevoegd.
