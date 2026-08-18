(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboNavigation = {
    attach(context) {
      once('brebo-navigation', '.site-header', context).forEach((header) => {
        const toggle = header.querySelector('.site-header__toggle');
        const navigation = header.querySelector('.block-menu');
        const overlay = header.querySelector('.site-header__overlay');

        if (!toggle || !navigation) {
          return;
        }

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
    }
  };
})(Drupal, once);
