(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnConfigurator = {
    attach(context) {
      once('brebo-europakozijn', '[data-europakozijn-configurator]', context).forEach((root) => {
        const form = root.querySelector('[data-ek-form]');
        const drawing = root.querySelector('[data-ek-drawing]');
        const ns = 'http://www.w3.org/2000/svg';

        const text = (selector, value) => {
          root.querySelector(selector).textContent = value;
        };

        const line = (x1, y1, x2, y2, className) => {
          const el = document.createElementNS(ns, 'line');
          el.setAttribute('x1', x1); el.setAttribute('y1', y1);
          el.setAttribute('x2', x2); el.setAttribute('y2', y2);
          el.setAttribute('class', className);
          drawing.appendChild(el);
        };

        const render = () => {
          const data = new FormData(form);
          const width = Number(data.get('width')) || 1200;
          const height = Number(data.get('height')) || 1500;
          const fields = Number(data.get('fields')) || 1;
          const type = data.get('type');
          const colour = data.get('colour');
          const glass = data.get('glass');
          const maxW = 500;
          const maxH = 350;
          const scale = Math.min(maxW / width, maxH / height);
          const w = width * scale;
          const h = height * scale;
          const x = (640 - w) / 2;
          const y = (430 - h) / 2;

          drawing.replaceChildren();
          const frame = document.createElementNS(ns, 'rect');
          frame.setAttribute('x', x); frame.setAttribute('y', y);
          frame.setAttribute('width', w); frame.setAttribute('height', h);
          frame.setAttribute('class', 'ek-drawing__frame');
          drawing.appendChild(frame);

          for (let i = 1; i < fields; i += 1) {
            const lx = x + (w / fields) * i;
            line(lx, y, lx, y + h, 'ek-drawing__mullion');
          }

          if (type === 'draaikiep') {
            for (let i = 0; i < fields; i += 1) {
              const left = x + (w / fields) * i;
              const right = left + w / fields;
              line(left + 10, y + 10, right - 10, y + h - 10, 'ek-drawing__operation');
              line(right - 10, y + 10, left + 10, y + h - 10, 'ek-drawing__operation');
            }
          }
          if (type === 'deur') {
            line(x + 12, y + 12, x + w / fields - 12, y + h - 12, 'ek-drawing__operation');
          }

          text('[data-ek-size]', `${width} × ${height} mm`);
          text('[data-ek-type]', type === 'draaikiep' ? 'Draai-kiep' : type === 'deur' ? 'Deur' : 'Vast glas');
          text('[data-ek-colour]', colour);
          text('[data-ek-glass]', glass);
        };

        form.addEventListener('input', render);
        form.addEventListener('change', render);
        root.querySelector('[data-ek-request]').addEventListener('click', () => {
          root.querySelector('[data-ek-status]').textContent = 'Configuratie gereed voor BREBO-aanvraag';
        });
        render();
      });
    }
  };
})(Drupal, once);
