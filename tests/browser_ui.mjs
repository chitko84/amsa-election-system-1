import {spawn} from 'node:child_process';
import {mkdirSync,writeFileSync} from 'node:fs';
import {join} from 'node:path';
import {tmpdir} from 'node:os';
import {fileURLToPath} from 'node:url';

const base='http://127.0.0.1:8098';
const chromePath='C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const phpPath='C:\\xampp\\php\\php.exe';
const projectRoot=fileURLToPath(new URL('../',import.meta.url));
const debugPort=9300+(process.pid%500);
const artifacts=new URL('./artifacts/',import.meta.url);
mkdirSync(artifacts,{recursive:true});
let passed=0,failed=0,blocked=0;
const lines=[];
const check=(condition,name,detail='')=>{if(condition){passed++;lines.push(`PASS ${name}`);}else{failed++;lines.push(`FAIL ${name}${detail?`: ${detail}`:''}`);}};
const block=(name,detail='')=>{blocked++;lines.push(`BLOCKED ${name}${detail?`: ${detail}`:''}`);};
const delay=ms=>new Promise(resolve=>setTimeout(resolve,ms));

class HttpClient{
  cookies=new Map();
  async call(path,{method='GET',json,headers={}}={}){
    const h={...headers};if(this.cookies.size)h.Cookie=[...this.cookies].map(([k,v])=>`${k}=${v}`).join('; ');if(json!==undefined)h['Content-Type']='application/json';
    const response=await fetch(base+path,{method,headers:h,body:json===undefined?undefined:JSON.stringify(json)});
    for(const value of response.headers.getSetCookie()){const part=value.split(';',1)[0],at=part.indexOf('=');if(at>0)this.cookies.set(part.slice(0,at),part.slice(at+1));}
    const body=await response.json().catch(()=>null);return {status:response.status,body};
  }
  async csrf(){return (await this.call('/api/index.php?action=csrf')).body.data.csrf;}
  async post(action,json,csrf){return this.call(`/api/index.php?action=${action}`,{method:'POST',json,headers:{'X-CSRF-Token':csrf}});}
}

async function prepareFixture(){
  const admin=new HttpClient(),firstCsrf=await admin.csrf();
  let response=await admin.post('admin-login',{username:'test_super',password:'ElectionTest!2026'},firstCsrf);
  if(response.status!==200)throw new Error('Could not authenticate browser-test administrator');
  const csrf=await admin.csrf();
  await admin.post('election-control',{control:'registration-open'},csrf);
  const positions=(await admin.call('/api/index.php?action=positions')).body.data.positions;
  for(let i=0;i<positions.length;i++){
    response=await admin.post('candidate-save',{id:0,student_id:`AIU810000${i+1}`,name:`Mobile Candidate ${i+1}`,position_id:positions[i].id,manifesto:'A concise candidate manifesto that should wrap cleanly on narrow screens.',photo_path:''},csrf);
    if(response.status!==200)throw new Error(`Could not create UI candidate ${i+1}`);
    response=await admin.post('candidate-save',{id:0,student_id:`AIU820000${i+1}`,name:`Second Candidate ${i+1}`,position_id:positions[i].id,manifesto:'A second fictional candidate used to verify presenter-controlled slideshow navigation.',photo_path:''},csrf);
    if(response.status!==200)throw new Error(`Could not create second UI candidate ${i+1}`);
  }
  response=await admin.post('voter-create',{student_id:'AIU9100001',full_name:'Mobile Test Voter With A Long Name',gender:'Prefer not to say',year_of_study:'1',major:'Interface Testing',school:'Automated QA',student_email:''},csrf);
  if(response.status!==200)throw new Error('Could not create UI voter');
  const voter={student_id:'AIU9100001',pin:response.body.data.pin};
  await admin.post('election-control',{control:'registration-close'},csrf);
  await admin.post('election-control',{control:'open'},csrf);
  return {admin,csrf,voter};
}

class CDP{
  constructor(url){this.ws=new WebSocket(url);this.id=0;this.pending=new Map();this.listeners=new Map();this.ready=new Promise((resolve,reject)=>{const timer=setTimeout(()=>reject(new Error('Timed out opening DevTools WebSocket')),10000);this.ws.onopen=()=>{clearTimeout(timer);resolve();};this.ws.onerror=event=>{clearTimeout(timer);reject(new Error(`DevTools WebSocket error: ${event.type}`));};this.ws.onclose=()=>{clearTimeout(timer);};});this.ws.onmessage=event=>{const message=JSON.parse(event.data);if(message.id){const item=this.pending.get(message.id);if(item){clearTimeout(item.timer);this.pending.delete(message.id);message.error?item.reject(new Error(message.error.message)):item.resolve(message.result);}}else{for(const callback of this.listeners.get(message.method)||[])callback(message.params);}};}
  async send(method,params={},timeout=10000){await this.ready;return new Promise((resolve,reject)=>{const id=++this.id;const timer=setTimeout(()=>{this.pending.delete(id);reject(new Error(`Timed out calling ${method}`));},timeout);this.pending.set(id,{resolve,reject,timer});this.ws.send(JSON.stringify({id,method,params}));});}
  on(method,callback){if(!this.listeners.has(method))this.listeners.set(method,[]);this.listeners.get(method).push(callback);}
  once(method,timeout=10000){return new Promise((resolve,reject)=>{const callback=params=>{clearTimeout(timer);const list=this.listeners.get(method)||[];this.listeners.set(method,list.filter(value=>value!==callback));resolve(params);};const timer=setTimeout(()=>reject(new Error(`Timed out waiting for ${method}`)),timeout);this.on(method,callback);});}
  close(){this.ws.close();}
}

const profile=join(tmpdir(),`amsa-chrome-${process.pid}`);
const sessionDir=join(tmpdir(),`amsa-php-sessions-${process.pid}`);
mkdirSync(sessionDir,{recursive:true});
const testServer=spawn(phpPath,['-d',`session.save_path=${sessionDir}`,'-d',`upload_tmp_dir=${sessionDir}`,'-S','127.0.0.1:8098','-t',projectRoot],{cwd:projectRoot,env:{...process.env,AMSA_TEST_DB:'amsa_election_test'},stdio:'ignore'});
const chrome=spawn(chromePath,['--headless=new','--disable-gpu','--no-sandbox','--disable-dev-shm-usage','--disable-extensions','--no-first-run','--no-default-browser-check',`--remote-debugging-port=${debugPort}`,`--user-data-dir=${profile}`,'about:blank'],{stdio:'ignore'});
let cdp;

async function waitDebugger(){for(let i=0;i<80;i++){try{return await (await fetch(`http://127.0.0.1:${debugPort}/json/list`)).json();}catch{await delay(100);}}throw new Error('Chrome DevTools endpoint did not start');}
async function waitServer(){for(let i=0;i<80;i++){try{const response=await fetch(base+'/');if(response.ok)return;}catch{}await delay(100);}throw new Error('Isolated PHP browser-test server did not start');}
async function evaluate(expression){const result=await cdp.send('Runtime.evaluate',{expression,awaitPromise:true,returnByValue:true});if(result.exceptionDetails)throw new Error(result.exceptionDetails.text);return result.result.value;}
async function navigate(url){let result=await cdp.send('Page.navigate',{url});if(result.errorText==='net::ERR_ABORTED'){await delay(500);result=await cdp.send('Page.navigate',{url});}if(result.errorText&&result.errorText!=='net::ERR_ABORTED')throw new Error(`Navigation failed: ${result.errorText}`);const target=JSON.stringify(new URL(url).href);const until=Date.now()+20000;while(Date.now()<until){try{if(await evaluate(`location.href.startsWith(${target})&&document.readyState!=='loading'`)){await delay(250);return;}}catch{}await delay(100);}let actual='unknown';try{actual=await evaluate('location.href')}catch{}throw new Error(`Timed out navigating to ${url}${result.errorText?` (${result.errorText})`:''}; actual=${actual}`);}
async function waitFor(expression,timeout=10000){const until=Date.now()+timeout;while(Date.now()<until){if(await evaluate(expression))return true;await delay(100);}throw new Error(`Timed out waiting for: ${expression}`);}
async function viewport(width,height=900,mobile=false){await cdp.send('Emulation.setDeviceMetricsOverride',{width,height,deviceScaleFactor:1,mobile,screenWidth:width,screenHeight:height});await cdp.send('Emulation.setTouchEmulationEnabled',{enabled:mobile,maxTouchPoints:mobile?5:1});await delay(100);}
async function screenshot(name){try{const data=(await cdp.send('Page.captureScreenshot',{format:'png',captureBeyondViewport:false,fromSurface:true},8000)).data;writeFileSync(new URL(name,artifacts),Buffer.from(data,'base64'));check(true,`${name} screenshot captured`);return true;}catch(error){check(false,`${name} screenshot captured`,error.message);return false;}}
async function inspectLayout(label){const value=await evaluate(`(()=>({url:location.href,width:innerWidth,scrollWidth:document.documentElement.scrollWidth,broken:[...document.images].filter(i=>i.complete&&i.naturalWidth===0).map(i=>i.src),favicon:[...document.querySelectorAll('link[rel~="icon"]')].map(i=>i.href)}))()`);check(value.url.startsWith(base),`${label} rendered local application`,value.url);check(value.scrollWidth<=value.width,`${label} has no horizontal page overflow`,`${value.scrollWidth}>${value.width}`);check(value.broken.length===0,`${label} has no broken images`,value.broken.join(','));check(value.favicon.length>=1,`${label} includes favicon`);}

try{
  await waitServer();
  const fixture=await prepareFixture();
  const pages=await waitDebugger();
  const browserPage=pages.find(page=>page.type==='page'&&!String(page.url).startsWith('chrome-extension://'));
  if(!browserPage)throw new Error('Chrome did not expose a controllable page target');
  cdp=new CDP(browserPage.webSocketDebuggerUrl);
  await Promise.all([cdp.send('Page.enable'),cdp.send('Runtime.enable'),cdp.send('Network.enable'),cdp.send('Log.enable')]);
  const consoleErrors=[],httpErrors=[];
  cdp.on('Runtime.exceptionThrown',event=>consoleErrors.push(event.exceptionDetails?.text||'exception'));
  cdp.on('Log.entryAdded',event=>{if(event.entry?.level==='error')consoleErrors.push(event.entry.text);});
  cdp.on('Network.responseReceived',event=>{if(event.response.status>=400&&event.response.url.startsWith(base))httpErrors.push(`${event.response.status} ${event.response.url}`);});

  const widths=[320,360,375,390,414,430,768,1024,1440,1920];
  await navigate(base+'/');
  for(const width of widths){await viewport(width,width<600?844:900,width<600);await inspectLayout(`landing ${width}px`);}
  await viewport(320,700,true);await navigate(base+'/user/login.php');await inspectLayout('voter login 320px');await screenshot('voter-login-320.png');

  await viewport(390,844,true);await navigate(base+'/admin/admin-login.php');
  await evaluate(`(()=>{document.querySelector('#admin-username').value='test_super';document.querySelector('#admin-password').value='ElectionTest!2026';document.querySelector('#admin-login-form').requestSubmit();return true})()`);
  await waitFor(`location.pathname.endsWith('/admin/admin-dashboard.php')`);
  await waitFor(`document.querySelector('#admin-results-message')?.textContent.includes('CLOSED')`);
  await inspectLayout('admin dashboard 390px');
  const adminStatuses=await evaluate(`['#admin-registration-message','#admin-voting-message','#admin-results-message'].map(q=>document.querySelector(q)?.textContent)`);
  check(JSON.stringify(adminStatuses)===JSON.stringify(['Voter registration is currently CLOSED.','Voting is currently OPEN.','Election results are currently CLOSED for viewing.']),'admin dashboard shows three exact database-backed statuses',JSON.stringify(adminStatuses));
  await screenshot('admin-dashboard-390.png');

  await navigate(base+'/admin/result-reveal.php');await waitFor(`document.querySelector('.reveal-empty-state')`);
  const closedReveal=await evaluate(`(()=>{const title=document.querySelector('.reveal-empty-state h1'),desc=document.querySelector('.reveal-empty-state>p:last-child'),style=getComputedStyle(title),descStyle=getComputedStyle(desc),background=getComputedStyle(document.body).backgroundColor,parse=value=>(value.match(/[\\d.]+/g)||[]).slice(0,3).map(Number),lum=value=>{const values=parse(value).map(v=>{v/=255;return v<=.03928?v/12.92:Math.pow((v+.055)/1.055,2.4)});return .2126*values[0]+.7152*values[1]+.0722*values[2]},ratio=(a,b)=>{const x=lum(a),y=lum(b);return (Math.max(x,y)+.05)/(Math.min(x,y)+.05)};return {title:title.textContent,display:style.display,visibility:style.visibility,opacity:style.opacity,color:style.color,descColor:descStyle.color,bodyBackground:background,titleContrast:ratio(style.color,background),descContrast:ratio(descStyle.color,background),leaked:document.body.innerText.includes('vote')&&!document.body.innerText.includes('not been published')}})()`);
  check(closedReveal.title==='Results Not Published Yet'&&closedReveal.visibility==='visible'&&+closedReveal.opacity===1,'closed result reveal text is visibly rendered',JSON.stringify(closedReveal));
  check(closedReveal.titleContrast>=4.5&&closedReveal.descContrast>=4.5,'closed result reveal text meets WCAG AA contrast against presentation background',JSON.stringify(closedReveal));
  check(!closedReveal.leaked,'closed result reveal exposes no candidate totals');
  for(const width of [320,390,768,1024,1440,1920]){await viewport(width,width<600?844:900,width<600);await inspectLayout(`closed reveal ${width}px`);}
  await viewport(390,844,true);await screenshot('result-reveal-closed-390.png');
  await navigate(base+'/admin/admin-dashboard.php');

  const opened=await evaluate(`(()=>{const b=document.querySelector('[data-menu-toggle]');b.click();return {expanded:b.getAttribute('aria-expanded'),open:document.querySelector('.sidebar').classList.contains('open'),overlay:document.querySelector('.sidebar-overlay').classList.contains('show'),body:document.body.classList.contains('menu-open'),hidden:document.querySelector('.sidebar').getAttribute('aria-hidden'),inert:document.querySelector('.sidebar').inert}})()`);
  check(opened.expanded==='true'&&opened.open&&opened.overlay&&opened.body&&opened.hidden==='false'&&!opened.inert,'hamburger opens accessible drawer');
  await evaluate(`document.dispatchEvent(new KeyboardEvent('keydown',{key:'Escape',bubbles:true}))`);
  const escaped=await evaluate(`(()=>{const b=document.querySelector('[data-menu-toggle]'),s=document.querySelector('.sidebar');return {expanded:b.getAttribute('aria-expanded'),open:s.classList.contains('open'),inert:s.inert,body:document.body.classList.contains('menu-open')}})()`);
  check(escaped.expanded==='false'&&!escaped.open&&escaped.inert&&!escaped.body,'Escape closes drawer and disables hidden links');
  await evaluate(`document.querySelector('[data-menu-toggle]').click();document.querySelector('.sidebar-overlay').click()`);
  check(!(await evaluate(`document.querySelector('.sidebar').classList.contains('open')`)),'outside overlay click closes drawer');

  await navigate(base+'/user/login.php');
  await evaluate(`(()=>{document.querySelector('#student-id').value='${fixture.voter.student_id}';document.querySelector('#voting-pin').value='${fixture.voter.pin}';document.querySelector('#voter-login-form').requestSubmit();return true})()`);
  await waitFor(`location.pathname.endsWith('/user/member-dashboard.php')`);
  await waitFor(`document.querySelector('[data-voter-name]')?.textContent.includes('Mobile Test Voter')`);
  const expected=['President','Vice President','Secretary','Male Treasurer','Female Treasurer'];
  const dashboardOrder=await evaluate(`[...document.querySelectorAll('.dashboard-position-list strong')].map(e=>e.textContent.trim())`);
  check(JSON.stringify(dashboardOrder)===JSON.stringify(expected),'member dashboard cards use required order');
  for(const width of [320,390,768,1024,1440]){await viewport(width,width<600?844:900,width<600);await inspectLayout(`member dashboard ${width}px`);}
  await viewport(390,844,true);await screenshot('member-dashboard-390.png');

  await navigate(base+'/user/vote.php');await waitFor(`document.querySelectorAll('.position-section').length===5`);
  const ballotOrder=await evaluate(`[...document.querySelectorAll('.position-section h2')].map(e=>e.textContent.trim())`);
  check(JSON.stringify(ballotOrder)===JSON.stringify(expected),'rendered ballot uses required order');
  for(const width of [320,360,375,390,414,430,768,1024,1440]){await viewport(width,width<600?844:900,width<600);await inspectLayout(`ballot ${width}px`);}
  await viewport(390,844,true);
  await evaluate(`(()=>{const ids=[...new Set([...document.querySelectorAll('.candidate-card')].map(button=>button.dataset.p))];ids.forEach(id=>document.querySelector('.candidate-card[data-p="'+id+'"]').click());return ids.length})()`);
  const selection=await evaluate(`({progress:document.querySelector('#ballot-progress-value').textContent,enabled:!document.querySelector('#review-ballot-btn').disabled,selected:document.querySelectorAll('.candidate-card.selected').length})`);
  check(selection.progress==='5 of 5'&&selection.enabled&&selection.selected===5,'candidate selection and ballot progress work');
  await screenshot('ballot-selected-390.png');
  await evaluate(`document.querySelector('#review-ballot-btn').click()`);await waitFor(`document.querySelector('#reviewModal')?.classList.contains('show')`);
  check((await evaluate(`document.querySelectorAll('#review-list>div').length`))===5,'review modal accurately lists five selections');
  await evaluate(`document.querySelector('#submit-final-ballot').click()`);
  await waitFor(`!document.querySelector('#amsa-dialog-layer')?.hidden`);
  const voteDialog=await evaluate(`({title:document.querySelector('#amsa-dialog-title').textContent,message:document.querySelector('#amsa-dialog-message').textContent,cancel:document.querySelector('[data-dialog-cancel]').textContent,confirm:document.querySelector('[data-dialog-confirm]').textContent})`);
  check(voteDialog.title==='Confirm Your Vote'&&voteDialog.cancel==='Review Ballot'&&voteDialog.confirm==='Confirm & Submit Vote','final vote uses the required custom confirmation modal',JSON.stringify(voteDialog));
  const dialogA11y=await evaluate(`(()=>{const box=document.querySelector('.amsa-dialog');return {role:box.getAttribute('role'),modal:box.getAttribute('aria-modal'),labelledby:box.getAttribute('aria-labelledby'),focused:document.activeElement===document.querySelector('[data-dialog-cancel]')}})()`);
  check(dialogA11y.role==='dialog'&&dialogA11y.modal==='true'&&dialogA11y.labelledby==='amsa-dialog-title'&&dialogA11y.focused,'custom confirmation modal exposes accessible dialog semantics and initial focus',JSON.stringify(dialogA11y));
  await evaluate(`document.dispatchEvent(new KeyboardEvent('keydown',{key:'Escape',bubbles:true}))`);
  check(await evaluate(`document.querySelector('#amsa-dialog-layer').hidden&&document.querySelector('#vote-success').classList.contains('d-none')`),'Escape cancels confirmation without submitting');
  await evaluate(`document.querySelector('#submit-final-ballot').click()`);await waitFor(`!document.querySelector('#amsa-dialog-layer')?.hidden`);
  await evaluate(`document.querySelector('[data-dialog-confirm]').click()`);
  await waitFor(`!document.querySelector('#vote-success').classList.contains('d-none')`,15000);
  check(await evaluate(`document.querySelector('#vote-success h1').textContent.includes('Thank')||document.querySelector('#vote-success .eyebrow').textContent.includes('submitted')`),'browser ballot submission reaches success state');
  await screenshot('voting-success-390.png');

  await fixture.admin.post('election-control',{control:'close'},fixture.csrf);
  await fixture.admin.post('election-control',{control:'publish'},fixture.csrf);
  await navigate(base+'/user/member-results.php');await waitFor(`document.querySelector('#member-results-list .amsa-card')`);
  await inspectLayout('member results 390px');await screenshot('member-results-390.png');
  check((await evaluate(`document.querySelectorAll('#member-results-list .amsa-card').length`))===5,'opened results render all five positions');

  await navigate(base+'/admin/admin-login.php');
  await evaluate(`(()=>{document.querySelector('#admin-username').value='test_super';document.querySelector('#admin-password').value='ElectionTest!2026';document.querySelector('#admin-login-form').requestSubmit();return true})()`);
  await waitFor(`location.pathname.endsWith('/admin/admin-dashboard.php')`);
  await navigate(base+'/admin/result-reveal.php');await waitFor(`document.querySelector('#beginReveal')`);
  check(await evaluate(`document.querySelector('.cinema-main-title')?.textContent.includes('AMSA AIU')`),'cinematic opening screen renders');
  check(!(await evaluate(`document.querySelector('#reveal-content').innerText.includes(' votes')`)),'opening screen exposes no vote totals');
  for(const width of [320,360,375,390,414,430,768,1024,1440,1920]){await viewport(width,width<600?844:900,width<600);await inspectLayout(`cinematic opening ${width}px`);}
  await viewport(1440,900,false);await evaluate(`document.querySelector('#beginReveal').click()`);await waitFor(`document.querySelector('.cinema-position-title')`);
  check((await evaluate(`document.querySelector('.cinema-position-title').textContent.trim()`))==='Female Treasurer','reveal starts with Female Treasurer');
  check(!(await evaluate(`document.querySelector('#reveal-content').innerText.includes(' votes')`)),'position introduction exposes no vote totals');
  await evaluate(`document.querySelector('#showFirstCandidate').click()`);await waitFor(`document.querySelector('.cinema-showcase-name')`);
  const firstCandidate=await evaluate(`({name:document.querySelector('.cinema-showcase-name').textContent.trim(),photo:document.querySelector('.cinema-showcase-photo').src,votes:document.querySelector('#reveal-content').innerText.includes(' votes')})`);
  check(Boolean(firstCandidate.name)&&firstCandidate.photo.startsWith(base)&&!firstCandidate.votes,'individual candidate introduction shows photo and name only',JSON.stringify(firstCandidate));
  await evaluate(`document.querySelector('#nextCandidateStage').click()`);await waitFor(`document.querySelector('.cinema-control-label')?.textContent.includes('02 / 02')`);
  check(Boolean(await evaluate(`document.querySelector('#previousCandidateStage')&&document.querySelector('#nextCandidateStage')`)),'presenter can navigate between individual candidates');
  const secondCandidateName=await evaluate(`document.querySelector('.cinema-showcase-name').textContent.trim()`);
  await evaluate(`document.querySelector('#previousCandidateStage').click()`);await waitFor(`document.querySelector('.cinema-control-label')?.textContent.includes('01 / 02')`);
  check((await evaluate(`document.querySelector('.cinema-showcase-name').textContent.trim()`))!==secondCandidateName,'Previous Candidate returns to the prior nominee');
  await evaluate(`document.querySelector('#nextCandidateStage').click()`);await waitFor(`document.querySelector('.cinema-control-label')?.textContent.includes('02 / 02')`);
  await evaluate(`document.querySelector('#nextCandidateStage').click()`);await waitFor(`document.querySelector('.cinema-presenter-hold')`);
  const holdBefore=await evaluate(`document.querySelector('.cinema-presenter-hold').outerHTML`);await delay(1200);
  check((await evaluate(`document.querySelector('.cinema-presenter-hold')?.outerHTML`))===holdBefore,'presenter hold does not reveal automatically');
  const holdState=await evaluate(`({cards:document.querySelectorAll('.cinema-hold-candidate').length,reveal:Boolean(document.querySelector('#revealNow')),votes:document.querySelector('#reveal-content').innerText.includes(' votes')})`);
  check(holdState.cards===2&&holdState.reveal&&!holdState.votes,'all-candidate hold remains private until explicit reveal',JSON.stringify(holdState));
  await evaluate(`document.querySelector('#backToLastCandidate').click()`);await waitFor(`document.querySelector('.cinema-control-label')?.textContent.includes('02 / 02')`);
  check((await evaluate(`document.querySelector('.cinema-showcase-name').textContent.trim()`))===secondCandidateName,'Back to Last Candidate returns to the last nominee');
  await evaluate(`document.querySelector('#nextCandidateStage').click()`);await waitFor(`document.querySelector('#revealNow')`);
  await evaluate(`document.querySelector('#revealNow').click()`);await waitFor(`document.querySelector('.cinema-final')`);
  const firstResult=await evaluate(`({winner:document.querySelector('.cinema-winner-name')?.textContent.trim(),photo:document.querySelector('.cinema-winner-photo')?.src,bars:document.querySelectorAll('.cinema-result-summary .result-card').length,previous:Boolean(document.querySelector('#backToPresenterHold')),next:Boolean(document.querySelector('#nextReveal'))})`);
  check(Boolean(firstResult.winner)&&firstResult.photo?.startsWith(base)&&firstResult.bars===2&&firstResult.previous&&firstResult.next,'explicit action renders winner portrait, totals, bars, and stage controls',JSON.stringify(firstResult));
  for(const width of [320,360,375,390,414,430,768,1024,1440,1920]){await viewport(width,width<600?844:900,width<600);await inspectLayout(`cinematic winner ${width}px`);}
  await viewport(1440,900,false);await screenshot('result-reveal-cinematic-winner-1440.png');
  const revealText=await evaluate(`(()=>{const sample=[...document.querySelectorAll('.cinema-final h1,.cinema-result-summary h2,.cinema-result-summary p,.cinema-result-summary span,.cinema-result-summary strong')];return {count:sample.length,invisible:sample.filter(e=>{const s=getComputedStyle(e);return s.display==='none'||s.visibility!=='visible'||+s.opacity<.99||s.color==='rgba(0, 0, 0, 0)'}).length}})()`);
  check(revealText.count>5&&revealText.invisible===0,'winner names and official totals remain visibly rendered',JSON.stringify(revealText));
  await evaluate(`document.querySelector('#backToPresenterHold').click()`);await waitFor(`document.querySelector('.cinema-presenter-hold')`);
  check(Boolean(await evaluate(`document.querySelector('#revealNow')`)),'Previous Stage returns to presenter hold');
  await evaluate(`document.querySelector('#revealNow').click()`);await waitFor(`document.querySelector('#nextReveal')`);
  const expectedRevealOrder=['Female Treasurer','Male Treasurer','Secretary','Vice President','President'];
  const visited=['Female Treasurer'];
  for(let index=1;index<expectedRevealOrder.length;index++){
    await evaluate(`document.querySelector('#nextReveal').click()`);await waitFor(`document.querySelector('.cinema-position-title')`);
    if(index===1){
      await evaluate(`document.querySelector('#previousResultFromPosition').click()`);await waitFor(`document.querySelector('.cinema-final')`);
      check((await evaluate(`document.querySelector('.cinema-winner-kicker').textContent`)).includes('Female Treasurer'),'Previous Result returns to the preceding official result');
      await evaluate(`document.querySelector('#nextReveal').click()`);await waitFor(`document.querySelector('.cinema-position-title')`);
    }
    visited.push(await evaluate(`document.querySelector('.cinema-position-title').textContent.trim()`));
    await evaluate(`document.querySelector('#showFirstCandidate').click()`);await waitFor(`document.querySelector('#nextCandidateStage')`);
    await evaluate(`document.querySelector('#nextCandidateStage').click()`);await waitFor(`document.querySelector('.cinema-control-label')?.textContent.includes('02 / 02')`);
    await evaluate(`document.querySelector('#nextCandidateStage').click()`);await waitFor(`document.querySelector('#revealNow')`);
    await evaluate(`document.querySelector('#revealNow').click()`);await waitFor(index===expectedRevealOrder.length-1?`document.querySelector('#finishReveal')`:`document.querySelector('#nextReveal')`);
  }
  check(JSON.stringify(visited)===JSON.stringify(expectedRevealOrder),'all five positions follow the reference ceremony order',JSON.stringify(visited));
  await evaluate(`document.querySelector('#finishReveal').click()`);await waitFor(`document.querySelector('#restartReveal')`);
  check((await evaluate(`document.querySelector('.cinema-main-title').textContent`)).includes('Complete'),'professional presentation-complete screen renders');
  await evaluate(`document.querySelector('#backToFinalResult').click()`);await waitFor(`document.querySelector('#finishReveal')`);
  check((await evaluate(`document.querySelector('.cinema-winner-kicker').textContent`)).includes('President'),'Back to Final Result returns to the President result');
  await evaluate(`document.querySelector('#finishReveal').click()`);await waitFor(`document.querySelector('#restartReveal')`);
  await evaluate(`document.querySelector('#restartReveal').click()`);await waitFor(`document.querySelector('#beginReveal')`);
  check(Boolean(await evaluate(`document.querySelector('#beginReveal')`)),'Restart Presentation returns to opening screen');
  try{
    const rect=await evaluate(`(()=>{const r=document.querySelector('[data-fullscreen-toggle]').getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2}})()`);
    await cdp.send('Input.dispatchMouseEvent',{type:'mousePressed',x:rect.x,y:rect.y,button:'left',clickCount:1});await cdp.send('Input.dispatchMouseEvent',{type:'mouseReleased',x:rect.x,y:rect.y,button:'left',clickCount:1});
    await waitFor(`Boolean(document.fullscreenElement)`,3000);check(true,'fullscreen control enters browser fullscreen');
    await inspectLayout('published reveal fullscreen');
    const exitRect=await evaluate(`(()=>{const r=document.querySelector('[data-fullscreen-toggle]').getBoundingClientRect();return {x:r.x+r.width/2,y:r.y+r.height/2}})()`);
    await cdp.send('Input.dispatchMouseEvent',{type:'mousePressed',x:exitRect.x,y:exitRect.y,button:'left',clickCount:1});await cdp.send('Input.dispatchMouseEvent',{type:'mouseReleased',x:exitRect.x,y:exitRect.y,button:'left',clickCount:1});await waitFor(`!document.fullscreenElement`,3000);check(true,'fullscreen presentation exits cleanly');
  }catch(error){block('headless fullscreen transition',error.message);}

  const localHttpErrors=httpErrors.filter(item=>!item.includes('favicon.ico'));
  check(localHttpErrors.length===0,'browser encountered no local HTTP 4xx/5xx',localHttpErrors.join(' | '));
  const applicationErrors=consoleErrors.filter(item=>!item.includes('ERR_NETWORK_ACCESS_DENIED')),externalErrors=consoleErrors.filter(item=>item.includes('ERR_NETWORK_ACCESS_DENIED'));
  check(applicationErrors.length===0,'browser encountered no uncaught application runtime errors',applicationErrors.join(' | '));
  if(externalErrors.length)block('external CDN requests in restricted browser environment',`${externalErrors.length} network-denied resource events`);
}catch(error){failed++;lines.push(`FAIL browser runner exception: ${error.stack||error}`);}
finally{try{await cdp?.send('Browser.close');}catch{}cdp?.close();chrome.kill();testServer.kill();}

for(const line of lines)console.log(line);
console.log(`SUMMARY passed=${passed} failed=${failed} blocked=${blocked}`);
process.exitCode=failed?1:0;
