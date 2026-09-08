(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnVisualPicker = {
    attach(context) {
      once('brebo-europakozijn-visual-picker', '[data-europakozijn-configurator]', context).forEach((root) => {
        const form = root.querySelector('[data-ek-form]');
        const functionPicker = root.querySelector('[data-ek-function-picker]');
        const drawing = root.querySelector('[data-ek-drawing]');
        const calibration = root.querySelector('[data-ek-calibration]');
        if (!form || !functionPicker || !drawing || !calibration) return;

        const extraTypes = [
          ['klep', 'Klepraam'],
          ['stulp', 'Stulpstel'],
          ['schuifpui', 'Schuifpui'],
        ];
        extraTypes.forEach(([value, label]) => {
          if (functionPicker.querySelector(`[data-ek-function="${value}"]`)) return;
          const button = document.createElement('button');
          button.type = 'button';
          button.dataset.ekFunction = value;
          button.textContent = label;
          functionPicker.appendChild(button);
        });

        const section = document.createElement('section');
        section.className = 'ek-step ek-step--visual-types';
        section.innerHTML = `
          <div class="ek-step__number">0</div>
          <div class="ek-step__body">
            <h2>Kies wat u wilt maken</h2>
            <p class="ek-step__intro">Klik op een type. Het geselecteerde vak in de tekening verandert direct mee.</p>
            <div class="ek-type-grid" data-ek-type-grid>
              ${card('vast','Vast raam',icon('vast'))}
              ${card('draaikiep','Draai-kiep',icon('draaikiep'))}
              ${card('klep','Klepraam',icon('klep'))}
              ${card('deur','Enkele deur',icon('deur'))}
              ${card('stulp','Stulpstel',icon('stulp'))}
              ${card('schuifpui','Schuifpui',icon('schuifpui'))}
            </div>
          </div>`;
        form.prepend(section);

        const grid = section.querySelector('[data-ek-type-grid]');
        grid.querySelectorAll('[data-ek-type]').forEach((button) => {
          button.addEventListener('click', () => {
            const target = functionPicker.querySelector(`[data-ek-function="${button.dataset.ekType}"]`);
            if (target) target.click();
            syncCards(button.dataset.ekType);
          });
        });

        functionPicker.addEventListener('click', (event) => {
          const button = event.target.closest('[data-ek-function]');
          if (button) syncCards(button.dataset.ekFunction);
        });

        const observer = new MutationObserver(() => {
          window.requestAnimationFrame(renderSpecialOperations);
          const active = functionPicker.querySelector('.is-active[data-ek-function]');
          if (active) syncCards(active.dataset.ekFunction);
        });
        observer.observe(drawing, {childList: true, subtree: true});
        renderSpecialOperations();

        function syncCards(type) {
          grid.querySelectorAll('[data-ek-type]').forEach((cardButton) => {
            const active = cardButton.dataset.ekType === type;
            cardButton.classList.toggle('is-active', active);
            cardButton.setAttribute('aria-pressed', active ? 'true' : 'false');
          });
        }

        function renderSpecialOperations() {
          drawing.querySelector('[data-ek-special-operations]')?.remove();
          let payload;
          try { payload = JSON.parse(calibration.value || calibration.textContent || '{}'); }
          catch (error) { return; }
          const fields = payload?.geometry?.fields || [];
          if (!fields.length) return;
          const ns = 'http://www.w3.org/2000/svg';
          const group = document.createElementNS(ns, 'g');
          group.setAttribute('data-ek-special-operations', '');
          group.setAttribute('class', 'ek-special-operations');

          fields.forEach((fieldData) => {
            if (!['klep','stulp','schuifpui'].includes(fieldData.function)) return;
            const field = drawing.querySelector(`[data-field-key="${fieldData.id}"]`);
            if (!field) return;
            const x = Number(field.getAttribute('x'));
            const y = Number(field.getAttribute('y'));
            const w = Number(field.getAttribute('width'));
            const h = Number(field.getAttribute('height'));
            const inset = Math.min(14, Math.max(6, w * .05));
            const line = (x1,y1,x2,y2,klass='ek-special-operation__line') => {
              const el = document.createElementNS(ns,'line');
              [['x1',x1],['y1',y1],['x2',x2],['y2',y2],['class',klass]].forEach(([k,v])=>el.setAttribute(k,v));
              group.appendChild(el);
            };
            if (fieldData.function === 'klep') {
              line(x+inset,y+inset,x+w/2,y+h-inset);
              line(x+w-inset,y+inset,x+w/2,y+h-inset);
            }
            if (fieldData.function === 'stulp') {
              const mid = x+w/2;
              line(mid,y+inset,mid,y+h-inset,'ek-special-operation__center');
              line(x+inset,y+inset,mid,y+h/2);
              line(x+inset,y+h-inset,mid,y+h/2);
              line(x+w-inset,y+inset,mid,y+h/2);
              line(x+w-inset,y+h-inset,mid,y+h/2);
            }
            if (fieldData.function === 'schuifpui') {
              const mid = x+w/2;
              line(mid,y+inset,mid,y+h-inset,'ek-special-operation__center');
              const arrow = document.createElementNS(ns,'path');
              arrow.setAttribute('d',`M ${x+w*.38} ${y+h*.5} H ${x+w*.66} M ${x+w*.66} ${y+h*.5} l -12 -8 M ${x+w*.66} ${y+h*.5} l -12 8`);
              arrow.setAttribute('class','ek-special-operation__arrow');
              group.appendChild(arrow);
            }
          });
          drawing.appendChild(group);
        }

        function card(value, label, svg) {
          return `<button type="button" class="ek-type-card${value==='vast'?' is-active':''}" data-ek-type="${value}" aria-pressed="${value==='vast'?'true':'false'}"><span class="ek-type-card__visual" aria-hidden="true">${svg}</span><span>${label}</span></button>`;
        }

        function icon(type) {
          const base = '<rect x="10" y="8" width="60" height="64" rx="2" class="f"/><rect x="17" y="15" width="46" height="50" class="g"/>';
          if (type === 'vast') return `<svg viewBox="0 0 80 80">${base}</svg>`;
          if (type === 'draaikiep') return `<svg viewBox="0 0 80 80">${base}<path d="M18 16 L62 40 L18 64 M18 64 L40 16 L62 64" class="o"/></svg>`;
          if (type === 'klep') return `<svg viewBox="0 0 80 80">${base}<path d="M18 16 L40 64 L62 16" class="o"/></svg>`;
          if (type === 'deur') return `<svg viewBox="0 0 80 80">${base}<path d="M18 16 L62 40 L18 64" class="o"/><circle cx="58" cy="40" r="2" class="h"/></svg>`;
          if (type === 'stulp') return `<svg viewBox="0 0 80 80">${base}<path d="M40 15 V65 M18 16 L40 40 L18 64 M62 16 L40 40 L62 64" class="o"/></svg>`;
          return `<svg viewBox="0 0 80 80">${base}<path d="M40 15 V65 M28 40 H54 M54 40 l-8-6 M54 40 l-8 6" class="o"/></svg>`;
        }
      });
    }
  };
})(Drupal, once);
