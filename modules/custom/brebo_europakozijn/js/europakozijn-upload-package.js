(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnUploadPackage = {
    attach(context) {
      once('brebo-europakozijn-upload-package', '[data-ek-upload-panel]', context).forEach((panel) => {
        const input = panel.querySelector('input[name="project_drawing"]');
        if (!input) return;

        input.accept = '.pdf,.png,.jpg,.jpeg,.webp,.zip,.xls,.xlsx,.doc,.docx,application/pdf,image/png,image/jpeg,image/webp,application/zip,application/x-zip-compressed,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        input.multiple = true;

        const hint = panel.querySelector('.ek-intake-entry__hint');
        if (hint) hint.textContent = 'PDF, afbeelding, schets, Excel-, Word-bestand of ZIP-projectpakket. U kunt ook meerdere losse bestanden tegelijk kiezen. BREBO behandelt herkenning als voorstel: onzekere informatie blijft onzeker.';

        const status = document.createElement('p');
        status.className = 'ek-intake-entry__hint';
        status.dataset.ekUploadSelection = '';
        status.setAttribute('aria-live', 'polite');
        status.textContent = 'Nog geen projectstukken geselecteerd.';
        input.closest('label')?.insertAdjacentElement('afterend', status);

        const uploadButton = document.createElement('button');
        uploadButton.type = 'button';
        uploadButton.className = 'ek-configurator__cta';
        uploadButton.textContent = 'Projectstukken uploaden en herkennen';
        uploadButton.disabled = true;
        status.insertAdjacentElement('afterend', uploadButton);

        input.addEventListener('change', () => {
          const files = Array.from(input.files || []);
          uploadButton.disabled = !files.length;
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

        uploadButton.addEventListener('click', async () => {
          const files = Array.from(input.files || []);
          if (!files.length) return;
          const data = new FormData();
          files.forEach((file) => data.append('project_files[]', file, file.name));
          uploadButton.disabled = true;
          uploadButton.textContent = 'Uploaden en herkennen…';
          status.textContent = 'BREBO ontvangt de projectstukken en probeert herkenbare kozijninformatie als voorstel uit te lezen.';
          try {
            const response = await fetch('/europakozijn/api/project-upload', {
              method: 'POST',
              body: data,
              credentials: 'same-origin',
              headers: { 'Accept': 'application/json' },
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok || !result.ok) throw new Error(result.message || 'Upload of herkenning mislukt.');
            panel.dataset.ekIntakeId = result.intake_id || '';
            const count = Array.isArray(result.files) ? result.files.length : files.length;
            status.textContent = `${count} projectstuk${count === 1 ? '' : 'ken'} ontvangen. De herkende informatie wordt geopend.`;
            uploadButton.textContent = 'Herkende informatie openen';
            if (result.result_url) {
              window.location.assign(result.result_url);
              return;
            }
            uploadButton.disabled = false;
          }
          catch (error) {
            status.textContent = error.message || 'Upload mislukt. Probeer het opnieuw.';
            uploadButton.disabled = false;
            uploadButton.textContent = 'Opnieuw uploaden';
          }
        });
      });
    },
  };
})(Drupal, once);
