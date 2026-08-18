(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboLens = {
    attach(context) {
      once('brebo-lens-v3', '[data-brebo-lens]', context).forEach((root) => {
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
        let rotation = 0;
        let transitionTimer = null;
        let transitionToken = 0;

        const images = nodes.map((node) => node.dataset.lensImage).filter(Boolean);
        images.forEach((src) => {
          const image = new Image();
          image.decoding = 'async';
          image.src = src;
        });

        const normaliseDelta = (from, to) => {
          let delta = to - from;
          if (delta > nodes.length / 2) delta -= nodes.length;
          if (delta < -nodes.length / 2) delta += nodes.length;
          return delta;
        };

        const swapImagePair = (current, incoming, imageUrl, token) => {
          if (!current || !incoming || !imageUrl) return;

          if (reducedMotion) {
            current.src = imageUrl;
            incoming.src = imageUrl;
            return;
          }

          incoming.src = imageUrl;
          incoming.classList.add('is-visible');
          current.classList.add('is-leaving');

          window.setTimeout(() => {
            if (token !== transitionToken) return;
            current.src = imageUrl;
            current.classList.remove('is-leaving');
            incoming.classList.remove('is-visible');
          }, 560);
        };

        const show = (requestedIndex, focusNode = false) => {
          const newIndex = (requestedIndex + nodes.length) % nodes.length;
          if (newIndex === active && root.dataset.lensReady === 'true') return;

          const delta = normaliseDelta(active, newIndex);
          rotation += delta * 60;
          active = newIndex;
          transitionToken += 1;
          const token = transitionToken;

          root.style.setProperty('--lens-rotation', `${rotation}deg`);
          root.classList.add('is-changing');
          window.clearTimeout(transitionTimer);

          nodes.forEach((node, i) => {
            const selected = i === active;
            node.classList.toggle('is-active', selected);
            node.setAttribute('aria-pressed', selected ? 'true' : 'false');
            if (selected && focusNode) node.focus({ preventScroll: true });
          });

          panels.forEach((panel, i) => {
            const selected = i === active;
            panel.classList.toggle('is-active', selected);
            panel.hidden = !selected;
          });

          const imageUrl = nodes[active]?.dataset.lensImage;
          swapImagePair(currentPhoto, nextPhoto, imageUrl, token);
          swapImagePair(currentBackdrop, nextBackdrop, imageUrl, token);

          transitionTimer = window.setTimeout(() => {
            if (token === transitionToken) root.classList.remove('is-changing');
          }, 620);

          root.dataset.lensReady = 'true';
        };

        nodes.forEach((node, index) => {
          node.addEventListener('click', () => show(index));
          node.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
              event.preventDefault();
              show(active + 1, true);
            }
            if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
              event.preventDefault();
              show(active - 1, true);
            }
            if (event.key === 'Home') {
              event.preventDefault();
              show(0, true);
            }
            if (event.key === 'End') {
              event.preventDefault();
              show(nodes.length - 1, true);
            }
          });
        });

        prev?.addEventListener('click', () => show(active - 1));
        next?.addEventListener('click', () => show(active + 1));
        show(0);
      });
    },
  };
})(Drupal, once);
