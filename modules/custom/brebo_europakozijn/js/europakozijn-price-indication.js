(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnPriceIndication = {
    attach(context) {
      once('brebo-europakozijn-price-indication', '[data-europakozijn-configurator]', context).forEach((root) => {
        const calibration = root.querySelector('[data-ek-calibration]');
        const status = root.querySelector('[data-ek-price-status]');
        if (!calibration || !status) return;

        let timer = null;
        let requestSequence = 0;

        const euro = (value) => new Intl.NumberFormat('nl-NL', {
          style: 'currency',
          currency: 'EUR',
          maximumFractionDigits: 0,
        }).format(Number(value));

        const readPayload = () => {
          try {
            return JSON.parse(calibration.value || calibration.textContent || '{}');
          }
          catch (error) {
            return null;
          }
        };

        const eligibleConfiguration = (payload) => {
          if (!payload || payload.schema_version !== 4 || !payload.geometry) return null;
          const fields = Array.isArray(payload.geometry.fields) ? payload.geometry.fields : [];
          if (fields.length !== 1) return null;

          const field = fields[0] || {};
          if (!['vast', 'draaikiep'].includes(field.function)) return null;

          const product = payload.product_selection || {};
          const finish = payload.finish || {};

          if ((product.brand || 'aluplast') !== 'aluplast') return null;
          if ((product.joint_type || 'normal') !== 'normal') return null;
          if ((product.rebate_type || 'with_rebate') !== 'with_rebate') return null;
          if ((finish.glass || 'HR++') !== 'HR++') return null;
          if ((finish.colour || 'RAL 7016') !== 'RAL 7016') return null;

          return {
            system: product.system || 'ideal7000_nl',
            width_mm: Number(payload.geometry.width_mm),
            height_mm: Number(payload.geometry.height_mm),
            fields: 1,
            type: field.function === 'draaikiep' ? 'draai-kiep' : 'vast',
          };
        };

        const showUnavailable = (message = 'Prijsindicatie volgt na controle') => {
          status.textContent = message;
          delete root.dataset.ekPriceIndication;
        };

        const render = (indication) => {
          if (!indication || indication.status !== 'indicative') {
            showUnavailable();
            return;
          }
          const low = Number(indication.low);
          const high = Number(indication.high);
          const expected = Number(indication.expected);
          if (![low, high, expected].every(Number.isFinite) || low <= 0 || high <= 0 || expected <= 0) {
            showUnavailable();
            return;
          }

          status.textContent = low === high
            ? euro(expected)
            : euro(low) + ' - ' + euro(high);
          root.dataset.ekPriceIndication = JSON.stringify(indication);
        };

        const requestPrice = async () => {
          const payload = readPayload();
          const configuration = eligibleConfiguration(payload);
          if (!configuration) {
            showUnavailable('Prijs volgt na technische controle');
            return;
          }

          const sequence = ++requestSequence;
          status.textContent = 'Prijsindicatie wordt berekend...';

          try {
            const response = await fetch('/europakozijn/api/price-indication', {
              method: 'POST',
              credentials: 'same-origin',
              headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
              body: JSON.stringify({configuration}),
            });
            const data = await response.json();
            if (sequence !== requestSequence) return;
            if (!response.ok || data?.status !== 'ok') {
              showUnavailable();
              return;
            }
            render(data.indication);
          }
          catch (error) {
            if (sequence === requestSequence) showUnavailable();
          }
        };

        const schedule = () => {
          window.clearTimeout(timer);
          timer = window.setTimeout(requestPrice, 350);
        };

        root.addEventListener('ek:configuration-rendered', schedule);
        root.querySelector('[data-ek-form]')?.addEventListener('change', schedule);
        schedule();
      });
    },
  };
})(Drupal, once);
