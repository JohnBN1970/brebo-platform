(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.breboEuropakozijnVisualPicker = {
    attach(context) {
      once('brebo-europakozijn-visual-picker', '[data-europakozijn-configurator]', context).forEach((root) => {
        const form = root.querySelector('[data-ek-form]');
        const functionPicker = root.querySelector('[data-ek-function-picker]');
        const drawing = root.querySelector('[data-ek-drawing]');
        const calibration = root.querySelector('[data-ek-calibration]');
        if (!form || !functionPicker || !drawing || !calibration) return;

        const extraTypes = [['klep','Klepraam'],['stulp','Stulpstel'],['schuifpui','Schuifpui']];
        extraTypes.forEach(([value,label]) => {
          if (functionPicker.querySelector(`[data-ek-function="${value}"]`)) return;
          const button=document.createElement('button'); button.type='button'; button.dataset.ekFunction=value; button.textContent=label; functionPicker.appendChild(button);
        });

        const section=document.createElement('section'); section.className='ek-step ek-step--visual-types';
        section.innerHTML=`<div class="ek-step__number">0</div><div class="ek-step__body"><h2>Wat wilt u maken?</h2><p class="ek-step__intro">Kies eerst het complete element. Daarna kunt u de indeling, vakken en openingsfuncties verder aanpassen.</p><div class="ek-type-grid" data-ek-type-grid>${card('vast','Vast raam',icon('vast'))}${card('draaikiep','Draai-kiep',icon('draaikiep'))}${card('klep','Klepraam',icon('klep'))}${card('deur','Enkele deur',icon('deur'))}${card('stulp','Stulpstel',icon('stulp'))}${card('schuifpui','Schuifpui',icon('schuifpui'))}</div><p class="ek-element-type-status" data-ek-element-type-status>Elementtype: Vast raam</p></div>`;
        form.prepend(section);
        const grid=section.querySelector('[data-ek-type-grid]'); const status=section.querySelector('[data-ek-element-type-status]');
        let elementType='vast';
        const labels={vast:'Vast raam',draaikiep:'Draai-kiepraam',klep:'Klepraam',deur:'Enkele deur',stulp:'Stulpstel',schuifpui:'Schuifpui'};

        grid.querySelectorAll('[data-ek-type]').forEach((button)=>button.addEventListener('click',()=>{
          elementType=button.dataset.ekType; root.dataset.ekElementType=elementType; status.textContent=`Elementtype: ${labels[elementType]}`; syncCards(elementType);
          const target=functionPicker.querySelector(`[data-ek-function="${elementType}"]`); if(target) target.click();
          window.requestAnimationFrame(renderSpecialOperations);
        }));
        root.dataset.ekElementType=elementType;

        const observer=new MutationObserver(()=>window.requestAnimationFrame(renderSpecialOperations)); observer.observe(drawing,{childList:true,subtree:true}); renderSpecialOperations();

        function syncCards(type){grid.querySelectorAll('[data-ek-type]').forEach((b)=>{const active=b.dataset.ekType===type;b.classList.toggle('is-active',active);b.setAttribute('aria-pressed',active?'true':'false');});}
        function renderSpecialOperations(){
          drawing.querySelector('[data-ek-special-operations]')?.remove(); let payload; try{payload=JSON.parse(calibration.value||calibration.textContent||'{}');}catch(e){return;}
          const fields=payload?.geometry?.fields||[]; if(!fields.length)return; const ns='http://www.w3.org/2000/svg'; const group=document.createElementNS(ns,'g'); group.setAttribute('data-ek-special-operations',''); group.setAttribute('class','ek-special-operations');
          fields.forEach((fieldData)=>{if(!['klep','stulp','schuifpui'].includes(fieldData.function))return; const field=drawing.querySelector(`[data-field-key="${fieldData.id}"]`); if(!field)return; const x=+field.getAttribute('x'),y=+field.getAttribute('y'),w=+field.getAttribute('width'),h=+field.getAttribute('height'),inset=Math.min(14,Math.max(6,w*.05));
            const line=(x1,y1,x2,y2,klass='ek-special-operation__line')=>{const el=document.createElementNS(ns,'line');[['x1',x1],['y1',y1],['x2',x2],['y2',y2],['class',klass]].forEach(([k,v])=>el.setAttribute(k,v));group.appendChild(el);};
            if(fieldData.function==='klep'){line(x+inset,y+inset,x+w/2,y+h-inset);line(x+w-inset,y+inset,x+w/2,y+h-inset);}
            if(fieldData.function==='stulp'){const mid=x+w/2;line(mid,y+inset,mid,y+h-inset,'ek-special-operation__center');line(x+inset,y+inset,mid,y+h/2);line(x+inset,y+h-inset,mid,y+h/2);line(x+w-inset,y+inset,mid,y+h/2);line(x+w-inset,y+h-inset,mid,y+h/2);}
            if(fieldData.function==='schuifpui'){const mid=x+w/2;line(mid,y+inset,mid,y+h-inset,'ek-special-operation__center');const arrow=document.createElementNS(ns,'path');arrow.setAttribute('d',`M ${x+w*.34} ${y+h*.5} H ${x+w*.68} M ${x+w*.68} ${y+h*.5} l -12 -8 M ${x+w*.68} ${y+h*.5} l -12 8`);arrow.setAttribute('class','ek-special-operation__arrow');group.appendChild(arrow);}
          }); drawing.appendChild(group);
        }
        function card(value,label,svg){return `<button type="button" class="ek-type-card${value==='vast'?' is-active':''}" data-ek-type="${value}" aria-pressed="${value==='vast'?'true':'false'}"><span class="ek-type-card__visual" aria-hidden="true">${svg}</span><span>${label}</span></button>`;}
        function icon(type){const base='<rect x="8" y="6" width="64" height="68" rx="2" class="f"/><rect x="15" y="13" width="50" height="54" class="g"/>';if(type==='vast')return `<svg viewBox="0 0 80 80">${base}</svg>`;if(type==='draaikiep')return `<svg viewBox="0 0 80 80">${base}<path d="M16 14 L64 40 L16 66 M16 66 L40 14 L64 66" class="o"/></svg>`;if(type==='klep')return `<svg viewBox="0 0 80 80">${base}<path d="M16 14 L40 66 L64 14" class="o"/></svg>`;if(type==='deur')return `<svg viewBox="0 0 80 80">${base}<path d="M16 14 L64 40 L16 66" class="o"/><circle cx="60" cy="40" r="2" class="h"/></svg>`;if(type==='stulp')return `<svg viewBox="0 0 80 80">${base}<path d="M40 13 V67 M16 14 L40 40 L16 66 M64 14 L40 40 L64 66" class="o"/></svg>`;return `<svg viewBox="0 0 80 80">${base}<path d="M40 13 V67 M24 40 H58 M58 40 l-9-7 M58 40 l-9 7" class="o"/></svg>`;}
      });
    }
  };
})(Drupal, once);
