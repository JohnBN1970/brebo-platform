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
        let glassState = {status: 'pending', value: 'Broncontrole loopt', message: 'Glasvelden worden tegen de geverifieerde Kenniscentrum Glas-scope gehouden.'};

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
          const glassOk = glassState.status === 'ok';

          grid.replaceChildren(
            card('Locatie', addressOk ? 'Officieel adres bevestigd' : 'Nog niet bevestigd', addressOk ? 'ok' : 'warn', addressOk ? 'PDOK/BAG-adres is als bron vastgelegd.' : 'Bevestig eerst postcode en huisnummer bij Situatie.'),
            card('Productregels', productOk ? 'Gecontroleerd' : productBlocked ? 'Niet akkoord' : 'Controle loopt', productOk ? 'ok' : 'warn', productState.message),
            card('Windbelasting', 'Technische beoordeling nodig', 'warn', 'De windrekenkern is aanwezig; automatische locatie/hoogte-naar-winddruk koppeling moet nog worden vrijgegeven.'),
            card('Glas & veiligheid', glassState.value, glassOk ? 'ok' : 'warn', glassState.message),
            card('Ventilatie', contextOk ? 'Invoer bekend' : 'Invoer aanvullen', 'warn', contextOk ? 'De ventilatiesituatie is bekend, maar de vereiste ventilatieprestatie wordt nog niet automatisch berekend.' : 'Ruimte, oppervlakte, verdieping en ventilatiesysteem moeten compleet zijn.')
          );

          result.className = 'ek-control-screen__result is-blocked';
          if (!addressOk || !contextOk || !productOk) {
            result.innerHTML = '<strong>Nog niet vrijgegeven voor betrouwbare prijs</strong><span>Alleen aantoonbaar uitgevoerde controles tellen mee. Vul ontbrekende invoer aan en los eventuele productregels op.</span>';
          }
          else if (!glassOk) {
            result.innerHTML = '<strong>Technische invoer klopt, glasadvies nog niet vrijgegeven</strong><span>De glasvelden zijn nu bron- en scopegestuurd gecontroleerd. Een automatische glasopbouw volgt pas zodra de geverifieerde tabeldata en winddruk-koppeling compleet zijn.</span>';
          }
          else {
            result.innerHTML = '<strong>Technische invoer klopt, eindcontrole nog niet compleet</strong><span>Product- en glasregels zijn gecontroleerd. Windbelasting en ventilatieberekening moeten nog compleet zijn voordat een betrouwbare prijs wordt vrijgegeven.</span>';
          }
          if (priceStatus && contextOk) priceStatus.textContent = 'Technische eindcontrole nog niet compleet';
        };

        const validate = async () => {
          const body = payload();
          if (!body || body.schema_version !== 4) {
            productState = {status: 'pending', message: 'Configuratie is nog niet gereed voor productcontrole.'};
            glassState = {status: 'pending', value: 'Nog niet beoordeeld', message: 'Configuratie is nog niet gereed voor glascontrole.'};
            render();
            return;
          }
          const current = ++sequence;
          productState = {status: 'pending', message: 'Productregels worden server-side gecontroleerd.'};
          glassState = {status: 'pending', value: 'Broncontrole loopt', message: 'Glasvelden worden tegen de geverifieerde Kenniscentrum Glas-scope gehouden.'};
          render();

          const [productResult, glassResult] = await Promise.allSettled([
            fetch('/europakozijn/api/rules', {
              method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}, body: JSON.stringify(body)
            }).then(async (response) => ({response, data: await response.json()})),
            fetch('/europakozijn/api/glass-advice', {
              method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}, body: JSON.stringify(body)
            }).then(async (response) => ({response, data: await response.json()}))
          ]);
          if (current !== sequence) return;

          if (productResult.status === 'fulfilled') {
            const {response, data} = productResult.value;
            if (response.ok && data.valid === true) {
              productState = {status: 'ok', message: `Servercontrole geslaagd${data.ruleset_version ? ` · regels ${data.ruleset_version}` : ''}.`};
            }
            else {
              const first = Array.isArray(data.errors) ? data.errors[0] : null;
              productState = {status: 'blocked', message: first?.message || 'Deze configuratie voldoet niet aan de huidige productregels.'};
            }
          }
          else {
            productState = {status: 'pending', message: 'Servercontrole tijdelijk niet beschikbaar; daarom geen groen resultaat.'};
          }

          if (glassResult.status === 'fulfilled') {
            const {response, data} = glassResult.value;
            if (response.ok && data.state === 'source_scope_verified') {
              const count = Array.isArray(data.fields) ? data.fields.length : 0;
              glassState = {
                status: 'pending',
                value: `${count} glasvak${count === 1 ? '' : 'ken'} binnen tabelscope`,
                message: `${data.source?.publisher || 'Kenniscentrum Glas'} · bron ${data.source?.source_version || ''}. Exact glasadvies wacht nog op geverifieerde tabelwaarden en winddruk.`
              };
            }
            else if (response.ok && data.state === 'technical_review') {
              glassState = {status: 'blocked', value: 'Technische beoordeling nodig', message: data.message || 'Minimaal één glasvak valt buiten de automatische tabelscope.'};
            }
            else {
              glassState = {status: 'blocked', value: 'Nog niet beoordeeld', message: data.message || 'Glascontrole kon niet worden uitgevoerd.'};
            }
          }
          else {
            glassState = {status: 'pending', value: 'Broncontrole niet beschikbaar', message: 'Glasadvies-endpoint is tijdelijk niet bereikbaar; daarom geen groen resultaat.'};
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
