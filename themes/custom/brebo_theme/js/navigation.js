(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboNavigation = {
    attach(context) {
      const headers = context.matches?.('.site-header')
        ? [context]
        : Array.from(context.querySelectorAll?.('.site-header') || []);

      headers.forEach((header) => {
        const toggle = header.querySelector('.site-header__toggle');
        const navigation = header.querySelector('.block-menu, .main-navigation');
        const overlay = header.querySelector('.site-header__overlay');

        // BigPipe can attach the header before the menu block itself arrives.
        // Only mark the component initialized once all required elements exist,
        // so a later Drupal behavior pass can still wire the hamburger menu.
        if (!toggle || !navigation || header.dataset.breboNavigationBound === 'true') {
          return;
        }
        header.dataset.breboNavigationBound = 'true';

        navigation.id = 'brebo-mobile-navigation';

        const setMenuState = (open) => {
          header.classList.toggle('is-menu-open', open);
          document.body.classList.toggle('has-open-mobile-menu', open);
          toggle.setAttribute('aria-expanded', String(open));

          if (overlay) {
            overlay.hidden = !open;
          }
        };

        const setScrollState = () => {
          header.classList.toggle('is-scrolled', window.scrollY > 12);
        };

        toggle.addEventListener('click', () => {
          setMenuState(!header.classList.contains('is-menu-open'));
        });

        if (overlay) {
          overlay.addEventListener('click', () => setMenuState(false));
        }

        navigation.addEventListener('click', (event) => {
          if (
            event.target.closest('a') &&
            window.matchMedia('(max-width: 1023px)').matches
          ) {
            setMenuState(false);
          }
        });

        document.addEventListener('keydown', (event) => {
          if (event.key === 'Escape' && header.classList.contains('is-menu-open')) {
            setMenuState(false);
            toggle.focus();
          }
        });

        window.addEventListener('resize', () => {
          if (!window.matchMedia('(max-width: 1023px)').matches) {
            setMenuState(false);
          }
        });

        window.addEventListener('scroll', setScrollState, { passive: true });
        setScrollState();
      });

      once('brebo-back-to-top', 'body', context).forEach((body) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'brebo-back-to-top';
        button.setAttribute('aria-label', Drupal.t('Terug naar boven'));
        button.setAttribute('title', Drupal.t('Terug naar boven'));
        body.appendChild(button);

        const setBackToTopState = () => {
          button.classList.toggle('is-visible', window.scrollY > 500);
        };

        button.addEventListener('click', () => {
          window.scrollTo({
            top: 0,
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'
          });
        });

        window.addEventListener('scroll', setBackToTopState, { passive: true });
        setBackToTopState();
      });
    }
  };
})(Drupal, once);
