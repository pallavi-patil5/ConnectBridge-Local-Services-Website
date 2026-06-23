<?php
// Load .env file
$env = parse_ini_file(__DIR__ . '/../.env');

define('DB_HOST',    $env['DB_HOST']   ?? 'localhost');
define('DB_NAME',    $env['DB_NAME']   ?? 'worker_db');
define('DB_USER',    $env['DB_USER']   ?? 'root');
define('DB_PASS',    $env['DB_PASS']   ?? '');
define('BASE_URL',   $env['BASE_URL']  ?? '/DBMS/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
