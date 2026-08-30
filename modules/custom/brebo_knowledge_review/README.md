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

Een KnowledgeItem kan inhoudelijk technisch correct zijn zonder dat dezelfde maatregel altijd noodzakelijk is. De beoordeling moet daarom waar relevant onderscheid maken tussen:

1. technische prestatie en comfort;
2. functionele bruikbaarheid, waaronder zicht en gebruik;
3. esthetische kwaliteit;
4. risico en urgentie;
5. passende volgende stap.

Voorbeeld: condens of blijvende waas tussen glasbladen kan op een defecte randafdichting wijzen. Dat betekent niet automatisch dat onmiddellijke vervanging op uitsluitend thermische gronden noodzakelijk is. Blijvende waas kan echter het doorzicht en de esthetische kwaliteit aantasten en daarmee vervanging praktisch of esthetisch wel passend maken. Vervanging van de isolatieglaseenheid betekent niet automatisch vervanging van het kozijn.

## Eerste runtime

De eerste runtime levert bewust alleen de redactionele correctiestap:

- restricted permission `review brebo knowledge items`;
- route `/admin/content/brebo-knowledge/{node}/review`;
- toegang uitsluitend voor `brebo_knowledge_item` nodes;
- hetzelfde formulier bevat de zeven canonieke inhoudsvelden;
- de vijf canoniek verplichte velden blijven verplicht;
- het bestaande tekstformaat per veld blijft behouden;
- iedere opslag wordt als nieuwe node-revisie vastgelegd;
- een redactionele revisietoelichting is verplicht.

Goedkeuringsstatus, bronmetadata en AI-vrijgave zijn in deze stap nog niet geïmplementeerd. Daarmee kan inhoudelijke correctie niet per ongeluk als goedkeuring of AI-vrijgave worden geïnterpreteerd.

## Verificatie

`KnowledgeReviewFormTest` bewijst functioneel:

- zonder reviewpermission volgt HTTP 403;
- een node van een ander contenttype kan niet via de reviewroute worden aangepast;
- het reviewformulier is bereikbaar voor een bevoegde reviewer;
- de zeven canonieke velden worden via het bestaande KnowledgeItem bijgewerkt;
- opslaan creëert exact één extra revisie en bewaart de revisietoelichting.

De aparte GitHub Actions-workflow controleert daarnaast Composer-metadata, diff-whitespace, Drupal coding standards, PHP-syntax en de functionele test.

Er worden geen nieuwe canonieke KnowledgeItem-velden toegevoegd en geen bestanden onder `modules/custom/brebo_knowledge` gewijzigd.
