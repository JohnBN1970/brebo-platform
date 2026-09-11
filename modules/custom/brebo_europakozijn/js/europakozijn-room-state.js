(function (Drupal, once) {
  'use strict';
  Drupal.behaviors.breboEuropakozijnRoomState = { attach(context) {
    once('brebo-europakozijn-room-state', '[data-europakozijn-configurator]', context).forEach((root) => {
      const form=root.querySelector('[data-ek-form]'); if(!form)return;
      const roomId=form.elements.namedItem('room_id'),roomType=form.elements.namedItem('room_type'),roomName=form.elements.namedItem('room_name'),roomArea=form.elements.namedItem('room_area_m2'),ventilation=form.elements.namedItem('ventilation_system'),frameRoom=form.elements.namedItem('frame_room_id');
      const list=root.querySelector('[data-ek-room-list]'); if(!roomId||!roomType||!roomName||!roomArea||!ventilation||!frameRoom||!list)return;
      const storageKey='brebo_europakozijn_rooms_v1'; let state={version:1,rooms:[],active:null};
      try{const stored=JSON.parse(localStorage.getItem(storageKey)||'null');if(stored?.version===1&&Array.isArray(stored.rooms))state=stored;}catch(e){}
      const esc=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
      const typeLabel=v=>({woonkamer:'Woonkamer',slaapkamer:'Slaapkamer',keuken:'Keuken',badkamer:'Badkamer',werkkamer:'Werkkamer',overig:'Andere ruimte'})[v]||v;
      const persist=()=>localStorage.setItem(storageKey,JSON.stringify(state));
      const roomLabel=r=>r.name||typeLabel(r.type)||'Ruimte';
      const syncSelect=()=>{const current=frameRoom.value;frameRoom.innerHTML='<option value="">Kies ruimte</option>'+state.rooms.map(r=>`<option value="${esc(r.id)}">${esc(roomLabel(r))}</option>`).join('');if(state.rooms.some(r=>r.id===current))frameRoom.value=current;else if(state.active&&state.rooms.some(r=>r.id===state.active))frameRoom.value=state.active;};
      const load=id=>{const r=state.rooms.find(x=>x.id===id);if(!r)return;state.active=id;roomId.value=id;roomType.value=r.type||'';roomName.value=r.name||'';roomArea.value=r.area_m2||'';ventilation.value=r.ventilation_system||'';frameRoom.value=id;persist();render();form.dispatchEvent(new Event('change',{bubbles:true}));};
      const save=()=>{if(!roomType.value||!(Number(roomArea.value)>0))return false;const id=roomId.value||`room-${Date.now()}`;const r={id,type:roomType.value,name:roomName.value.trim()||typeLabel(roomType.value),area_m2:Number(roomArea.value),ventilation_system:ventilation.value};const i=state.rooms.findIndex(x=>x.id===id);if(i>=0)state.rooms[i]=r;else state.rooms.push(r);state.active=id;roomId.value=id;frameRoom.value=id;persist();render();root.dispatchEvent(new CustomEvent('ek:rooms-changed',{bubbles:true,detail:{rooms:state.rooms,active_room_id:id}}));return true;};
      const fresh=()=>{state.active=null;roomId.value='';roomType.value='';roomName.value='';roomArea.value='';ventilation.value='';render();roomType.focus();};
      function render(){list.replaceChildren();state.rooms.forEach(r=>{const el=document.createElement('div');el.className=`ek-profile-list__item${r.id===state.active?' is-active':''}`;el.innerHTML=`<button type="button" data-ek-open-room><strong>${esc(roomLabel(r))}</strong><span>${esc(r.area_m2)} m²</span></button>`;el.querySelector('[data-ek-open-room]').addEventListener('click',()=>load(r.id));list.appendChild(el);});if(!state.rooms.length){const empty=document.createElement('p');empty.className='ek-empty';empty.textContent='Nog geen ruimtes opgeslagen.';list.appendChild(empty);}syncSelect();}
      root.querySelector('[data-ek-save-room]')?.addEventListener('click',save);root.querySelector('[data-ek-new-room]')?.addEventListener('click',fresh);frameRoom.addEventListener('change',()=>{if(frameRoom.value)state.active=frameRoom.value;persist();});
      render();if(state.active)load(state.active);root.breboEuropakozijnRooms={getRooms:()=>JSON.parse(JSON.stringify(state.rooms)),getRoom:id=>state.rooms.find(r=>r.id===id)||null};
    });
  }};
})(Drupal, once);
