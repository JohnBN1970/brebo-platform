# Git-scope BREBO Platform 1.0

## Doel

Dit document bepaalt welke soorten bestanden in de BREBO-repository thuishoren zolang ADR-0001 van kracht is.

## In Git

| Pad of bestandstype | Beleid | Toelichting |
|---|---|---|
| `composer.json` | opnemen | Declaratie van projectafhankelijkheden en installatiepaden. |
| `composer.lock` | opnemen | Borgt reproduceerbare dependencyversies. |
| `core/` | tijdelijk opnemen | Onderdeel van het huidige legacy-root- en Hostinger-model. |
| `vendor/` | tijdelijk opnemen | Blijft zolang deployment geen betrouwbare Composer-build uitvoert. |
| `modules/contrib/` | tijdelijk opnemen | Volgt het huidige deploymentmodel. |
| `themes/contrib/` | tijdelijk opnemen | Volgt het huidige deploymentmodel. |
| `modules/custom/` | opnemen | BREBO-functionaliteit en domeinlogica. |
| `themes/custom/` | opnemen | BREBO-presentatielaag. |
| `config/` of vastgestelde config-exportmap | opnemen | Alleen gecontroleerde Drupal-configuratie. |
| `sites/*/settings.php` | opnemen onder voorwaarden | Geen secrets; alleen veilige standaard- en include-logica. |
| `.github/` | opnemen | Issues, Pull Requests en CI-governance. |
| `docs/` | opnemen | Architectuur, besluiten, ontwikkeling en deployment. |
| tests en ontwikkelconfiguratie | opnemen wanneer generiek | Geen lokale credentials of machinegebonden paden. |

## Niet in Git

| Pad of bestandstype | Reden |
|---|---|
| `sites/*/files/` | Runtimeuploads en afgeleide bestanden. |
| `sites/*/private/` | Private runtimebestanden. |
| `sites/simpletest/` | Gegenereerde testbestanden. |
| `settings.local.php` | Lokale en gevoelige configuratie. |
| `services.local.yml` | Lokale service-overrides. |
| `.env` en `.env.*` | Secrets en omgevingsvariabelen. |
| `*.sql`, `*.sql.gz` | Database-exports kunnen persoonsgegevens en secrets bevatten. |
| `backup/`, `backups/` | Niet-versioneerbare herstelkopieën. |
| logs, caches en tijdelijke bestanden | Runtime- en machinegebonden gegevens. |
| IDE- en OS-bestanden | Persoonlijke ontwikkelomgeving. |
| `.git-credentials` | Authenticatiegegevens. |

## Veiligheidsregels

1. Een `.gitignore` voorkomt niet dat reeds gevolgde bestanden in Git blijven staan.
2. Voor iedere wijziging aan secrets of runtimepaden moet ook worden gecontroleerd of de bestanden al in de Git-historie voorkomen.
3. Mogelijke blootstelling van een secret vereist rotatie; alleen verwijderen uit de huidige branch is niet voldoende.
4. Database-exports en uploads worden nooit gebruikt als makkelijke overdrachtsmethode via Git.
5. Omgevingsspecifieke waarden worden buiten de repository gehouden en vanuit `settings.php` veilig ingeladen.

## Toekomstige doelrichting

Na invoering van reproduceerbare builds wordt opnieuw beoordeeld of `core/`, `vendor/` en contribcode uit Git kunnen verdwijnen en tijdens deployment uit `composer.lock` kunnen worden opgebouwd.
