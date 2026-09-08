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
      const panel = document.createElement('section'); panel.className = 'ek-request-state';
      panel.innerHTML = '<div class="ek-request-state__head"><div><strong>Kozijnen in deze aanvraag</strong><span data-ek-request-count></span></div><label>Aantal <input type="number" min="1" max="99" step="1" value="1" data-ek-quantity></label></div><div class="ek-request-state__list" data-ek-request-list></div><div class="ek-request-state__actions"><button type="button" data-ek-save-frame>Opslaan</button><button type="button" class="ek-request-state__next" data-ek-next-frame>＋ Volgende kozijn</button></div><p class="ek-request-state__hint">Klik op een opgeslagen kozijn om het opnieuw in de tekenaar te openen. Uw kozijnstaat wordt op dit apparaat tussentijds bewaard.</p>';
      form.appendChild(panel);
      const quantity = panel.querySelector('[data-ek-quantity]'); const list = panel.querySelector('[data-ek-request-list]'); const count = panel.querySelector('[data-ek-request-count]');
      const payload = () => { try { return JSON.parse(calibration.textContent || '{}'); } catch (e) { return {}; } };
      const persist = () => localStorage.setItem(storageKey, JSON.stringify(request)); const label = (i) => `K${String(i + 1).padStart(2, '0')}`;
      const setField = (name, value) => { const field = form.elements.namedItem(name); if (!field || value === undefined || value === null) return; field.value = String(value); field.dispatchEvent(new Event('change', {bubbles: true})); };
      const loadFrame = (frame) => {
        const c = frame.configuration || {}; const d = c.geometry?.outer_dimensions || {}; const p = c.product_selection || {}; const b = c.building_context || {}; const r = c.room_context || {};
        setField('width', d.width_mm); setField('height', d.height_mm); setField('brand', p.brand); setField('joint_type', p.joint_type); setField('rebate_type', p.rebate_type); setField('colour', p.colour); setField('glass', p.glass);
        setField('postcode', b.postcode); setField('house_number', b.house_number); setField('building_type', b.building_type); setField('floor_level', b.floor_level); setField('room_type', r.room_type); setField('room_area_m2', r.area_m2); setField('ventilation_system', r.ventilation_system);
        quantity.value = String(frame.quantity || 1);
        root.dispatchEvent(new CustomEvent('ek:load-configuration', {bubbles: true, detail: c}));
      };
      const saveCurrent = () => { const current = payload(); if (!current?.geometry) return false; request.frames[request.active] = {id: request.frames[request.active]?.id || `ek-${Date.now()}-${request.active}`, position: label(request.active), quantity: Math.max(1, Number(quantity.value) || 1), saved_at: new Date().toISOString(), configuration: current}; persist(); renderList(); return true; };
      const removeFrame = (index) => { request.frames.splice(index, 1); request.active = Math.min(request.active, request.frames.length); request.frames.forEach((frame, i) => frame.position = label(i)); persist(); renderList(); };
      const duplicateFrame = (index) => { const source = request.frames[index]; if (!source) return; const copy = JSON.parse(JSON.stringify(source)); copy.id = `ek-${Date.now()}-${request.frames.length}`; copy.saved_at = new Date().toISOString(); request.frames.push(copy); request.active = request.frames.length - 1; request.frames.forEach((frame, i) => frame.position = label(i)); persist(); loadFrame(copy); renderList(); };
      function renderList() {
        list.replaceChildren(); request.frames.forEach((frame, index) => { const d = frame.configuration?.geometry?.outer_dimensions || {}; const wrap = document.createElement('div'); wrap.className = `ek-request-state__item${index === request.active ? ' is-active' : ''}`; wrap.innerHTML = `<button type="button" class="ek-request-state__open"><strong>${label(index)}</strong><span>${d.width_mm || '—'} × ${d.height_mm || '—'} mm</span><b>×${frame.quantity}</b></button><button type="button" class="ek-request-state__copy" title="Dupliceren" aria-label="${label(index)} dupliceren">⧉</button><button type="button" class="ek-request-state__delete" title="Verwijderen" aria-label="${label(index)} verwijderen">×</button>`;
          wrap.querySelector('.ek-request-state__open').addEventListener('click', () => { request.active = index; persist(); loadFrame(frame); renderList(); }); wrap.querySelector('.ek-request-state__copy').addEventListener('click', () => duplicateFrame(index)); wrap.querySelector('.ek-request-state__delete').addEventListener('click', () => removeFrame(index)); list.appendChild(wrap); });
        const total = request.frames.reduce((sum, frame) => sum + (Number(frame.quantity) || 1), 0); count.textContent = request.frames.length ? `${request.frames.length} typen · ${total} kozijnen` : 'Nog geen kozijnen opgeslagen';
      }
      panel.querySelector('[data-ek-save-frame]').addEventListener('click', saveCurrent); panel.querySelector('[data-ek-next-frame]').addEventListener('click', () => { if (!saveCurrent()) return; request.active = request.frames.length; quantity.value = '1'; persist(); renderList(); form.querySelector('[name="width"]')?.focus(); });
      if (request.frames[request.active]) quantity.value = String(request.frames[request.active].quantity || 1); renderList();
    });
  }};
})(Drupal, once);
