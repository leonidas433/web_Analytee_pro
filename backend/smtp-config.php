<?php
require_once __DIR__ . '/../dashboard/env.php';
loadEnv(__DIR__ . '/../.env');

return [
    'host' => $_ENV['SMTP_HOST'] ?? 'smtp.ionos.es',
    'port' => $_ENV['SMTP_PORT'] ?? 465,
    'username' => $_ENV['SMTP_USER'] ?? '',
    'password' => $_ENV['SMTP_PASS'] ?? '',
    'encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'ssl',
    'from_email' => $_ENV['SMTP_FROM_EMAIL'] ?? '',
    'from_name' => $_ENV['SMTP_FROM_NAME'] ?? '',
];
