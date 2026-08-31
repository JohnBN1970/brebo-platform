# BREBO Knowledge Review

`brebo_knowledge_review` is de ondersteunende redactionele laag bovenop de canonieke module `brebo_knowledge`.

## Verantwoordelijkheid

Deze module mag:

- KnowledgeItems ter menselijke beoordeling aanbieden;
- de zeven bestaande canonieke inhoudsvelden via de Drupal entity API bewerkbaar maken;
- bronbeoordeling en redactionele besluitvorming ondersteunen;
- een afzonderlijke AI-vrijgave beheren;
- revisies gebruiken om inhoudelijke wijzigingen herleidbaar te houden.

Deze module mag niet:

- de zestien canonieke configuratieobjecten van `brebo_knowledge` wijzigen of dupliceren;
- eigen kopieën van KnowledgeItem-inhoud als bron van waarheid opslaan;
- automatisch kandidaatkennis als goedgekeurde kennis behandelen;
- redactionele goedkeuring gelijkstellen aan AI-vrijgave;
- AI-vrijgave afleiden uit Drupal-publicatiestatus alleen.

## Redactionele beslisregel

Een KnowledgeItem kan inhoudelijk technisch correct zijn zonder dat dezelfde maatregel altijd noodzakelijk is. De beoordeling maakt daarom waar relevant onderscheid tussen technische prestatie/comfort, functionele bruikbaarheid, esthetische kwaliteit, risico/urgentie en passende vervolgstap.

Voorbeeld: condens of blijvende waas tussen glasbladen kan op een defecte randafdichting wijzen. Dat betekent niet automatisch dat onmiddellijke vervanging op uitsluitend thermische gronden noodzakelijk is. Blijvende waas kan echter het doorzicht en de esthetische kwaliteit aantasten en daarmee vervanging praktisch of esthetisch wel passend maken. Vervanging van de isolatieglaseenheid betekent niet automatisch vervanging van het kozijn.

## Runtime

De reviewroute is `/admin/content/brebo-knowledge/{node}/review` en vereist de restricted permission `review brebo knowledge items`.

Het formulier:

- bewerkt rechtstreeks de zeven canonieke KnowledgeItem-velden;
- bewaart iedere inhoudelijke opslag als nieuwe node-revisie;
- vereist een redactionele revisietoelichting;
- registreert daarnaast een lichte reviewstatus in de reviewmodule zelf.

De reviewstatus kent vier toestanden:

- `Te beoordelen`;
- `In beoordeling`;
- `Goedgekeurd`;
- `Herziening nodig`.

Een reviewbesluit wordt gekoppeld aan de exacte KnowledgeItem-revisie, beoordelaar en het tijdstip van het besluit. Wanneer daarna een nieuwere node-revisie ontstaat, geldt de eerdere goedkeuring niet automatisch voor die nieuwe inhoud en wordt effectief `Herziening nodig` getoond.

De reviewstatus is ondersteunende metadata en geen tweede bron van waarheid voor de kennisinhoud.

## AI-vrijgave

AI-vrijgave is nog niet geïmplementeerd en blijft bewust een afzonderlijk besluit. Een KnowledgeItem kan dus redactioneel goedgekeurd zijn zonder voor AI te zijn vrijgegeven.

## Verificatie

`KnowledgeReviewFormTest` bewijst functioneel:

- zonder reviewpermission volgt HTTP 403;
- een node van een ander contenttype kan niet via de reviewroute worden aangepast;
- het reviewformulier is bereikbaar voor een bevoegde reviewer;
- de zeven canonieke velden worden via het bestaande KnowledgeItem bijgewerkt;
- opslaan creëert exact één extra revisie en bewaart de revisietoelichting;
- een goedkeuring wordt aan de opgeslagen revisie gekoppeld;
- een latere inhoudelijke revisie maakt die eerdere goedkeuring effectief `Herziening nodig`.

De aparte GitHub Actions-workflow controleert daarnaast Composer-metadata, diff-whitespace, Drupal coding standards, PHP-syntax en de functionele test.

Er worden geen nieuwe canonieke KnowledgeItem-velden toegevoegd en geen bestanden onder `modules/custom/brebo_knowledge` gewijzigd.

## Vervolg

Na groene runtimevalidatie volgen in afzonderlijke stappen:

1. bronregistratie en bronbeoordeling;
2. expliciete, afzonderlijke AI-vrijgave;
3. gecontroleerde opschaling van de redactionele kennisitems.
