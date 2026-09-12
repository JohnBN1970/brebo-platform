(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnCustomerChoiceCards = {
    attach(context) {
      once('brebo-europakozijn-customer-choice-cards', '[data-europakozijn-configurator]', context).forEach((root) => {
        const form = root.querySelector('[data-ek-form]');
        if (!form) return;

        const assetBase = '/modules/custom/brebo_europakozijn/images/choices/';
        const brandPicker = root.querySelector('[data-ek-brand-picker]');
        const brandStep = brandPicker?.closest('.ek-step');
        let materialInput = form.elements.namedItem('material');
        if (!materialInput) {
          materialInput = document.createElement('input');
          materialInput.type = 'hidden';
          materialInput.name = 'material';
          materialInput.value = 'kunststof';
          form.appendChild(materialInput);
        }

        if (brandStep && !brandStep.querySelector('[data-ek-material-picker-v2]')) {
          brandPicker.hidden = true;
          const heading = brandStep.querySelector('h2');
          const intro = brandStep.querySelector('.ek-step__intro');
          if (heading) heading.textContent = 'Materiaal';
          if (intro) intro.textContent = 'Kies het materiaal. Het merk en profielsysteem bepaalt BREBO later op basis van de technische eisen en de aanvraag.';

          const picker = document.createElement('div');
          picker.className = 'ek-choice-grid ek-choice-grid--materials';
          picker.dataset.ekMaterialPickerV2 = '';
          picker.innerHTML = [
            materialCard('kunststof', 'Kunststof', 'Onderhoudsarm en veelzijdig', 'material-kunststof.svg', true),
            materialCard('aluminium', 'Aluminium', 'Slank, sterk en strak', 'material-aluminium.svg'),
            materialCard('hout', 'Hout', 'Natuurlijk en karaktervol', 'material-hout.svg'),
          ].join('');
          brandPicker.insertAdjacentElement('afterend', picker);

          picker.addEventListener('click', (event) => {
            const button = event.target.closest('[data-ek-material-choice]');
            if (!button) return;
            materialInput.value = button.dataset.ekMaterialChoice;
            picker.querySelectorAll('[data-ek-material-choice]').forEach((item) => {
              const active = item === button;
              item.classList.toggle('is-active', active);
              item.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            materialInput.dispatchEvent(new Event('change', { bubbles: true }));
            syncJointAvailability();
          });
        }

        const jointSelect = form.elements.namedItem('joint_type');
        const jointLabel = jointSelect?.closest('label');
        let jointPicker = root.querySelector('[data-ek-joint-picker-v2]');
        if (jointSelect && jointLabel && !jointPicker) {
          jointLabel.hidden = true;
          jointPicker = document.createElement('div');
          jointPicker.className = 'ek-choice-block';
          jointPicker.dataset.ekJointPickerV2 = '';
          jointPicker.innerHTML = `
            <div class="ek-choice-block__heading"><strong>Verbinding</strong><span>Kies alleen de uitstraling; BREBO controleert of de uitvoering technisch toepasbaar is.</span></div>
            <div class="ek-choice-grid ek-choice-grid--joints">
              ${jointCard('normal', 'Normale verbinding', '45° versteklas', 'joint-normal.svg', true)}
              ${jointCard('hvl', 'HVL-verbinding', 'Rechte houtlookverbinding', 'joint-hvl.svg')}
            </div>
            <p class="ek-choice-note" data-ek-joint-note>HVL is beschikbaar bij kunststof.</p>`;
          jointLabel.insertAdjacentElement('afterend', jointPicker);
          jointPicker.addEventListener('click', (event) => {
            const button = event.target.closest('[data-ek-joint-choice]');
            if (!button || button.disabled) return;
            jointSelect.value = button.dataset.ekJointChoice;
            jointPicker.querySelectorAll('[data-ek-joint-choice]').forEach((item) => {
              const active = item === button;
              item.classList.toggle('is-active', active);
              item.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            jointSelect.dispatchEvent(new Event('change', { bubbles: true }));
          });
        }

        function syncJointAvailability() {
          if (!jointPicker || !jointSelect) return;
          const isPlastic = (materialInput.value || 'kunststof') === 'kunststof';
          const hvl = jointPicker.querySelector('[data-ek-joint-choice="hvl"]');
          const note = jointPicker.querySelector('[data-ek-joint-note]');
          if (hvl) {
            hvl.disabled = !isPlastic;
            hvl.classList.toggle('is-disabled', !isPlastic);
          }
          if (note) note.textContent = isPlastic ? 'HVL is beschikbaar bij kunststof.' : 'Voor dit materiaal wordt de standaard verbinding toegepast.';
          if (!isPlastic && jointSelect.value === 'hvl') {
            jointSelect.value = 'normal';
            jointPicker.querySelectorAll('[data-ek-joint-choice]').forEach((item) => {
              const active = item.dataset.ekJointChoice === 'normal';
              item.classList.toggle('is-active', active);
              item.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            jointSelect.dispatchEvent(new Event('change', { bubbles: true }));
          }
        }

        function materialCard(value, title, copy, image, active = false) {
          return `<button type="button" class="ek-choice-card${active ? ' is-active' : ''}" data-ek-material-choice="${value}" aria-pressed="${active ? 'true' : 'false'}"><span class="ek-choice-card__image"><img src="${assetBase}${image}" alt="" loading="lazy" decoding="async"></span><span class="ek-choice-card__text"><strong>${title}</strong><small>${copy}</small></span></button>`;
        }

        function jointCard(value, title, copy, image, active = false) {
          return `<button type="button" class="ek-choice-card ek-choice-card--joint${active ? ' is-active' : ''}" data-ek-joint-choice="${value}" aria-pressed="${active ? 'true' : 'false'}"><span class="ek-choice-card__image"><img src="${assetBase}${image}" alt="" loading="lazy" decoding="async"></span><span class="ek-choice-card__text"><strong>${title}</strong><small>${copy}</small></span></button>`;
        }

        syncJointAvailability();
      });
    },
  };
})(Drupal, once);
