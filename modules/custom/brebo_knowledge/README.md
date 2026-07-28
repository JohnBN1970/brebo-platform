# BREBO Knowledge

## Doel

`brebo_knowledge` is eigenaar van `OBJ-001 KnowledgeItem` en implementeert de minimale Drupal-runtime voor probleemgerichte, revisioneerbare kennisbijdragen.

## Opslagmapping

- entity type: `node`;
- bundle: `brebo_knowledge_item`;
- functionele identifier: `field_brebo_stable_id`;
- formaat: `KI-000001`;
- lifecycle: `concept`, `published`, `archived`.

## Velden

- `field_brebo_stable_id`;
- `field_brebo_observation`;
- `field_brebo_meaning`;
- `field_brebo_urgency`;
- `field_brebo_first_step`;
- `field_brebo_risks`;
- `field_brebo_lens_stages`;
- `field_brebo_lifecycle_status` als expliciete technische lifecyclemapping.

## Installatie

```bash
vendor/bin/drush en brebo_knowledge -y
vendor/bin/drush cr
vendor/bin/drush config:get node.type.brebo_knowledge_item
```

De installatie maakt geen content aan en overschrijft geen conflicterende veldopslag. Bij een onverenigbaar bestaand veld stopt de installatie gecontroleerd.

## Rechten

De module levert afzonderlijke capabilities voor:

- aanmaken;
- eigen of alle kennisbijdragen bewerken;
- publiceren;
- archiveren;
- revisies bekijken.

Koppel deze permissions via siteconfiguratie aan de rollen kennisredacteur en kennisbeheerder.

## Stable ID

Bij het aanmaken wordt automatisch de eerstvolgende vrije ID gegenereerd. De ID is redactioneel niet wijzigbaar, blijft gelijk over revisies en wordt op uniciteit gecontroleerd.

## Testen

```bash
vendor/bin/phpunit modules/custom/brebo_knowledge/tests/src/Kernel/KnowledgeItemRuntimeTest.php
```

Aanvullend op dev-platform:

```bash
vendor/bin/drush cr
vendor/bin/drush php:eval 'print_r(array_keys(\Drupal::service("entity_type.bundle.info")->getBundleInfo("node")));'
```

## Buiten scope

Deze implementatieslice bevat geen relaties naar diensten, werkzaamheden of media, geen lokale gebouwclassificaties, geen API, MCP, zoekindex of themepresentatie.

## Rollback en uninstall

Uninstall is alleen veilig wanneer geen KnowledgeItem-content behouden hoeft te blijven. Maak bij bestaande content eerst een expliciete export of migratie. De module verwijdert geen configuratie of gegevens van andere domeinmodules.
