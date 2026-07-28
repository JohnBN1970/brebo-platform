# Bijdragen aan BREBO Platform

## Vooraf

Iedere wijziging begint met een herleidbare aanleiding:

- een goedgekeurd GitHub Issue;
- een RFC wanneer meerdere oplossingsrichtingen of brede impact bestaan;
- een ADR wanneer een architectuurkeuze wordt vastgelegd;
- een formeel besluit wanneer scope, risico of governance dat vereist.

Losse ideeën worden eerst in de Ideeënbus getriageerd.

## Branches

Maak branches vanaf `develop`.

```text
feature/korte-omschrijving
fix/korte-omschrijving
hotfix/korte-omschrijving
docs/korte-omschrijving
```

Een branch behandelt één afgebakend doel. Stapel branches alleen wanneer de afhankelijkheid expliciet in alle betrokken Pull Requests staat.

## Commits

Gebruik korte, beschrijvende commitberichten in gebiedende wijs.

Voorbeelden:

```text
Voeg CTA-configuratie toe
Corrigeer cachemetadata contextresolver
Documenteer deployment naar dev-platform
```

Commit geen secrets, uploads, database-exports, lokale instellingen of runtimebestanden.

## Pull Requests

Een Pull Request bevat minimaal:

- aanleiding en gekoppeld Issue;
- wat is gewijzigd;
- wat bewust niet is gewijzigd;
- architectuurimpact;
- risico's en terugvalmogelijkheid;
- uitgevoerde technische controles;
- functionele teststappen;
- eventuele deployment- of updatecommando's.

Maak de Pull Request standaard als draft totdat de wijziging technisch en inhoudelijk toetsbaar is.

## Reviewvoorwaarden

Een wijziging mag pas worden gemerged wanneer:

- de scope overeenkomt met het Issue;
- onbedoelde bestanden ontbreken in de diff;
- afhankelijkheden correct zijn vastgelegd;
- relevante tests en controles zijn uitgevoerd;
- documentatie is bijgewerkt;
- database-, configuratie- en deploymentimpact bekend zijn;
- een terugvalroute bestaat voor risicovolle wijzigingen.

## Drupal-richtlijnen

- Gebruik dependency injection in services en classes.
- Houd modules klein en doelgericht.
- Laat bestaande configuratie en content ongemoeid tenzij wijziging expliciet onderdeel van de opdracht is.
- Maak installatie- en updatepaden herhaalbaar.
- Beheer geen secrets in Git.
- Voeg cache tags, contexts en max-age correct toe wanneer output contextafhankelijk is.
- Gebruik machine-namen consequent en wijzig bestaande machine-namen alleen via een goedgekeurd migratieplan.

## Merge en release

Feature- en fix-branches mergen naar `develop`. Na acceptatie op dev-platform wordt een release voorbereid richting `main`. Hotfixes worden afzonderlijk beoordeeld en daarna teruggesynchroniseerd naar `develop`.
