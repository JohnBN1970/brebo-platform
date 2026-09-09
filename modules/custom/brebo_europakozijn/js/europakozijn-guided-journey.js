(function (Drupal, once) {
  'use strict';
  Drupal.behaviors.breboEuropakozijnGuidedJourney = { attach(context) {
    once('brebo-europakozijn-guided-journey', '[data-europakozijn-configurator]', context).forEach((root) => {
      const form=root.querySelector('[data-ek-form]'); const workspace=root.querySelector('.ek-builder__workspace'); if(!form||!workspace)return;
      const contextStep=root.querySelector('[data-ek-context-step]'); if(!contextStep)return;

      // Indeling is drawing behaviour, not a form step. Keep the existing controls and
      // event bindings, but place them next to the drawing and remove the accordion step.
      const layoutStep=[...form.querySelectorAll('.ek-step')].find(step=>step.querySelector('h2')?.textContent.trim()==='Indeling');
      if(layoutStep){
        const layoutBody=layoutStep.querySelector('.ek-step__body');
        const layoutTools=document.createElement('section');
        layoutTools.className='ek-layout-workspace';
        layoutTools.innerHTML='<div class="ek-layout-workspace__head"><div><strong>Tekening bewerken</strong><span>Voeg alleen profielen toe wanneer uw kozijn extra vakken nodig heeft.</span></div><label class="ek-view-side">Aanzicht <select data-ek-view-side aria-label="Aanzicht van het kozijn"><option value="exterior" selected>Van buiten gezien</option><option value="interior">Van binnen gezien</option></select></label></div><div class="ek-view-side__label" data-ek-view-side-label>AANZICHT BUITENZIJDE</div><div class="ek-layout-workspace__controls" data-ek-layout-controls></div>';
        const controls=layoutTools.querySelector('[data-ek-layout-controls]');
        [...layoutBody.children].forEach(child=>{if(child.matches('h2,.ek-step__intro'))return;controls.appendChild(child);});
        workspace.querySelector('.ek-canvas')?.before(layoutTools);
        layoutStep.remove();
        const viewSide=layoutTools.querySelector('[data-ek-view-side]');
        const viewLabel=layoutTools.querySelector('[data-ek-view-side-label]');
        viewSide?.addEventListener('change',()=>{const exterior=viewSide.value==='exterior';viewLabel.textContent=exterior?'AANZICHT BUITENZIJDE':'AANZICHT BINNENZIJDE';root.dataset.ekViewSide=viewSide.value;root.dispatchEvent(new CustomEvent('ek:view-side-changed',{bubbles:true,detail:{view_side:viewSide.value}}));});
        root.dataset.ekViewSide='exterior';
      }

      const controlPanel=document.createElement('section');
      controlPanel.className='ek-control-screen';
      controlPanel.hidden=true;
      controlPanel.innerHTML='<header class="ek-control-screen__head"><span>Technische controle</span><h2>We controleren wat nodig is voor een betrouwbare prijs</h2><p>Uw kozijn en locatie zijn bekend. We tonen alleen wat nog gecontroleerd of aangevuld moet worden.</p></header><div class="ek-control-screen__grid" data-ek-control-grid></div><div class="ek-control-screen__result" data-ek-control-result></div>';
      workspace.appendChild(controlPanel);
      const controlGrid=controlPanel.querySelector('[data-ek-control-grid]');
      const controlResult=controlPanel.querySelector('[data-ek-control-result]');

      const val=(name)=>String(form.elements.namedItem(name)?.value||'').trim();
      const human={building_type:{tussenwoning:'Tussenwoning',hoekwoning:'Hoekwoning',vrijstaand:'Vrijstaande woning',appartement:'Appartement / woongebouw',overig:'Anders'},floor_level:{'0':'Begane grond','1':'1e verdieping','2':'2e verdieping','3':'3e verdieping','4':'4e verdieping','5_plus':'5e verdieping of hoger'},room_type:{woonkamer:'Woonkamer',slaapkamer:'Slaapkamer',keuken:'Keuken',badkamer:'Badkamer',werkkamer:'Werkkamer',overig:'Andere ruimte'},ventilation_system:{natural:'Natuurlijke ventilatie',mechanical_extract:'Mechanische afvoer',balanced:'Balansventilatie / WTW',other:'Anders'}};
      const labelFor=(name,value)=>human[name]?.[value]||value;
      const card=(title,value,state,help)=>{const el=document.createElement('article');el.className=`ek-control-card is-${state}`;el.innerHTML=`<span>${title}</span><strong>${value}</strong><small>${help}</small>`;return el;};
      const renderControl=()=>{
        controlGrid.replaceChildren();
        const addressOk=Boolean(val('bag_adresseerbaar_object_id'));
        const address=[val('street'),val('house_number'),val('postcode'),val('city')].filter(Boolean).join(' ');
        const sizeOk=Number(val('width'))>0&&Number(val('height'))>0;
        const floorOk=Boolean(val('floor_level'));
        const roomOk=Boolean(val('room_type'));
        const areaOk=Number(val('room_area_m2'))>0;
        const ventilationOk=Boolean(val('ventilation_system'));
        const requiredMissing=[];
        if(!addressOk)requiredMissing.push('adres');
        if(!floorOk)requiredMissing.push('verdieping');
        if(!roomOk)requiredMissing.push('ruimte');
        if(!areaOk)requiredMissing.push('oppervlakte ruimte');
        if(!ventilationOk)requiredMissing.push('ventilatie');
        controlGrid.append(
          card('Kozijn',sizeOk?`${val('width')} × ${val('height')} mm`:'Maat ontbreekt',sizeOk?'ok':'warn',sizeOk?`${val('brand')||'Merk nog niet gekozen'} · ${val('glass')||'Glas nog niet gekozen'}`:'Vul eerst de kozijnmaat in.'),
          card('Locatie',addressOk?(address||'Officieel adres gevonden'):'Nog niet bevestigd',addressOk?'ok':'warn',addressOk?'Adres gecontroleerd via PDOK/BAG.':'Vul postcode en huisnummer in bij Situatie.'),
          card('Ruimte',roomOk?labelFor('room_type',val('room_type')):'Nog niet gekozen',roomOk&&areaOk?'ok':'warn',roomOk&&areaOk?`${val('room_area_m2')} m² · ${floorOk?labelFor('floor_level',val('floor_level')):'verdieping ontbreekt'}`:'Ruimtetype en oppervlakte zijn nodig voor de beoordeling.'),
          card('Ventilatie',ventilationOk?labelFor('ventilation_system',val('ventilation_system')):'Nog niet bekend',ventilationOk?'ok':'warn',ventilationOk?'Wordt meegenomen in de technische beoordeling.':'Geef aan hoe de woning nu wordt geventileerd.')
        );
        if(requiredMissing.length===0){
          controlResult.className='ek-control-screen__result is-ready';
          controlResult.innerHTML='<strong>Technische context compleet</strong><span>De invoer is compleet genoeg om de technische regels en prijsberekening uit te voeren. De definitieve productkeuze volgt uit die controle.</span>';
        }else{
          controlResult.className='ek-control-screen__result is-blocked';
          controlResult.innerHTML=`<strong>Nog niet klaar voor prijsberekening</strong><span>Vul eerst aan: ${requiredMissing.join(', ')}.</span>`;
        }
      };
      form.addEventListener('input',renderControl);form.addEventListener('change',renderControl);root.addEventListener('ek:address-resolved',renderControl);renderControl();

      const steps=[...form.querySelectorAll('.ek-step')];
      const shell=document.createElement('div'); shell.className='ek-journey'; shell.innerHTML='<nav class="ek-journey__nav" aria-label="Aanvraagstappen"><button type="button" data-ek-screen="build" class="is-active"><b>1</b><span>Kozijnen</span></button><button type="button" data-ek-screen="situation"><b>2</b><span>Situatie</span></button><button type="button" data-ek-screen="check"><b>3</b><span>Controle</span></button><button type="button" data-ek-screen="offer"><b>4</b><span>Offerte</span></button></nav><div class="ek-journey__actions"><button type="button" data-ek-back hidden>← Terug</button><button type="button" data-ek-forward>Verder →</button></div>';
      root.querySelector('.ek-configurator__header')?.after(shell);
      const nav=[...shell.querySelectorAll('[data-ek-screen]')],back=shell.querySelector('[data-ek-back]'),forward=shell.querySelector('[data-ek-forward]'); let screen=0;
      const buildSteps=steps.filter(s=>s!==contextStep); buildSteps.forEach((step,i)=>{step.classList.add('ek-step--accordion');const body=step.querySelector('.ek-step__body');const h=step.querySelector('h2');if(!body||!h)return;const toggle=document.createElement('button');toggle.type='button';toggle.className='ek-step__toggle';toggle.innerHTML=`<span>${h.textContent}</span><small data-ek-step-summary>${i===0?'Vast raam':'Open'}</small><b aria-hidden="true">⌄</b>`;step.insertBefore(toggle,body);h.hidden=true;toggle.addEventListener('click',()=>{const open=step.classList.toggle('is-open');toggle.setAttribute('aria-expanded',String(open));});if(i===0)step.classList.add('is-open');});
      const summary=()=>{const activeType=root.querySelector('[data-ek-type-choice].is-active')?.textContent?.trim()||'Kies type';const map=[activeType,`${val('width')} × ${val('height')} mm`,val('brand')||'Kies merk',`${val('colour')} · ${val('glass')}`];buildSteps.forEach((s,i)=>{const el=s.querySelector('[data-ek-step-summary]');if(el)el.textContent=map[i]||'Ingevuld';});};form.addEventListener('input',summary);form.addEventListener('change',summary);root.addEventListener('click',(event)=>{if(event.target.closest('[data-ek-type-choice]'))setTimeout(summary,0);});summary();
      const render=()=>{root.dataset.ekJourneyScreen=['build','situation','check','offer'][screen];nav.forEach((n,i)=>n.classList.toggle('is-active',i===screen));back.hidden=screen===0;forward.textContent=screen===3?'Offerte aanvragen':'Verder →';contextStep.classList.toggle('ek-journey-context-visible',screen===1);root.querySelector('[data-ek-request]')?.classList.toggle('ek-journey-request-visible',screen===3);controlPanel.hidden=screen!==2;if(screen===2)renderControl(); if(screen===0){workspace.hidden=false;buildSteps.forEach(s=>s.hidden=false);contextStep.hidden=true;}else if(screen===1){workspace.hidden=true;buildSteps.forEach(s=>s.hidden=true);contextStep.hidden=false;}else{workspace.hidden=false;buildSteps.forEach(s=>s.hidden=true);contextStep.hidden=true;}}
      nav.forEach((n,i)=>n.addEventListener('click',()=>{screen=i;render();root.scrollIntoView({behavior:'smooth',block:'start'});}));back.addEventListener('click',()=>{screen=Math.max(0,screen-1);render();});forward.addEventListener('click',()=>{if(screen<3){screen++;render();root.scrollIntoView({behavior:'smooth',block:'start'});}else root.querySelector('[data-ek-request]')?.click();});render();
    });
  }};
})(Drupal, once);
