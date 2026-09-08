(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnConfigurator = {
    attach(context) {
      once('brebo-europakozijn', '[data-europakozijn-configurator]', context).forEach((root) => {
        const form = root.querySelector('[data-ek-form]');
        const drawing = root.querySelector('[data-ek-drawing]');
        const calibration = root.querySelector('[data-ek-calibration]');
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

        const evenlySpacedPositions = (count, spanMm) => {
          const positions = [];
          for (let i = 1; i <= count; i += 1) {
            positions.push(Math.round((spanMm / (count + 1)) * i));
          }
          return positions;
        };

        const render = () => {
          const data = new FormData(form);
          const width = Number(data.get('width')) || 1200;
          const height = Number(data.get('height')) || 1500;
          const verticalMullions = Number(data.get('vertical_mullions')) || 0;
          const horizontalTransoms = Number(data.get('horizontal_transoms')) || 0;
          const columns = verticalMullions + 1;
          const rows = horizontalTransoms + 1;
          const fieldCount = columns * rows;
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
          const mullionPositions = evenlySpacedPositions(verticalMullions, width);
          const transomPositions = evenlySpacedPositions(horizontalTransoms, height);

          drawing.replaceChildren();
          const frame = document.createElementNS(ns, 'rect');
          frame.setAttribute('x', x); frame.setAttribute('y', y);
          frame.setAttribute('width', w); frame.setAttribute('height', h);
          frame.setAttribute('class', 'ek-drawing__frame');
          drawing.appendChild(frame);

          mullionPositions.forEach((positionMm) => {
            const lx = x + positionMm * scale;
            line(lx, y, lx, y + h, 'ek-drawing__mullion');
          });

          transomPositions.forEach((positionMm) => {
            const ly = y + positionMm * scale;
            line(x, ly, x + w, ly, 'ek-drawing__mullion');
          });

          if (type === 'draaikiep') {
            for (let row = 0; row < rows; row += 1) {
              for (let column = 0; column < columns; column += 1) {
                const left = x + (w / columns) * column;
                const right = left + w / columns;
                const top = y + (h / rows) * row;
                const bottom = top + h / rows;
                line(left + 10, top + 10, right - 10, bottom - 10, 'ek-drawing__operation');
                line(right - 10, top + 10, left + 10, bottom - 10, 'ek-drawing__operation');
              }
            }
          }
          if (type === 'deur') {
            const right = x + w / columns;
            const bottom = y + h / rows;
            line(x + 12, y + 12, right - 12, bottom - 12, 'ek-drawing__operation');
          }

          const observationGeometry = {
            schema_version: 1,
            width_mm: width,
            height_mm: height,
            vertical_mullions: mullionPositions.map((positionMm) => ({
              position_mm: positionMm,
              length_mm: height,
            })),
            horizontal_transoms: transomPositions.map((positionMm) => ({
              position_mm: positionMm,
              length_mm: width,
            })),
            field_count: fieldCount,
            function: type,
            colour,
            glass,
          };
          calibration.value = JSON.stringify(observationGeometry);
          calibration.textContent = calibration.value;

          text('[data-ek-size]', `${width} × ${height} mm`);
          text('[data-ek-type]', type === 'draaikiep' ? 'Draai-kiep' : type === 'deur' ? 'Deur' : 'Vast glas');
          text('[data-ek-layout]', `${verticalMullions} stijl(en), ${horizontalTransoms} dorpel(s), ${fieldCount} vak(ken)`);
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
