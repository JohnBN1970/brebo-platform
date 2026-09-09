(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnControlTruthGate = {
    attach(context) {
      once('brebo-europakozijn-control-truth-gate', '[data-europakozijn-configurator]', context).forEach((root) => {
        const form = root.querySelector('[data-ek-form]');
        const calibration = root.querySelector('[data-ek-calibration]');
        const panel = root.querySelector('.ek-control-screen');
        const grid = panel?.querySelector('[data-ek-control-grid]');
        const result = panel?.querySelector('[data-ek-control-result]');
        const priceStatus = root.querySelector('[data-ek-price-status]');
        if (!form || !calibration || !panel || !grid || !result) return;

        let sequence = 0;
        let timer = null;
        let productState = {status: 'pending', message: 'Productregels worden server-side gecontroleerd.'};

        const val = (name) => String(form.elements.namedItem(name)?.value || '').trim();
        const card = (title, value, state, help) => {
          const el = document.createElement('article');
          el.className = `ek-control-card is-${state}`;
          const label = document.createElement('span');
          label.textContent = title;
          const strong = document.createElement('strong');
          strong.textContent = value;
          const small = document.createElement('small');
          small.textContent = help;
          el.append(label, strong, small);
          return el;
        };
        const payload = () => {
          try { return JSON.parse(calibration.value || calibration.textContent || '{}'); }
          catch (error) { return null; }
        };
        const contextComplete = () => Boolean(
          val('bag_adresseerbaar_object_id') &&
          val('floor_level') !== '' &&
          val('room_type') &&
          Number(val('room_area_m2')) > 0 &&
          val('ventilation_system')
        );

        const render = () => {
          const addressOk = Boolean(val('bag_adresseerbaar_object_id'));
          const contextOk = contextComplete();
          const productOk = productState.status === 'ok';
          const productBlocked = productState.status === 'blocked';

          grid.replaceChildren(
            card('Locatie', addressOk ? 'Officieel adres bevestigd' : 'Nog niet bevestigd', addressOk ? 'ok' : 'warn', addressOk ? 'PDOK/BAG-adres is als bron vastgelegd.' : 'Bevestig eerst postcode en huisnummer bij Situatie.'),
            card('Productregels', productOk ? 'Gecontroleerd' : productBlocked ? 'Niet akkoord' : 'Controle loopt', productOk ? 'ok' : productBlocked ? 'warn' : 'warn', productState.message),
            card('Windbelasting', 'Technische beoordeling nodig', 'warn', 'Nog geen windbelastingberekening gekoppeld. Daarom geven we hier bewust geen groen vinkje.'),
            card('Glas & veiligheid', 'Technische beoordeling nodig', 'warn', 'Glasopbouw en veiligheidsglas zijn nog niet door een geverifieerde regelengine bepaald.'),
            card('Ventilatie', contextOk ? 'Invoer bekend' : 'Invoer aanvullen', 'warn', contextOk ? 'De ventilatiesituatie is bekend, maar de vereiste ventilatieprestatie wordt nog niet automatisch berekend.' : 'Ruimte, oppervlakte, verdieping en ventilatiesysteem moeten compleet zijn.')
          );

          if (!addressOk || !contextOk || !productOk) {
            result.className = 'ek-control-screen__result is-blocked';
            result.innerHTML = '<strong>Nog niet vrijgegeven voor betrouwbare prijs</strong><span>Alleen aantoonbaar uitgevoerde controles tellen mee. Vul ontbrekende invoer aan en los eventuele productregels op.</span>';
          }
          else {
            result.className = 'ek-control-screen__result is-blocked';
            result.innerHTML = '<strong>Technische invoer klopt, eindcontrole nog niet compleet</strong><span>Productregels zijn gecontroleerd. Windbelasting, glas/veiligheid en ventilatieberekening moeten nog door de BREBO technische engines worden beoordeeld voordat een betrouwbare prijs wordt vrijgegeven.</span>';
          }
          if (priceStatus && contextOk) priceStatus.textContent = 'Technische eindcontrole nog niet compleet';
        };

        const validate = async () => {
          const body = payload();
          if (!body || body.schema_version !== 4) {
            productState = {status: 'pending', message: 'Configuratie is nog niet gereed voor productcontrole.'};
            render();
            return;
          }
          const current = ++sequence;
          productState = {status: 'pending', message: 'Productregels worden server-side gecontroleerd.'};
          render();
          try {
            const response = await fetch('/europakozijn/api/rules', {
              method: 'POST',
              headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
              body: JSON.stringify(body)
            });
            const data = await response.json();
            if (current !== sequence) return;
            if (response.ok && data.valid === true) {
              productState = {status: 'ok', message: `Servercontrole geslaagd${data.ruleset_version ? ` · regels ${data.ruleset_version}` : ''}.`};
            }
            else {
              const first = Array.isArray(data.errors) ? data.errors[0] : null;
              productState = {status: 'blocked', message: first?.message || 'Deze configuratie voldoet niet aan de huidige productregels.'};
            }
          }
          catch (error) {
            if (current !== sequence) return;
            productState = {status: 'pending', message: 'Servercontrole tijdelijk niet beschikbaar; daarom geen groen resultaat.'};
          }
          render();
        };

        const schedule = () => {
          window.clearTimeout(timer);
          timer = window.setTimeout(validate, 180);
        };
        form.addEventListener('input', schedule);
        form.addEventListener('change', schedule);
        root.addEventListener('ek:address-resolved', schedule);
        root.addEventListener('ek:configuration-loaded', schedule);
        root.addEventListener('click', (event) => {
          if (event.target.closest('[data-ek-screen="check"]')) window.setTimeout(validate, 0);
        });
        schedule();
      });
    }
  };
})(Drupal, once);
