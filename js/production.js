/* Real AMSA API bindings. Browser holds only unsent ballot selections temporarily. */
(() => {
 'use strict';
 const $=(q,root=document)=>root.querySelector(q), $$=(q,root=document)=>Array.from(root.querySelectorAll(q));
 const escape=s=>String(s??'').replace(/[&<>"']/g,x=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[x]));
 const csrf=$('meta[name="csrf-token"]')?.content||'';
 const request=async(action,payload=null,params={})=>{
   const query=new URLSearchParams({action,...params});const url='../api/index.php?'+query.toString();
   const res=await fetch(url,{method:payload===null?'GET':'POST',credentials:'same-origin',cache:'no-store',headers:payload===null?{}:{'Content-Type':'application/json','X-CSRF-Token':csrf},body:payload===null?undefined:JSON.stringify(payload)});
   const data=await res.json().catch(()=>({ok:false,error:{message:'The server returned an invalid response.'}}));
   if(!res.ok||!data.ok)throw Error(data.error?.message||'Request failed');
   return data.data;
 };
  const message=(id,t)=>{const e=$(id);if(e){e.textContent=t;e.classList.toggle('d-none',!t);}};
  const modal=(id)=>{
    const element=$(id);if(!element)return {show(){},hide(){}};
    if(window.bootstrap?.Modal)return window.bootstrap.Modal.getOrCreateInstance(element);
    const close=()=>{element.classList.remove('show','amsa-modal-fallback');element.style.display='none';element.setAttribute('aria-hidden','true');document.body.classList.remove('modal-open');};
    if(!element.dataset.fallbackBound){element.dataset.fallbackBound='true';element.addEventListener('click',event=>{if(event.target===element||event.target.closest('[data-bs-dismiss="modal"]'))close();});document.addEventListener('keydown',event=>{if(event.key==='Escape'&&element.classList.contains('show'))close();});}
    return {show(){element.style.display='block';element.classList.add('show','amsa-modal-fallback');element.removeAttribute('aria-hidden');element.setAttribute('aria-modal','true');element.setAttribute('role','dialog');document.body.classList.add('modal-open');element.querySelector('button,input,select,textarea')?.focus();},hide:close};
  };
  let activeDialog=null;
  function ensureDialog(){
    let layer=$('#amsa-dialog-layer');if(layer)return layer;
    layer=document.createElement('div');layer.id='amsa-dialog-layer';layer.className='amsa-dialog-layer';layer.hidden=true;
    layer.innerHTML=`<div class="amsa-dialog" role="dialog" aria-modal="true" aria-labelledby="amsa-dialog-title" aria-describedby="amsa-dialog-message"><div class="amsa-dialog-icon" aria-hidden="true"><i class="fa-solid fa-circle-info"></i></div><div class="amsa-dialog-copy"><h2 id="amsa-dialog-title"></h2><p id="amsa-dialog-message"></p></div><div class="amsa-dialog-actions"><button type="button" class="btn-amsa btn-amsa-outline" data-dialog-cancel>Cancel</button><button type="button" class="btn-amsa btn-amsa-primary" data-dialog-confirm>Continue</button></div></div>`;
    document.body.append(layer);return layer;
  }
  function dialog({title='AMSA AIU Election',message:body='',confirmText='OK',cancelText='',tone='info'}={}){
    if(activeDialog)activeDialog(false);
    const layer=ensureDialog(),box=$('.amsa-dialog',layer),titleEl=$('#amsa-dialog-title',layer),messageEl=$('#amsa-dialog-message',layer),confirm=$('[data-dialog-confirm]',layer),cancel=$('[data-dialog-cancel]',layer),prior=document.activeElement;
    titleEl.textContent=title;messageEl.textContent=body;confirm.textContent=confirmText;cancel.textContent=cancelText||'Cancel';cancel.hidden=!cancelText;box.dataset.tone=tone;layer.hidden=false;document.body.classList.add('dialog-open');
    return new Promise(resolve=>{
      let settled=false;
      const finish=value=>{if(settled)return;settled=true;layer.hidden=true;titleEl.textContent='';messageEl.textContent='';document.body.classList.remove('dialog-open');document.removeEventListener('keydown',keys,true);activeDialog=null;prior?.focus?.();resolve(value);};
      const keys=e=>{if(e.key==='Escape'&&cancelText){e.preventDefault();finish(false);}if(e.key==='Tab'){const items=$$('button:not([hidden])',box);if(!items.length)return;const first=items[0],last=items.at(-1);if(e.shiftKey&&document.activeElement===first){e.preventDefault();last.focus();}else if(!e.shiftKey&&document.activeElement===last){e.preventDefault();first.focus();}}};
      activeDialog=finish;confirm.onclick=()=>finish(true);cancel.onclick=()=>finish(false);layer.onclick=e=>{if(e.target===layer&&cancelText)finish(false);};document.addEventListener('keydown',keys,true);(cancelText?cancel:confirm).focus();
    });
  }
  const report=e=>{console.error(e);return dialog({title:'Unable to complete the action',message:e?.message||'Operation failed. Please try again.',confirmText:'Close',tone:'error'});};
  const setBusy=(btn,busy,busyText='Processing...')=>{if(!btn)return;if(busy){btn.dataset.originalText=btn.innerHTML;btn.disabled=true;btn.setAttribute('aria-busy','true');btn.textContent=busyText;}else{btn.disabled=false;btn.removeAttribute('aria-busy');if(btn.dataset.originalText){btn.innerHTML=btn.dataset.originalText;delete btn.dataset.originalText;}}};
  const paintStatus=(messageEl,badgeEl,isOpen,openText,closedText)=>{
    if(messageEl){messageEl.textContent=isOpen?openText:closedText;messageEl.classList.toggle('is-open',isOpen);messageEl.classList.toggle('is-closed',!isOpen);}
    if(badgeEl){badgeEl.textContent=isOpen?'OPEN':'CLOSED';badgeEl.classList.toggle('success',isOpen);badgeEl.classList.toggle('closed',!isOpen);}
  };
 let me=null;
 async function init(){
   const page=document.body.dataset.page||'';
   if(!page)return;
   if(!window.bootstrap?.Modal){$$('[data-bs-toggle="modal"][data-bs-target]').forEach(button=>button.addEventListener('click',event=>{event.preventDefault();modal(button.dataset.bsTarget).show();}));}
   try {me=await request('me');}catch(e){report(e);return;}
   const role=location.pathname.includes('/admin/')?'admin':'voter';
   if(!['login','admin-login','forgot-password','reset-password','register'].includes(page)) {
     if(!me.user||me.user.type!==role){location.replace(role==='admin'?'admin-login.php':'login.php');return;}
   }
   $$('[data-voter-name]').forEach(el=>el.textContent=me.user?.name||'Member');
   $$('.user-chip .fw-semibold,.user-chip .hide-mobile').forEach(el=>{
      if(/^(Member|Administrator|Demo Voter|Demo Administrator)$/.test(el.textContent.trim()))el.textContent=me.user?.name||el.textContent;
   });
   $$('[data-demo-signout]').forEach(el=>el.addEventListener('click',async e=>{e.preventDefault();try{await request('logout',{});location.assign(el.getAttribute('href'));}catch(err){report(err);}}));
   try {
     if(page==='login'||page==='admin-login')login(page==='admin-login');
     if(page==='member-management')await voterManagement();
     if(page==='member-dashboard')dashboard();
     if(page==='vote')await voting();
     if(page==='candidates')await publicCandidates();
     if(page==='member-results')await results(false);
     if(page==='candidate-management')await candidateManagement();
     if(page==='election-control')await control();
     if(page==='admin-results')await results(true);
     if(page==='admin-dashboard')await adminDashboard();
     if(page==='result-reveal')await reveal();
     if(page==='forgot-password')helpForm();
   }catch(e){report(e);}
 }
 function login(isAdmin){
  const form=$(isAdmin?'#admin-login-form':'#voter-login-form');if(!form)return;
   form.addEventListener('submit',async e=>{
     e.preventDefault(); const error=isAdmin?'#admin-login-error':'#login-error',btn=form.querySelector('[type="submit"]');message(error,'');setBusy(btn,true,'Signing in...');
     try{
       const d=await request(isAdmin?'admin-login':'voter-login',isAdmin?{username:form.elements.username.value.trim(),password:form.elements.password.value}:{student_id:form.elements.student_id.value.trim(),pin:form.elements.voting_pin.value});
       location.assign(isAdmin?'admin-dashboard.php':'member-dashboard.php');
     }catch(err){message(error,err.message);setBusy(btn,false);}
  });
 }
 function helpForm(){
  $('#forgot-pin-form')?.addEventListener('submit',async e=>{e.preventDefault();const form=e.currentTarget,btn=form.querySelector('[type="submit"]'),feedback=$('#forgot-pin-feedback');setBusy(btn,true,'Submitting request...');feedback?.classList.add('d-none');try{const d=await request('pin-recovery-request',{student_id:form.elements.student_id.value.trim()});if(feedback){feedback.textContent=d.message;feedback.classList.remove('d-none');}form.reset();await dialog({title:'Request Submitted',message:d.message,confirmText:'Done',tone:'success'});}catch(err){await report(err);}finally{setBusy(btn,false);}});
 }
 function dashboard(){
  const v=me.user,r=me.round;
  const heading=$('.member-welcome-panel .page-title');if(heading)heading.textContent='Welcome back, '+v.name+'.';
  const badge=$('#dashboard-ballot-status'),head=$('#dashboard-ballot-heading'),note=$('#dashboard-ballot-note'),a=$('#dashboard-ballot-action');
  if(v.has_voted){if(badge){badge.className='status-badge success';badge.textContent='VOTED';}if(head)head.textContent='Ballot submitted';if(note)note.textContent='Your ballot was accepted. You cannot vote again in this round.';if(a){a.textContent='View voting status';a.href='vote.php';}}
  else if(!v.eligible||r?.status!=='open'){if(badge)badge.textContent='Voting '+(r?.status||'unavailable');if(head)head.textContent='Voting is not available';if(note)note.textContent='Please check back when voting opens or ask the help counter.';if(a){a.textContent='Voting unavailable';a.href='vote.php';}}
 }
 const defaultCandidatePhoto='../assets/candidates/candidate-a.svg';
 const candidatePhoto=c=>/^\.\.\/uploads\/candidates\/[a-f0-9]{32}\.(?:jpg|png|webp)$/.test(c?.photo||c?.photo_path||'')?(c.photo||c.photo_path):defaultCandidatePhoto;
 const candidateAlt=c=>`${c?.name||'Candidate'} portrait`;
 let voterList=[],voterFilter='all';
 async function loadVoters(){const data=await request('voters');voterList=data.voters;renderVoters();}
 function renderVoters(){
  const needle=($('#voter-search')?.value||'').toLowerCase();
  const list=voterList.filter(v=>(v.full_name+' '+v.student_id).toLowerCase().includes(needle)&&(voterFilter==='all'||(voterFilter==='voted'?(+v.voted===1):(+v.voted===0))));
  $('#voter-table-body').innerHTML=list.map(v=>`<tr><td>${escape(v.full_name)}</td><td>${escape(v.student_id)}</td><td><span class="status-badge ${+v.voted?'success':''}">${+v.voted?'VOTED':'Not voted'}</span></td><td>${+v.active?'Active':'Disabled'}</td><td><button class="btn-amsa btn-amsa-outline btn-sm-amsa" data-voter-toggle="${v.id}" data-status="${+v.active?0:1}">${+v.active?'Disable':'Enable'}</button> ${+v.voted?'':`<button class="btn-amsa btn-amsa-gold btn-sm-amsa" data-reset="${v.id}">Reset PIN</button>`}</td></tr>`).join('')||'<tr><td colspan="5">No matching voters</td></tr>';
 }
 let oneTimePin='';
 function showPin(name,sid,pin){
  oneTimePin=pin;$('#issued-voter-name').textContent=name;$('#issued-voter-id').textContent=sid;$('#issued-pin-code').textContent=pin;
  $('#pin-handoff-mode').textContent='This PIN is not saved in readable form. It will disappear when this window closes.';
  const prev=$('.modal.show');if(prev&&prev.id!=='issuedPinModal'){prev.addEventListener('hidden.bs.modal',()=>modal('#issuedPinModal').show(),{once:true});modal('#'+prev.id).hide();}else modal('#issuedPinModal').show();
 }
 async function voterManagement(){
  const n=$('#voter-system-notice');if(n){n.hidden=false;n.textContent='Database-backed voter management. PINs are generated on the server and shown only at the attendance counter.';}
  await loadVoters();$('#voter-search')?.addEventListener('input',renderVoters);
  $$('[data-filter]').forEach(b=>b.addEventListener('click',()=>{voterFilter=b.dataset.filter;$$('[data-filter]').forEach(x=>x.classList.toggle('active',x===b));renderVoters();}));
    $('#create-voter-form')?.addEventListener('submit',async e=>{e.preventDefault();message('#create-voter-error','');const btn=e.currentTarget.querySelector('[type="submit"]');setBusy(btn,true,'Creating voter...');try{
     const full_name=$('#voter-name').value.trim(),student_id=$('#voter-student-id').value.trim();
     const d=await request('voter-create',{full_name,student_id,gender:$('#voter-gender').value,year_of_study:$('#voter-year').value,major:$('#voter-major').value,school:$('#voter-school').value,student_email:$('#voter-email').value});$('#create-voter-form').reset();await loadVoters();showPin(d.voter.full_name,d.voter.student_id,d.pin);
   }catch(err){message('#create-voter-error',err.message);}finally{setBusy(btn,false);}});
  $('#voter-table-body')?.addEventListener('click',async e=>{
    const reset=e.target.closest('[data-reset]'),toggle=e.target.closest('[data-voter-toggle]');
    if(reset){const v=voterList.find(v=>+v.id===+reset.dataset.reset);$('#reset-voter-id').value=v.id;$('#reset-pin-voter').textContent=v.full_name+' · '+v.student_id;$('#identity-verified').checked=false;modal('#resetPinModal').show();}
    if(toggle){const enabling=toggle.dataset.status==='1',v=voterList.find(item=>+item.id===+toggle.dataset.voterToggle);if(await dialog({title:enabling?'Enable Voter Account':'Disable Voter Account',message:`${v?.full_name||'This voter'} will ${enabling?'regain':'lose'} access to voter authentication.`,cancelText:'Cancel',confirmText:enabling?'Enable Account':'Disable Account',tone:enabling?'info':'danger'})){try{await request('voter-toggle',{id:+toggle.dataset.voterToggle,active:enabling});await loadVoters();}catch(err){report(err);}}}
  });
  $('#reset-pin-form')?.addEventListener('submit',async e=>{e.preventDefault();message('#reset-pin-error','');try{
    const id=+$('#reset-voter-id').value,v=voterList.find(v=>+v.id===id);
    const d=await request('voter-reset',{id,identity_verified:$('#identity-verified').checked});showPin(v.full_name,v.student_id,d.pin);
  }catch(err){message('#reset-pin-error',err.message);}});
  $('#issuedPinModal')?.addEventListener('hidden.bs.modal',()=>{oneTimePin='';$('#issued-pin-code').textContent='';});
  $('#copy-issued-pin')?.addEventListener('click',()=>{if(oneTimePin)navigator.clipboard?.writeText(oneTimePin).then(()=>$('#copy-issued-pin').textContent='Copied');});
 }
 async function voting(){
   const ballot=await request('ballot');
   if(ballot.has_voted||!ballot.eligible||ballot.round.status!=='open'){
     $('#vote-main')?.classList.add('d-none');$('#vote-success')?.classList.remove('d-none');
     const title=$('#vote-success h1');if(title)title.textContent=ballot.has_voted?'Ballot already submitted':'Voting is unavailable';
     const p=$('#vote-success .text-ink-muted');if(p)p.textContent=ballot.has_voted?'You have already voted in this round. Your choices cannot be changed.':'You cannot vote right now. Please contact the attendance counter if you need assistance.';
     return;
   }
   const sections=ballot.positions,choices={};
   function update(){
      $('#vote-sections').innerHTML=sections.map((pos,i)=>{const slug=String(pos.name).toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');return `<section id="position-${slug}" class="position-section mb-4"><div class="d-flex align-items-center gap-3 mb-4"><span class="position-num">${String(i+1).padStart(2,'0')}</span><h2 class="h5 fw-bold text-maroon mb-1">${escape(pos.name)}</h2></div><div class="row g-3">${pos.candidates.map(c=>`<div class="col-md-6 col-xl-4"><button type="button" aria-pressed="${choices[pos.id]===c.id}" class="candidate-card ${choices[pos.id]===c.id?'selected':''}" data-p="${pos.id}" data-c="${c.id}"><img class="ballot-candidate-photo" src="${escape(candidatePhoto(c))}" alt="${escape(candidateAlt(c))}" loading="lazy"><div class="candidate-copy"><div class="d-flex align-items-start justify-content-between gap-2"><h3 class="h6 fw-bold">${escape(c.name)}</h3><span class="select-dot" aria-hidden="true"><i class="fa-solid fa-check"></i></span></div><p class="small text-secondary">${escape(c.student_id)}</p><p class="small text-ink-muted">${escape(c.manifesto)}</p></div></button></div>`).join('')}</div></section>`;}).join('');
     const count=Object.keys(choices).length;$('#ballot-progress-value').textContent=`${count} of ${sections.length}`;$('#ballot-progress-bar').style.width=(100*count/sections.length)+'%';$('#ballot-remaining').textContent=count===sections.length?'Ready for review':`${sections.length-count} positions remaining`;$('#review-ballot-btn').disabled=count!==sections.length;
   }
   $('#vote-sections').addEventListener('click',e=>{const b=e.target.closest('[data-p][data-c]');if(!b)return;choices[b.dataset.p]=+b.dataset.c;update();});
   $('#review-ballot-btn')?.addEventListener('click',()=>{
     $('#review-list').innerHTML=sections.map(p=>{const c=p.candidates.find(c=>c.id===choices[p.id]);return `<div class="ballot-review-choice"><img src="${escape(candidatePhoto(c))}" alt="${escape(candidateAlt(c))}"><div><p class="small fw-bold text-maroon mb-1">${escape(p.name)}</p><p class="fw-bold mb-0">${escape(c?.name||'Not selected')}</p></div></div>`;}).join('');
     modal('#reviewModal').show();
   });
   $('#submit-final-ballot')?.addEventListener('click',async e=>{
      const btn=e.currentTarget;if(Object.keys(choices).length!==sections.length){await report(new Error('Select one candidate for every position before submitting.'));return;}
      const confirmed=await dialog({title:'Confirm Your Vote',message:'You are about to submit your final ballot. Once submitted, your vote cannot be changed or submitted again.',cancelText:'Review Ballot',confirmText:'Confirm & Submit Vote',tone:'warning'});if(!confirmed)return;
      setBusy(btn,true,'Submitting securely...');
     try{
       await request('submit-vote',{choices});modal('#reviewModal').hide();$('#vote-main').classList.add('d-none');$('#vote-success').classList.remove('d-none');
       const p=$('#vote-success .text-ink-muted');if(p)p.textContent='Your anonymous ballot was accepted and your account is marked VOTED. You cannot vote again in this round.';
       const sub=$('#vote-success .eyebrow');if(sub)sub.textContent='Ballot submitted';
       const sign=$('#vote-success a[href="login.php"]');if(sign){sign.textContent='Return to dashboard';sign.href='member-dashboard.php';}
      }catch(err){report(err);setBusy(btn,false);}
   });
    update();
    if(location.hash){requestAnimationFrame(()=>document.querySelector(location.hash)?.scrollIntoView({block:'start'}));}
  }
 function candidateCard(c,position){return `<article class="amsa-card public-candidate-card h-100"><div class="public-candidate-photo-wrap"><img class="public-candidate-photo" src="${escape(candidatePhoto(c))}" alt="${escape(candidateAlt(c))}" loading="lazy"></div><div class="card-pad"><p class="eyebrow mb-1">${escape(position||c.position_name||'Candidate')}</p><h3 class="public-candidate-name">${escape(c.name)}</h3><p class="small text-secondary mb-2">${escape(c.student_id||'')}</p><p class="small text-ink-muted candidate-manifesto-clamp">${escape(c.manifesto||'No manifesto provided.')}</p><button type="button" class="candidate-details-link" data-public-detail="${c.id}">Read More <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button></div></article>`;}
 async function publicCandidates(){
   const data=await request('ballot');
   const all=data.positions.flatMap(p=>p.candidates.map(c=>({...c,position_name:p.name})));
   $('#candidate-public-list').innerHTML=data.positions.map(p=>`<section class="mb-4"><h2 class="h4 text-maroon fw-bold mb-3">${escape(p.name)}</h2><div class="row g-3">${p.candidates.map(c=>`<div class="col-md-6 col-xl-4">${candidateCard(c,p.name)}</div>`).join('')}</div></section>`).join('');
   $('#candidate-public-list').addEventListener('click',e=>{const button=e.target.closest('[data-public-detail]');if(!button)return;const c=all.find(item=>+item.id===+button.dataset.publicDetail);if(!c)return;$('[data-detail-photo]').src=candidatePhoto(c);$('[data-detail-photo]').alt=candidateAlt(c);$('[data-detail-position]').textContent=c.position_name;$('[data-detail-name]').textContent=c.name;$('[data-detail-student-id]').textContent=c.student_id;$('[data-detail-manifesto]').textContent=c.manifesto||'No manifesto provided.';modal('#candidateProfileModal').show();});
 }
 async function candidateManagement(){
   const all=await Promise.all([request('candidate-list'),request('positions')]);let people=all[0].candidates,positions=all[1].positions;
   const select=$('#candidate-position');select.innerHTML=positions.map(p=>`<option value="${p.id}">${escape(p.name)}</option>`).join('');
   const form=$('#candidateModal form');let saving=false;
   let removePhoto=false,previewUrl='';
   function paintPreview(path,name='Candidate'){const img=$('#candidate-photo-preview');img.src=path||defaultCandidatePhoto;img.alt=`${name} photo preview`;$('#candidate-photo-state').textContent=path?'Saved or selected candidate photograph.':'A default avatar is used when no photo is saved.';}
   function fill(c=null){if(previewUrl)URL.revokeObjectURL(previewUrl);previewUrl='';removePhoto=false;$('#candidate-id').value=c?.id||'';$('#candidate-name').value=c?.name||'';$('#candidate-student-id').value=c?.student_id||'';$('#candidate-position').value=c?.position_id||positions[0].id;$('#candidate-manifesto').value=c?.manifesto||'';$('#candidate-photo').value='';$('#candidate-current-photo').value=c?.photo_path||'';$('#candidate-modal-title').textContent=c?'Edit Candidate':'Add Candidate';message('#candidate-form-error','');paintPreview(c?.photo_path||'',c?.name);}
   function display(){
     $('#candidate-management-list').innerHTML=positions.map(p=>`<section class="mb-4"><h2 class="h5 fw-bold text-maroon mb-3">${escape(p.name)}</h2><div class="row g-3">${people.filter(c=>+c.position_id===+p.id&&+c.active===1).map(c=>`<div class="col-md-6 col-xl-4"><article class="amsa-card admin-candidate-card h-100"><div class="admin-candidate-photo-wrap"><img class="admin-candidate-photo" src="${escape(candidatePhoto(c))}" alt="${escape(candidateAlt(c))}" loading="lazy"></div><div class="card-pad"><h3 class="admin-candidate-name">${escape(c.name)}</h3><p class="small text-secondary">${escape(c.student_id)}</p><p class="small text-ink-muted candidate-manifesto-clamp">${escape(c.manifesto||'')}</p><div class="d-flex flex-wrap gap-2"><button class="btn-amsa btn-amsa-outline btn-sm-amsa" data-edit="${c.id}">Edit</button><button class="btn-amsa btn-amsa-outline btn-sm-amsa" data-admin-detail="${c.id}">Read More</button><button class="btn-amsa btn-danger-soft btn-sm-amsa" data-disable="${c.id}">Deactivate</button></div></div></article></div>`).join('')||'<div class="small text-secondary">No candidates</div>'}</div></section>`).join('');
   }
   $$('[data-bs-target="#candidateModal"]').forEach(b=>b.addEventListener('click',()=>fill()));
   $('#candidate-management-list').addEventListener('click',async e=>{
     const edit=e.target.closest('[data-edit]'),disable=e.target.closest('[data-disable]'),detail=e.target.closest('[data-admin-detail]');
     if(edit){fill(people.find(c=>+c.id===+edit.dataset.edit));modal('#candidateModal').show();}
     if(detail){const c=people.find(item=>+item.id===+detail.dataset.adminDetail);$('[data-detail-photo]').src=candidatePhoto(c);$('[data-detail-photo]').alt=candidateAlt(c);$('[data-detail-position]').textContent=c.position_name;$('[data-detail-name]').textContent=c.name;$('[data-detail-student-id]').textContent=c.student_id;$('[data-detail-manifesto]').textContent=c.manifesto||'No manifesto provided.';$('[data-detail-edit]').dataset.editFromDetail=c.id;modal('#candidateDetailModal').show();}
     if(disable){const c=people.find(item=>+item.id===+disable.dataset.disable);if(await dialog({title:'Deactivate Candidate',message:`${c?.name||'This candidate'} will no longer appear on the ballot. Existing IDs and records are preserved.`,cancelText:'Cancel',confirmText:'Deactivate',tone:'danger'})){try{await request('candidate-disable',{id:+disable.dataset.disable});people=(await request('candidate-list')).candidates;display();}catch(err){report(err);}}}
   });
   $('[data-detail-edit]')?.addEventListener('click',e=>{const c=people.find(item=>+item.id===+e.currentTarget.dataset.editFromDetail);modal('#candidateDetailModal').hide();$('#candidateDetailModal').addEventListener('hidden.bs.modal',()=>{fill(c);modal('#candidateModal').show();},{once:true});});
   $('#candidate-photo')?.addEventListener('change',e=>{const file=e.target.files[0];if(!file)return;if(previewUrl)URL.revokeObjectURL(previewUrl);previewUrl=URL.createObjectURL(file);removePhoto=false;paintPreview(previewUrl,$('#candidate-name').value||'Candidate');});
   $('#candidate-remove-photo')?.addEventListener('click',()=>{removePhoto=true;$('#candidate-photo').value='';$('#candidate-current-photo').value='';if(previewUrl)URL.revokeObjectURL(previewUrl);previewUrl='';paintPreview('',$('#candidate-name').value||'Candidate');});
   form.addEventListener('submit',async e=>{e.preventDefault();if(saving)return;saving=true;const button=form.querySelector('[type="submit"]');setBusy(button,true,'Saving candidate...');message('#candidate-form-error','');try{
     let photo_path=$('#candidate-current-photo').value;
     const file=$('#candidate-photo').files[0];
     if(file){const body=new FormData();body.append('photo',file);const response=await fetch('../api/upload.php',{method:'POST',credentials:'same-origin',headers:{'X-CSRF-Token':csrf},body});const d=await response.json();if(!d.ok)throw Error(d.error?.message||'Upload failed');photo_path=d.data.path;}
     await request('candidate-save',{id:+$('#candidate-id').value||0,name:$('#candidate-name').value,student_id:$('#candidate-student-id').value,position_id:+$('#candidate-position').value,manifesto:$('#candidate-manifesto').value,photo_path,remove_photo:removePhoto});
     modal('#candidateModal').hide();people=(await request('candidate-list')).candidates;display();
   }catch(err){message('#candidate-form-error',err.message);}finally{saving=false;setBusy(button,false);}});
   display();
 }
   async function control(){
     const confirmations={
       'registration-open':['Open Registration','Administrators will be able to register eligible voters.'],
       'registration-close':['Close Registration','New voter registrations will be disabled until registration is reopened.'],
       open:['Open Voting','Eligible registered students will be able to submit their ballots.'],
       close:['Close Voting','Students will no longer be able to submit ballots.'],
       publish:['Publish Results','Election results will become visible according to the existing results visibility settings.'],
       hide:['Hide Results','Previously visible public results will no longer be accessible.']
     };
    function paint(){
      const votingOpen=me.round?.status==='open',registrationOpen=Boolean(me.round?.registration_open),resultsOpen=Boolean(me.round?.results_published);
      paintStatus($('#voting-status-message'),$('#voting-status'),votingOpen,'Voting is currently OPEN.','Voting is currently CLOSED.');
      paintStatus($('#results-status-message'),$('#publish-status'),resultsOpen,'Election results are currently OPEN for viewing.','Election results are currently CLOSED for viewing.');
      paintStatus($('#registration-status'),null,registrationOpen,'Voter registration is currently OPEN.','Voter registration is currently CLOSED.');
    }
    $$('[data-control],[data-registration]').forEach(btn=>btn.addEventListener('click',async()=>{
      const control=btn.dataset.control||btn.dataset.registration;
       const copy=confirmations[control]||['Confirm Election Change','Apply this election setting?'];if(!await dialog({title:copy[0],message:copy[1],cancelText:'Cancel',confirmText:copy[0],tone:['close','hide','registration-close'].includes(control)?'warning':'info'}))return;
      setBusy(btn,true,'Updating...');
      try{await request('election-control',{control});me=await request('me');paint();await refreshTurnout();}catch(err){report(err);}finally{setBusy(btn,false);}
   }));
    $('#create-runoff-btn')?.addEventListener('click',async()=>{if(!await dialog({title:'Create Runoff Round',message:'Create a separate round containing only the candidates tied for first place?',cancelText:'Cancel',confirmText:'Create Runoff',tone:'warning'}))return;try{await request('runoff-create',{});location.reload();}catch(err){report(err);}});
   async function refreshTurnout(){const d=await request('results');
     const values=$$('.main-content .display-6, .main-content .fs-5.text-secondary, .main-content .text-sm-end .h2');if(values.length>=3){values[0].textContent=d.results.voted;values[1].textContent='/ '+d.results.registered;values[2].textContent=(d.results.registered?(100*d.results.voted/d.results.registered):0).toFixed(1)+'%';}
     const bar=$('.main-content .progress-amsa .bar');if(bar)bar.style.width=(d.results.registered?(100*d.results.voted/d.results.registered):0)+'%';
   }
   paint();await refreshTurnout();
 }
 const resultMarkup=(res)=>res.positions.map(pos=>{
   let total=pos.candidates.reduce((sum,c)=>sum+c.votes,0);
   return `<section class="amsa-card card-pad mb-4"><h2 class="h5 fw-bold text-maroon">${escape(pos.name)}</h2>${pos.candidates.map(c=>`<div class="mb-3"><div class="d-flex justify-content-between"><strong>${escape(c.name)}</strong><span>${c.votes} vote${c.votes===1?'':'s'}</span></div><div class="progress-amsa mt-2"><div class="bar" style="width:${total?Math.round(c.votes/total*100):0}%"></div></div></div>`).join('')||'<p>No candidates registered.</p>'}</section>`;
 }).join('');
 async function results(isAdmin){
   const host=$(isAdmin?'#admin-results-list':'#member-results-list'),d=await request('results');
   if(!d.published&&!isAdmin){host.innerHTML='<div class="notice-paper">Results are not published yet. Please check back after the announcement.</div>';return;}
   host.innerHTML=`<div class="notice-paper mb-4">Round ${d.results.round_number} (${escape(d.results.round_type)}) · ${d.results.voted} ballots submitted</div>`+resultMarkup(d.results);
 }
   async function adminDashboard(){
   const d=await request('results');const stats=$$('.stat-card .stat-value');
   if(stats.length>=5){stats[0].textContent=d.results.registered;stats[1].textContent=d.results.voted;stats[2].textContent=Math.max(0,d.results.registered-d.results.voted);stats[3].textContent=d.results.positions.reduce((n,p)=>n+p.candidates.length,0);stats[4].textContent=me.round?.status||'draft';}
   $$('.stat-card .stat-detail').forEach(x=>{if(/Browser|demo/i.test(x.textContent))x.textContent='Verified database records';});
    const votingOpen=me.round?.status==='open',registrationOpen=Boolean(me.round?.registration_open),resultsOpen=Boolean(me.round?.results_published);
    paintStatus($('#admin-registration-message'),$('#admin-registration-badge'),registrationOpen,'Voter registration is currently OPEN.','Voter registration is currently CLOSED.');
    paintStatus($('#admin-voting-message'),$('#admin-voting-badge'),votingOpen,'Voting is currently OPEN.','Voting is currently CLOSED.');
    paintStatus($('#admin-results-message'),$('#admin-results-badge'),resultsOpen,'Election results are currently OPEN for viewing.','Election results are currently CLOSED for viewing.');
     const summary=$('#admin-voting-summary');if(summary){summary.textContent=votingOpen?'OPEN':'CLOSED';summary.classList.toggle('text-success',votingOpen);summary.classList.toggle('text-danger',!votingOpen);}
     let recoveryFilter='pending';
     async function loadRecoveries(){
       const data=await request('pin-recovery-list',null,{status:recoveryFilter}),count=$('#recovery-pending-count'),host=$('#pin-recovery-list');
       if(count){count.textContent=data.pending_count;count.hidden=data.pending_count<1;}
       host.innerHTML=data.requests.map(item=>`<article class="recovery-request-card"><div><div class="d-flex align-items-center flex-wrap gap-2"><h3>${escape(item.full_name)}</h3><span class="recovery-status is-${escape(item.status)}">${escape(item.status)}</span></div><p><strong>${escape(item.student_id)}</strong> · Requested ${escape(new Date(item.created_at).toLocaleString())}</p>${item.resolved_at?`<p class="recovery-resolution">Reviewed by ${escape(item.resolved_by_name||'authorized administrator')} · ${escape(new Date(item.resolved_at).toLocaleString())}</p>`:''}</div>${item.status==='pending'&&me.user?.role!=='viewer'?`<div class="recovery-actions"><button type="button" class="btn-amsa btn-amsa-primary btn-sm-amsa" data-recovery-resolve="${item.id}">Review & Generate PIN</button><button type="button" class="btn-amsa btn-danger-soft btn-sm-amsa" data-recovery-reject="${item.id}">Reject</button></div>`:''}</article>`).join('')||`<div class="recovery-empty"><i class="fa-regular fa-circle-check" aria-hidden="true"></i><p>No ${escape(recoveryFilter)} PIN requests.</p></div>`;
     }
     $$('[data-recovery-filter]').forEach(button=>button.addEventListener('click',async()=>{recoveryFilter=button.dataset.recoveryFilter;$$('[data-recovery-filter]').forEach(item=>{item.classList.toggle('active',item===button);item.classList.toggle('btn-amsa-gold',item===button);item.classList.toggle('btn-amsa-outline',item!==button);});await loadRecoveries();}));
     $('[data-scroll-recoveries]')?.addEventListener('click',()=>$('#pin-recovery-panel')?.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth'}));
     $('#pin-recovery-list')?.addEventListener('click',async e=>{
       const resolve=e.target.closest('[data-recovery-resolve]'),reject=e.target.closest('[data-recovery-reject]');
       if(resolve){const yes=await dialog({title:'Confirm Identity Verification',message:'Only continue after you have inspected the student’s physical Student ID at the AMSA Help Counter. Generating a new PIN immediately invalidates the old PIN but does not change voting status.',cancelText:'Cancel',confirmText:'Identity Verified',tone:'warning'});if(!yes)return;try{const d=await request('pin-recovery-action',{id:+resolve.dataset.recoveryResolve,decision:'resolve',identity_verified:true});await dialog({title:'New Voting PIN',message:`${d.voter.full_name} (${d.voter.student_id})\n\n${d.pin}\n\nProvide this PIN directly to the verified student. It will disappear when this window closes.`,confirmText:'I Have Recorded the PIN',tone:'pin'});await loadRecoveries();}catch(err){await report(err);}}
       if(reject){const yes=await dialog({title:'Reject PIN Request',message:'Reject this pending assistance request without changing the voter’s PIN?',cancelText:'Cancel',confirmText:'Reject Request',tone:'danger'});if(!yes)return;try{await request('pin-recovery-action',{id:+reject.dataset.recoveryReject,decision:'reject'});await loadRecoveries();}catch(err){await report(err);}}
     });
     await loadRecoveries();
  }
 async function reveal(){
   const d=await request('results',null,{presentation:'1'});const el=$('#reveal-content');
   const fullscreen=$('[data-fullscreen-toggle]');fullscreen?.addEventListener('click',async()=>{try{if(document.fullscreenElement)await document.exitFullscreen();else await document.documentElement.requestFullscreen();}catch(error){report(new Error('Fullscreen mode is not available in this browser.'));}});
   document.addEventListener('fullscreenchange',()=>{const icon=fullscreen?.querySelector('i'),label=fullscreen?.querySelector('[data-fullscreen-label]'),active=Boolean(document.fullscreenElement);if(icon)icon.className=active?'fa-solid fa-compress':'fa-solid fa-expand';if(label)label.textContent=active?'Exit Fullscreen':'Fullscreen';fullscreen?.setAttribute('aria-label',active?'Exit fullscreen presentation':'Enter fullscreen presentation');});
   if(!d.published){el.innerHTML='<section class="reveal-empty-state" role="status"><div class="reveal-empty-icon"><i class="fa-regular fa-clock" aria-hidden="true"></i></div><p class="reveal-kicker">Official Announcement</p><h1>Results Not Published Yet</h1><p>Final election results have not been published. Please check again once the results are officially released.</p></section>';return;}
   if(!window.AmsaResultReveal)throw Error('The cinematic presentation module could not be loaded.');
   window.AmsaResultReveal.mount(el,d.results);
 }
 document.addEventListener('DOMContentLoaded',init);
})();
