# BREBO Platform Doctrine

## Missie

Het BREBO Platform wordt ontwikkeld als een duurzaam, modulair en onderhoudbaar Drupal-platform dat de dienstverlening van BREBO ondersteunt. Iedere technische keuze moet bijdragen aan kwaliteit, eenvoud, betrouwbaarheid en toekomstbestendigheid.

---

## Kernprincipes

### 1. Het platform staat centraal
We bouwen één platform dat eenvoudig uitbreidbaar is. Functionaliteit wordt toegevoegd, niet vervangen.

### 2. GitHub is de enige bron van waarheid
Iedere wijziging wordt beheerd via Git. De productieomgeving is nooit leidend.

### 3. Drupal Core blijft onaangetast
Core wordt nooit aangepast. Alle maatwerk bevindt zich in custom modules, themes of configuratie.

### 4. Modulair ontwikkelen
Iedere functionele uitbreiding krijgt een eigen module met een duidelijke verantwoordelijkheid. Modules zijn onafhankelijk en herbruikbaar.

### 5. Scheiding van verantwoordelijkheden
Content, configuratie en presentatie blijven strikt gescheiden.

- Content behoort in entities.
- Configuratie behoort in config.
- Presentatie behoort in Twig en CSS.
- Logica behoort in PHP.

### 6. Kleine gecontroleerde wijzigingen
Werk in kleine, afgeronde stappen. Iedere wijziging moet binnen ongeveer één uur realiseerbaar en controleerbaar zijn.

### 7. Eerst testen, daarna committen
Iedere wijziging wordt gecontroleerd voordat deze wordt vastgelegd.

Volgorde:

- ontwikkelen
- testen
- committen
- pushen

### 8. Geen herstel vanuit oude releases
Oude releases dienen uitsluitend als referentie. Nieuwe ontwikkeling vindt altijd plaats vanuit de actuele hoofdbranch.

### 9. Documentatie is onderdeel van de software
Nieuwe modules, architectuurkeuzes en uitzonderingen worden direct gedocumenteerd.

### 10. Stabiliteit boven snelheid
Een stabiel platform heeft altijd prioriteit boven het snel opleveren van nieuwe functionaliteit.

---

## Ontwikkelregels

- Eén feature per branch.
- Eén verantwoordelijkheid per module.
- Geen duplicatie van code.
- Geen ongebruikte modules.
- Geen tijdelijke oplossingen in productie.
- Iedere release moet reproduceerbaar zijn.

---

## Kwaliteitsdoel

Het BREBO Platform moet ook over vijf jaar nog eenvoudig uitbreidbaar zijn, zonder afhankelijk te zijn van herstelwerk, workarounds of verborgen kennis.