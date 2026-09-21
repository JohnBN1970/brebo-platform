(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnPriceIndication = {
    attach(context) {
      once('brebo-europakozijn-price-indication', '[data-europakozijn-configurator]', context).forEach((root) => {
        const calibration = root.querySelector('[data-ek-calibration]');
        const priceStatus = root.querySelector('[data-ek-price-status]');
        if (!calibration || !priceStatus) return;

        let timer = null;
        let sequence = 0;

        const euro = (value) => new Intl.NumberFormat('nl-NL', {
          style: 'currency', currency: 'EUR', maximumFractionDigits: 0
        }).format(Number(value));

        const configuration = () => {
          let payload;
          try { payload = JSON.parse(calibration.value || calibration.textContent || '{}'); }
          catch (error) { return null; }
          if (!payload || payload.schema_version !== 4 || !payload.geometry) return null;

          const fields = Array.isArray(payload.geometry.fields) ? payload.geometry.fields : [];
          if (fields.length !== 1) return null;
          const fn = fields[0]?.function;
          if (!['vast', 'draaikiep'].includes(fn)) return null;

          const brand = payload.product_selection?.brand || null;
          if (brand !== 'aluplast') return null;
          return {
            system: payload.product_selection?.system || 'ideal7000_nl',
            width_mm: Number(payload.geometry.width_mm),
            height_mm: Number(payload.geometry.height_mm),
            fields: 1,
            type: fn === 'draaikiep' ? 'draai-kiep' : 'vast'
          };
        };

        const render = (indication) => {
          if (!indication || indication.status !== 'indicative') {
            priceStatus.textContent = 'Prijsindicatie volgt na controle';
            root.dataset.ekPriceIndication = JSON.stringify(indication || {status: 'temporarily_unavailable'});
            return;
          }
          const expected = Number(indication.expected);
          const low = Number(indication.low);
          const high = Number(indication.high);
          if (![expected, low, high].every((value) => Number.isFinite(value) && value > 0)) {
            priceStatus.textContent = 'Prijsindicatie volgt na controle';
            return;
          }
          priceStatus.textContent = low === high
            ? euro(expected)
            : `${euro(low)} – ${euro(high)}`;
          root.dataset.ekPriceIndication = JSON.stringify(indication);
          root.dispatchEvent(new CustomEvent('ek:price-indication', {bubbles: true, detail: indication}));
        };

        const requestPrice = async () => {
          const current = ++sequence;
          const config = configuration();
          if (!config || !Number.isFinite(config.width_mm) || !Number.isFinite(config.height_mm)) {
            render({status: 'insufficient_calibration'});
            return;
          }
          priceStatus.textContent = 'Prijsindicatie wordt berekend…';
          try {
            const response = await fetch('/europakozijn/api/price-indication', {
              method: 'POST',
              headers: {'Content-Type': 'application/json', Accept: 'application/json'},
              body: JSON.stringify({configuration: config})
            });
            const data = await response.json();
            if (current !== sequence) return;
            if (!response.ok || data.status !== 'ok') throw new Error('price_unavailable');
            render(data.indication);
          }
          catch (error) {
            if (current !== sequence) return;
            render({status: 'temporarily_unavailable'});
          }
        };

        const schedule = () => {
          window.clearTimeout(timer);
          timer = window.setTimeout(requestPrice, 250);
        };
        root.addEventListener('input', schedule);
        root.addEventListener('change', schedule);
        root.addEventListener('ek:configuration-loaded', schedule);
        root.addEventListener('ek:configuration-rendered', schedule);
        schedule();
      });
    }
  };
})(Drupal, once);
