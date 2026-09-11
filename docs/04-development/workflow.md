# Ontwikkelworkflow BREBO Platform

## 1. Intake

Nieuwe voorstellen starten in de Ideeënbus. Tijdens triage wordt bepaald of het voorstel wordt:

- afgewezen of uitgesteld;
- direct omgezet naar een afgebakend Issue;
- eerst uitgewerkt als RFC;
- vastgelegd als ADR of formeel besluit;
- gekoppeld aan een release of milestone.

## 2. Branchstrategie

```text
main
└── develop
    ├── feature/*
    ├── fix/*
    └── docs/*

main
└── hotfix/*
```

### `main`

Bevat alleen geaccepteerde releases en urgente productiecorrecties.

### `develop`

Is de integratiebranch voor Platform 1.0 en de bron voor dev-platform.

### Feature- en fix-branches

Starten vanaf de actuele `develop` en keren via een Pull Request terug naar `develop`.

### Hotfixes

Starten vanaf `main`, worden getest en daarna zowel naar `main` als terug naar `develop` verwerkt.

## 3. Stacked Pull Requests

Gestapelde PR's zijn alleen toegestaan wanneer:

- de afhankelijkheid technisch noodzakelijk is;
- iedere PR de juiste base branch gebruikt;
- de keten zichtbaar in de PR-beschrijving staat;
- iedere PR één afzonderlijk doel houdt;
- na merge van een voorganger de volgende PR opnieuw op de correcte base wordt gecontroleerd.

Voor gewone wijzigingen hebben onafhankelijke PR's de voorkeur.

## 4. Review

Review controleert minimaal:

- scope en gekoppeld Issue;
- modulegrenzen en architectuur;
- configuratie- en data-impact;
- security en secrets;
- cacheability en toegangscontrole;
- testresultaten;
- deployment- en rollbackstappen;
- documentatie.

## 5. Merge

Gebruik bij voorkeur squash merge voor afgebakende feature- en fix-PR's, zodat `develop` een leesbare geschiedenis houdt. Een merge commit kan worden gebruikt wanneer een bewuste branchgeschiedenis behouden moet blijven.

## 6. Deployment naar dev-platform

Het exacte Hostinger-deploymentproces wordt vastgesteld in Issue #6. Tot dat besluit geldt:

- voer geen stilzwijgende wijzigingen uit aan core/vendor-tracking;
- documenteer handmatige servercommando's in iedere PR;
- controleer branch en working tree vóór pull of checkout;
- maak database- of configuratie-impact expliciet;
- voer na deployment minimaal cache rebuild en functionele verificatie uit.

## 7. Acceptatie en release

Na merge naar `develop`:

1. deploy naar dev-platform;
2. technische controles uitvoeren;
3. functionele acceptatie registreren;
4. gebreken als aparte Issues vastleggen;
5. geaccepteerde wijzigingen bundelen in een release naar `main`;
6. release notes en changelog bijwerken.
