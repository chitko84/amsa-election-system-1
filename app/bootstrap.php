<?php
declare(strict_types=1);
ini_set('display_errors', '0');
error_reporting(E_ALL);
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
$configFile = __DIR__.'/config.local.php';
if (!is_file($configFile)) {
    if (PHP_SAPI === 'cli') throw new RuntimeException('Copy app/config.example.php to app/config.local.php');
    http_response_code(503); exit('Application setup required. See README.md.');
}
$CONFIG = require $configFile;
$testDatabase=getenv('AMSA_TEST_DB');
if(in_array(PHP_SAPI,['cli','cli-server'],true)&&is_string($testDatabase)&&preg_match('/^amsa_election_test(?:_[a-z0-9_]+)?$/D',$testDatabase)){
    $CONFIG['db_name']=$testDatabase;
}
if (empty($CONFIG['secret']) || strlen($CONFIG['secret']) < 48 || str_contains($CONFIG['secret'],'REPLACE')) {
    if (PHP_SAPI === 'cli') throw new RuntimeException('Set a secure application secret');
    http_response_code(503); exit('Application secret not configured.');
}
function db(): PDO {
    global $CONFIG;
    static $db;
    if (!$db) {
        $dsn='mysql:host='.$CONFIG['db_host'].';port='.$CONFIG['db_port'].';dbname='.$CONFIG['db_name'].';charset=utf8mb4';
        $db=new PDO($dsn,$CONFIG['db_user'],$CONFIG['db_pass'],[
          PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES=>false
        ]);
    }
    return $db;
}
function secure_session(): void {
    if (PHP_SAPI==='cli' || session_status()===PHP_SESSION_ACTIVE) return;
    ini_set('session.use_strict_mode','1');
    $https=!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off';
    session_name('AMSA2026');
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','httponly'=>true,'secure'=>$https,'samesite'=>'Lax']);
    session_start();
    if (!isset($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
}
function csrf(): string {secure_session();return $_SESSION['csrf']??'';}
function verify_csrf(): void {
    secure_session();
    $v=$_SERVER['HTTP_X_CSRF_TOKEN']??'';
    if (!$v || !hash_equals(csrf(),$v)) fail('Invalid CSRF token',419);
}
function jsonout(array $data, int $status=200): never {
    http_response_code($status); header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);exit;
}
function ok(array $data=[]): never {jsonout(['ok'=>true,'data'=>$data]);}
function fail(string $msg,int $status=400): never {jsonout(['ok'=>false,'error'=>['message'=>$msg]],$status);}
function body(): array {
    $raw=file_get_contents('php://input');
    $v=json_decode($raw ?: '',true);
    if (!is_array($v)) fail('Expected JSON object',400);
    return $v;
}
function account(string $kind): array {
    secure_session();
    $id=$_SESSION[$kind.'_id']??null;
    if (!$id) fail('Authentication required',401);
    $table=$kind==='admin'?'admins':'voters';
    $s=db()->prepare("SELECT * FROM $table WHERE id=? AND active=1");$s->execute([$id]);
    $v=$s->fetch();if (!$v) fail('Authentication required',401);
    if($kind==='voter'&&(int)$v['pin_version']!==(int)($_SESSION['voter_pin_version']??-1)){
        unset($_SESSION['voter_id'],$_SESSION['voter_pin_version']);
        fail('Authentication required',401);
    }
    return $v;
}
function admin(bool $write=false): array {
    $a=account('admin');
    if ($write && $a['role']==='viewer') fail('Permission denied',403);
    return $a;
}
function current_round(bool $locked=false): ?array {
    $sql='SELECT r.*,e.title,e.registration_open,e.results_published FROM election_rounds r JOIN elections e ON e.id=r.election_id WHERE e.slug=? ORDER BY r.round_number DESC LIMIT 1';
    if($locked) $sql.=' FOR UPDATE';
    $s=db()->prepare($sql);$s->execute(['amsa-aiu-2026']);return $s->fetch()?:null;
}
function audit(int $adminId,string $action,string $detail=''): void {
    $s=db()->prepare('INSERT INTO admin_audit_logs(admin_id,event,detail) VALUES(?,?,?)');
    $s->execute([$adminId,$action,mb_substr($detail,0,255)]);
}
function cleanid($v): string {
    $id=strtoupper(trim((string)$v));
    if(!preg_match('/^AIU[0-9]{4,20}$/D',$id)) fail('Student ID must be AIU followed by digits',422);
    return $id;
}
function pinhash(string $pin): string {
    global $CONFIG;return hash_hmac('sha256',$pin,$CONFIG['secret']);
}
function secret_hash(string $pin): string {return password_hash(pinhash($pin),PASSWORD_DEFAULT);}
function secret_verify(string $pin,string $hash): bool {return password_verify(pinhash($pin),$hash);}
function ratelimit(string $kind,string $identifier,?bool $success=null): void {
    global $CONFIG;
    $h=hash_hmac('sha256',strtolower($identifier),$CONFIG['secret']);
    $ip=hash_hmac('sha256',$_SERVER['REMOTE_ADDR']??'cli',$CONFIG['secret']);
    if($success===null) {
      $s=db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE succeeded=0 AND created_at>DATE_SUB(NOW(),INTERVAL 15 MINUTE) AND (identifier_hash=? OR ip_hash=?)');
      $s->execute([$h,$ip]);if((int)$s->fetchColumn()>=7) fail('Too many attempts. Try again in 15 minutes.',429);
    }
    if($success!==null){$s=db()->prepare('INSERT INTO login_attempts(account_kind,identifier_hash,ip_hash,succeeded) VALUES(?,?,?,?)');
    $s->execute([$kind,$h,$ip,(int)$success]);}
}
secure_session();
