<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        $_SESSION['flash_message'] = 'Silakan login terlebih dahulu.';
        $_SESSION['flash_type'] = 'warning';
        header('Location: /pages/login.php');
        exit;
    }
}

function requireRole(string $role): void {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        $_SESSION['flash_message'] = 'Akses tidak diizinkan untuk role ini.';
        $_SESSION['flash_type'] = 'error';
        header('Location: /pages/dashboard.php');
        exit;
    }
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'   => $_SESSION['user_id'],
        'role' => $_SESSION['role'],
        'name' => $_SESSION['name'] ?? '',
    ];
}
