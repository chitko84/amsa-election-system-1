import {readFileSync,existsSync} from 'node:fs';
import {basename,resolve} from 'node:path';

const base=process.env.AMSA_TEST_BASE||'http://127.0.0.1:8099';
let passed=0,failed=0;const lines=[];
function check(value,name,detail=''){if(value){passed++;lines.push(`PASS ${name}`);}else{failed++;lines.push(`FAIL ${name}${detail?`: ${detail}`:''}`);}}

class Client{
  cookies=new Map();
  async call(path,{method='GET',json,body,headers={},redirect='follow'}={}){
    const h={...headers};if(this.cookies.size)h.Cookie=[...this.cookies].map(([k,v])=>`${k}=${v}`).join('; ');if(json!==undefined)h['Content-Type']='application/json';
    const response=await fetch(base+path,{method,headers:h,body:json===undefined?body:JSON.stringify(json),redirect});
    for(const value of response.headers.getSetCookie()){const pair=value.split(';',1)[0],i=pair.indexOf('=');if(i>0)this.cookies.set(pair.slice(0,i),pair.slice(i+1));}
    const text=await response.text();let data=null;try{data=JSON.parse(text);}catch{}
    return {status:response.status,headers:response.headers,text,body:data};
  }
  async csrf(){return (await this.call('/api/index.php?action=csrf')).body.data.csrf;}
  async post(action,json,csrf){return this.call(`/api/index.php?action=${action}`,{method:'POST',json,headers:{'X-CSRF-Token':csrf}});}
  async upload(filePath,csrf,name=basename(filePath)){
    const form=new FormData();form.append('photo',new Blob([readFileSync(filePath)]),name);
    return this.call('/api/upload.php',{method:'POST',body:form,headers:{'X-CSRF-Token':csrf}});
  }
}
async function adminLogin(username='test_super',password='ElectionTest!2026'){const client=new Client(),token=await client.csrf();const response=await client.post('admin-login',{username,password},token);return {client,response,csrf:await client.csrf()};}
async function voterLogin(student_id,pin){const client=new Client(),token=await client.csrf();const response=await client.post('voter-login',{student_id,pin},token);return {client,response,csrf:await client.csrf()};}

try{
  const adminSession=await adminLogin(),admin=adminSession.client,csrf=adminSession.csrf;
  check(adminSession.response.status===200,'super administrator signs in');
  const anonymous=new Client(),anonymousToken=await anonymous.csrf();
  let response=await anonymous.upload(resolve('assets/voting-visual.jpg'),anonymousToken);
  check(response.status===401,'anonymous candidate photo upload rejected');

  const positions=(await admin.call('/api/index.php?action=positions')).body.data.positions;
  const firstUpload=await admin.upload(resolve('assets/voting-visual.jpg'),csrf,'portrait-renamed.php.jpg');
  check(firstUpload.status===200&&/\.jpg$/.test(firstUpload.body.data.path),'valid JPEG content uploaded with safe server filename');
  response=await admin.post('candidate-save',{id:0,student_id:'AIU8100001',name:'Photo Candidate',position_id:positions[0].id,manifesto:'Photo test candidate',photo_path:firstUpload.body.data.path},csrf);
  const candidateId=response.body.data.candidate_id;
  check(response.status===200,'candidate created with photograph');
  let candidate=(await admin.call('/api/index.php?action=candidate-list')).body.data.candidates.find(c=>+c.id===+candidateId);
  check(candidate.photo_path===firstUpload.body.data.path,'candidate photograph mapping persists');
  check((await admin.call('/'+candidate.photo_path.replace('../',''))).status===200,'saved candidate image is publicly readable');
  response=await admin.post('candidate-save',{id:0,student_id:'AIU8100099',name:'Shared Photo Candidate',position_id:positions[0].id,manifesto:'Shared photograph reference',photo_path:firstUpload.body.data.path},csrf);
  const sharedCandidateId=response.body.data.candidate_id;

  const webpUpload=await admin.upload(resolve('tests/runtime/candidate-test.webp'),csrf,'candidate.webp');
  check(webpUpload.status===200&&/\.webp$/.test(webpUpload.body.data.path),'valid WebP photograph upload succeeds');
  response=await admin.post('candidate-save',{id:candidateId,student_id:'AIU8100001',name:'Photo Candidate',position_id:positions[0].id,manifesto:'WebP photograph',photo_path:webpUpload.body.data.path},csrf);
  check(response.status===200,'candidate can use WebP photograph');
  check((await admin.call('/'+firstUpload.body.data.path.replace('../',''))).status===200,'replacement never deletes an image still referenced by another candidate');
  response=await admin.post('candidate-save',{id:sharedCandidateId,student_id:'AIU8100099',name:'Shared Photo Candidate',position_id:positions[0].id,manifesto:'Default avatar',photo_path:'',remove_photo:true},csrf);
  check(response.status===200,'shared candidate photograph can be removed independently');
  const secondUpload=await admin.upload(resolve('assets/amsa-logo.png'),csrf,'replacement.png');
  check(secondUpload.status===200&&/\.png$/.test(secondUpload.body.data.path),'valid PNG photograph upload succeeds');
  response=await admin.post('candidate-save',{id:candidateId,student_id:'AIU8100001',name:'Photo Candidate',position_id:positions[0].id,manifesto:'Updated photograph',photo_path:secondUpload.body.data.path},csrf);
  check(response.status===200,'candidate photograph replacement succeeds');
  check((await admin.call('/'+firstUpload.body.data.path.replace('../',''))).status===404,'image is deleted after its final database reference is removed');
  check((await admin.call('/'+webpUpload.body.data.path.replace('../',''))).status===404,'unreferenced WebP replacement is removed safely');
  response=await admin.post('candidate-save',{id:candidateId,student_id:'AIU8100001',name:'Photo Candidate',position_id:positions[0].id,manifesto:'Default avatar',photo_path:'',remove_photo:true},csrf);
  candidate=(await admin.call('/api/index.php?action=candidate-list')).body.data.candidates.find(c=>+c.id===+candidateId);
  check(response.status===200&&candidate.photo_path===null,'candidate photograph removal restores null fallback state');
  check((await admin.call('/'+secondUpload.body.data.path.replace('../',''))).status===404,'removed unreferenced photograph file is cleaned up');

  const malicious=new FormData();malicious.append('photo',new Blob(['<?php echo 1; ?>'],{type:'image/png'}),'attack.php.png');
  response=await admin.call('/api/upload.php',{method:'POST',body:malicious,headers:{'X-CSRF-Token':csrf}});
  check(response.status===422,'malicious non-image upload rejected');
  const oversized=new FormData();oversized.append('photo',new Blob([new Uint8Array(5*1024*1024+1)],{type:'image/png'}),'oversized.png');
  response=await admin.call('/api/upload.php',{method:'POST',body:oversized,headers:{'X-CSRF-Token':csrf}});
  check(response.status===422,'upload over 5 MB rejected');
  response=await admin.upload(resolve('assets/amsa-logo.png'),'invalid-csrf','csrf.png');
  check(response.status===419,'candidate upload requires CSRF validation');

  await admin.post('election-control',{control:'registration-open'},csrf);
  response=await admin.post('voter-create',{student_id:'AIU9100001',full_name:'Recovery Test Voter'},csrf);
  const oldPin=response.body.data.pin;
  const known=new Client(),knownCsrf=await known.csrf();
  const generic='Your PIN assistance request has been submitted. Please visit the AMSA Help Counter with your Student ID for identity verification.';
  const knownResponse=await known.post('pin-recovery-request',{student_id:'AIU9100001'},knownCsrf);
  const unknown=new Client(),unknownCsrf=await unknown.csrf();
  const unknownResponse=await unknown.post('pin-recovery-request',{student_id:'AIU9990001'},unknownCsrf);
  check(knownResponse.status===200&&unknownResponse.status===200&&knownResponse.body.data.message===generic&&unknownResponse.body.data.message===generic,'known and unknown Student IDs receive identical generic response');
  await known.post('pin-recovery-request',{student_id:'AIU9100001'},knownCsrf);
  let recoveries=(await admin.call('/api/index.php?action=pin-recovery-list&status=pending')).body.data;
  check(recoveries.pending_count===1&&recoveries.requests.length===1,'duplicate assistance request reuses one pending notification');
  const requestId=recoveries.requests[0].id;
  response=await admin.post('pin-recovery-action',{id:requestId,decision:'resolve',identity_verified:false},csrf);
  check(response.status===422,'PIN regeneration requires identity verification confirmation');
  const viewer=await adminLogin('test_viewer');
  response=await viewer.client.post('pin-recovery-action',{id:requestId,decision:'reject'},viewer.csrf);
  check(response.status===403,'read-only administrator cannot resolve recovery request');
  const officer=await adminLogin('test_officer');
  const concurrentReset=await Promise.all([admin.post('pin-recovery-action',{id:requestId,decision:'resolve',identity_verified:true},csrf),officer.client.post('pin-recovery-action',{id:requestId,decision:'resolve',identity_verified:true},officer.csrf)]);
  const successfulReset=concurrentReset.find(item=>item.status===200),newPin=successfulReset?.body.data.pin;
  check(concurrentReset.filter(item=>item.status===200).length===1&&concurrentReset.filter(item=>item.status===409).length===1,'concurrent PIN reset permits exactly one administrator');
  check(/^\d{6}$/.test(newPin),'verified recovery generates a six-digit replacement PIN');
  check((await voterLogin('AIU9100001',oldPin)).response.status===401,'old PIN is invalid after recovery');
  let voterSession=await voterLogin('AIU9100001',newPin);
  check(voterSession.response.status===200,'replacement PIN authenticates voter');
  recoveries=(await admin.call('/api/index.php?action=pin-recovery-list&status=resolved')).body.data;
  check(recoveries.requests.some(r=>+r.id===+requestId&&r.resolved_by_name),'resolved request persists with administrator and timestamp');

  for(let i=1;i<positions.length;i++)await admin.post('candidate-save',{id:0,student_id:`AIU810000${i+1}`,name:`Candidate ${i+1}`,position_id:positions[i].id,manifesto:'Election rehearsal candidate',photo_path:''},csrf);
  await admin.post('election-control',{control:'registration-close'},csrf);
  await admin.post('election-control',{control:'open'},csrf);
  const ballot=(await voterSession.client.call('/api/index.php?action=ballot')).body.data;
  check(ballot.positions.every(p=>p.candidates.length>=1),'ballot exposes candidates for all five positions');
  const choices=Object.fromEntries(ballot.positions.map(p=>[String(p.id),p.candidates[0].id]));
  response=await voterSession.client.post('submit-vote',{choices},voterSession.csrf);
  check(response.status===200,'recovered voter submits one complete ballot');

  const postVoteRequest=new Client(),postVoteCsrf=await postVoteRequest.csrf();
  await postVoteRequest.post('pin-recovery-request',{student_id:'AIU9100001'},postVoteCsrf);
  recoveries=(await admin.call('/api/index.php?action=pin-recovery-list&status=pending')).body.data;
  const postVoteId=recoveries.requests.find(r=>r.student_id==='AIU9100001')?.id;
  response=await admin.post('pin-recovery-action',{id:postVoteId,decision:'resolve',identity_verified:true},csrf);
  const afterVotePin=response.body.data.pin;
  check(response.status===200,'PIN can be securely replaced without editing ballot participation');
  const afterVoteLogin=await voterLogin('AIU9100001',afterVotePin);
  const afterVoteBallot=await afterVoteLogin.client.call('/api/index.php?action=ballot');
  check(afterVoteBallot.body.data.has_voted===true,'PIN recovery preserves completed voting status');
  response=await afterVoteLogin.client.post('submit-vote',{choices},afterVoteLogin.csrf);
  check(response.status===409,'PIN recovery does not bypass duplicate-vote prevention');

  const auditText=(await admin.call('/api/index.php?action=pin-recovery-list&status=all')).text;
  check(!auditText.includes(newPin)&&!auditText.includes(afterVotePin),'recovery history does not persist plaintext PINs');
  check(existsSync(resolve('uploads/candidates/.htaccess')),'candidate upload directory has local execution protection');
}catch(error){failed++;lines.push(`FAIL test runner exception: ${error.stack||error}`);}

for(const line of lines)console.log(line);
console.log(`SUMMARY passed=${passed} failed=${failed}`);
process.exitCode=failed?1:0;
