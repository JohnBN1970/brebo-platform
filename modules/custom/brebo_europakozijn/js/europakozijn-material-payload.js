(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnMaterialPayload = {
    attach(context) {
      once('brebo-europakozijn-material-payload', '[data-europakozijn-configurator]', context).forEach((root) => {
        const form = root.querySelector('[data-ek-form]');
        const calibration = root.querySelector('[data-ek-calibration]');
        const brandSummary = root.querySelector('[data-ek-brand-summary]');
        if (!form || !calibration) return;

        const labels = { kunststof: 'Kunststof', aluminium: 'Aluminium', hout: 'Hout' };
        let scheduled = false;

        const sync = () => {
          scheduled = false;
          const material = form.elements.namedItem('material')?.value || 'kunststof';
          if (brandSummary) {
            brandSummary.textContent = labels[material] || labels.kunststof;
            const dt = brandSummary.closest('div')?.querySelector('dt');
            if (dt) dt.textContent = 'Materiaal';
          }

          try {
            const current = calibration.value || calibration.textContent || '{}';
            const payload = JSON.parse(current);
            payload.product_selection = payload.product_selection || {};
            payload.product_selection.customer_material = material;
            payload.product_selection.brand_status = 'brebo_to_select';
            const json = JSON.stringify(payload);
            if (json !== current) {
              calibration.value = json;
              calibration.textContent = json;
            }
          }
          catch (error) {
            // The canonical configurator will populate the payload on its next render.
          }
        };

        const scheduleSync = () => {
          if (scheduled) return;
          scheduled = true;
          window.requestAnimationFrame(sync);
        };

        form.addEventListener('input', scheduleSync);
        form.addEventListener('change', scheduleSync);
        root.addEventListener('ek:configuration-loaded', scheduleSync);
        scheduleSync();
      });
    },
  };
})(Drupal, once);
