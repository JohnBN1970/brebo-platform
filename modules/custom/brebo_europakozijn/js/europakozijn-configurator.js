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
        const openingPicker = root.querySelector('[data-ek-opening-picker]');
        const brandInput = root.querySelector('[data-ek-brand]');
        const brandPicker = root.querySelector('[data-ek-brand-picker]');
        const priceStatus = root.querySelector('[data-ek-price-status]');
        const ruleFeedback = root.querySelector('[data-ek-rule-feedback]');
        const contextState = root.querySelector('[data-ek-context-state]');
        const ns = 'http://www.w3.org/2000/svg';

        const brands = {
          aluplast: {label: 'Aluplast', pricing: 'calibrated'},
          koemmerling: {label: 'Kömmerling', pricing: 'pending'},
          rehau: {label: 'REHAU', pricing: 'pending'},
          schueco: {label: 'Schüco', pricing: 'pending'},
        };
        const jointTypes = {normal: 'Normale verbinding', hvl: 'HVL-verbinding'};
        const rebateTypes = {with_rebate: 'Met aanslag', without_rebate: 'Zonder aanslag'};
        const state = {
          mullions: [],
          transoms: [],
          selectedField: 'r0c0',
          fieldFunctions: {r0c0: 'vast'},
          openingDirections: {r0c0: 'left'},
          validationTimer: null,
          validationSequence: 0,
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
        const clean = (value) => typeof value === 'string' ? value.trim() : '';

        const ensureFields = (rows, columns) => {
          const nextFunctions = {};
          const nextDirections = {};
          for (let row = 0; row < rows; row += 1) {
            for (let column = 0; column < columns; column += 1) {
              const key = fieldKey(row, column);
              nextFunctions[key] = state.fieldFunctions[key] || 'vast';
              nextDirections[key] = state.openingDirections[key] || 'left';
            }
          }
          state.fieldFunctions = nextFunctions;
          state.openingDirections = nextDirections;
          if (!state.fieldFunctions[state.selectedField]) state.selectedField = 'r0c0';
        };

        const showRuleFeedback = (result) => {
          drawing.querySelectorAll('.ek-drawing__field.is-invalid').forEach((field) => field.classList.remove('is-invalid'));
          if (!ruleFeedback) return;

          if (!result || result.valid) {
            ruleFeedback.hidden = true;
            ruleFeedback.classList.remove('is-error');
            return;
          }

          const first = Array.isArray(result.errors) ? result.errors[0] : null;
          if (!first) {
            ruleFeedback.hidden = true;
            return;
          }

          if (first.field_id) {
            const field = drawing.querySelector(`[data-field-key="${first.field_id}"]`);
            if (field) field.classList.add('is-invalid');
          }

          text('[data-ek-rule-title]', 'Technisch niet mogelijk');
          text('[data-ek-rule-message]', first.message || 'Deze configuratie voldoet niet aan de productregels.');
          ruleFeedback.hidden = false;
          ruleFeedback.classList.add('is-error');
        };

        const validateRules = async (payload, sequence) => {
          try {
            const response = await fetch('/europakozijn/api/rules', {
              method: 'POST',
              headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
              body: JSON.stringify(payload),
            });
            const result = await response.json();
            if (sequence !== state.validationSequence) return;
            showRuleFeedback(result);
          }
          catch (error) {
            if (sequence !== state.validationSequence || !ruleFeedback) return;
            text('[data-ek-rule-title]', 'Technische controle tijdelijk niet beschikbaar');
            text('[data-ek-rule-message]', 'U kunt verder configureren; de definitieve controle volgt vóór de aanvraag.');
            ruleFeedback.hidden = false;
            ruleFeedback.classList.remove('is-error');
          }
        };

        const scheduleRuleValidation = (payload) => {
          state.validationSequence += 1;
          const sequence = state.validationSequence;
          window.clearTimeout(state.validationTimer);
          state.validationTimer = window.setTimeout(() => validateRules(payload, sequence), 140);
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
              const original = sorted(target)[entry.index];
              target[target.indexOf(original)] = Number(slider.value);
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

        const drawOperation = (left, top, right, bottom, fn, direction) => {
          const inset = Math.min(12, Math.max(5, (right - left) * 0.06));
          const midX = (left + right) / 2;
          const midY = (top + bottom) / 2;
          const line = (x1, y1, x2, y2) => drawing.appendChild(svgElement('line', {x1, y1, x2, y2, class: 'ek-drawing__operation'}));

          if (fn === 'deur' || fn === 'draaikiep') {
            const hingeX = direction === 'right' ? right - inset : left + inset;
            const lockX = direction === 'right' ? left + inset : right - inset;
            line(hingeX, top + inset, lockX, midY);
            line(hingeX, bottom - inset, lockX, midY);
          }
          if (fn === 'draaikiep') {
            line(left + inset, bottom - inset, midX, top + inset);
            line(right - inset, bottom - inset, midX, top + inset);
          }
          if (fn === 'deur') {
            const handleX = direction === 'right' ? left + inset * 1.5 : right - inset * 1.5;
            drawing.appendChild(svgElement('circle', {cx: handleX, cy: midY, r: 3, class: 'ek-drawing__handle'}));
          }
        };

        const render = () => {
          const data = new FormData(form);
          const width = Number(data.get('width')) || 1200;
          const height = Number(data.get('height')) || 1500;
          const brandKey = data.get('brand') || 'aluplast';
          const brand = brands[brandKey] || brands.aluplast;
          const jointType = data.get('joint_type') || 'normal';
          const rebateType = data.get('rebate_type') || 'with_rebate';
          const colour = data.get('colour');
          const glass = data.get('glass');
          const postcode = clean(data.get('postcode'));
          const houseNumber = clean(data.get('house_number'));
          const buildingType = clean(data.get('building_type'));
          const floorLevel = clean(data.get('floor_level'));
          const roomType = clean(data.get('room_type'));
          const roomAreaM2 = Number(data.get('room_area_m2')) || 0;
          const ventilationSystem = clean(data.get('ventilation_system'));
          const contextComplete = Boolean(postcode && houseNumber && buildingType && floorLevel !== '' && roomType && roomAreaM2 > 0 && ventilationSystem);

          const xPositionsPct = [0, ...sorted(state.mullions), 100];
          const yPositionsPct = [0, ...sorted(state.transoms), 100];
          const columns = xPositionsPct.length - 1;
          const rows = yPositionsPct.length - 1;
          ensureFields(rows, columns);

          const maxW = 560;
          const maxH = 390;
          const scale = Math.min(maxW / width, maxH / height);
          const w = width * scale;
          const h = height * scale;
          const x = (720 - w) / 2;
          const y = (480 - h) / 2 + 10;
          drawing.replaceChildren();

          const fieldPayload = [];
          for (let row = 0; row < rows; row += 1) {
            for (let column = 0; column < columns; column += 1) {
              const key = fieldKey(row, column);
              const leftPct = xPositionsPct[column];
              const rightPct = xPositionsPct[column + 1];
              const topPct = yPositionsPct[row];
              const bottomPct = yPositionsPct[row + 1];
              const left = x + w * leftPct / 100;
              const right = x + w * rightPct / 100;
              const top = y + h * topPct / 100;
              const bottom = y + h * bottomPct / 100;
              const fn = state.fieldFunctions[key];
              const direction = state.openingDirections[key];
              fieldPayload.push({
                id: key,
                function: fn,
                opening_direction: fn === 'vast' ? null : direction,
                width_mm: Math.round(width * (rightPct - leftPct) / 100),
                height_mm: Math.round(height * (bottomPct - topPct) / 100),
              });

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
              field.addEventListener('click', () => { state.selectedField = key; render(); });
              field.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                  event.preventDefault();
                  state.selectedField = key;
                  render();
                }
              });
              drawing.appendChild(field);
              drawOperation(left, top, right, bottom, fn, direction);
              const label = svgElement('text', {x: (left + right) / 2, y: (top + bottom) / 2, class: 'ek-drawing__field-label'});
              label.textContent = `${row * columns + column + 1}`;
              drawing.appendChild(label);
            }
          }

          drawing.appendChild(svgElement('rect', {x, y, width: w, height: h, class: 'ek-drawing__frame'}));
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
          const selectedDirection = state.openingDirections[state.selectedField];
          const directionLabel = selectedDirection === 'right' ? 'scharnieren rechts' : 'scharnieren links';

          text('[data-ek-selected]', `Vak ${selectedIndex} · ${selectedFunction === 'draaikiep' ? 'Draai-kiep' : selectedFunction === 'deur' ? 'Deur' : 'Vast glas'}`);
          text('[data-ek-opening-summary]', selectedFunction === 'vast' ? '—' : directionLabel);
          text('[data-ek-field-help]', `Vak ${selectedIndex} geselecteerd. Kies hieronder de functie.`);
          functionPicker.querySelectorAll('[data-ek-function]').forEach((button) => button.classList.toggle('is-active', button.dataset.ekFunction === selectedFunction));
          openingPicker.hidden = selectedFunction === 'vast';
          openingPicker.querySelectorAll('[data-ek-opening]').forEach((button) => button.classList.toggle('is-active', button.dataset.ekOpening === selectedDirection));
          brandPicker.querySelectorAll('[data-ek-brand-choice]').forEach((button) => {
            const active = button.dataset.ekBrandChoice === brandKey;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
          });

          const observationGeometry = {
            schema_version: 4,
            source: 'europakozijn_web_configurator',
            geometry: {
              width_mm: width,
              height_mm: height,
              vertical_mullions: sorted(state.mullions).map((positionPct) => ({position_mm: Math.round(width * positionPct / 100), length_mm: height})),
              horizontal_transoms: sorted(state.transoms).map((positionPct) => ({position_mm: Math.round(height * positionPct / 100), length_mm: width})),
              fields: fieldPayload,
            },
            product_selection: {brand: brandKey, system: null, joint_type: jointType, rebate_type: rebateType},
            finish: {colour, glass},
            building_context: {
              postcode,
              house_number: houseNumber,
              building_type: buildingType || null,
              floor_level: floorLevel || null,
            },
            room_context: {
              room_type: roomType || null,
              area_m2: roomAreaM2 || null,
              ventilation_system: ventilationSystem || null,
            },
            pricing_readiness: {
              technical_context_complete: contextComplete,
            },
          };

          calibration.value = JSON.stringify(observationGeometry);
          calibration.textContent = calibration.value;
          scheduleRuleValidation(observationGeometry);

          text('[data-ek-brand-summary]', brand.label);
          text('[data-ek-size]', `${width} × ${height} mm`);
          text('[data-ek-layout]', `${columns} × ${rows} · ${columns * rows} vak${columns * rows === 1 ? '' : 'ken'}`);
          text('[data-ek-joint-summary]', jointTypes[jointType] || jointTypes.normal);
          text('[data-ek-rebate-summary]', rebateTypes[rebateType] || rebateTypes.with_rebate);
          text('[data-ek-colour]', colour);
          text('[data-ek-glass]', glass);
          text('[data-ek-context-summary]', contextComplete ? 'Compleet' : 'Onvolledig');

          if (contextState) {
            contextState.textContent = contextComplete
              ? 'Technische basisgegevens compleet. De volgende stap is automatische beoordeling van wind, glas, veiligheid en ventilatie.'
              : 'Nog niet genoeg technische gegevens voor een betrouwbare prijs.';
            contextState.classList.toggle('is-complete', contextComplete);
          }

          if (priceStatus) {
            if (!contextComplete) {
              priceStatus.textContent = 'Vul eerst de technische situatie aan';
            }
            else if (brand.pricing === 'calibrated') {
              priceStatus.textContent = `${brand.label} · technische context compleet · prijsmodel kan daarna rekenen`;
            }
            else {
              priceStatus.textContent = `${brand.label} · technische context compleet · prijsmodel wordt gekalibreerd`;
            }
          }
        };

        root.querySelector('[data-ek-add-mullion]').addEventListener('click', () => addProfile('vertical'));
        root.querySelector('[data-ek-add-transom]').addEventListener('click', () => addProfile('horizontal'));
        functionPicker.querySelectorAll('[data-ek-function]').forEach((button) => button.addEventListener('click', () => {
          state.fieldFunctions[state.selectedField] = button.dataset.ekFunction;
          render();
        }));
        openingPicker.querySelectorAll('[data-ek-opening]').forEach((button) => button.addEventListener('click', () => {
          state.openingDirections[state.selectedField] = button.dataset.ekOpening;
          render();
        }));
        brandPicker.querySelectorAll('[data-ek-brand-choice]').forEach((button) => button.addEventListener('click', () => {
          brandInput.value = button.dataset.ekBrandChoice;
          brandInput.dispatchEvent(new Event('change', {bubbles: true}));
        }));
        form.addEventListener('input', (event) => { if (!event.target.matches('.ek-profile-control input')) render(); });
        form.addEventListener('change', render);
        root.querySelector('[data-ek-request]').addEventListener('click', () => {
          root.querySelector('[data-ek-status]').textContent = 'Configuratie gereed voor BREBO-aanvraag';
        });
        render();
      });
    }
  };
})(Drupal, once);
