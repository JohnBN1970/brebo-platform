(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnDimensions = {
    attach(context) {
      once('brebo-europakozijn-dimensions', '[data-europakozijn-configurator]', context).forEach((root) => {
        const drawing = root.querySelector('[data-ek-drawing]');
        const form = root.querySelector('[data-ek-form]');
        if (!drawing || !form) return;

        const ns = 'http://www.w3.org/2000/svg';
        let rendering = false;
        let timer = null;

        const svg = (name, attrs = {}) => {
          const element = document.createElementNS(ns, name);
          Object.entries(attrs).forEach(([key, value]) => element.setAttribute(key, value));
          return element;
        };

        const number = (name, fallback) => {
          const value = Number(new FormData(form).get(name));
          return Number.isFinite(value) && value > 0 ? value : fallback;
        };

        const addLine = (group, x1, y1, x2, y2, className) => {
          group.appendChild(svg('line', {x1, y1, x2, y2, class: className}));
        };

        const addText = (group, x, y, value, className, anchor = 'middle') => {
          const label = svg('text', {x, y, class: className, 'text-anchor': anchor});
          label.textContent = value;
          group.appendChild(label);
        };

        const renderDimensions = () => {
          if (rendering) return;
          rendering = true;
          drawing.querySelector('[data-ek-dimensions]')?.remove();

          const frame = drawing.querySelector('.ek-drawing__frame');
          if (!frame) {
            rendering = false;
            return;
          }

          const x = Number(frame.getAttribute('x'));
          const y = Number(frame.getAttribute('y'));
          const w = Number(frame.getAttribute('width'));
          const h = Number(frame.getAttribute('height'));
          const widthMm = number('width', 1200);
          const heightMm = number('height', 1500);
          const group = svg('g', {'data-ek-dimensions': '', class: 'ek-dimensions'});

          // Buitenmaat van het kozijn is de dagmaat.
          const bottomY = y + h + 30;
          addLine(group, x, y + h, x, bottomY + 5, 'ek-dimensions__extension');
          addLine(group, x + w, y + h, x + w, bottomY + 5, 'ek-dimensions__extension');
          addLine(group, x, bottomY, x + w, bottomY, 'ek-dimensions__line');
          addText(group, x + w / 2, bottomY - 5, `Dagmaat ${Math.round(widthMm)} mm`, 'ek-dimensions__label');

          const leftX = x - 34;
          addLine(group, x, y, leftX - 5, y, 'ek-dimensions__extension');
          addLine(group, x, y + h, leftX - 5, y + h, 'ek-dimensions__extension');
          addLine(group, leftX, y, leftX, y + h, 'ek-dimensions__line');
          const heightLabel = svg('text', {
            x: leftX - 6,
            y: y + h / 2,
            class: 'ek-dimensions__label',
            'text-anchor': 'middle',
            transform: `rotate(-90 ${leftX - 6} ${y + h / 2})`,
          });
          heightLabel.textContent = `Dagmaat ${Math.round(heightMm)} mm`;
          group.appendChild(heightLabel);

          // Stijlen worden vanaf de linker buitenzijde op hun hartlijn bemaat.
          const verticals = [...drawing.querySelectorAll('.ek-drawing__mullion')]
            .filter((line) => Number(line.getAttribute('x1')) === Number(line.getAttribute('x2')))
            .map((line) => Number(line.getAttribute('x1')))
            .sort((a, b) => a - b);
          verticals.forEach((profileX, index) => {
            const mm = Math.round(((profileX - x) / w) * widthMm);
            const dimensionY = y - 28 - index * 20;
            addLine(group, x, y, x, dimensionY - 4, 'ek-dimensions__extension');
            addLine(group, profileX, y, profileX, dimensionY - 4, 'ek-dimensions__extension');
            addLine(group, x, dimensionY, profileX, dimensionY, 'ek-dimensions__line');
            addText(group, (x + profileX) / 2, dimensionY - 5, `Stijl h.o.h. ${mm} mm`, 'ek-dimensions__label');
          });

          // Kalven/tussendorpels worden vanaf de bovenste buitenzijde op hun hartlijn bemaat.
          const horizontals = [...drawing.querySelectorAll('.ek-drawing__mullion')]
            .filter((line) => Number(line.getAttribute('y1')) === Number(line.getAttribute('y2')))
            .map((line) => Number(line.getAttribute('y1')))
            .sort((a, b) => a - b);
          horizontals.forEach((profileY, index) => {
            const mm = Math.round(((profileY - y) / h) * heightMm);
            const dimensionX = x + w + 34 + index * 24;
            addLine(group, x + w, y, dimensionX + 4, y, 'ek-dimensions__extension');
            addLine(group, x + w, profileY, dimensionX + 4, profileY, 'ek-dimensions__extension');
            addLine(group, dimensionX, y, dimensionX, profileY, 'ek-dimensions__line');
            const label = svg('text', {
              x: dimensionX + 7,
              y: (y + profileY) / 2,
              class: 'ek-dimensions__label',
              'text-anchor': 'middle',
              transform: `rotate(90 ${dimensionX + 7} ${(y + profileY) / 2})`,
            });
            label.textContent = `Kalf h.o.h. ${mm} mm`;
            group.appendChild(label);
          });

          drawing.appendChild(group);
          rendering = false;
        };

        const schedule = () => {
          if (rendering) return;
          window.clearTimeout(timer);
          timer = window.setTimeout(renderDimensions, 0);
        };

        const observer = new MutationObserver(schedule);
        observer.observe(drawing, {childList: true, subtree: true, attributes: true});
        form.addEventListener('input', schedule);
        form.addEventListener('change', schedule);
        renderDimensions();
      });
    },
  };
})(Drupal, once);
