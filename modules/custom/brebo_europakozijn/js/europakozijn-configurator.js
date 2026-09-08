(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnConfigurator = {
    attach(context) {
      once('brebo-europakozijn', '[data-europakozijn-configurator]', context).forEach((root) => {
        const form = root.querySelector('[data-ek-form]');
        const drawing = root.querySelector('[data-ek-drawing]');
        const calibration = root.querySelector('[data-ek-calibration]');
        const profileList = root.querySelector('[data-ek-profile-list]');
        const functionPicker = root.querySelector('[data-ek-function-picker]');
        const ns = 'http://www.w3.org/2000/svg';

        const state = {
          mullions: [],
          transoms: [],
          selectedField: 'r0c0',
          fieldFunctions: {'r0c0': 'vast'},
        };

        const text = (selector, value) => {
          const element = root.querySelector(selector);
          if (element) element.textContent = value;
        };

        const svgElement = (name, attrs = {}) => {
          const el = document.createElementNS(ns, name);
          Object.entries(attrs).forEach(([key, value]) => el.setAttribute(key, value));
          return el;
        };

        const sorted = (values) => [...values].sort((a, b) => a - b);
        const fieldKey = (row, column) => `r${row}c${column}`;
        const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

        const ensureFieldFunctions = (rows, columns) => {
          const next = {};
          for (let row = 0; row < rows; row += 1) {
            for (let column = 0; column < columns; column += 1) {
              const key = fieldKey(row, column);
              next[key] = state.fieldFunctions[key] || 'vast';
            }
          }
          state.fieldFunctions = next;
          if (!state.fieldFunctions[state.selectedField]) state.selectedField = 'r0c0';
        };

        const addProfile = (direction) => {
          const values = direction === 'vertical' ? state.mullions : state.transoms;
          if (values.length >= 4) return;
          const candidate = Math.round(100 / (values.length + 2));
          let position = candidate;
          while (values.some((value) => Math.abs(value - position) < 8)) position += 8;
          values.push(clamp(position, 12, 88));
          render();
        };

        const removeProfile = (direction, index) => {
          const values = direction === 'vertical' ? state.mullions : state.transoms;
          values.splice(index, 1);
          render();
        };

        const renderProfileControls = (width, height) => {
          profileList.querySelectorAll('[data-ek-profile-control]').forEach((el) => el.remove());
          const entries = [
            ...sorted(state.mullions).map((position, index) => ({direction: 'vertical', position, index})),
            ...sorted(state.transoms).map((position, index) => ({direction: 'horizontal', position, index})),
          ];
          const empty = root.querySelector('[data-ek-profile-empty]');
          empty.hidden = entries.length > 0;

          entries.forEach((entry) => {
            const row = document.createElement('div');
            row.className = 'ek-profile-control';
            row.dataset.ekProfileControl = '';
            const dimension = entry.direction === 'vertical' ? width : height;
            const mm = Math.round(dimension * entry.position / 100);
            row.innerHTML = `<div><strong>${entry.direction === 'vertical' ? 'Verticale stijl' : 'Horizontale dorpel'}</strong><span>${mm} mm vanaf ${entry.direction === 'vertical' ? 'links' : 'boven'}</span></div><input type="range" min="10" max="90" step="1" value="${entry.position}" aria-label="Positie profiel"><button type="button" aria-label="Profiel verwijderen">×</button>`;
            const slider = row.querySelector('input');
            slider.addEventListener('input', () => {
              const target = entry.direction === 'vertical' ? state.mullions : state.transoms;
              const sortedValues = sorted(target);
              const original = sortedValues[entry.index];
              const sourceIndex = target.indexOf(original);
              target[sourceIndex] = Number(slider.value);
              render();
            });
            row.querySelector('button').addEventListener('click', () => {
              const target = entry.direction === 'vertical' ? state.mullions : state.transoms;
              const original = sorted(target)[entry.index];
              removeProfile(entry.direction, target.indexOf(original));
            });
            profileList.appendChild(row);
          });
        };

        const drawOperation = (left, top, right, bottom, fn) => {
          const inset = Math.min(12, Math.max(5, (right - left) * 0.06));
          if (fn === 'draaikiep') {
            drawing.appendChild(svgElement('line', {x1: left + inset, y1: top + inset, x2: right - inset, y2: bottom - inset, class: 'ek-drawing__operation'}));
            drawing.appendChild(svgElement('line', {x1: right - inset, y1: top + inset, x2: left + inset, y2: bottom - inset, class: 'ek-drawing__operation'}));
          }
          if (fn === 'deur') {
            drawing.appendChild(svgElement('line', {x1: left + inset, y1: top + inset, x2: right - inset, y2: bottom - inset, class: 'ek-drawing__operation'}));
            const handle = svgElement('circle', {cx: right - inset * 1.5, cy: (top + bottom) / 2, r: 3, class: 'ek-drawing__handle'});
            drawing.appendChild(handle);
          }
        };

        const render = () => {
          const data = new FormData(form);
          const width = Number(data.get('width')) || 1200;
          const height = Number(data.get('height')) || 1500;
          const colour = data.get('colour');
          const glass = data.get('glass');
          const xPositionsPct = [0, ...sorted(state.mullions), 100];
          const yPositionsPct = [0, ...sorted(state.transoms), 100];
          const columns = xPositionsPct.length - 1;
          const rows = yPositionsPct.length - 1;
          ensureFieldFunctions(rows, columns);

          const maxW = 560;
          const maxH = 390;
          const scale = Math.min(maxW / width, maxH / height);
          const w = width * scale;
          const h = height * scale;
          const x = (720 - w) / 2;
          const y = (480 - h) / 2 + 10;

          drawing.replaceChildren();

          for (let row = 0; row < rows; row += 1) {
            for (let column = 0; column < columns; column += 1) {
              const key = fieldKey(row, column);
              const left = x + w * xPositionsPct[column] / 100;
              const right = x + w * xPositionsPct[column + 1] / 100;
              const top = y + h * yPositionsPct[row] / 100;
              const bottom = y + h * yPositionsPct[row + 1] / 100;
              const field = svgElement('rect', {
                x: left,
                y: top,
                width: right - left,
                height: bottom - top,
                class: `ek-drawing__field${state.selectedField === key ? ' is-selected' : ''}`,
                'data-field-key': key,
                tabindex: '0',
                role: 'button',
                'aria-label': `Vak ${row * columns + column + 1}`,
              });
              field.addEventListener('click', () => {
                state.selectedField = key;
                render();
              });
              field.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                  event.preventDefault();
                  state.selectedField = key;
                  render();
                }
              });
              drawing.appendChild(field);
              drawOperation(left, top, right, bottom, state.fieldFunctions[key]);
              const label = svgElement('text', {x: (left + right) / 2, y: (top + bottom) / 2, class: 'ek-drawing__field-label'});
              label.textContent = `${row * columns + column + 1}`;
              drawing.appendChild(label);
            }
          }

          const frame = svgElement('rect', {x, y, width: w, height: h, class: 'ek-drawing__frame'});
          drawing.appendChild(frame);
          sorted(state.mullions).forEach((position) => {
            const px = x + w * position / 100;
            drawing.appendChild(svgElement('line', {x1: px, y1: y, x2: px, y2: y + h, class: 'ek-drawing__mullion'}));
          });
          sorted(state.transoms).forEach((position) => {
            const py = y + h * position / 100;
            drawing.appendChild(svgElement('line', {x1: x, y1: py, x2: x + w, y2: py, class: 'ek-drawing__mullion'}));
          });

          renderProfileControls(width, height);

          const selectedIndex = Object.keys(state.fieldFunctions).indexOf(state.selectedField) + 1;
          const selectedFunction = state.fieldFunctions[state.selectedField];
          text('[data-ek-selected]', `Vak ${selectedIndex} · ${selectedFunction === 'draaikiep' ? 'Draai-kiep' : selectedFunction === 'deur' ? 'Deur' : 'Vast glas'}`);
          text('[data-ek-field-help]', `Vak ${selectedIndex} geselecteerd. Kies hieronder de functie.`);
          functionPicker.querySelectorAll('[data-ek-function]').forEach((button) => button.classList.toggle('is-active', button.dataset.ekFunction === selectedFunction));

          const observationGeometry = {
            schema_version: 2,
            width_mm: width,
            height_mm: height,
            vertical_mullions: sorted(state.mullions).map((positionPct) => ({position_mm: Math.round(width * positionPct / 100), length_mm: height})),
            horizontal_transoms: sorted(state.transoms).map((positionPct) => ({position_mm: Math.round(height * positionPct / 100), length_mm: width})),
            fields: Object.entries(state.fieldFunctions).map(([id, fn]) => ({id, function: fn})),
            colour,
            glass,
          };
          calibration.value = JSON.stringify(observationGeometry);
          calibration.textContent = calibration.value;

          text('[data-ek-size]', `${width} × ${height} mm`);
          text('[data-ek-layout]', `${columns} × ${rows} · ${columns * rows} vak${columns * rows === 1 ? '' : 'ken'}`);
          text('[data-ek-colour]', colour);
          text('[data-ek-glass]', glass);
        };

        root.querySelector('[data-ek-add-mullion]').addEventListener('click', () => addProfile('vertical'));
        root.querySelector('[data-ek-add-transom]').addEventListener('click', () => addProfile('horizontal'));
        functionPicker.querySelectorAll('[data-ek-function]').forEach((button) => {
          button.addEventListener('click', () => {
            state.fieldFunctions[state.selectedField] = button.dataset.ekFunction;
            render();
          });
        });
        form.addEventListener('input', (event) => {
          if (!event.target.matches('.ek-profile-control input')) render();
        });
        form.addEventListener('change', render);
        root.querySelector('[data-ek-request]').addEventListener('click', () => {
          root.querySelector('[data-ek-status]').textContent = 'Configuratie gereed voor BREBO-aanvraag';
        });
        render();
      });
    }
  };
})(Drupal, once);
