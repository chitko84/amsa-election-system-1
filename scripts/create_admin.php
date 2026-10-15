<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') {http_response_code(403);exit;}
require dirname(__DIR__).'/app/bootstrap.php';
if ($argc<4) {fwrite(STDERR,"Usage: php scripts/create_admin.php username full_name super|officer|viewer\n");exit(1);}
[$file,$username,$name,$role]=$argv;
if (!preg_match('/^[a-zA-Z0-9_.-]{3,60}$/',$username) || !in_array($role,['super','officer','viewer'],true)) exit("Invalid username or role\n");
echo "Password for $username (input visible; use a private terminal): ";
$pass=trim((string)fgets(STDIN));
if(strlen($pass)<12) exit("At least 12 characters required\n");
$s=db()->prepare('INSERT INTO admins(username,password_hash,full_name,role) VALUES(?,?,?,?)');
$s->execute([$username,password_hash($pass,PASSWORD_DEFAULT),$name,$role]);
echo "Admin created.\n";
