<?php
$pageTitle = 'Masuk';
require_once '../config/database.php';
require_once '../config/session.php';

if (isLoggedIn()) { header('Location: /pages/dashboard.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password']  ?? '';

    if (empty($email) || empty($pass)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, password_hash, role FROM users WHERE email = ? AND is_active = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($pass, $user['password_hash'])) {
            $name = $email;
            if ($user['role'] === 'musician') {
                $s = $db->prepare("SELECT full_name FROM musicians WHERE user_id = ?");
                $s->bind_param("i", $user['id']); $s->execute();
                $row = $s->get_result()->fetch_assoc();
                if ($row && $row['full_name']) $name = $row['full_name'];
            } elseif ($user['role'] === 'band') {
                $s = $db->prepare("SELECT band_name FROM bands WHERE user_id = ?");
                $s->bind_param("i", $user['id']); $s->execute();
                $row = $s->get_result()->fetch_assoc();
                if ($row && $row['band_name']) $name = $row['band_name'];
            } else {
                $name = 'Admin';
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['name']    = $name;

            $_SESSION['flash_message'] = 'Selamat datang, ' . $name . '!';
            $_SESSION['flash_type']    = 'success';
            header('Location: /pages/dashboard.php');
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card p-4 p-md-5">
            <p class="kicker mb-3"><i class="fas fa-record-vinyl me-1"></i> Side A · Masuk</p>
            <h1 class="h2 mb-1" style="text-transform:uppercase;letter-spacing:-0.03em;">Masuk Lagi</h1>
            <p class="text-muted mb-4">Lanjutin dari mana kamu berhenti.</p>

            <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required
                           placeholder="contoh@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required
                           placeholder="Password kamu">
                </div>
                <button type="submit" class="btn btn-warning w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Masuk
                </button>
            </form>
            <p class="text-center mt-4 mb-0 text-muted">
                Belum punya akun? <a href="/pages/register.php">Daftar</a>
            </p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
