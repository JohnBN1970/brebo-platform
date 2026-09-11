(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnOfficeSubmit = { attach(context) {
    once('brebo-europakozijn-office-submit', '[data-europakozijn-configurator]', context).forEach((root) => {
      const form = root.querySelector('[data-ek-form]');
      const button = root.querySelector('[data-ek-request]');
      if (!form || !button) return;

      const status = document.createElement('p');
      status.className = 'ek-submit-status';
      status.setAttribute('aria-live', 'polite');
      button.insertAdjacentElement('afterend', status);

      const field = (name) => String(form.elements.namedItem(name)?.value || '').trim();
      const numberOrNull = (name) => {
        const value = Number(field(name));
        return Number.isFinite(value) && field(name) !== '' ? value : null;
      };
      const uuid = () => {
        if (window.crypto?.randomUUID) return window.crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
          const r = Math.random() * 16 | 0;
          const v = c === 'x' ? r : (r & 0x3 | 0x8);
          return v.toString(16);
        });
      };
      const loadJson = (key, fallback) => {
        try { return JSON.parse(localStorage.getItem(key) || '') || fallback; }
        catch (e) { return fallback; }
      };

      const buildPayload = () => {
        root.querySelector('[data-ek-save-frame]')?.click();

        const roomState = loadJson('brebo_europakozijn_rooms_v1', { rooms: [] });
        const requestState = loadJson('brebo_europakozijn_request_v1', { frames: [] });
        const rooms = Array.isArray(roomState.rooms) ? roomState.rooms : [];
        const storedFrames = Array.isArray(requestState.frames) ? requestState.frames : [];

        const observedFrames = storedFrames.map((frame) => ({
          id: frame.id || null,
          position: frame.position || null,
          room_id: frame.room_id || null,
          quantity: Math.max(1, Number(frame.quantity) || 1),
          geometry: frame.configuration?.geometry || null,
        }));

        const selectedFrames = storedFrames.map((frame) => {
          const configuration = frame.configuration || {};
          return {
            id: frame.id || null,
            product_selection: configuration.product_selection || null,
            joint_type: configuration.joint_type || null,
            rebate_type: configuration.rebate_type || null,
            colour: configuration.colour || null,
            glass: configuration.glass || null,
          };
        });

        return {
          request_id: uuid(),
          observed: {
            building: {
              postcode: field('postcode'),
              house_number: field('house_number'),
              floor_level: field('floor_level') || null,
            },
            rooms: rooms.map((room) => ({
              id: room.id || null,
              type: room.type || null,
              name: room.name || null,
              area_m2: Number(room.area_m2) || null,
              ventilation_system: room.ventilation_system || null,
            })),
            frames: observedFrames,
          },
          detected: {
            building: {
              street: field('street') || null,
              city: field('city') || null,
              bag_nummeraanduiding_id: field('bag_nummeraanduiding_id') || null,
              bag_adresseerbaar_object_id: field('bag_adresseerbaar_object_id') || null,
              bag_verblijfsobject_id: field('bag_verblijfsobject_id') || null,
              bag_pand_id: field('bag_pand_id') || null,
              gebruiksdoel: field('bag_gebruiksdoel') || null,
              oppervlakte_m2: numberOrNull('bag_oppervlakte_m2'),
              bouwjaar: numberOrNull('bag_bouwjaar'),
              aantal_verblijfsobjecten: numberOrNull('bag_aantal_verblijfsobjecten'),
              coordinates: {
                x: numberOrNull('pdok_x'),
                y: numberOrNull('pdok_y'),
              },
              source: 'PDOK/BAG',
            },
          },
          calculated: {
            website_indication: {
              status: root.querySelector('[data-ek-price-status]')?.textContent?.trim() || null,
              disclaimer: 'Indicatie; BREBO Office valideert technisch en commercieel opnieuw.',
            },
          },
          selected: {
            frames: selectedFrames,
          },
        };
      };

      button.addEventListener('click', async () => {
        const payload = buildPayload();
        if (!payload.observed.building.postcode || !payload.observed.building.house_number) {
          status.textContent = 'Vul eerst het gebouwadres volledig in.';
          return;
        }
        if (!payload.observed.rooms.length) {
          status.textContent = 'Voeg eerst minimaal één ruimte toe.';
          return;
        }
        if (!payload.observed.frames.length || payload.observed.frames.some((frame) => !frame.room_id || !frame.geometry)) {
          status.textContent = 'Sla eerst minimaal één compleet kozijn op en koppel ieder kozijn aan een ruimte.';
          return;
        }

        button.disabled = true;
        status.textContent = 'Aanvraag veilig naar BREBO versturen…';
        try {
          const response = await fetch('/europakozijn/api/request', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(payload),
          });
          const result = await response.json().catch(() => ({}));
          if (!response.ok || result.status !== 'ok') {
            throw new Error(result?.error?.code || 'submit_failed');
          }
          status.textContent = `Aanvraag ontvangen door BREBO. Referentie: ${result.request_id}`;
          root.dispatchEvent(new CustomEvent('ek:request-submitted', { bubbles: true, detail: result }));
        }
        catch (error) {
          status.textContent = 'De aanvraag kon nog niet worden verstuurd. Uw configuratie blijft bewaard; probeer het opnieuw.';
        }
        finally {
          button.disabled = false;
        }
      });
    });
  }};
})(Drupal, once);
