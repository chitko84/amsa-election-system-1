<?php
declare(strict_types=1);
require_once __DIR__.'/bootstrap.php';
$page=basename((string)($_SERVER['SCRIPT_NAME']??''));
$segment=basename(dirname((string)($_SERVER['SCRIPT_NAME']??'')));
$openPublic=['login.php','register.php','forgot-password.php','reset-password.php','admin-login.php'];
if(!in_array($page,$openPublic,true)){
 if($segment==='admin'){
  if(empty($_SESSION['admin_id'])){header('Location: admin-login.php');exit;}
  $s=db()->prepare('SELECT id FROM admins WHERE id=? AND active=1');$s->execute([$_SESSION['admin_id']]);
  if(!$s->fetchColumn()){unset($_SESSION['admin_id']);header('Location: admin-login.php');exit;}
 }elseif($segment==='user'){
  if(empty($_SESSION['voter_id'])){header('Location: login.php');exit;}
  $s=db()->prepare('SELECT pin_version FROM voters WHERE id=? AND active=1');$s->execute([$_SESSION['voter_id']]);
  $version=$s->fetchColumn();if($version===false||(int)$version!==(int)($_SESSION['voter_pin_version']??-1)){unset($_SESSION['voter_id']);header('Location: login.php');exit;}
 }
}
header('Cache-Control: no-store');
