# Documentresultaat - bezoekerspresentatie

Aanleiding: #179. Websitepresentatie, geen nieuwe Office-verwerking.

## Gedrag

De resultaatpagina gebruikt een eigen library, zonder configurator-JavaScript.
Het overzicht en de status per bestand blijven zichtbaar. Alleen de uitgelezen
brontekst klapt open. Desktop gebruikt een tabel; mobiel dezelfde gegevens als
kaarten. De eerste vijf Office-documenten staan in bronvolgorde bovenaan,
inclusief kinderen uit een ZIP. Zonder kindlijst blijft het ontvangen archief
zichtbaar als een enkele upload met een nog ontbrekend uitleesresultaat.

De presenter berekent de zichtbare statussen uit de bestaande documentregels.
Ontbrekende regels of nieuwe statuswaarden worden nooit als geslaagde uitlezing
of als bewezen fout ingevuld. Interne extractornamen, scores en ruwe foutcodes
staan niet in het bezoekersmodel. Er wordt geen projectscope of classificatie
verzonnen en er wordt geen contractveld toegevoegd aan de Office-client.

## Previews en grenzen

Maximaal vijf JPEG/PNG/WebP-bronnen worden lokaal verkleind en opnieuw als JPEG
gecodeerd, uitsluitend uit de bestaande intake-directory. Geen nieuwe route,
geen externe URL, geen opslag in public:// en geen bronbestand in de HTML.
ZIPs worden read-only bekeken, nooit uitgepakt op schijf. Onduidelijke dubbele
bestandsnamen, traversal, ontbrekende bestanden en actieve inhoud leveren een
fallback op. Grenzen: 2000 ZIP-items, 5 MiB per beeldbron, 12 megapixels,
compressieverhouding maximaal 200, geheugencontrole en 128 KiB per eindpreview.
GD en ZIP zijn optioneel voor runtime; ontbrekende ondersteuning blokkeert de
resultaatpagina niet. Bestaande resultaattoegang en cache max-age 0 blijven gelijk.

PDF en HEIC hebben in deze fase een eerlijke documenttegel, geen nagebootste
thumbnail. Een veilige previewvoorziening van Office kan later worden aangesloten
zonder een herkenningsmotor in Platform te bouwen. Een previewstatus is niet de
uitleesstatus. Bij een CSP die data:-afbeeldingen blokkeert moet de previewkeuze
worden herzien; verruim niet automatisch het sitebrede beveiligingsbeleid.

## Controles en acceptatie

`php modules/custom/brebo_europakozijn/tests/project-result.php`

De aparte PR-workflow test PHP, GD/ZIP-bronnen, onveilige/misleidende bestanden,
ontbrekende resultaten, zeven regels/3 uitgelezen/4 fouten, vijf kaarten en echte
Twig-rendering met HTML-escaping. De HTML-testfixture bevat alleen testgegevens.
Na deployment: open een bestaand resultaat met een ZIP en controleer kaartvolgorde,
alle zeven regels, zichtbare foutstatussen, een uitklapbaar bronfragment, toetsenbord-
focus en de pagina op 390 px. Test minstens een ondersteund werkelijk bronbeeld.
Een bestaande browserweergave en de nieuwe layout hebben dezelfde Office-inhoud.

Deployment: normale Platform-deploy en `drush cr` voor library/service-discovery.
Geen database-update, geen migratie en geen wijziging aan upload, Office, HMAC of
API-v1-clients uit #178. Rollback: revert de layoutcommit en bouw de cache opnieuw.
