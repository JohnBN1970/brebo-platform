(function (Drupal, once) {
  'use strict';
  Drupal.behaviors.breboEuropakozijnRequestState = { attach(context) {
    once('brebo-europakozijn-request-state', '[data-europakozijn-configurator]', context).forEach((root) => {
      const form = root.querySelector('[data-ek-form]');
      const calibration = root.querySelector('[data-ek-calibration]');
      if (!form || !calibration) return;
      const storageKey = 'brebo_europakozijn_request_v1';
      let request = {version: 1, frames: [], active: 0};
      try { const stored = JSON.parse(localStorage.getItem(storageKey) || 'null'); if (stored && stored.version === 1 && Array.isArray(stored.frames)) request = stored; } catch (e) {}
      const panel = document.createElement('section');
      panel.className = 'ek-request-state';
      panel.innerHTML = '<div class="ek-request-state__head"><div><strong>Kozijnen in deze aanvraag</strong><span data-ek-request-count></span></div><label>Aantal <input type="number" min="1" max="99" step="1" value="1" data-ek-quantity></label></div><div class="ek-request-state__list" data-ek-request-list></div><div class="ek-request-state__actions"><button type="button" data-ek-save-frame>Opslaan</button><button type="button" class="ek-request-state__next" data-ek-next-frame>＋ Volgende kozijn</button></div><p class="ek-request-state__hint">Uw kozijnstaat wordt op dit apparaat tussentijds bewaard. De definitieve aanvraag wordt pas verzonden wanneer u daarvoor kiest.</p>';
      form.appendChild(panel);
      const quantity = panel.querySelector('[data-ek-quantity]'); const list = panel.querySelector('[data-ek-request-list]'); const count = panel.querySelector('[data-ek-request-count]');
      const payload = () => { try { return JSON.parse(calibration.textContent || '{}'); } catch (e) { return {}; } };
      const persist = () => localStorage.setItem(storageKey, JSON.stringify(request));
      const label = (i) => `K${String(i + 1).padStart(2, '0')}`;
      const renderList = () => {
        list.replaceChildren();
        request.frames.forEach((frame, index) => {
          const d = frame.configuration?.geometry?.outer_dimensions || {};
          const row = document.createElement('button'); row.type = 'button'; row.className = `ek-request-state__item${index === request.active ? ' is-active' : ''}`;
          row.innerHTML = `<strong>${label(index)}</strong><span>${d.width_mm || '—'} × ${d.height_mm || '—'} mm</span><b>×${frame.quantity}</b><em>✓</em>`;
          row.addEventListener('click', () => { request.active = index; quantity.value = String(frame.quantity || 1); persist(); renderList(); }); list.appendChild(row);
        });
        const total = request.frames.reduce((sum, frame) => sum + (Number(frame.quantity) || 1), 0); count.textContent = request.frames.length ? `${request.frames.length} typen · ${total} kozijnen` : 'Nog geen kozijnen opgeslagen';
      };
      const saveCurrent = () => {
        const current = payload(); if (!current?.geometry) return false;
        request.frames[request.active] = {id: request.frames[request.active]?.id || `ek-${Date.now()}-${request.active}`, position: label(request.active), quantity: Math.max(1, Number(quantity.value) || 1), saved_at: new Date().toISOString(), configuration: current};
        persist(); renderList(); return true;
      };
      panel.querySelector('[data-ek-save-frame]').addEventListener('click', saveCurrent);
      panel.querySelector('[data-ek-next-frame]').addEventListener('click', () => { if (!saveCurrent()) return; request.active = request.frames.length; quantity.value = '1'; persist(); renderList(); form.querySelector('[name="width"]')?.focus(); });
      if (request.frames[request.active]) quantity.value = String(request.frames[request.active].quantity || 1); renderList();
    });
  }};
})(Drupal, once);
