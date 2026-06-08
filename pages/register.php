<?php
$pageTitle = 'Daftar';
require_once '../config/database.php';
require_once '../config/session.php';

if (isLoggedIn()) { header('Location: /pages/dashboard.php'); exit; }

$errors   = [];
$formData = ['name' => '', 'email' => '', 'role' => 'musician'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password']         ?? '';
    $passC = $_POST['password_confirm'] ?? '';
    $role  = $_POST['role']             ?? 'musician';

    $formData = ['name' => $name, 'email' => $email, 'role' => $role];

    if (empty($name))                              $errors[] = 'Nama tidak boleh kosong.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (strlen($pass) < 8)                         $errors[] = 'Password minimal 8 karakter.';
    if ($pass !== $passC)                          $errors[] = 'Konfirmasi password tidak cocok.';
    if (!in_array($role, ['musician', 'band']))    $errors[] = 'Role tidak valid.';

    if (empty($errors)) {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Email sudah terdaftar.';
        } else {
            $hash  = password_hash($pass, PASSWORD_DEFAULT);
            $stmt2 = $db->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)");
            $stmt2->bind_param("sss", $email, $hash, $role);
            if ($stmt2->execute()) {
                $newUserId = $db->insert_id;
                // Create minimal profile so session name works after first login
                if ($role === 'musician') {
                    $sp = $db->prepare("INSERT INTO musicians (user_id, full_name) VALUES (?, ?)");
                    $sp->bind_param("is", $newUserId, $name);
                    $sp->execute();
                } else {
                    $sp = $db->prepare("INSERT INTO bands (user_id, band_name) VALUES (?, ?)");
                    $sp->bind_param("is", $newUserId, $name);
                    $sp->execute();
                }
                $_SESSION['flash_message'] = 'Akun dibuat. Sekarang masuk.';
                $_SESSION['flash_type']    = 'success';
                header('Location: /pages/login.php');
                exit;
            } else {
                $errors[] = 'Gagal mendaftar. Coba lagi.';
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card taped p-4 p-md-5 position-relative">
            <p class="kicker mb-3"><i class="fas fa-user-plus me-1"></i> Side B · Sign Up</p>
            <h1 class="h2 mb-1" style="text-transform:uppercase;letter-spacing:-0.03em;">Join the Scene</h1>
            <p class="text-muted mb-4">Daftar sekarang, langsung kelihatan di board.</p>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap / Nama Band</label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($formData['name']) ?>" required
                           maxlength="100" placeholder="Nama kamu atau nama band">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($formData['email']) ?>" required
                           placeholder="contoh@email.com">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required
                               placeholder="Minimal 8 karakter">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirm" class="form-control" required
                               placeholder="Ulangi password">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Daftar sebagai</label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="role" value="musician"
                                   id="roleMus" <?= $formData['role'] === 'musician' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="roleMus">
                                <i class="fas fa-guitar me-1 text-warning"></i>Musisi
                                <small class="d-block text-muted" style="font-size:.72rem;">Cari band atau gig</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="role" value="band"
                                   id="roleBand" <?= $formData['role'] === 'band' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="roleBand">
                                <i class="fas fa-users me-1 text-warning"></i>Band
                                <small class="d-block text-muted" style="font-size:.72rem;">Cari personel baru</small>
                            </label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-warning w-100">
                    <i class="fas fa-bolt me-2"></i>Buat Akun
                </button>
            </form>
            <p class="text-center mt-4 mb-0 text-muted">
                Sudah punya akun? <a href="/pages/login.php">Masuk</a>
            </p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
