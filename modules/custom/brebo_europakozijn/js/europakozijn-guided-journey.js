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

      const steps=[...form.querySelectorAll('.ek-step')];
      const shell=document.createElement('div'); shell.className='ek-journey'; shell.innerHTML='<nav class="ek-journey__nav" aria-label="Aanvraagstappen"><button type="button" data-ek-screen="build" class="is-active"><b>1</b><span>Kozijnen</span></button><button type="button" data-ek-screen="situation"><b>2</b><span>Situatie</span></button><button type="button" data-ek-screen="check"><b>3</b><span>Controle</span></button><button type="button" data-ek-screen="offer"><b>4</b><span>Offerte</span></button></nav><div class="ek-journey__actions"><button type="button" data-ek-back hidden>← Terug</button><button type="button" data-ek-forward>Verder →</button></div>';
      root.querySelector('.ek-configurator__header')?.after(shell);
      const nav=[...shell.querySelectorAll('[data-ek-screen]')],back=shell.querySelector('[data-ek-back]'),forward=shell.querySelector('[data-ek-forward]'); let screen=0;
      const buildSteps=steps.filter(s=>s!==contextStep); buildSteps.forEach((step,i)=>{step.classList.add('ek-step--accordion');const body=step.querySelector('.ek-step__body');const h=step.querySelector('h2');if(!body||!h)return;const toggle=document.createElement('button');toggle.type='button';toggle.className='ek-step__toggle';toggle.innerHTML=`<span>${h.textContent}</span><small data-ek-step-summary>${i===0?'Vast raam':'Open'}</small><b aria-hidden="true">⌄</b>`;step.insertBefore(toggle,body);h.hidden=true;toggle.addEventListener('click',()=>{const open=step.classList.toggle('is-open');toggle.setAttribute('aria-expanded',String(open));});if(i===0)step.classList.add('is-open');});
      const summary=()=>{const val=n=>form.elements[n]?.value||'';const activeType=root.querySelector('[data-ek-type-choice].is-active')?.textContent?.trim()||'Kies type';const map=[activeType,`${val('width')} × ${val('height')} mm`,val('brand')||'Kies merk',`${val('colour')} · ${val('glass')}`];buildSteps.forEach((s,i)=>{const el=s.querySelector('[data-ek-step-summary]');if(el)el.textContent=map[i]||'Ingevuld';});};form.addEventListener('input',summary);form.addEventListener('change',summary);root.addEventListener('click',(event)=>{if(event.target.closest('[data-ek-type-choice]'))setTimeout(summary,0);});summary();
      const render=()=>{root.dataset.ekJourneyScreen=['build','situation','check','offer'][screen];nav.forEach((n,i)=>n.classList.toggle('is-active',i===screen));back.hidden=screen===0;forward.textContent=screen===3?'Offerte aanvragen':'Verder →';contextStep.classList.toggle('ek-journey-context-visible',screen===1);root.querySelector('[data-ek-request]')?.classList.toggle('ek-journey-request-visible',screen===3); if(screen===0){workspace.hidden=false;buildSteps.forEach(s=>s.hidden=false);contextStep.hidden=true;}else if(screen===1){workspace.hidden=true;buildSteps.forEach(s=>s.hidden=true);contextStep.hidden=false;}else{workspace.hidden=false;buildSteps.forEach(s=>s.hidden=true);contextStep.hidden=true;}}
      nav.forEach((n,i)=>n.addEventListener('click',()=>{screen=i;render();root.scrollIntoView({behavior:'smooth',block:'start'});}));back.addEventListener('click',()=>{screen=Math.max(0,screen-1);render();});forward.addEventListener('click',()=>{if(screen<3){screen++;render();root.scrollIntoView({behavior:'smooth',block:'start'});}else root.querySelector('[data-ek-request]')?.click();});render();
    });
  }};
})(Drupal, once);
