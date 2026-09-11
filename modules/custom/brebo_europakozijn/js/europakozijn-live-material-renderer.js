(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnLiveMaterialRenderer = {
    attach(context) {
      once('brebo-europakozijn-live-material-renderer', '[data-europakozijn-configurator]', context).forEach((root) => {
        const form = root.querySelector('[data-ek-form]');
        const drawing = root.querySelector('[data-ek-drawing]');
        const brandPicker = root.querySelector('[data-ek-brand-picker]');
        const brandInput = root.querySelector('[data-ek-brand]');
        if (!form || !drawing) return;

        const materialLabels = {
          kunststof: 'Kunststof',
          aluminium: 'Aluminium',
          hout: 'Hout',
        };
        const colourMap = {
          'RAL 7016': '#383e42',
          'RAL 9010': '#f1efe7',
          'RAL 9005': '#151515',
        };

        let materialInput = form.elements.namedItem('material');
        if (!materialInput) {
          materialInput = document.createElement('input');
          materialInput.type = 'hidden';
          materialInput.name = 'material';
          materialInput.value = 'kunststof';
          form.appendChild(materialInput);
        }

        // Replace the customer-facing brand step with the actual decision the
        // visitor can make. Brand/system remain an internal BREBO selection.
        if (brandPicker) {
          const step = brandPicker.closest('.ek-step');
          if (step) {
            const heading = step.querySelector('h2');
            const intro = step.querySelector('.ek-step__intro');
            if (heading) heading.textContent = 'Materiaal';
            if (intro) intro.textContent = 'Kies de uitstraling en basis van het kozijn. BREBO bepaalt daarna het passende merk en profielsysteem.';
            brandPicker.hidden = true;

            const picker = document.createElement('div');
            picker.className = 'ek-material-picker';
            picker.setAttribute('role', 'group');
            picker.setAttribute('aria-label', 'Kies materiaal');
            picker.innerHTML = `
              <button type="button" class="ek-material-card is-active" data-ek-material="kunststof" aria-pressed="true">
                <span class="ek-material-card__visual ek-material-card__visual--kunststof"><span></span></span>
                <strong>Kunststof</strong><small>Onderhoudsarm en veelzijdig</small>
              </button>
              <button type="button" class="ek-material-card" data-ek-material="aluminium" aria-pressed="false">
                <span class="ek-material-card__visual ek-material-card__visual--aluminium"><span></span></span>
                <strong>Aluminium</strong><small>Slank, sterk en strak</small>
              </button>
              <button type="button" class="ek-material-card" data-ek-material="hout" aria-pressed="false">
                <span class="ek-material-card__visual ek-material-card__visual--hout"><span></span></span>
                <strong>Hout</strong><small>Natuurlijk en karaktervol</small>
              </button>`;
            brandPicker.insertAdjacentElement('afterend', picker);

            picker.addEventListener('click', (event) => {
              const button = event.target.closest('[data-ek-material]');
              if (!button) return;
              materialInput.value = button.dataset.ekMaterial;
              picker.querySelectorAll('[data-ek-material]').forEach((item) => {
                const active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-pressed', active ? 'true' : 'false');
              });
              materialInput.dispatchEvent(new Event('change', { bubbles: true }));
              updateMaterialRules();
              applyRenderer();
            });
          }
        }

        if (brandInput) {
          brandInput.closest('label')?.setAttribute('hidden', 'hidden');
        }

        const jointSelect = form.elements.namedItem('joint_type');
        const jointLabel = jointSelect?.closest('label');
        let jointPicker = null;
        if (jointSelect && jointLabel) {
          jointLabel.hidden = true;
          jointPicker = document.createElement('div');
          jointPicker.className = 'ek-joint-picker';
          jointPicker.innerHTML = `
            <span class="ek-joint-picker__title">Verbinding</span>
            <div class="ek-joint-picker__grid">
              <button type="button" class="ek-joint-card is-active" data-ek-joint="normal" aria-pressed="true">
                <span class="ek-joint-preview ek-joint-preview--normal"></span>
                <strong>Normale verbinding</strong><small>Gelaste verstekhoek</small>
              </button>
              <button type="button" class="ek-joint-card" data-ek-joint="hvl" aria-pressed="false">
                <span class="ek-joint-preview ek-joint-preview--hvl"></span>
                <strong>HVL-verbinding</strong><small>Rechte houtlookverbinding</small>
              </button>
            </div>`;
          jointLabel.insertAdjacentElement('afterend', jointPicker);
          jointPicker.addEventListener('click', (event) => {
            const button = event.target.closest('[data-ek-joint]');
            if (!button || button.disabled) return;
            jointSelect.value = button.dataset.ekJoint;
            jointPicker.querySelectorAll('[data-ek-joint]').forEach((item) => {
              const active = item === button;
              item.classList.toggle('is-active', active);
              item.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            jointSelect.dispatchEvent(new Event('change', { bubbles: true }));
            applyRenderer();
          });
        }

        const ensureDefs = () => {
          let defs = drawing.querySelector('defs[data-ek-renderer-defs]');
          if (defs) return defs;
          defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
          defs.dataset.ekRendererDefs = '';
          defs.innerHTML = `
            <linearGradient id="ek-profile-plastic" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0" stop-color="var(--ek-profile-light)"/>
              <stop offset="0.48" stop-color="var(--ek-profile-base)"/>
              <stop offset="1" stop-color="var(--ek-profile-dark)"/>
            </linearGradient>
            <linearGradient id="ek-profile-aluminium" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0" stop-color="var(--ek-profile-light)"/>
              <stop offset="0.35" stop-color="var(--ek-profile-base)"/>
              <stop offset="1" stop-color="var(--ek-profile-dark)"/>
            </linearGradient>
            <pattern id="ek-profile-wood" width="20" height="20" patternUnits="userSpaceOnUse">
              <rect width="20" height="20" fill="var(--ek-profile-base)"/>
              <path d="M0 5 C5 2 12 8 20 4 M0 12 C7 8 12 15 20 11 M2 18 C8 15 15 20 20 17" fill="none" stroke="var(--ek-profile-dark)" stroke-opacity=".24" stroke-width="1"/>
            </pattern>
            <linearGradient id="ek-glass" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0" stop-color="#eefaff" stop-opacity=".92"/>
              <stop offset=".45" stop-color="#cfe9f2" stop-opacity=".76"/>
              <stop offset="1" stop-color="#a8d2df" stop-opacity=".68"/>
            </linearGradient>
            <filter id="ek-profile-shadow" x="-30%" y="-30%" width="160%" height="160%">
              <feDropShadow dx="0" dy="4" stdDeviation="4" flood-color="#000" flood-opacity=".22"/>
            </filter>`;
          drawing.prepend(defs);
          return defs;
        };

        const shade = (hex, amount) => {
          const value = hex.replace('#', '');
          const number = parseInt(value, 16);
          const clamp = (v) => Math.max(0, Math.min(255, v));
          const r = clamp((number >> 16) + amount);
          const g = clamp(((number >> 8) & 255) + amount);
          const b = clamp((number & 255) + amount);
          return `#${[r, g, b].map((v) => v.toString(16).padStart(2, '0')).join('')}`;
        };

        const addJointLines = (frame, jointType) => {
          drawing.querySelectorAll('[data-ek-joint-line]').forEach((node) => node.remove());
          if (!frame) return;
          const x = Number(frame.getAttribute('x'));
          const y = Number(frame.getAttribute('y'));
          const w = Number(frame.getAttribute('width'));
          const h = Number(frame.getAttribute('height'));
          const d = Math.min(28, Math.max(16, Math.min(w, h) * 0.055));
          const lines = jointType === 'hvl'
            ? [[x + d, y, x + d, y + d], [x + w - d, y, x + w - d, y + d], [x + d, y + h - d, x + d, y + h], [x + w - d, y + h - d, x + w - d, y + h]]
            : [[x, y, x + d, y + d], [x + w, y, x + w - d, y + d], [x, y + h, x + d, y + h - d], [x + w, y + h, x + w - d, y + h - d]];
          lines.forEach(([x1, y1, x2, y2]) => {
            const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.dataset.ekJointLine = '';
            line.setAttribute('x1', x1);
            line.setAttribute('y1', y1);
            line.setAttribute('x2', x2);
            line.setAttribute('y2', y2);
            line.setAttribute('class', 'ek-drawing__joint-line');
            drawing.appendChild(line);
          });
        };

        const updateMaterialRules = () => {
          const material = materialInput.value || 'kunststof';
          if (!jointPicker || !jointSelect) return;
          const hvl = jointPicker.querySelector('[data-ek-joint="hvl"]');
          const supportsHvl = material === 'kunststof';
          if (hvl) {
            hvl.disabled = !supportsHvl;
            hvl.classList.toggle('is-disabled', !supportsHvl);
          }
          if (!supportsHvl && jointSelect.value === 'hvl') {
            jointSelect.value = 'normal';
            jointPicker.querySelectorAll('[data-ek-joint]').forEach((item) => {
              const active = item.dataset.ekJoint === 'normal';
              item.classList.toggle('is-active', active);
              item.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            jointSelect.dispatchEvent(new Event('change', { bubbles: true }));
          }
        };

        const applyRenderer = () => {
          const material = materialInput.value || 'kunststof';
          const colourValue = form.elements.namedItem('colour')?.value || 'RAL 7016';
          const base = colourMap[colourValue] || '#383e42';
          const jointType = form.elements.namedItem('joint_type')?.value || 'normal';

          drawing.dataset.ekMaterial = material;
          drawing.dataset.ekColour = colourValue;
          drawing.dataset.ekJoint = jointType;
          drawing.style.setProperty('--ek-profile-base', base);
          drawing.style.setProperty('--ek-profile-light', shade(base, material === 'hout' ? 28 : 24));
          drawing.style.setProperty('--ek-profile-dark', shade(base, material === 'hout' ? -38 : -32));

          ensureDefs();
          const fill = material === 'hout' ? 'url(#ek-profile-wood)' : material === 'aluminium' ? 'url(#ek-profile-aluminium)' : 'url(#ek-profile-plastic)';

          drawing.querySelectorAll('.ek-drawing__field').forEach((field) => {
            field.setAttribute('fill', 'url(#ek-glass)');
            field.setAttribute('stroke', 'rgba(44, 79, 91, .42)');
            field.setAttribute('stroke-width', '1.5');
          });

          const frame = drawing.querySelector('.ek-drawing__frame');
          if (frame) {
            frame.setAttribute('fill', 'none');
            frame.setAttribute('stroke', fill);
            frame.setAttribute('stroke-width', material === 'aluminium' ? '28' : '38');
            frame.setAttribute('stroke-linejoin', 'miter');
            frame.setAttribute('filter', 'url(#ek-profile-shadow)');
          }
          drawing.querySelectorAll('.ek-drawing__mullion').forEach((profile) => {
            profile.setAttribute('stroke', fill);
            profile.setAttribute('stroke-width', material === 'aluminium' ? '22' : '30');
            profile.setAttribute('stroke-linecap', 'butt');
          });
          drawing.querySelectorAll('.ek-drawing__operation').forEach((line) => {
            line.setAttribute('stroke', material === 'hout' ? '#5c3c25' : '#51636b');
            line.setAttribute('stroke-width', '2');
            line.setAttribute('stroke-opacity', '.65');
          });
          addJointLines(frame, jointType);

          const materialSummary = root.querySelector('[data-ek-material-summary]');
          if (materialSummary) materialSummary.textContent = materialLabels[material];
        };

        // Add material summary without changing the existing geometry payload yet.
        const summary = root.querySelector('.ek-configurator__summary');
        if (summary && !summary.querySelector('[data-ek-material-summary]')) {
          const item = document.createElement('div');
          item.innerHTML = '<dt>Materiaal</dt><dd data-ek-material-summary>Kunststof</dd>';
          summary.prepend(item);
        }

        form.addEventListener('input', () => requestAnimationFrame(applyRenderer));
        form.addEventListener('change', () => requestAnimationFrame(applyRenderer));
        root.addEventListener('ek:configuration-loaded', () => requestAnimationFrame(applyRenderer));

        const observer = new MutationObserver(() => requestAnimationFrame(applyRenderer));
        observer.observe(drawing, { childList: true });

        updateMaterialRules();
        requestAnimationFrame(applyRenderer);
      });
    },
  };
})(Drupal, once);
