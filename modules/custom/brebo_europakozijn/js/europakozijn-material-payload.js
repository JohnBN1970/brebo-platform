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

        const sync = () => {
          const material = form.elements.namedItem('material')?.value || 'kunststof';
          if (brandSummary) {
            brandSummary.textContent = labels[material] || labels.kunststof;
            const dt = brandSummary.closest('div')?.querySelector('dt');
            if (dt) dt.textContent = 'Materiaal';
          }

          try {
            const payload = JSON.parse(calibration.value || calibration.textContent || '{}');
            payload.product_selection = payload.product_selection || {};
            payload.product_selection.customer_material = material;
            payload.product_selection.brand_status = 'brebo_to_select';
            const json = JSON.stringify(payload);
            calibration.value = json;
            calibration.textContent = json;
          }
          catch (error) {
            // The canonical configurator will populate the payload on its next render.
          }
        };

        form.addEventListener('input', () => requestAnimationFrame(sync));
        form.addEventListener('change', () => requestAnimationFrame(sync));
        root.addEventListener('ek:configuration-loaded', () => requestAnimationFrame(sync));
        const observer = new MutationObserver(() => requestAnimationFrame(sync));
        observer.observe(calibration, { childList: true, characterData: true, subtree: true });
        requestAnimationFrame(sync);
      });
    },
  };
})(Drupal, once);
