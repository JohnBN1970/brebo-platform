(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnUploadPackage = {
    attach(context) {
      once('brebo-europakozijn-upload-package', '[data-ek-upload-panel]', context).forEach((panel) => {
        const input = panel.querySelector('input[name="project_drawing"]');
        if (!input) return;

        input.accept = '.pdf,.png,.jpg,.jpeg,.webp,.zip,application/pdf,image/png,image/jpeg,image/webp,application/zip,application/x-zip-compressed';
        input.multiple = true;

        const hint = panel.querySelector('.ek-intake-entry__hint');
        if (hint) {
          hint.textContent = 'PDF, afbeelding, duidelijke schets of ZIP-projectpakket. U kunt ook meerdere losse bestanden tegelijk kiezen. BREBO behandelt herkenning als voorstel: onzekere informatie blijft onzeker.';
        }

        const status = document.createElement('p');
        status.className = 'ek-intake-entry__hint';
        status.dataset.ekUploadSelection = '';
        status.setAttribute('aria-live', 'polite');
        status.textContent = 'Nog geen projectstukken geselecteerd.';
        input.closest('label')?.insertAdjacentElement('afterend', status);

        input.addEventListener('change', () => {
          const files = Array.from(input.files || []);
          if (!files.length) {
            status.textContent = 'Nog geen projectstukken geselecteerd.';
            return;
          }

          const zipCount = files.filter((file) => /\.zip$/i.test(file.name) || /zip/i.test(file.type || '')).length;
          if (files.length === 1 && zipCount === 1) {
            status.textContent = `ZIP-projectpakket geselecteerd: ${files[0].name}`;
            return;
          }

          const parts = [`${files.length} bestand${files.length === 1 ? '' : 'en'} geselecteerd`];
          if (zipCount) parts.push(`${zipCount} ZIP-projectpakket${zipCount === 1 ? '' : 'ten'}`);
          status.textContent = parts.join(' · ');
        });
      });
    },
  };
})(Drupal, once);
