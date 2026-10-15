const base=process.env.AMSA_TEST_BASE||'http://127.0.0.1:8099';
let passed=0,failed=0;const report=[];
const check=(condition,name,detail='')=>{if(condition){passed++;report.push(`PASS ${name}`);}else{failed++;report.push(`FAIL ${name}${detail?`: ${detail}`:''}`);}};

class Client{
  cookies=new Map();
  async call(path,{method='GET',json,headers={},redirect='follow'}={}){const h={...headers};if(this.cookies.size)h.Cookie=[...this.cookies].map(([k,v])=>`${k}=${v}`).join('; ');if(json!==undefined)h['Content-Type']='application/json';const response=await fetch(base+path,{method,headers:h,body:json===undefined?undefined:JSON.stringify(json),redirect});for(const value of response.headers.getSetCookie()){const pair=value.split(';',1)[0],i=pair.indexOf('=');if(i>0)this.cookies.set(pair.slice(0,i),pair.slice(i+1));}const text=await response.text();let body=null;try{body=JSON.parse(text);}catch{}return {status:response.status,body,text,headers:response.headers};}
  async csrf(){return (await this.call('/api/index.php?action=csrf')).body.data.csrf;}
  async post(action,json,token){return this.call(`/api/index.php?action=${action}`,{method:'POST',json,headers:{'X-CSRF-Token':token}});}
  async upload(filePath,token){const form=new FormData();form.append('photo',new Blob([readFileSync(filePath)]),'fictional-candidate.jpg');const h={'X-CSRF-Token':token};if(this.cookies.size)h.Cookie=[...this.cookies].map(([k,v])=>`${k}=${v}`).join('; ');const response=await fetch(base+'/api/upload.php',{method:'POST',headers:h,body:form});const text=await response.text();let body=null;try{body=JSON.parse(text);}catch{}return {status:response.status,body,text};}
}
async function loginAdmin(){const client=new Client(),token=await client.csrf();const response=await client.post('admin-login',{username:'test_super',password:'ElectionTest!2026'},token);return {client,response,token:await client.csrf()};}
async function loginVoter(student_id,pin){const client=new Client(),token=await client.csrf();const response=await client.post('voter-login',{student_id,pin},token);return {client,response,token:await client.csrf()};}

try{
  const adminSession=await loginAdmin(),admin=adminSession.client,token=adminSession.token;
  check(adminSession.response.status===200,'fictional administrator signs in');
  const adminDirectory=await admin.call('/admin/',{redirect:'manual'});check(adminDirectory.status===302&&adminDirectory.headers.get('location')==='admin-dashboard.php','authenticated admin directory redirects to dashboard');
  const anonymousAdminDirectory=await new Client().call('/admin/',{redirect:'manual'});check(anonymousAdminDirectory.status===302&&anonymousAdminDirectory.headers.get('location')==='admin-login.php','anonymous admin directory redirects to login');
  const anonymousUserDirectory=await new Client().call('/user/',{redirect:'manual'});check(anonymousUserDirectory.status===302&&anonymousUserDirectory.headers.get('location')==='login.php','anonymous user directory redirects to voter login');
  await admin.post('election-control',{control:'registration-open'},token);
  const positions=(await admin.call('/api/index.php?action=positions')).body.data.positions;
  const photoUpload=await admin.upload(resolve('assets/voting-visual.jpg'),token),sharedPhoto=photoUpload.body.data.path;
  check(photoUpload.status===200,'fictional candidate photograph uploads through authorized route');
  const candidateIds=new Map();
  for(let p=0;p<positions.length;p++){
    const ids=[];
    for(let c=0;c<2;c++){const response=await admin.post('candidate-save',{id:0,student_id:`AIU82${p}${c}001`,name:`Rehearsal ${positions[p].name} ${c+1}`,position_id:positions[p].id,manifesto:`Fictional rehearsal manifesto ${p+1}-${c+1}`,photo_path:sharedPhoto},token);ids.push(response.body.data.candidate_id);}
    candidateIds.set(String(positions[p].id),ids);
  }
  check([...candidateIds.values()].flat().length===10,'two fictional candidates created for every position');

  const voters=[];
  for(let i=1;i<=50;i++){const student_id=`AIU92${String(i).padStart(5,'0')}`,response=await admin.post('voter-create',{student_id,full_name:`Fictional Voter ${String(i).padStart(2,'0')}`,gender:'Prefer not to say',year_of_study:'1',major:'Election Rehearsal',school:'Test Data',student_email:''},token);if(response.status!==200)throw new Error(`Voter ${i} creation failed: ${response.text}`);voters.push({student_id,pin:response.body.data.pin});}
  check(voters.length===50&&voters.every(v=>/^\d{6}$/.test(v.pin)),'50 fictional voters created with six-digit PINs');

  const recoveryClient=new Client(),recoveryCsrf=await recoveryClient.csrf();await recoveryClient.post('pin-recovery-request',{student_id:voters[0].student_id},recoveryCsrf);
  const pending=(await admin.call('/api/index.php?action=pin-recovery-list&status=pending')).body.data;
  check(pending.pending_count===1,'administrator receives persistent PIN assistance notification');
  let response=await admin.post('pin-recovery-action',{id:pending.requests[0].id,decision:'resolve',identity_verified:true},token);
  voters[0].pin=response.body.data.pin;check(response.status===200,'verified fictional voter receives replacement PIN');

  await admin.post('election-control',{control:'registration-close'},token);
  response=await admin.post('voter-create',{student_id:'AIU9299999',full_name:'Blocked Registration'},token);
  check(response.status===409,'closed registration blocks additional account creation');
  response=await admin.post('election-control',{control:'open'},token);check(response.status===200,'voting opens after complete roster validation');

  const sessions=[];
  for(let i=0;i<voters.length;i++)sessions.push(await loginVoter(voters[i].student_id,voters[i].pin));
  check(sessions.every(s=>s.response.status===200),'all 50 fictional voters authenticate through application route');
  const voterDirectory=await sessions[0].client.call('/user/',{redirect:'manual'});check(voterDirectory.status===302&&voterDirectory.headers.get('location')==='member-dashboard.php','authenticated voter directory redirects to member dashboard');
  const ballot=(await sessions[0].client.call('/api/index.php?action=ballot')).body.data;
  check(ballot.positions.length===5&&ballot.positions.every(p=>p.candidates.length===2),'five-position ballot contains correct rehearsal candidates');
  check(ballot.positions.every(p=>p.candidates.every(c=>c.photo===sharedPhoto)),'candidate photograph mapping reaches every ballot card');
  const choicesFor=index=>Object.fromEntries(ballot.positions.map(p=>[String(p.id),candidateIds.get(String(p.id))[index<35?0:1]]));
  const submissions=[];
  for(let start=0;start<50;start+=5){const batch=[];for(let i=start;i<Math.min(start+5,50);i++)batch.push(sessions[i].client.post('submit-vote',{choices:choicesFor(i)},sessions[i].token));submissions.push(...await Promise.all(batch));}
  check(submissions.every(item=>item.status===200),'all 50 fictional voters submit complete ballots');
  response=await sessions[0].client.post('submit-vote',{choices:choicesFor(0)},sessions[0].token);check(response.status===409,'duplicate ballot submission is rejected');

  const hidden=await sessions[1].client.call('/api/index.php?action=results');
  check(hidden.body.data.published===false&&!('results' in hidden.body.data),'unpublished totals are absent from voter API response');
  response=await admin.post('election-control',{control:'close'},token);check(response.status===200,'voting closes');
  response=await sessions[2].client.post('submit-vote',{choices:choicesFor(2)},sessions[2].token);check(response.status===409,'closed voting rejects further submissions');
  response=await admin.post('election-control',{control:'publish'},token);check(response.status===200,'results publish only after voting closes');
  const results=(await admin.call('/api/index.php?action=results')).body.data.results;
  check(results.voted===50,'turnout records exactly 50 completed voters');
  check(results.positions.every(p=>p.candidates.reduce((sum,c)=>sum+c.votes,0)===50),'each position totals exactly 50 selections');
  check(results.positions.reduce((sum,p)=>sum+p.candidates.reduce((n,c)=>n+c.votes,0),0)===250,'database-backed results contain exactly 250 valid choices');
  check(results.positions.every(p=>p.candidates[0].votes===35&&p.candidates[1].votes===15),'published candidate totals match simulated selections');
  const reveal=await admin.call('/admin/result-reveal.php');check(reveal.status===200&&reveal.text.includes('id="reveal-content"'),'authorized result reveal route loads');
  await admin.post('election-control',{control:'hide'},token);
  const rehidden=await sessions[3].client.call('/api/index.php?action=results');check(rehidden.body.data.published===false&&!('results' in rehidden.body.data),'closing results immediately removes voter access');
}catch(error){failed++;report.push(`FAIL rehearsal exception: ${error.stack||error}`);}

for(const line of report)console.log(line);console.log(`SUMMARY passed=${passed} failed=${failed}`);process.exitCode=failed?1:0;
import {readFileSync} from 'node:fs';
import {resolve} from 'node:path';
