import {execFileSync} from 'node:child_process';

const base='http://127.0.0.1:8099';
const mysql='C:\\xampp\\mysql\\bin\\mysql.exe';
let passed=0,failed=0;
const results=[];

function check(condition,name,detail=''){
  if(condition){passed++;results.push(`PASS ${name}`);}
  else{failed++;results.push(`FAIL ${name}${detail?`: ${detail}`:''}`);}
}

class Client{
  cookies=new Map();
  async call(path,{method='GET',json,headers={},redirect='follow'}={}){
    const requestHeaders={...headers};
    if(this.cookies.size)requestHeaders.Cookie=[...this.cookies].map(([k,v])=>`${k}=${v}`).join('; ');
    if(json!==undefined){requestHeaders['Content-Type']='application/json';}
    const response=await fetch(base+path,{method,headers:requestHeaders,body:json===undefined?undefined:JSON.stringify(json),redirect});
    for(const value of response.headers.getSetCookie()){
      const pair=value.split(';',1)[0],split=pair.indexOf('=');
      if(split>0)this.cookies.set(pair.slice(0,split),pair.slice(split+1));
    }
    const text=await response.text();
    let body=null;try{body=JSON.parse(text);}catch{}
    return {status:response.status,headers:response.headers,text,body};
  }
  async csrf(){const response=await this.call('/api/index.php?action=csrf');return response.body.data.csrf;}
  async post(action,payload,csrf){return this.call(`/api/index.php?action=${action}`,{method:'POST',json:payload,headers:{'X-CSRF-Token':csrf}});}
}

async function loginAdmin(username='test_super',password='ElectionTest!2026'){
  const client=new Client(),csrf=await client.csrf();
  const response=await client.post('admin-login',{username,password},csrf);
  return {client,response,csrf:await client.csrf()};
}

async function loginVoter(student_id,pin){
  const client=new Client(),csrf=await client.csrf();
  const response=await client.post('voter-login',{student_id,pin},csrf);
  return {client,response,csrf:await client.csrf()};
}

try{
  const root=await new Client().call('/');
  check(root.status===200,'landing page loads');
  check(root.text.includes('assets/favicon-32x32.png'),'landing favicon is linked');
  const favicon=await new Client().call('/assets/favicon-32x32.png');
  check(favicon.status===200&&favicon.headers.get('content-type')?.includes('image/png'),'favicon returns PNG HTTP 200');

  const protectedPage=await new Client().call('/admin/admin-dashboard.php',{redirect:'manual'});
  check(protectedPage.status===302&&protectedPage.headers.get('location')==='admin-login.php','admin page rejects anonymous access');

  const badAdmin=await loginAdmin('test_super','incorrect-password');
  check(badAdmin.response.status===401,'incorrect administrator credentials rejected');
  const adminLogin=await loginAdmin();
  check(adminLogin.response.status===200,'administrator login succeeds');
  const admin=adminLogin.client;
  let adminCsrf=adminLogin.csrf;

  const initialMe=await admin.call('/api/index.php?action=me');
  check(initialMe.body.data.round.registration_open===false,'registration initially closed');
  check(initialMe.body.data.round.status==='draft','voting initially closed/draft');
  check(initialMe.body.data.round.results_published===false,'results initially closed');

  let response=await admin.post('election-control',{control:'registration-open'},adminCsrf);
  check(response.status===200,'registration opens');
  let persisted=await admin.call('/api/index.php?action=me');
  check(persisted.body.data.round.registration_open===true,'registration setting persists');

  response=await admin.post('election-control',{control:'registration-close'},'invalid-token');
  check(response.status===419,'settings update rejects invalid CSRF');
  response=await admin.post('election-control',{control:'registration-open'},adminCsrf);
  check(response.status===200,'independent registration control remains usable');

  const positions=(await admin.call('/api/index.php?action=positions')).body.data.positions;
  const expected=['President','Vice President','Secretary','Male Treasurer','Female Treasurer'];
  check(JSON.stringify(positions.map(p=>p.name))===JSON.stringify(expected),'five positions use required explicit order');

  const candidateIds=[];
  for(let index=0;index<positions.length;index++){
    response=await admin.post('candidate-save',{id:0,student_id:`AIU800000${index+1}`,name:`Test Candidate ${index+1}`,position_id:positions[index].id,manifesto:`Manifesto for ${positions[index].name}`,photo_path:''},adminCsrf);
    check(response.status===200,`candidate created for ${positions[index].name}`);
    candidateIds.push(response.body.data.candidate_id);
  }
  response=await admin.post('candidate-save',{id:candidateIds[0],student_id:'AIU8000001',name:'Test Candidate 1 Updated',position_id:positions[0].id,manifesto:'Updated test manifesto',photo_path:''},adminCsrf);
  check(response.status===200,'candidate editing works before voting');

  const voters=[];
  for(let index=1;index<=4;index++){
    response=await admin.post('voter-create',{student_id:`AIU900000${index}`,full_name:`Test Voter ${index}`,gender:'Prefer not to say',year_of_study:'1',major:'Testing',school:'Automated QA',student_email:''},adminCsrf);
    check(response.status===200&&/^\d{6}$/.test(response.body.data.pin),`voter ${index} created with six-digit PIN`);
    voters.push({student_id:`AIU900000${index}`,pin:response.body.data.pin});
  }
  response=await admin.post('voter-create',{student_id:voters[0].student_id,full_name:'Duplicate Test Voter'},adminCsrf);
  check(response.status===409,'duplicate Student ID rejected');
  response=await admin.post('election-control',{control:'registration-close'},adminCsrf);
  check(response.status===200,'registration closes independently');
  response=await admin.post('voter-create',{student_id:'AIU9000099',full_name:'Closed Registration'},adminCsrf);
  check(response.status===409,'closed registration blocks voter creation endpoint');

  const badVoter=await loginVoter(voters[0].student_id,'999999');
  check(badVoter.response.status===401,'incorrect voter PIN rejected');
  const missingVoter=await loginVoter('AIU9999999','999999');
  check(missingVoter.response.status===401,'nonexistent voter rejected');

  const viewerLogin=await loginAdmin('test_viewer');
  response=await viewerLogin.client.post('election-control',{control:'registration-open'},viewerLogin.csrf);
  check(response.status===403,'viewer cannot modify election settings');

  response=await admin.post('election-control',{control:'open'},adminCsrf);
  check(response.status===200,'voting opens with complete candidate roster');
  persisted=await admin.call('/api/index.php?action=me');
  check(persisted.body.data.round.status==='open'&&persisted.body.data.round.results_published===false,'opening voting does not publish results');

  const sessions=[];
  for(const voter of voters){sessions.push(await loginVoter(voter.student_id,voter.pin));}
  check(sessions.every(session=>session.response.status===200),'all test voters can authenticate');
  const ballotResponse=await sessions[0].client.call('/api/index.php?action=ballot');
  const ballot=ballotResponse.body.data;
  check(JSON.stringify(ballot.positions.map(p=>p.name))===JSON.stringify(expected),'voter ballot has correct position order');
  check(ballot.positions.every(p=>p.candidates.length===1&&p.candidates[0].id),'candidates map to the correct positions');
  const choices=Object.fromEntries(ballot.positions.map(p=>[String(p.id),p.candidates[0].id]));

  response=await sessions[0].client.post('submit-vote',{choices:{[String(ballot.positions[0].id)]:ballot.positions[0].candidates[0].id}},sessions[0].csrf);
  check(response.status===422,'incomplete ballot rejected');
  response=await sessions[0].client.post('submit-vote',{choices},sessions[0].csrf);
  check(response.status===200,'complete ballot submission succeeds');
  response=await sessions[0].client.post('submit-vote',{choices},sessions[0].csrf);
  check(response.status===409,'repeat ballot submission rejected');

  const secondSession=await loginVoter(voters[1].student_id,voters[1].pin);
  const concurrent=await Promise.all([
    sessions[1].client.post('submit-vote',{choices},sessions[1].csrf),
    secondSession.client.post('submit-vote',{choices},secondSession.csrf)
  ]);
  check(concurrent.filter(r=>r.status===200).length===1&&concurrent.filter(r=>r.status===409).length===1,'concurrent submissions create only one ballot');

  response=await sessions[2].client.post('election-control',{control:'close'},sessions[2].csrf);
  check(response.status===401,'voter cannot modify election settings');
  const hiddenResults=await sessions[0].client.call('/api/index.php?action=results');
  check(hiddenResults.status===200&&hiddenResults.body.data.published===false&&!('results' in hiddenResults.body.data),'hidden results API leaks no totals');
  const hiddenPresentation=await sessions[0].client.call('/api/index.php?action=results&presentation=1');
  check(hiddenPresentation.status===200&&hiddenPresentation.body.data.published===false&&!('results' in hiddenPresentation.body.data),'reveal-only query cannot bypass unpublished result privacy');

  execFileSync(mysql,['-u','root','-D','amsa_election_test','-e',"CREATE TRIGGER test_fail_choices BEFORE INSERT ON ballot_choices FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Injected test failure'"],{stdio:'pipe'});
  response=await sessions[3].client.post('submit-vote',{choices},sessions[3].csrf);
  check(response.status===500,'injected database failure reaches safe error response');
  execFileSync(mysql,['-u','root','-D','amsa_election_test','-e','DROP TRIGGER test_fail_choices'],{stdio:'pipe'});
  const afterFailure=await sessions[3].client.call('/api/index.php?action=ballot');
  check(afterFailure.body.data.has_voted===false,'database failure rolls back voter participation');
  response=await sessions[3].client.post('submit-vote',{choices},sessions[3].csrf);
  check(response.status===200,'voter can retry after rolled-back database failure');

  response=await admin.post('election-control',{control:'close'},adminCsrf);
  check(response.status===200,'voting closes');
  response=await sessions[2].client.post('submit-vote',{choices},sessions[2].csrf);
  check(response.status===409,'previously authenticated voter cannot submit after voting closes');

  response=await admin.post('election-control',{control:'publish'},adminCsrf);
  check(response.status===200,'results open independently after voting closes');
  persisted=await admin.call('/api/index.php?action=me');
  check(persisted.body.data.round.status==='closed'&&persisted.body.data.round.results_published===true,'publishing results does not reopen voting');
  const publicResults=await sessions[0].client.call('/api/index.php?action=results');
  check(publicResults.body.data.published===true,'authorized voter can view opened results');
  check(publicResults.body.data.results.positions.every(p=>p.candidates[0].votes===3),'result totals match three successful ballots');
  const presentationResults=await admin.call('/api/index.php?action=results&presentation=1');
  check(presentationResults.status===200&&presentationResults.body.data.results.positions.length===5,'published presenter response contains all five final positions');

  response=await admin.post('election-control',{control:'hide'},adminCsrf);
  check(response.status===200,'results close independently');
  const rehidden=await sessions[0].client.call('/api/index.php?action=results');
  check(rehidden.body.data.published===false&&!('results' in rehidden.body.data),'closed results become inaccessible again');

  const logout=await sessions[0].client.post('logout',{},sessions[0].csrf);
  check(logout.status===200,'voter logout succeeds');
  const afterLogout=await sessions[0].client.call('/api/index.php?action=ballot');
  check(afterLogout.status===401,'logged-out voter session cannot access ballot');

  const dbSummary=execFileSync(mysql,['-u','root','-D','amsa_election_test','--batch','--skip-column-names','-e',"SELECT COUNT(*) FROM anonymous_ballots; SELECT COUNT(*) FROM ballot_choices; SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='amsa_election_test' AND table_name IN ('anonymous_ballots','ballot_choices') AND column_name='voter_id'; SELECT COUNT(*) FROM information_schema.referential_constraints WHERE constraint_schema='amsa_election_test';"],{encoding:'utf8'}).trim().split(/\s+/).map(Number);
  check(dbSummary[0]===3&&dbSummary[1]===15,'database stores three complete five-choice ballots');
  check(dbSummary[2]===0,'anonymous ballot tables contain no voter identifier');
  check(dbSummary[3]===18,'all expected foreign-key relationships exist');
}catch(error){
  failed++;results.push(`FAIL test runner exception: ${error.stack||error}`);
}

for(const line of results)console.log(line);
console.log(`SUMMARY passed=${passed} failed=${failed}`);
process.exitCode=failed?1:0;
