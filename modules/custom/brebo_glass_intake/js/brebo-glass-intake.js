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
        const resultGoal = root.querySelector('[data-glass-result-goal]');
        const resultBuilding = root.querySelector('[data-glass-result-building]');
        const resultSource = root.querySelector('[data-glass-result-source]');

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
            const active = Number(step.dataset.glassStep || 0) <= number;
            step.hidden = !active;
            step.classList.toggle('is-active', Number(step.dataset.glassStep || 0) === number);
          });
          progress.forEach((item) => {
            const n = Number(item.dataset.glassProgress || 0);
            item.classList.toggle('is-active', n === number);
            item.classList.toggle('is-complete', n < number);
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
            if (result && !result.hidden && selectedSource) {
              renderResult(false);
            }
            showStep(2);
            window.requestAnimationFrame(() => {
              root.querySelector('[data-glass-step="2"] input, [data-glass-step="2"] select')?.focus({ preventScroll: true });
            });
          });
        });

        continueButton?.addEventListener('click', () => {
          showStep(3);
          window.requestAnimationFrame(() => {
            root.querySelector('[data-glass-step="3"] button')?.focus({ preventScroll: true });
          });
        });

        const renderResult = (scroll = true) => {
          if (!selectedGoal || !selectedSource || !result) return;
          const building = root.querySelector('[data-glass-field="building"]')?.value?.trim() || 'Nog niet opgegeven';
          const location = root.querySelector('[data-glass-field="location"]')?.value?.trim();
          const quantity = root.querySelector('[data-glass-field="quantity"]')?.value?.trim();

          if (resultGoal) resultGoal.textContent = labels.goals[selectedGoal] || '';
          if (resultBuilding) resultBuilding.textContent = location ? building + ' — ' + location : building;
          if (resultSource) resultSource.textContent = labels.sources[selectedSource] || '';

          const details = [];
          if (quantity) details.push(quantity + ' glasvak' + (quantity === '1' ? '' : 'ken'));
          const frame = root.querySelector('[data-glass-field="frame"]');
          if (frame?.value) details.push(frame.options[frame.selectedIndex]?.text || '');

          if (resultText) {
            resultText.textContent = details.length
              ? 'U heeft al ' + details.join(' en ') + ' aangegeven. BREBO gebruikt dit als uitgangspunt en vraagt alleen verder waar dat voor deze situatie nog nodig is.'
              : 'BREBO gebruikt uw gekozen aanleiding en beschikbare informatie als uitgangspunt en vraagt alleen verder waar dat voor deze situatie nog nodig is.';
          }
          result.hidden = false;
          if (scroll) result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        };

        root.querySelectorAll('[data-glass-field]').forEach((field) => {
          field.addEventListener('input', refreshResult);
          field.addEventListener('change', refreshResult);
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
