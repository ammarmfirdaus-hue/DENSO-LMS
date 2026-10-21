<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/includes/Database.php';

$npk = trim((string) ($argv[1] ?? ''));
$fullname = trim((string) ($argv[2] ?? ''));
$email = trim((string) ($argv[3] ?? ''));
$password = getenv('STUDENT_PASSWORD') ?: '';
if ($npk === '' || strlen($npk) > 50 || $fullname === '' || strlen($fullname) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
    fwrite(STDERR, "Usage: set STUDENT_PASSWORD securely, then php scripts/provision-student.php NPK \"Full Name\" email@example.com (password must be 12+ chars)\n");
    exit(2);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$statement = db()->prepare("INSERT INTO student_accounts(npk,fullname,email,password_hash) VALUES(:npk,:fullname,:email,:hash)");
$statement->execute(['npk' => $npk, 'fullname' => $fullname, 'email' => $email, 'hash' => $hash]);
fwrite(STDOUT, "Student account provisioned for {$npk}.\n");
