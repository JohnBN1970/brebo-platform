(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboLens = {
    attach(context) {
      once('brebo-lens-v5', '[data-brebo-lens]', context).forEach((root) => {
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

        const journey = root.querySelector('[data-brebo-journey]');
        if (!journey) return;

        const routes = {
          'orientatie': {
            label: 'Ik weet nog niet wat er nodig is',
            question: 'Wat is voor u nu de belangrijkste aanleiding?',
            contexts: [
              ['staat-inzicht', 'Ik wil eerst weten wat de staat van het gebouw is'],
              ['onderhoudsplanning', 'Ik wil onderhoud of investeringen beter kunnen plannen'],
              ['keuze-onduidelijk', 'Ik twijfel tussen meerdere technische oplossingen'],
              ['risico-kosten', 'Ik wil risico, kosten en prioriteiten eerst helder krijgen'],
            ],
          },
          'probleem': {
            label: 'Ik heb een concreet onderhoudsprobleem',
            question: 'Waar merkt u het probleem vooral aan?',
            contexts: [
              ['lekkage-tocht', 'Lekkage, tocht of vocht'],
              ['schade-slijtage', 'Schade, slijtage of houtrot'],
              ['glas-condens', 'Glas, condens of doorzicht'],
              ['functioneren', 'Ramen, deuren of onderdelen functioneren niet goed'],
            ],
          },
          'kozijnen-glas': {
            label: 'Ik wil kozijnen of glas aanpakken',
            question: 'Wat wilt u als eerste laten beoordelen?',
            contexts: [
              ['kozijnen', 'Kozijnen'],
              ['glas', 'Glas'],
              ['kozijnen-glas', 'Kozijnen én glas in samenhang'],
              ['ventilatie', 'Kozijnen, glas en ventilatie samen'],
            ],
          },
          'documenten': {
            label: 'Ik heb al plannen of documenten',
            question: 'Welke informatie heeft u al beschikbaar?',
            contexts: [
              ['mjop-rapport', 'MJOP, inspectie of technisch rapport'],
              ['tekening-kozijnstaat', 'Tekeningen of kozijnstaat'],
              ['offerte-bestek', 'Offerte, bestek of aanvraagstukken'],
              ['fotos-overig', 'Foto’s of andere projectinformatie'],
            ],
          },
          'bouwbegeleiding': {
            label: 'Ik zoek begeleiding bij een project',
            question: 'Waar in het project heeft u vooral ondersteuning nodig?',
            contexts: [
              ['voorbereiding', 'Planvorming en voorbereiding'],
              ['inkoop-aanbesteding', 'Inkoop, aanbesteding of contractvorming'],
              ['uitvoering-toezicht', 'Uitvoering, toezicht en kwaliteitsbewaking'],
              ['oplevering-nazorg', 'Oplevering, restpunten en nazorg'],
            ],
          },
        };

        const steps = Array.from(journey.querySelectorAll('[data-journey-step]'));
        const progress = Array.from(journey.querySelectorAll('[data-journey-progress]'));
        const contextRoot = journey.querySelector('[data-journey-contexts]');
        const question = journey.querySelector('[data-journey-question]');
        const routeSummary = journey.querySelector('[data-journey-route-summary]');
        const contextSummary = journey.querySelector('[data-journey-context-summary]');
        const cta = journey.querySelector('[data-journey-cta]');
        let selectedRoute = '';
        let selectedContext = '';
        let selectedContextLabel = '';

        const showJourneyStep = (stepNumber, moveFocus = true) => {
          let activeStep = null;
          steps.forEach((step) => {
            const selected = step.dataset.journeyStep === String(stepNumber);
            step.hidden = !selected;
            step.classList.toggle('is-active', selected);
            if (selected) activeStep = step;
          });
          progress.forEach((item) => {
            const itemStep = Number(item.dataset.journeyProgress || 0);
            item.classList.toggle('is-active', itemStep === stepNumber);
            item.classList.toggle('is-complete', itemStep < stepNumber);
          });
          if (moveFocus && activeStep) {
            window.requestAnimationFrame(() => {
              const focusTarget = activeStep.querySelector('button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])');
              focusTarget?.focus({ preventScroll: true });
            });
          }
        };

        const renderContexts = (routeKey) => {
          const config = routes[routeKey];
          if (!config || !contextRoot || !question) return;
          question.textContent = config.question;
          contextRoot.replaceChildren();
          config.contexts.forEach(([value, label]) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'brebo-journey__choice brebo-journey__choice--context';
            button.dataset.journeyContext = value;
            const strong = document.createElement('strong');
            strong.textContent = label;
            const span = document.createElement('span');
            span.textContent = 'Kies deze situatie';
            button.append(strong, span);
            button.addEventListener('click', () => {
              selectedContext = value;
              selectedContextLabel = label;
              if (routeSummary) routeSummary.textContent = config.label;
              if (contextSummary) contextSummary.textContent = label;
              if (cta) {
                const params = new URLSearchParams({ route: selectedRoute, context: selectedContext });
                cta.href = '/contact/bericht?' + params.toString();
              }
              showJourneyStep(3);
            });
            contextRoot.appendChild(button);
          });
        };

        journey.querySelectorAll('[data-journey-route]').forEach((button) => {
          button.addEventListener('click', () => {
            selectedRoute = button.dataset.journeyRoute || '';
            selectedContext = '';
            selectedContextLabel = '';
            renderContexts(selectedRoute);
            showJourneyStep(2);
          });
        });

        journey.querySelectorAll('[data-journey-back]').forEach((button) => {
          button.addEventListener('click', () => showJourneyStep(Number(button.dataset.journeyBack || 1)));
        });
      });
    },
  };
})(Drupal, once);
