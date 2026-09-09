(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnAddressLookup = { attach(context) {
    once('brebo-europakozijn-address-lookup', '[data-europakozijn-configurator]', context).forEach((root) => {
      const form = root.querySelector('[data-ek-form]');
      const postcode = form?.elements.namedItem('postcode');
      const houseNumber = form?.elements.namedItem('house_number');
      if (!form || !postcode || !houseNumber) return;

      const grid = postcode.closest('.ek-control-grid');
      if (!grid) return;

      const result = document.createElement('div');
      result.className = 'ek-address-result';
      result.hidden = true;
      result.setAttribute('aria-live', 'polite');
      grid.after(result);

      const hiddenFields = ['street', 'city', 'bag_nummeraanduiding_id', 'bag_adresseerbaar_object_id', 'pdok_x', 'pdok_y'];
      const hidden = {};
      hiddenFields.forEach((name) => {
        let field = form.elements.namedItem(name);
        if (!field) {
          field = document.createElement('input');
          field.type = 'hidden';
          field.name = name;
          form.appendChild(field);
        }
        hidden[name] = field;
      });

      let timer = null;
      let sequence = 0;
      let controller = null;
      let activeKey = '';
      let resolvedKey = '';
      const memoryCache = new Map();

      const clearResolved = () => {
        resolvedKey = '';
        Object.values(hidden).forEach((field) => { field.value = ''; });
        result.hidden = true;
        result.className = 'ek-address-result';
        result.replaceChildren();
      };

      const normalizedPostcode = () => String(postcode.value || '').toUpperCase().replace(/\s+/g, '');
      const normalizedHouseNumber = () => String(houseNumber.value || '').trim().toUpperCase();
      const lookupKey = () => `${normalizedPostcode()}|${normalizedHouseNumber()}`;
      const valid = () => /^[1-9][0-9]{3}[A-Z]{2}$/.test(normalizedPostcode()) && /^\d+.*$/u.test(normalizedHouseNumber());

      const renderMessage = (message, state) => {
        result.hidden = false;
        result.className = `ek-address-result is-${state}`;
        result.textContent = message;
      };

      const renderAddress = (address) => {
        result.hidden = false;
        result.className = 'ek-address-result is-found';
        result.replaceChildren();
        const title = document.createElement('strong');
        const line = document.createElement('span');
        const source = document.createElement('small');
        title.textContent = 'Adres gevonden';
        line.textContent = address.display || '';
        source.textContent = 'Bron: officiële PDOK/BAG';
        result.append(title, line, source);
      };

      const applyPayload = (payload, key) => {
        const address = payload.address;
        hidden.street.value = address.street || '';
        hidden.city.value = address.city || '';
        hidden.bag_nummeraanduiding_id.value = address.bag_nummeraanduiding_id || '';
        hidden.bag_adresseerbaar_object_id.value = address.bag_adresseerbaar_object_id || '';
        hidden.pdok_x.value = address.coordinates?.x ?? '';
        hidden.pdok_y.value = address.coordinates?.y ?? '';
        resolvedKey = key;
        renderAddress(address);
        form.dispatchEvent(new Event('change', { bubbles: true }));
        root.dispatchEvent(new CustomEvent('ek:address-resolved', { bubbles: true, detail: payload }));
      };

      const lookup = async () => {
        window.clearTimeout(timer);
        timer = null;

        if (!valid()) {
          controller?.abort();
          activeKey = '';
          clearResolved();
          return;
        }

        const key = lookupKey();
        if (key === resolvedKey || key === activeKey) return;

        const cached = memoryCache.get(key);
        if (cached) {
          applyPayload(cached, key);
          return;
        }

        controller?.abort();
        controller = new AbortController();
        activeKey = key;
        const current = ++sequence;
        const raw = normalizedPostcode();
        postcode.value = `${raw.slice(0, 4)} ${raw.slice(4)}`;
        renderMessage('Officieel adres controleren via PDOK/BAG…', 'loading');

        try {
          const params = new URLSearchParams({
            postcode: raw,
            house_number: normalizedHouseNumber(),
          });
          const response = await fetch(`/europakozijn/api/address?${params.toString()}`, {
            headers: { Accept: 'application/json' },
            signal: controller.signal,
          });
          const payload = await response.json();
          if (current !== sequence || key !== activeKey) return;
          activeKey = '';

          if (!response.ok || !payload.found || !payload.address) {
            Object.values(hidden).forEach((field) => { field.value = ''; });
            renderMessage(payload.message || 'Adres niet gevonden in de officiële BAG.', 'error');
            return;
          }

          memoryCache.set(key, payload);
          applyPayload(payload, key);
        }
        catch (error) {
          if (error?.name === 'AbortError') return;
          if (current !== sequence) return;
          activeKey = '';
          Object.values(hidden).forEach((field) => { field.value = ''; });
          renderMessage('De officiële adrescontrole is tijdelijk niet beschikbaar.', 'error');
        }
      };

      const schedule = () => {
        if (!valid()) {
          controller?.abort();
          activeKey = '';
          clearResolved();
          window.clearTimeout(timer);
          return;
        }
        const key = lookupKey();
        if (key === resolvedKey || key === activeKey) return;
        window.clearTimeout(timer);
        timer = window.setTimeout(lookup, 160);
      };

      const lookupOnBlur = () => {
        if (!valid()) return;
        const key = lookupKey();
        if (key === resolvedKey || key === activeKey) return;
        lookup();
      };

      postcode.addEventListener('input', schedule);
      houseNumber.addEventListener('input', schedule);
      postcode.addEventListener('blur', lookupOnBlur);
      houseNumber.addEventListener('blur', lookupOnBlur);
      if (valid()) lookup();
    });
  }};
})(Drupal, once);
