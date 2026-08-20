(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboLens = {
    attach(context) {
      once('brebo-lens-v4', '[data-brebo-lens]', context).forEach((root) => {
        const nodes = Array.from(root.querySelectorAll('[data-lens-step]'));
        const panels = Array.from(root.querySelectorAll('[data-lens-panel]'));
        const currentPhoto = root.querySelector('[data-lens-photo-current]');
        const nextPhoto = root.querySelector('[data-lens-photo-next]');
        const currentBackdrop = root.querySelector('[data-lens-backdrop-current]');
        const nextBackdrop = root.querySelector('[data-lens-backdrop-next]');
        const prev = root.querySelector('.brebo-lens__nav--prev');
        const next = root.querySelector('.brebo-lens__nav--next');
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let active = 0;
        let token = 0;

        nodes.map((node) => node.dataset.lensImage).filter(Boolean).forEach((src) => {
          const image = new Image(); image.decoding = 'async'; image.src = src;
        });

        const swap = (current, incoming, src, currentToken) => {
          if (!current || !incoming || !src) return;
          if (reducedMotion) { current.src = src; incoming.src = src; return; }
          incoming.src = src;
          incoming.classList.add('is-visible');
          current.classList.add('is-leaving');
          window.setTimeout(() => {
            if (currentToken !== token) return;
            current.src = src;
            current.classList.remove('is-leaving');
            incoming.classList.remove('is-visible');
          }, 480);
        };

        const show = (requestedIndex, focusNode = false) => {
          active = (requestedIndex + nodes.length) % nodes.length;
          token += 1;
          const currentToken = token;
          nodes.forEach((node, index) => {
            const selected = index === active;
            node.classList.toggle('is-active', selected);
            node.setAttribute('aria-pressed', selected ? 'true' : 'false');
            if (selected && focusNode) node.focus({ preventScroll: true });
          });
          panels.forEach((panel, index) => {
            const selected = index === active;
            panel.classList.toggle('is-active', selected);
            panel.hidden = !selected;
          });
          const src = nodes[active]?.dataset.lensImage;
          swap(currentPhoto, nextPhoto, src, currentToken);
          swap(currentBackdrop, nextBackdrop, src, currentToken);
        };

        nodes.forEach((node, index) => {
          node.addEventListener('click', () => show(index));
          node.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowRight' || event.key === 'ArrowDown') { event.preventDefault(); show(active + 1, true); }
            if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') { event.preventDefault(); show(active - 1, true); }
            if (event.key === 'Home') { event.preventDefault(); show(0, true); }
            if (event.key === 'End') { event.preventDefault(); show(nodes.length - 1, true); }
          });
        });
        prev?.addEventListener('click', () => show(active - 1));
        next?.addEventListener('click', () => show(active + 1));
        show(0);
      });
    },
  };
})(Drupal, once);
