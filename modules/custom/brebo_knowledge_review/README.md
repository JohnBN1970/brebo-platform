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

## Eerste implementatiefase

De eerste fase blijft bewust klein:

- modulegrens en dependency vastleggen;
- reviewroute en permission toevoegen;
- één reviewformulier voor bestaande `brebo_knowledge_item` nodes;
- zeven canonieke velden in hetzelfde formulier kunnen corrigeren;
- reviewstatus en AI-vrijgave expliciet gescheiden modelleren;
- functionele tests voor toegangscontrole, revisies en de scheiding tussen goedkeuring en AI-vrijgave.

Er worden in deze fase geen nieuwe canonieke KnowledgeItem-velden toegevoegd.
