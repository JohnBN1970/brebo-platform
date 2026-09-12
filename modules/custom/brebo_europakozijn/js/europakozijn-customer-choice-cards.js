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
        let materialInput = ensureHidden('material', 'kunststof');
        let profileShapeInput = ensureHidden('profile_shape', 'flat');

        if (brandStep && !brandStep.querySelector('[data-ek-material-picker-v2]')) {
          brandPicker.hidden = true;
          const heading = brandStep.querySelector('h2');
          const intro = brandStep.querySelector('.ek-step__intro');
          if (heading) heading.textContent = 'Materiaal';
          if (intro) intro.textContent = 'Kies kunststof of aluminium. Houten kozijnen levert BREBO ook, maar die behandelen we als maatwerkaanvraag.';

          const picker = document.createElement('div');
          picker.className = 'ek-choice-grid ek-choice-grid--materials';
          picker.dataset.ekMaterialPickerV2 = '';
          picker.innerHTML = [
            materialCard('kunststof', 'Kunststof', 'Onderhoudsarm en veelzijdig', 'material-kunststof.svg', true),
            materialCard('aluminium', 'Aluminium', 'Slank, sterk en strak', 'material-aluminium.svg'),
          ].join('');
          brandPicker.insertAdjacentElement('afterend', picker);

          const profilePicker = document.createElement('div');
          profilePicker.className = 'ek-choice-block';
          profilePicker.dataset.ekProfileShapePicker = '';
          profilePicker.innerHTML = `<div class="ek-choice-block__heading"><strong>Profielvorm</strong><span>Kies bij kunststof voor een vlak of verdiept profiel.</span></div><div class="ek-choice-grid"><button type="button" class="ek-choice-card is-active" data-ek-profile-shape="flat" aria-pressed="true"><span class="ek-choice-card__text"><strong>Vlak</strong><small>Strakke, vlakke uitstraling</small></span></button><button type="button" class="ek-choice-card" data-ek-profile-shape="recessed" aria-pressed="false"><span class="ek-choice-card__text"><strong>Verdiept</strong><small>Verdiepte, meer traditionele profilering</small></span></button></div>`;
          picker.insertAdjacentElement('afterend', profilePicker);

          picker.addEventListener('click', (event) => {
            const button = event.target.closest('[data-ek-material-choice]');
            if (!button) return;
            materialInput.value = button.dataset.ekMaterialChoice;
            setActive(picker, '[data-ek-material-choice]', button);
            materialInput.dispatchEvent(new Event('change', { bubbles: true }));
            syncConditionalChoices();
          });

          profilePicker.addEventListener('click', (event) => {
            const button = event.target.closest('[data-ek-profile-shape]');
            if (!button) return;
            profileShapeInput.value = button.dataset.ekProfileShape;
            setActive(profilePicker, '[data-ek-profile-shape]', button);
            profileShapeInput.dispatchEvent(new Event('change', { bubbles: true }));
            syncConditionalChoices();
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
          jointPicker.innerHTML = `<div class="ek-choice-block__heading"><strong>Verbinding</strong><span>Bij een verdiept kunststof profiel kunt u kiezen tussen verstek en HVL.</span></div><div class="ek-choice-grid ek-choice-grid--joints">${jointCard('normal', 'Verstekverbinding', '45° versteklas', 'joint-normal.svg', true)}${jointCard('hvl', 'HVL-verbinding', 'Rechte houtlookverbinding', 'joint-hvl.svg')}</div>`;
          jointLabel.insertAdjacentElement('afterend', jointPicker);
          jointPicker.addEventListener('click', (event) => {
            const button = event.target.closest('[data-ek-joint-choice]');
            if (!button) return;
            jointSelect.value = button.dataset.ekJointChoice;
            setActive(jointPicker, '[data-ek-joint-choice]', button);
            jointSelect.dispatchEvent(new Event('change', { bubbles: true }));
          });
        }

        function syncConditionalChoices() {
          const profilePicker = root.querySelector('[data-ek-profile-shape-picker]');
          const isPlastic = materialInput.value === 'kunststof';
          const isRecessed = profileShapeInput.value === 'recessed';
          if (profilePicker) profilePicker.hidden = !isPlastic;
          if (!isPlastic) profileShapeInput.value = 'flat';
          if (jointPicker) jointPicker.hidden = !(isPlastic && isRecessed);
          if (jointSelect && !(isPlastic && isRecessed) && jointSelect.value === 'hvl') {
            jointSelect.value = 'normal';
            jointSelect.dispatchEvent(new Event('change', { bubbles: true }));
          }
        }

        function ensureHidden(name, value) {
          let input = form.elements.namedItem(name);
          if (!input) {
            input = document.createElement('input');
            input.type = 'hidden'; input.name = name; input.value = value; form.appendChild(input);
          }
          return input;
        }
        function setActive(parent, selector, activeButton) {
          parent.querySelectorAll(selector).forEach((item) => { const active = item === activeButton; item.classList.toggle('is-active', active); item.setAttribute('aria-pressed', active ? 'true' : 'false'); });
        }
        function materialCard(value, title, copy, image, active = false) {
          return `<button type="button" class="ek-choice-card${active ? ' is-active' : ''}" data-ek-material-choice="${value}" aria-pressed="${active ? 'true' : 'false'}"><span class="ek-choice-card__image"><img src="${assetBase}${image}" alt="" loading="lazy" decoding="async"></span><span class="ek-choice-card__text"><strong>${title}</strong><small>${copy}</small></span></button>`;
        }
        function jointCard(value, title, copy, image, active = false) {
          return `<button type="button" class="ek-choice-card ek-choice-card--joint${active ? ' is-active' : ''}" data-ek-joint-choice="${value}" aria-pressed="${active ? 'true' : 'false'}"><span class="ek-choice-card__image"><img src="${assetBase}${image}" alt="" loading="lazy" decoding="async"></span><span class="ek-choice-card__text"><strong>${title}</strong><small>${copy}</small></span></button>`;
        }
        syncConditionalChoices();
      });
    },
  };
})(Drupal, once);
