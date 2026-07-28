# BREBO Knowledge

`brebo_knowledge` implementeert één revisioneerbaar Drupal-nodeobject voor probleemgerichte kennisbijdragen.

## Bundle

- machine name: `brebo_knowledge_item`
- label: `Probleemgerichte kennisbijdrage`
- nieuwe revisie standaard ingeschakeld
- geen bodyveld
- geen taxonomie, relaties, Views of presentatielaag

## Velden

Verplicht:

- `field_knowledge_observation`
- `field_knowledge_meaning`
- `field_knowledge_urgency`
- `field_knowledge_first_step`
- `field_knowledge_risks`

Optioneel:

- `field_knowledge_regie`
- `field_knowledge_realization`

Alle zeven velden zijn van het type `text_long`.

## Installatie en update

Nieuwe installaties gebruiken de configuratie in `config/optional`. Bestaande metadata-only installaties gebruiken `brebo_knowledge_update_11001()`.

De update voert vooraf conflictcontroles uit, overschrijft geen bestaande afwijkende configuratie en verwijdert bij een onverwachte fout uitsluitend configuratie die tijdens die run is aangemaakt.
