<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
$action=(string)($_GET['action']??'');
$method=$_SERVER['REQUEST_METHOD']??'GET';
if(!in_array($method,['GET','POST'],true)) fail('Method not allowed',405);
if($method==='POST') verify_csrf();
$p=$method==='POST'?body():[];
function post_only(): void {global $method;if($method!=='POST') fail('POST required',405);}
function superadmin(): array {$a=admin();if($a['role']!=='super')fail('Super admin required',403);return $a;}
function position_order_sql(string $alias='p'): string {
 if(!preg_match('/^[a-z][a-z0-9_]*$/i',$alias))throw new InvalidArgumentException('Invalid SQL alias');
 return "CASE $alias.slug WHEN 'president' THEN 1 WHEN 'vice-president' THEN 2 WHEN 'secretary' THEN 3 WHEN 'male-treasurer' THEN 4 WHEN 'female-treasurer' THEN 5 ELSE 99 END";
}
function candidate_photo_path_valid(string $path): bool {
 return $path===''||preg_match('~^\.\./uploads/candidates/[a-f0-9]{32}\.(?:jpg|png|webp)$~D',$path)===1;
}
function cleanup_candidate_photo(?string $path): void {
 if(!$path||!candidate_photo_path_valid($path))return;
 $s=db()->prepare('SELECT COUNT(*) FROM candidates WHERE photo_path=?');$s->execute([$path]);
 if((int)$s->fetchColumn()!==0)return;
 $file=dirname(__DIR__).'/uploads/candidates/'.basename($path);
 if(is_file($file))@unlink($file);
}
function recovery_hash(string $value): string {
 global $CONFIG;return hash_hmac('sha256',strtolower($value),$CONFIG['secret']);
}
function candidates_for_round(int $round): array {
 $s=db()->prepare('SELECT p.id AS position_id,p.name AS position_name,'.position_order_sql().' AS display_order,c.id AS candidate_id,c.name,c.student_id,c.manifesto,c.photo_path FROM round_positions rp JOIN positions p ON p.id=rp.position_id LEFT JOIN (round_candidates rc JOIN candidates c ON c.id=rc.candidate_id AND c.active=1) ON rc.round_id=rp.round_id AND c.position_id=p.id WHERE rp.round_id=? ORDER BY '.position_order_sql().',c.name');
 $s->execute([$round]);$out=[];
 foreach($s->fetchAll() as $v){
  $id=(string)$v['position_id'];
  if(!isset($out[$id]))$out[$id]=['id'=>(int)$v['position_id'],'name'=>$v['position_name'],'order'=>(int)$v['display_order'],'candidates'=>[]];
  if($v['candidate_id']!==null)$out[$id]['candidates'][]=['id'=>(int)$v['candidate_id'],'name'=>$v['name'],'student_id'=>$v['student_id'],'manifesto'=>$v['manifesto'],'photo'=>$v['photo_path']];
 }
 return array_values($out);
}
function has_voted(int $round,int $voter): bool {
 $s=db()->prepare('SELECT voted FROM voter_participation WHERE round_id=? AND voter_id=?');$s->execute([$round,$voter]);return (bool)$s->fetchColumn();
}
function is_eligible(array $round,int $voter): bool {
 if($round['round_type']==='main')return true;
 $s=db()->prepare("SELECT vp.voted FROM voter_participation vp JOIN election_rounds r ON r.id=vp.round_id WHERE r.election_id=? AND r.round_type='main' AND vp.voter_id=? LIMIT 1");
 $s->execute([$round['election_id'],$voter]);return (bool)$s->fetchColumn();
}
function published_final_results(array $round): array {
 $s=db()->prepare('SELECT r.*,e.results_published FROM election_rounds r JOIN elections e ON e.id=r.election_id WHERE r.election_id=? ORDER BY r.round_number DESC');
 $s->execute([$round['election_id']]);$all=$s->fetchAll();$byId=[];
 foreach($all as $r){$result=result_data($r);foreach($result['positions'] as $pos){if(!isset($byId[$pos['id']]))$byId[$pos['id']]=$pos;}}
 $s=db()->prepare('SELECT id FROM positions p WHERE election_id=? ORDER BY '.position_order_sql());$s->execute([$round['election_id']]);
 $ordered=[];foreach($s->fetchAll() as $pos){if(isset($byId[$pos['id']]))$ordered[]=$byId[$pos['id']];}
 $recent=result_data($round);$recent['positions']=$ordered;
 return $recent;
}
function result_data(array $round): array {
 $s=db()->prepare('SELECT p.id AS position_id,p.name AS position_name,'.position_order_sql().' AS display_order,c.id AS candidate_id,c.name,c.photo_path,COUNT(bc.ballot_id) AS votes FROM round_positions rp JOIN positions p ON p.id=rp.position_id JOIN round_candidates rc ON rc.round_id=rp.round_id JOIN candidates c ON c.id=rc.candidate_id AND c.position_id=p.id LEFT JOIN ballot_choices bc ON bc.candidate_id=c.id AND bc.position_id=p.id AND bc.ballot_id IN(SELECT id FROM anonymous_ballots WHERE round_id=?) WHERE rp.round_id=? GROUP BY p.id,p.name,p.slug,c.id,c.name,c.photo_path ORDER BY '.position_order_sql().',votes DESC,c.name');
 $s->execute([$round['id'],$round['id']]);$out=[];
 foreach($s->fetchAll() as $v){$id=(string)$v['position_id'];if(!isset($out[$id]))$out[$id]=['id'=>(int)$v['position_id'],'name'=>$v['position_name'],'candidates'=>[]];$out[$id]['candidates'][]=['id'=>(int)$v['candidate_id'],'name'=>$v['name'],'photo'=>$v['photo_path'],'votes'=>(int)$v['votes']];}
 $s=db()->prepare('SELECT COUNT(*) FROM voter_participation WHERE round_id=? AND voted=1');$s->execute([$round['id']]);$voted=(int)$s->fetchColumn();
 $s=db()->query('SELECT COUNT(*) FROM voters WHERE active=1');$eligible=(int)$s->fetchColumn();
 return ['positions'=>array_values($out),'voted'=>$voted,'registered'=>$eligible,'status'=>$round['status'],'round_id'=>(int)$round['id'],'round_number'=>(int)$round['round_number'],'round_type'=>$round['round_type'],'published'=>(bool)$round['results_published']];
}
try {
 switch($action){
  case 'csrf': ok(['csrf'=>csrf()]);
  case 'pin-recovery-request':
   post_only();
   $generic='Your PIN assistance request has been submitted. Please visit the AMSA Help Counter with your Student ID for identity verification.';
   $raw=strtoupper(trim((string)($p['student_id']??'')));
   $identifierHash=recovery_hash($raw);$ipHash=recovery_hash($_SERVER['REMOTE_ADDR']??'unknown');
   $s=db()->prepare('SELECT COUNT(*) FROM pin_recovery_attempts WHERE created_at>DATE_SUB(NOW(),INTERVAL 15 MINUTE) AND (identifier_hash=? OR ip_hash=?)');
   $s->execute([$identifierHash,$ipHash]);if((int)$s->fetchColumn()>=5)fail('Too many assistance requests. Please wait 15 minutes and try again.',429);
   $voter=null;
   if(preg_match('/^AIU[0-9]{4,20}$/D',$raw)){$s=db()->prepare('SELECT id FROM voters WHERE student_id=? AND active=1');$s->execute([$raw]);$voter=$s->fetchColumn();}
   $pdo=db();$pdo->beginTransaction();
   try{
    $s=$pdo->prepare('INSERT INTO pin_recovery_attempts(identifier_hash,ip_hash,recognized) VALUES(?,?,?)');$s->execute([$identifierHash,$ipHash,$voter?1:0]);
    if($voter){
     $s=$pdo->prepare('SELECT id FROM voters WHERE id=? FOR UPDATE');$s->execute([(int)$voter]);
     $s=$pdo->prepare("INSERT IGNORE INTO pin_recovery_requests(voter_id,status) VALUES(?,'pending')");$s->execute([(int)$voter]);
    }
    $pdo->commit();ok(['message'=>$generic]);
   }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
  case 'admin-login':
   post_only();$username=trim((string)($p['username']??''));$password=(string)($p['password']??'');
   ratelimit('admin',$username);
   $s=db()->prepare('SELECT * FROM admins WHERE username=? AND active=1');$s->execute([$username]);$a=$s->fetch();
   if(!$a || !password_verify($password,$a['password_hash'])){ratelimit('admin',$username,false);fail('Invalid credentials',401);}
   ratelimit('admin',$username,true);session_regenerate_id(true);$_SESSION['admin_id']=(int)$a['id'];unset($_SESSION['voter_id']);ok(['name'=>$a['full_name'],'role'=>$a['role']]);
  case 'voter-login':
   post_only();$sid=strtoupper(trim((string)($p['student_id']??'')));$pin=(string)($p['pin']??'');
   ratelimit('voter',$sid);
   $s=db()->prepare('SELECT * FROM voters WHERE student_id=? AND active=1');$s->execute([$sid]);$v=$s->fetch();
   if(!preg_match('/^\d{6}$/D',$pin)||!$v||!secret_verify($pin,$v['pin_hash'])){ratelimit('voter',$sid,false);fail('Invalid credentials',401);}
   ratelimit('voter',$sid,true);session_regenerate_id(true);$_SESSION['voter_id']=(int)$v['id'];$_SESSION['voter_pin_version']=(int)$v['pin_version'];unset($_SESSION['admin_id']);ok(['name'=>$v['full_name']]);
  case 'logout':
   post_only();$_SESSION=[];session_regenerate_id(true);ok();
  case 'me':
   $round=current_round();$who=null;
   if(!empty($_SESSION['admin_id'])){$a=account('admin');$who=['type'=>'admin','name'=>$a['full_name'],'role'=>$a['role']];}
   elseif(!empty($_SESSION['voter_id'])){$v=account('voter');$who=['type'=>'voter','name'=>$v['full_name'],'student_id'=>$v['student_id'],'has_voted'=>$round?has_voted((int)$round['id'],(int)$v['id']):false,'eligible'=>$round?is_eligible($round,(int)$v['id']):false];}
   ok(['user'=>$who,'round'=>$round?['id'=>(int)$round['id'],'type'=>$round['round_type'],'status'=>$round['status'],'registration_open'=>(bool)$round['registration_open'],'results_published'=>(bool)$round['results_published']]:null,'csrf'=>csrf()]);
  case 'ballot':
   $v=account('voter');$round=current_round();if(!$round)fail('No election',404);
   ok(['round'=>['id'=>(int)$round['id'],'status'=>$round['status'],'type'=>$round['round_type']],'eligible'=>is_eligible($round,(int)$v['id']),'has_voted'=>has_voted((int)$round['id'],(int)$v['id']),'positions'=>candidates_for_round((int)$round['id'])]);
  case 'submit-vote':
   post_only();$v=account('voter');$choices=$p['choices']??null;
   if(!is_array($choices)||array_is_list($choices))fail('Invalid ballot selections',422);
   $pdo=db();$pdo->beginTransaction();
   try{
    $round=current_round(true);
    if(!$round||$round['status']!=='open')fail('Voting is closed',409);
    $s=$pdo->prepare('SELECT active FROM voters WHERE id=? FOR UPDATE');$s->execute([$v['id']]);if(!(bool)$s->fetchColumn())fail('Account disabled',403);
    if(!is_eligible($round,(int)$v['id']))fail('Not eligible for this round',403);
    $s=$pdo->prepare('INSERT IGNORE INTO voter_participation(round_id,voter_id,voted) VALUES(?,?,0)');$s->execute([$round['id'],$v['id']]);
    $s=$pdo->prepare('SELECT voted FROM voter_participation WHERE round_id=? AND voter_id=? FOR UPDATE');$s->execute([$round['id'],$v['id']]);if((int)$s->fetchColumn()===1)fail('Already voted',409);
    $positions=candidates_for_round((int)$round['id']);
    if(!$positions||count($choices)!==count($positions))fail('Every required position needs exactly one selection',422);
    $selected=[];
    foreach($positions as $pos){
      $key=(string)$pos['id'];$id=$choices[$key]??null;
      if(!is_int($id)&&!(is_string($id)&&ctype_digit($id)))fail('Missing/invalid candidate',422);
      $allowed=array_column($pos['candidates'],'id');
      if(!in_array((int)$id,$allowed,true))fail('Candidate is not eligible for this position',422);
      $selected[$key]=(int)$id;
    }
    $ballot=bin2hex(random_bytes(16));
    $s=$pdo->prepare('INSERT INTO anonymous_ballots(id,round_id) VALUES(?,?)');$s->execute([$ballot,$round['id']]);
    $s=$pdo->prepare('INSERT INTO ballot_choices(ballot_id,position_id,candidate_id) VALUES(?,?,?)');
    foreach($selected as $position=>$candidate)$s->execute([$ballot,(int)$position,$candidate]);
    $s=$pdo->prepare('UPDATE voter_participation SET voted=1,voted_at=NOW() WHERE round_id=? AND voter_id=? AND voted=0');$s->execute([$round['id'],$v['id']]);
    if($s->rowCount()!==1)fail('Already voted',409);
    $pdo->commit();ok(['has_voted'=>true,'message'=>'Your ballot was accepted. Your selections cannot be changed.']);
   }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
  case 'results':
   $round=current_round();if(!$round)fail('No election',404);
   $authorized=!empty($_SESSION['admin_id']);
   if($authorized) admin();else {if(empty($_SESSION['voter_id']))fail('Authentication required',401);account('voter');if(!$round['results_published'])ok(['published'=>false]);}
   $presentation=$authorized&&($_GET['presentation']??'')==='1'&&(bool)$round['results_published'];
   ok(['published'=>(bool)$round['results_published'],'results'=>$presentation||!$authorized?published_final_results($round):result_data($round)]);
  case 'voters':
   admin();$round=current_round();
   $s=db()->prepare('SELECT v.id,v.full_name,v.student_id,v.active,COALESCE(vp.voted,0) AS voted FROM voters v LEFT JOIN voter_participation vp ON vp.voter_id=v.id AND vp.round_id=? ORDER BY v.created_at DESC LIMIT 1000');$s->execute([$round['id']]);ok(['voters'=>$s->fetchAll()]);
  case 'voter-create':
   post_only();$a=admin(true);$round=current_round();if(!$round||!$round['registration_open'])fail('Registration is closed',409);
   $sid=cleanid($p['student_id']??'');$name=trim((string)($p['full_name']??''));if(mb_strlen($name)<2||mb_strlen($name)>160)fail('Invalid name',422);
   $pin=str_pad((string)random_int(0,999999),6,'0',STR_PAD_LEFT);
   $gender=trim((string)($p['gender']??''));$year=trim((string)($p['year_of_study']??''));$major=trim((string)($p['major']??''));$school=trim((string)($p['school']??''));$email=strtolower(trim((string)($p['student_email']??'')));
   if($gender!==''&&!in_array($gender,['Male','Female','Other','Prefer not to say'],true))fail('Invalid gender',422);
   if(mb_strlen($year)>30||mb_strlen($major)>160||mb_strlen($school)>160)fail('Information too long',422);
   if($email!==''&&!preg_match('/^[a-zA-Z0-9._%+\-]+@student\.aiu\.edu\.my$/D',$email))fail('Use your official student.aiu.edu.my email',422);
   $s=db()->prepare('INSERT INTO voters(student_id,full_name,pin_hash,gender,year_of_study,major,school,student_email,created_by) VALUES(?,?,?,?,?,?,?,?,?)');
   $s->execute([$sid,$name,secret_hash($pin),$gender?:null,$year?:null,$major?:null,$school?:null,$email?:null,$a['id']]);
   audit((int)$a['id'],'voter.created',$sid);ok(['voter'=>['id'=>(int)db()->lastInsertId(),'student_id'=>$sid,'full_name'=>$name],'pin'=>$pin]);
  case 'voter-toggle':
   post_only();$a=admin(true);$id=(int)($p['id']??0);$active=filter_var($p['active']??null,FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE);if($id<1||$active===null)fail('Invalid status',422);
   $s=db()->prepare('UPDATE voters SET active=? WHERE id=?');$s->execute([(int)$active,$id]);audit((int)$a['id'],'voter.status',(string)$id);ok();
  case 'voter-reset':
   post_only();$a=admin(true);$round=current_round();$id=(int)($p['id']??0);
   if($id<1||empty($p['identity_verified']))fail('Verify identity at counter before resetting PIN',422);
   $pin=str_pad((string)random_int(0,999999),6,'0',STR_PAD_LEFT);
   $s=db()->prepare('UPDATE voters SET pin_hash=?,pin_version=pin_version+1 WHERE id=?');$s->execute([secret_hash($pin),$id]);if(!$s->rowCount())fail('Voter not found',404);
   audit((int)$a['id'],'voter.pin_reset',(string)$id);ok(['pin'=>$pin]);
  case 'pin-recovery-list':
   admin();$status=(string)($_GET['status']??'pending');if(!in_array($status,['pending','resolved','rejected','all'],true))fail('Invalid request filter',422);
   $where=$status==='all'?'':'WHERE pr.status=?';
   $sql="SELECT pr.id,pr.status,pr.created_at,pr.resolved_at,v.id AS voter_id,v.full_name,v.student_id,a.full_name AS resolved_by_name FROM pin_recovery_requests pr JOIN voters v ON v.id=pr.voter_id LEFT JOIN admins a ON a.id=pr.resolved_by $where ORDER BY (pr.status='pending') DESC,pr.created_at DESC LIMIT 500";
   $s=db()->prepare($sql);$s->execute($status==='all'?[]:[$status]);
   $count=(int)db()->query("SELECT COUNT(*) FROM pin_recovery_requests WHERE status='pending'")->fetchColumn();ok(['requests'=>$s->fetchAll(),'pending_count'=>$count]);
  case 'pin-recovery-action':
   post_only();$a=admin(true);$id=(int)($p['id']??0);$decision=(string)($p['decision']??'');
   if($id<1||!in_array($decision,['resolve','reject'],true))fail('Invalid recovery action',422);
   if($decision==='resolve'&&empty($p['identity_verified']))fail('Confirm in-person identity verification before generating a new PIN',422);
   $pdo=db();$pdo->beginTransaction();
   try{
    $s=$pdo->prepare("SELECT pr.*,v.full_name,v.student_id,v.pin_version FROM pin_recovery_requests pr JOIN voters v ON v.id=pr.voter_id WHERE pr.id=? FOR UPDATE");$s->execute([$id]);$request=$s->fetch();
    if(!$request)fail('Recovery request not found',404);if($request['status']!=='pending')fail('This recovery request has already been reviewed',409);
    $pin=null;
    if($decision==='resolve'){
     $pin=str_pad((string)random_int(0,999999),6,'0',STR_PAD_LEFT);
     $s=$pdo->prepare('UPDATE voters SET pin_hash=?,pin_version=pin_version+1 WHERE id=?');$s->execute([secret_hash($pin),$request['voter_id']]);
     $status='resolved';$event='pin_recovery.resolved';
    }else{$status='rejected';$event='pin_recovery.rejected';}
    $s=$pdo->prepare('UPDATE pin_recovery_requests SET status=?,resolved_at=NOW(),resolved_by=? WHERE id=? AND status="pending"');$s->execute([$status,$a['id'],$id]);
    if($s->rowCount()!==1)fail('This recovery request has already been reviewed',409);
    audit((int)$a['id'],$event,'request='.$id.' voter='.$request['voter_id']);$pdo->commit();
    ok(['status'=>$status,'pin'=>$pin,'voter'=>['id'=>(int)$request['voter_id'],'full_name'=>$request['full_name'],'student_id'=>$request['student_id']]]);
   }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
  case 'candidate-list':
   admin();$round=current_round();$s=db()->prepare('SELECT c.*,p.name AS position_name FROM candidates c JOIN positions p ON p.id=c.position_id WHERE c.election_id=? ORDER BY '.position_order_sql().',c.name');$s->execute([$round['election_id']]);ok(['candidates'=>$s->fetchAll()]);
  case 'candidate-save':
   post_only();$a=admin(true);$round=current_round();if(!$round||(int)$round['round_number']!==1||$round['status']!=='draft')fail('Candidate roster is frozen after voting begins',409);
   $id=(int)($p['id']??0);$sid=cleanid($p['student_id']??'');$name=trim((string)($p['name']??''));$manifesto=trim((string)($p['manifesto']??''));$position=(int)($p['position_id']??0);$photo=(string)($p['photo_path']??'');$removePhoto=!empty($p['remove_photo']);
   if(!candidate_photo_path_valid($photo))fail('Invalid photo path',422);
   if(mb_strlen($name)<2||mb_strlen($name)>160||mb_strlen($manifesto)>5000)fail('Invalid candidate details',422);
   $s=db()->prepare('SELECT id FROM positions WHERE election_id=? AND id=?');$s->execute([$round['election_id'],$position]);if(!$s->fetchColumn())fail('Invalid position',422);
   $oldPhoto=null;
   if($id){
    $s=db()->prepare('SELECT photo_path FROM candidates WHERE id=? AND election_id=?');$s->execute([$id,$round['election_id']]);$oldPhoto=$s->fetchColumn();if($oldPhoto===false)fail('Candidate not found',404);
    $newPhoto=$removePhoto?null:($photo!==''?$photo:$oldPhoto);
    $s=db()->prepare('UPDATE candidates SET student_id=?,name=?,manifesto=?,position_id=?,photo_path=? WHERE id=? AND election_id=?');$s->execute([$sid,$name,$manifesto,$position,$newPhoto,$id,$round['election_id']]);
   }
   else {$s=db()->prepare('INSERT INTO candidates(election_id,position_id,student_id,name,manifesto,photo_path) VALUES(?,?,?,?,?,?)');$s->execute([$round['election_id'],$position,$sid,$name,$manifesto,$photo?:null]);$id=(int)db()->lastInsertId();$s=db()->prepare('INSERT INTO round_candidates(round_id,candidate_id) VALUES(?,?)');$s->execute([$round['id'],$id]);}
   audit((int)$a['id'],'candidate.saved',(string)$id);if($oldPhoto&&($removePhoto||($photo!==''&&$photo!==$oldPhoto)))cleanup_candidate_photo((string)$oldPhoto);ok(['candidate_id'=>$id]);
  case 'candidate-disable':
   post_only();$a=admin(true);$round=current_round();if(!$round||$round['status']!=='draft'||(int)$round['round_number']!==1)fail('Candidates are frozen',409);
   $id=(int)($p['id']??0);$s=db()->prepare('UPDATE candidates SET active=0 WHERE id=? AND election_id=?');$s->execute([$id,$round['election_id']]);audit((int)$a['id'],'candidate.disabled',(string)$id);ok();
  case 'positions':
   admin();$round=current_round();$s=db()->prepare('SELECT id,name FROM positions p WHERE election_id=? ORDER BY '.position_order_sql());$s->execute([$round['election_id']]);ok(['positions'=>$s->fetchAll()]);
  case 'election-control':
   post_only();$a=admin(true);$which=(string)($p['control']??'');$pdo=db();$pdo->beginTransaction();
   try{$round=current_round(true);if(!$round)fail('Election missing',404);
    if($which==='registration-open'||$which==='registration-close'){$s=$pdo->prepare('UPDATE elections SET registration_open=? WHERE id=?');$s->execute([$which==='registration-open'?1:0,$round['election_id']]);}
    elseif($which==='open'){
      if($round['status']!=='draft')fail('Round must be in draft status',409);
      foreach(candidates_for_round((int)$round['id']) as $pos){if(count($pos['candidates'])<1)fail('A required position has no candidates',409);}
      $s=$pdo->prepare('UPDATE election_rounds SET status="open" WHERE id=?');$s->execute([$round['id']]);
    } elseif($which==='close'){$s=$pdo->prepare('UPDATE election_rounds SET status="closed" WHERE id=? AND status="open"');$s->execute([$round['id']]);if($s->rowCount()!==1)fail('Voting is not open',409);}
    elseif($which==='publish'){
      if($round['status']!=='closed')fail('Close voting before publishing results',409);
      $results=result_data($round);if(count($results['positions'])!==count(candidates_for_round((int)$round['id'])))fail('Incomplete candidate roster',409);
      foreach($results['positions'] as $pos){$counts=array_column($pos['candidates'],'votes');rsort($counts);if(count($counts)>1&&$counts[0]===$counts[1])fail('Tie detected. Resolve tie before publication',409);}
      $s=$pdo->prepare('UPDATE elections SET results_published=1 WHERE id=?');$s->execute([$round['election_id']]);
    }elseif($which==='hide'){$s=$pdo->prepare('UPDATE elections SET results_published=0 WHERE id=?');$s->execute([$round['election_id']]);}
    else fail('Unknown control',422);
    audit((int)$a['id'],'election.'.$which);$pdo->commit();ok();
   }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
  case 'runoff-create':
   post_only();$a=superadmin();$pdo=db();$pdo->beginTransaction();
   try{$round=current_round(true);if(!$round||$round['status']!=='closed')fail('Close current round first',409);
    $res=result_data($round);$ties=[];
    foreach($res['positions'] as $pos){$votes=$pos['candidates'];if(count($votes)<2)continue;$max=max(array_column($votes,'votes'));$top=array_values(array_filter($votes,fn($c)=>$c['votes']===$max));if(count($top)>1)$ties[$pos['id']]=array_column($top,'id');}
    if(!$ties)fail('No tied positions found',409);
    $s=$pdo->prepare('UPDATE elections SET results_published=0 WHERE id=?');$s->execute([$round['election_id']]);
    $s=$pdo->prepare('INSERT INTO election_rounds(election_id,round_number,round_type,status) VALUES(?,?,"runoff","draft")');$s->execute([$round['election_id'],(int)$round['round_number']+1]);$new=(int)$pdo->lastInsertId();
    $rp=$pdo->prepare('INSERT INTO round_positions(round_id,position_id) VALUES(?,?)');$rc=$pdo->prepare('INSERT INTO round_candidates(round_id,candidate_id) VALUES(?,?)');
    foreach($ties as $pid=>$ids){$rp->execute([$new,$pid]);foreach($ids as $id)$rc->execute([$new,$id]);}
    audit((int)$a['id'],'runoff.created',(string)$new);$pdo->commit();ok(['round_id'=>$new,'positions'=>count($ties)]);
   }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
  default: fail('Unknown endpoint',404);
 }
}catch(PDOException $e){
 if(db()->inTransaction()) db()->rollBack();
 error_log('AMSA DB error: '.$e->getMessage());
 if($e->getCode()==='23000')fail('Duplicate record or invalid reference',409);
 fail('A database error occurred',500);
}catch(Throwable $e){
 if(db()->inTransaction())db()->rollBack();
 error_log('AMSA exception: '.$e->getMessage());
 fail('Server operation failed',500);
}
