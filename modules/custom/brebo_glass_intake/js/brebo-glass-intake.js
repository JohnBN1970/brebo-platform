(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboGlassIntake = {
    attach(context) {
      once('brebo-glass-intake', '[data-glass-intake]', context).forEach((root) => {
        const steps = Array.from(root.querySelectorAll('[data-glass-step]'));
        const progress = Array.from(root.querySelectorAll('[data-glass-progress]'));
        const goalButtons = Array.from(root.querySelectorAll('[data-glass-goal]'));
        const sourceButtons = Array.from(root.querySelectorAll('[data-glass-source]'));
        const continueButton = root.querySelector('[data-glass-continue]');
        const goalSummary = root.querySelector('[data-glass-goal-summary]');
        const sourceSummary = root.querySelector('[data-glass-source-summary]');
        const result = root.querySelector('[data-glass-result]');
        const resultText = root.querySelector('[data-glass-result-text]');
        const resultHeading = root.querySelector('[data-glass-result-heading]');
        const resultGoal = root.querySelector('[data-glass-result-goal]');
        const resultBuilding = root.querySelector('[data-glass-result-building]');
        const resultSource = root.querySelector('[data-glass-result-source]');
        const submitLink = root.querySelector('[data-glass-submit]');
        const buildingSummary = root.querySelector('[data-glass-building-summary]');
        const technical = root.querySelector('[data-glass-technical]');
        const changeButtons = Array.from(root.querySelectorAll('[data-glass-change]'));

        const labels = {
          goals: {
            replace: 'Bestaand glas vervangen of verduurzamen',
            damage: 'Condens, lekkage of beschadiging',
            comfort: 'Meer comfort, minder geluid of minder zonbelasting',
            special: 'Veiligheid of bijzondere toepassing',
            unknown: 'Nog niet duidelijk wat technisch nodig is',
          },
          sources: {
            photos: 'Foto’s beschikbaar',
            documents: 'Tekeningen of glasstaat beschikbaar',
            none: 'Nog geen aanvullende informatie beschikbaar',
          },
        };

        let selectedGoal = '';
        let selectedSource = '';

        const refreshResult = () => {
          if (selectedGoal && selectedSource && result && !result.hidden) {
            renderResult(false);
          }
        };

        const showStep = (number) => {
          steps.forEach((step) => {
            const n = Number(step.dataset.glassStep || 0);
            step.hidden = n > number;
            step.classList.toggle('is-active', n === number);
            step.classList.toggle('is-complete', n < number);
          });
          progress.forEach((item) => {
            const n = Number(item.dataset.glassProgress || 0);
            item.classList.toggle('is-active', n === number);
            item.classList.toggle('is-complete', n < number);
          });
          changeButtons.forEach((button) => {
            const n = Number(button.dataset.glassChange || 0);
            button.hidden = n >= number;
          });
        };

        goalButtons.forEach((button) => {
          button.addEventListener('click', () => {
            selectedGoal = button.dataset.glassGoal || '';
            goalButtons.forEach((candidate) => candidate.classList.toggle('is-selected', candidate === button));
            if (goalSummary) {
              goalSummary.hidden = false;
              goalSummary.textContent = labels.goals[selectedGoal] || '';
            }
            // Changing the primary choice rewinds the journey. Downstream
            // information must be chosen again so progress and result stay in sync.
            selectedSource = '';
            sourceButtons.forEach((candidate) => candidate.classList.remove('is-selected'));
            if (sourceSummary) {
              sourceSummary.hidden = true;
              sourceSummary.textContent = '';
            }
            if (result) result.hidden = true;
            if (submitLink) submitLink.href = '/contact/bericht';
            showStep(2);
            root.querySelector('[data-glass-change="1"]')?.removeAttribute('hidden');
            window.requestAnimationFrame(() => {
              root.querySelector('[data-glass-step="2"] input, [data-glass-step="2"] select')?.focus({ preventScroll: true });
            });
          });
        });

        continueButton?.addEventListener('click', () => {
          const building = root.querySelector('[data-glass-field="building"]')?.value?.trim();
          if (buildingSummary) {
            buildingSummary.textContent = building || 'Gebouw nog niet opgegeven';
            buildingSummary.hidden = false;
          }
          root.querySelector('[data-glass-change="2"]')?.removeAttribute('hidden');
          showStep(3);
          window.requestAnimationFrame(() => {
            root.querySelector('[data-glass-step="3"] button')?.focus({ preventScroll: true });
          });
        });

        const renderResult = (scroll = true) => {
          if (!selectedGoal || !selectedSource || !result) return;
          const building = root.querySelector('[data-glass-field="building"]')?.value?.trim() || 'Nog niet opgegeven';
          if (resultGoal) resultGoal.textContent = labels.goals[selectedGoal] || '';
          if (resultBuilding) resultBuilding.textContent = building;
          if (resultSource) resultSource.textContent = labels.sources[selectedSource] || '';

          if (resultText) {
            resultText.textContent = 'BREBO gebruikt uw aanleiding, gebouwcontext en beschikbare informatie als uitgangspunt. Wij bepalen daarna zelf welke technische gegevens, glasposities of opname nog nodig zijn.';
          }
          if (submitLink) {
            const params = new URLSearchParams({
              route: 'kozijnen-glas',
              context: 'glas-aanvragen',
              aanleiding: selectedGoal,
              informatie: selectedSource,
            });
            if (building !== 'Nog niet opgegeven') params.set('gebouw', building);
            submitLink.href = '/contact/bericht?' + params.toString();
          }

          result.hidden = false;
          steps.forEach((step) => {
            step.hidden = true;
            step.classList.remove('is-active');
            step.classList.add('is-complete');
          });
          if (technical) technical.hidden = false;
          if (scroll) {
            result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            window.requestAnimationFrame(() => resultHeading?.focus({ preventScroll: true }));
          }
        };

        root.querySelectorAll('[data-glass-field]').forEach((field) => {
          field.addEventListener('input', refreshResult);
          field.addEventListener('change', refreshResult);
        });

        changeButtons.forEach((button) => {
          button.addEventListener('click', () => {
            const target = Number(button.dataset.glassChange || 1);
            button.hidden = true;
            if (result) result.hidden = true;
            if (technical) technical.hidden = true;
            showStep(target);
            window.requestAnimationFrame(() => {
              root.querySelector('[data-glass-step="' + target + '"] button, [data-glass-step="' + target + '"] input')?.focus({ preventScroll: true });
            });
          });
        });

        sourceButtons.forEach((button) => {
          button.addEventListener('click', () => {
            selectedSource = button.dataset.glassSource || '';
            sourceButtons.forEach((candidate) => candidate.classList.toggle('is-selected', candidate === button));
            if (sourceSummary) {
              sourceSummary.hidden = false;
              sourceSummary.textContent = labels.sources[selectedSource] || '';
            }
            progress.forEach((item) => {
              if (Number(item.dataset.glassProgress || 0) === 3) item.classList.add('is-complete');
            });
            renderResult();
          });
        });

        showStep(1);
      });
    },
  };
})(Drupal, once);
