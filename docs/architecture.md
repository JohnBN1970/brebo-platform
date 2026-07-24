# BREBO Platform Architecture

## Doel

Dit document beschrijft de functionele en technische architectuur van het BREBO Drupal-platform.

Het doel is om iedere module één duidelijke verantwoordelijkheid te geven en te voorkomen dat functionaliteit, presentatie en configuratie door elkaar gaan lopen.

---

## Architectuurprincipes

1. Iedere module heeft één duidelijke verantwoordelijkheid.
2. Functionaliteit wordt niet dubbel gebouwd.
3. Content, configuratie, logica en presentatie blijven gescheiden.
4. Modules zijn zo zelfstandig mogelijk.
5. Afhankelijkheden worden expliciet vastgelegd.
6. Configuratie wordt via Git beheerd.
7. Oude releases zijn geen ontwikkelbasis.
8. Nieuwe functionaliteit wordt toegevoegd vanuit de actuele codebase.

---

## Laagmodel

### Contentlaag

De contentlaag bevat redactionele gegevens zoals:

- projecten;
- diensten;
- voordelen;
- teksten;
- afbeeldingen;
- klantverhalen;
- teamleden.

Content wordt opgeslagen in Drupal entities, zoals nodes, media en taxonomy terms.

### Configuratielaag

De configuratielaag bevat onder andere:

- contenttypes;
- velden;
- Views;
- block-configuratie;
- form displays;
- view displays;
- rollen en rechten;
- overige Drupal-configuratie.

Configuratie wordt geëxporteerd naar de configuratiemap en beheerd via Git.

### Logica

PHP bevat uitsluitend functionele logica, zoals:

- installatielogica;
- preprocess-logica;
- services;
- plugins;
- validatie;
- entity queries;
- datatransformatie.

Logica hoort niet in Twig-templates.

### Presentatielaag

De presentatielaag bestaat uit:

- Twig;
- CSS;
- JavaScript;
- theme libraries;
- templates en componenten.

Presentatie bevat geen bedrijfslogica en slaat geen content op.

---

# Huidige modules

## brebo_benefits

### Verantwoordelijkheid

Beheert en presenteert de belangrijkste voordelen van BREBO.

### Functioneel doel

Bezoekers snel duidelijk maken waarom opdrachtgevers voor BREBO kiezen.

### Bevat

- voordeelitems;
- iconen of visuele kenmerken;
- presentatie als blok of sectie.

### Status

Actief.

### Architectuurnotitie

De module mag uitsluitend voordelen beheren en presenteren. Algemene content of methodiek hoort hier niet thuis.

---

## brebo_cta

### Verantwoordelijkheid

Beheert herbruikbare call-to-actionblokken.

### Functioneel doel

Bezoekers gericht begeleiden naar een volgende actie, zoals contact opnemen, projecten bekijken of kennismaken met de BREBO Lens.

### Bevat

- CTA-titel;
- CTA-tekst;
- primaire link;
- optionele secundaire link;
- CTA-presentatie.

### Status

Actief.

### Architectuurnotitie

CTA’s moeten herbruikbaar zijn en mogen niet hard worden gekoppeld aan één specifieke pagina.

---

## brebo_hero

### Verantwoordelijkheid

Beheert hero-secties voor belangrijke pagina’s.

### Functioneel doel

De hoofdboodschap, ondersteunende tekst, afbeelding en primaire acties bovenaan een pagina presenteren.

### Bevat

- eyebrow;
- titel;
- intro;
- afbeelding;
- primaire CTA;
- secundaire CTA;
- hero-template.

### Status

Actief.

### Architectuurnotitie

De hero is bedoeld voor prominente paginapresentatie. Reguliere paginabanners horen in `brebo_page_banner`.

---

## brebo_lens

### Verantwoordelijkheid

Beheert en presenteert de BREBO Lens-methodiek.

### Functioneel doel

Uitleggen hoe BREBO werkt volgens:

- Inzicht;
- Regie;
- Realisatie.

### Bevat

- methodiekonderdelen;
- inhoudelijke toelichting;
- Lens-presentatie;
- bijbehorende templates.

### Status

Actief.

### Architectuurnotitie

De inhoud van de methodiek moet uiteindelijk redactioneel beheerbaar zijn. Hardgecodeerde inhoud wordt alleen tijdelijk toegestaan.

---

## brebo_page_banner

### Verantwoordelijkheid

Beheert compacte paginabanners voor binnenpagina’s.

### Functioneel doel

Een pagina introduceren zonder de omvang en nadruk van een volledige hero.

### Bevat

- titel;
- intro;
- optionele afbeelding;
- bannerpresentatie.

### Status

Actief.

### Architectuurnotitie

Voorkom overlap met `brebo_hero`. Hero en page banner hebben ieder een duidelijk eigen gebruiksmoment.

---

## brebo_pillars

### Verantwoordelijkheid

Beheert de drie kernpijlers van BREBO.

### Functioneel doel

De brede dienstverlening van BREBO overzichtelijk structureren.

### Bevat

- pijlers;
- titels;
- toelichtingen;
- links;
- presentatie als sectie of blok.

### Status

Actief.

### Architectuurnotitie

De pijlers zijn een vaste inhoudelijke structuur en mogen niet worden gebruikt als generieke kaartcomponent.

---

## brebo_projects

### Verantwoordelijkheid

Beheert projecten en projectpresentatie.

### Functioneel doel

Referentieprojecten tonen aan woningcorporaties, VvE’s en professionele vastgoedeigenaren.

### Architectuur

```text
Contenttype
→ velden
→ Views
→ block display
→ Twig-template
→ theme styling