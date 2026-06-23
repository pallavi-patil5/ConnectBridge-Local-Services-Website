<?php
// Load .env file if it exists (local development)
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

define('DB_HOST',    $_ENV['MYSQLHOST']   ?? $_ENV['DB_HOST']  ?? 'localhost');
define('DB_NAME',    $_ENV['MYSQLDATABASE'] ?? $_ENV['DB_NAME'] ?? 'worker_db');
define('DB_USER',    $_ENV['MYSQLUSER']   ?? $_ENV['DB_USER']  ?? 'root');
define('DB_PASS',    $_ENV['MYSQLPASSWORD'] ?? $_ENV['DB_PASS'] ?? '');
define('DB_PORT',    $_ENV['MYSQLPORT']   ?? 3306);
define('BASE_URL',   $_ENV['BASE_URL']    ?? '/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
