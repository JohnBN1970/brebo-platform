# BREBO Coding Standards

## Doel

Deze standaard beschrijft hoe code binnen het BREBO Platform wordt geschreven.

Het doel is een consistente, onderhoudbare en hoogwaardige codebase die door iedere ontwikkelaar eenvoudig te begrijpen is.

---

# Algemene principes

## Leesbaarheid boven slimheid

Code wordt geschreven voor mensen.

Een eenvoudige oplossing heeft altijd de voorkeur boven een complexe oplossing.

---

## Eén verantwoordelijkheid

Iedere:

- module
- class
- service
- plugin
- Twig-template

heeft één duidelijke verantwoordelijkheid.

---

## Vermijd duplicatie

Gebruik bestaande componenten waar mogelijk.

Nieuwe functionaliteit wordt alleen gebouwd wanneer hergebruik niet mogelijk is.

---

# PHP

## Strict types

Iedere PHP-file begint met:

```php
<?php

declare(strict_types=1);
```

---

## Coding Standard

PSR-12 is verplicht.

---

## Constructor Injection

Gebruik dependency injection.

Voorkeur:

```php
public function __construct(EntityTypeManagerInterface $entityTypeManager)
```

Niet:

```php
\Drupal::service(...)
```

Behalve wanneer Drupal-hooks dit noodzakelijk maken.

---

## Classes

Gebruik waar mogelijk:

- final classes
- readonly properties (indien geschikt)

---

## PHPDoc

Iedere publieke methode bevat documentatie.

Voorbeeld:

```php
/**
 * Builds the hero section.
 */
public function build(): array
```

---

## Type hints

Altijd gebruiken.

Voorbeeld:

```php
public function build(): array
```

Niet:

```php
public function build()
```

---

# Drupal

## Geen wijzigingen aan Core

Drupal Core wordt nooit aangepast.

---

## Configuratie

Configuratie hoort in:

config/install

Niet in install hooks.

---

## Hooks

Gebruik hooks alleen waarvoor ze bedoeld zijn.

Businesslogica hoort in:

- Services
- Plugins
- Managers

---

## Services

Nieuwe functionaliteit wordt waar mogelijk als service gebouwd.

---

## Routing

Gebruik routes.

Geen hardcoded URL's.

---

## Permissions

Gebruik eigen permissions.

Niet:

```php
access content
```

voor maatwerkfunctionaliteit.

---

# Twig

Twig bevat uitsluitend presentatie.

Niet toegestaan:

- database-opvragingen
- entity queries
- businesslogica

Wel toegestaan:

- if
- for
- include
- component rendering

---

# CSS

Gebruik BEM.

Voorbeeld:

```css
.hero {}

.hero__content {}

.hero__title {}

.hero--dark {}
```

---

## Inline styles

Niet toegestaan.

---

## Kleuren

Gebruik CSS variabelen.

Voorbeeld:

```css
var(--color-primary)
```

---

## Responsive

Mobile First.

---

# JavaScript

Gebruik alleen JavaScript wanneer functioneel noodzakelijk.

Gebruik Drupal behaviors.

Geen globale scripts.

---

# HTML

Gebruik semantische HTML.

Voorbeelden:

- section
- article
- header
- nav
- aside
- footer

Niet overal div gebruiken.

---

# Git

Iedere feature:

- eigen branch
- kleine commits
- duidelijke commit messages

Voorbeeld:

```
Add services content type

Improve hero accessibility

Refactor projects module
```

Niet:

```
Update

Fix

Changes
```

---

# Modules

Iedere module bevat minimaal:

- info.yml
- README.md
- indien nodig install
- indien nodig uninstall
- config/install
- src
- templates
- css
- js

---

# Naamgeving

Modules:

```
brebo_services
```

Classes:

```
ServicesController
```

Services:

```
brebo.services.manager
```

Twig:

```
brebo-services.html.twig
```

CSS:

```
brebo-services.css
```

---

# Kwaliteitscontrole

Voor iedere commit wordt gecontroleerd:

- Drupal werkt
- Drush werkt
- Config export schoon
- Git status schoon
- Geen PHP warnings
- Geen Twig errors

---

# Reviewvragen

Voor iedere nieuwe feature:

- Is dit eenvoudig?
- Is dit onderhoudbaar?
- Past dit in de architectuur?
- Kan dit worden hergebruikt?
- Is de code leesbaar?
- Is documentatie aanwezig?
- Is testen uitgevoerd?

Wanneer één van deze vragen negatief wordt beantwoord, wordt de wijziging niet opgenomen.

---

# Definitie van gereed

Een feature is pas gereed wanneer:

- functionaliteit werkt
- code voldoet aan deze standaard
- configuratie is geëxporteerd
- documentatie is bijgewerkt
- Git commit is gemaakt
- wijzigingen zijn gepusht