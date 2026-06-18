<?php
function e($val): string {
    return htmlspecialchars((string)($val ?? ''), ENT_QUOTES, 'UTF-8');
}


function redirect(string $url): void {
    header("Location: $url");
    exit();
}

function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function requireWorker(): void {
    requireLogin();
    if ($_SESSION['role'] !== 'worker') {
        redirect('login.php');
    }
}

function requireAdmin(): void {
    if (!isset($_SESSION['admin_id'])) {
        redirect('admin_login.php');
    }
}
