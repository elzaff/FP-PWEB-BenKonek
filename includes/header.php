<?php
require_once __DIR__ . '/../config/session.php';

$flashMessage = '';
$flashType = '';
if (isset($_SESSION['flash_message'])) {
    $flashMessage = $_SESSION['flash_message'];
    $flashType    = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($pageTitle ?? 'BenKonek') ?> – BenKonek</title>
    <meta name="description" content="BenKonek — bingung mau jamming siapa dan bikin karya.Di sini tempatnya!!">
    <meta name="theme-color" content="#efe7d3">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Spline+Sans:wght@400;500;600;700&family=Spline+Sans+Mono:wght@400;500;600&family=Gochi+Hand&display=swap">
    <link rel="stylesheet" href="/assets/css/style.css?v=20260617-2">
</head>
<body>

<?php $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/index.php">
            <i class="fas fa-record-vinyl me-2"></i>BenKonek
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-label="Buka menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link <?= ($currentPath === '/index.php' || $currentPath === '/') ? 'active' : '' ?>" href="/index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link <?= (strpos($currentPath, '/pages/gigboard') === 0) ? 'active' : '' ?>" href="/pages/gigboard.php">GigBoard</a></li>
                <?php if ($currentUser && in_array($currentUser['role'], ['musician','band'], true)): ?>
                <li class="nav-item"><a class="nav-link <?= (strpos($currentPath, '/pages/connections') === 0) ? 'active' : '' ?>" href="/pages/connections.php">Pendaftaran</a></li>
                <?php endif; ?>
                <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                <li class="nav-item"><a class="nav-link <?= (strpos($currentPath, '/pages/users') === 0) ? 'active' : '' ?>" href="/pages/users.php">User</a></li>
                <li class="nav-item"><a class="nav-link <?= (strpos($currentPath, '/pages/rbac') === 0) ? 'active' : '' ?>" href="/pages/rbac.php">RBAC</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav align-items-center">
                <?php if ($currentUser): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/pages/dashboard.php">
                            <i class="fas fa-user-circle me-1 text-warning"></i>
                            <span class="text-warning"><?= htmlspecialchars($currentUser['name']) ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/pages/logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Keluar
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/pages/login.php">Masuk</a></li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-warning btn-sm fw-semibold px-3" href="/pages/register.php">Daftar</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4">
