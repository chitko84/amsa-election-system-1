<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}

putenv('AMSA_TEST_DB=amsa_election_test');
require dirname(__DIR__).'/app/bootstrap.php';

$pdo=db();
$pdo->prepare('INSERT INTO admins(username,password_hash,full_name,role,active) VALUES(?,?,?,?,1)')
    ->execute(['test_super',password_hash('ElectionTest!2026',PASSWORD_DEFAULT),'Automated Test Administrator','super']);
$pdo->prepare('INSERT INTO admins(username,password_hash,full_name,role,active) VALUES(?,?,?,?,1)')
    ->execute(['test_viewer',password_hash('ElectionTest!2026',PASSWORD_DEFAULT),'Automated Test Viewer','viewer']);
$pdo->prepare('INSERT INTO admins(username,password_hash,full_name,role,active) VALUES(?,?,?,?,1)')
    ->execute(['test_officer',password_hash('ElectionTest!2026',PASSWORD_DEFAULT),'Automated Test Officer','officer']);

echo "Test administrators created.\n";
