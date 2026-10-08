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
        if (!form || !calibration || !panel || !grid || !result) return;

        let sequence = 0;
        let timer = null;
        let productState = {status: 'pending', message: 'Productregels worden server-side gecontroleerd.'};
        let glassState = {status: 'pending', value: 'Broncontrole loopt', message: 'Glasvelden worden tegen de geverifieerde Kenniscentrum Glas-scope gehouden.'};
        let ventilationState = {status: 'pending', value: 'Beoordeling nodig', message: 'Ventilatie wordt server-side beoordeeld zodra voldoende geverifieerde informatie beschikbaar is.'};
        let windState = {status: 'pending', value: 'Beoordeling nodig', message: 'Windbelasting wordt server-side beoordeeld zodra traceerbare norminputs beschikbaar zijn.'};

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
          const ventilationOk = ventilationState.status === 'ok';
          const windOk = windState.status === 'ok';

          grid.replaceChildren(
            card('Locatie', addressOk ? 'Officieel adres bevestigd' : 'Nog niet bevestigd', addressOk ? 'ok' : 'warn', addressOk ? 'PDOK/BAG-adres is als bron vastgelegd.' : 'Bevestig eerst postcode en huisnummer bij Situatie.'),
            card('Productregels', productOk ? 'Gecontroleerd' : productBlocked ? 'Niet akkoord' : 'Controle loopt', productOk ? 'ok' : 'warn', productState.message),
            card('Windbelasting', windState.value, windOk ? 'ok' : 'warn', windState.message),
            card('Glas & veiligheid', glassState.value, glassOk ? 'ok' : 'warn', glassState.message),
            card('Ventilatie', contextOk ? ventilationState.value : 'Invoer aanvullen', contextOk && ventilationOk ? 'ok' : 'warn', contextOk ? ventilationState.message : 'Ruimte, oppervlakte, verdieping en ventilatiesysteem moeten compleet zijn.')
          );

          result.className = 'ek-control-screen__result is-blocked';
          if (!addressOk || !contextOk || !productOk) {
            result.innerHTML = '<strong>Nog niet vrijgegeven voor betrouwbare prijs</strong><span>Alleen aantoonbaar uitgevoerde controles tellen mee. Vul ontbrekende invoer aan en los eventuele productregels op.</span>';
          }
          else if (!glassOk) {
            result.innerHTML = '<strong>Technische invoer klopt, glasadvies nog niet vrijgegeven</strong><span>De glasvelden zijn nu bron- en scopegestuurd gecontroleerd. Een automatische glasopbouw volgt pas zodra de geverifieerde tabeldata en winddruk-koppeling compleet zijn.</span>';
          }
          else {
            result.innerHTML = '<strong>Technische invoer klopt, eindcontrole nog niet compleet</strong><span>Product- en glasregels zijn gecontroleerd. Windbelasting en ventilatiebeoordeling moeten nog compleet zijn voordat de technische eindcontrole vrijgegeven kan worden.</span>';
          }
        };

        const validate = async () => {
          const body = payload();
          if (!body || body.schema_version !== 4) {
            productState = {status: 'pending', message: 'Configuratie is nog niet gereed voor productcontrole.'};
            glassState = {status: 'pending', value: 'Nog niet beoordeeld', message: 'Configuratie is nog niet gereed voor glascontrole.'};
            ventilationState = {status: 'pending', value: 'Beoordeling nodig', message: 'Configuratie is nog niet gereed voor ventilatiebeoordeling.'};
            windState = {status: 'pending', value: 'Beoordeling nodig', message: 'Configuratie is nog niet gereed voor windbeoordeling.'};
            render();
            return;
          }
          const current = ++sequence;
          productState = {status: 'pending', message: 'Productregels worden server-side gecontroleerd.'};
          glassState = {status: 'pending', value: 'Broncontrole loopt', message: 'Glasvelden worden tegen de geverifieerde Kenniscentrum Glas-scope gehouden.'};
          ventilationState = {status: 'pending', value: 'Beoordeling loopt', message: 'Ventilatie-eis wordt server-side beoordeeld.'};
          windState = {status: 'pending', value: 'Beoordeling loopt', message: 'Windbelasting wordt server-side beoordeeld.'};
          render();

          const windPayload = body.wind_context && typeof body.wind_context === 'object' ? body.wind_context : {};
          const [productResult, glassResult, ventilationResult, windResult] = await Promise.allSettled([
            fetch('/europakozijn/api/rules', {
              method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}, body: JSON.stringify(body)
            }).then(async (response) => ({response, data: await response.json()})),
            fetch('/europakozijn/api/glass-advice', {
              method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}, body: JSON.stringify(body)
            }).then(async (response) => ({response, data: await response.json()})),
            fetch('/europakozijn/api/ventilation', {
              method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}, body: JSON.stringify(body)
            }).then(async (response) => ({response, data: await response.json()})),
            fetch('/europakozijn/api/wind', {
              method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}, body: JSON.stringify(windPayload)
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

          if (ventilationResult.status === 'fulfilled') {
            const {response, data} = ventilationResult.value;
            if (response.ok && data.frame_supply_required === false) {
              ventilationState = {status: 'ok', value: 'Geen extra kozijnventilatie vastgesteld', message: data.message || 'De ventilatiebeoordeling is afgerond.'};
            }
            else if (response.ok && data.frame_supply_required === true) {
              ventilationState = {status: 'ok', value: 'Ventilatie via kozijn vereist', message: data.message || 'Ventilatie via het kozijn is geverifieerd vereist.'};
            }
            else if (response.ok) {
              ventilationState = {status: 'pending', value: 'Beoordeling nodig', message: data.message || 'Nog niet genoeg geverifieerde informatie om de ventilatie-eis vast te stellen.'};
            }
            else {
              ventilationState = {status: 'blocked', value: 'Beoordeling niet mogelijk', message: data.message || 'Ventilatiebeoordeling kon niet worden uitgevoerd.'};
            }
          }
          else {
            ventilationState = {status: 'pending', value: 'Beoordeling niet beschikbaar', message: 'Ventilatie-endpoint is tijdelijk niet bereikbaar; daarom geen groen resultaat.'};
          }

          if (windResult.status === 'fulfilled') {
            const {response, data} = windResult.value;
            if (response.ok && data.state === 'passed' && data.verified === true) {
              windState = {
                status: 'ok',
                value: 'Geverifieerd',
                message: `Ontwerpwinddruk ${data.design_pressure_kpa} kPa · ${data.standard_reference} · bron ${data.calculation_reference}.`
              };
            }
            else if (data.state === 'needs_input') {
              const missing = Array.isArray(data.missing) ? data.missing.length : 0;
              windState = {
                status: 'pending',
                value: 'Brongegevens nodig',
                message: missing
                  ? `Nog ${missing} traceerbare windgegeven${missing === 1 ? '' : 's'} nodig voordat de windbelasting kan worden vrijgegeven.`
                  : (data.message || 'Traceerbare windgegevens ontbreken nog.')
              };
            }
            else {
              windState = {
                status: 'blocked',
                value: 'Nog niet geverifieerd',
                message: data.message || (Array.isArray(data.issues) ? data.issues[0] : null) || 'De windbelasting is nog niet vrijgegeven.'
              };
            }
          }
          else {
            windState = {status: 'pending', value: 'Beoordeling niet beschikbaar', message: 'Wind-endpoint is tijdelijk niet bereikbaar; daarom geen groen resultaat.'};
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
