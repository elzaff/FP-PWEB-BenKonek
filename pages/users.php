<?php
$pageTitle = 'Manajemen User';
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$db = getDB();
$errors = [];
$validRoles = ['musician', 'band', 'admin'];
$roleLabels = [
    'musician' => 'Musisi',
    'band' => 'Band',
    'admin' => 'Admin',
];

function syncProfileForRole(mysqli $db, int $userId, string $role, string $name): void {
    if ($role === 'musician') {
        $deleteBand = $db->prepare("DELETE FROM bands WHERE user_id = ?");
        $deleteBand->bind_param("i", $userId);
        $deleteBand->execute();

        $stmt = $db->prepare("
            INSERT INTO musicians (user_id, full_name)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE full_name = VALUES(full_name)
        ");
        $stmt->bind_param("is", $userId, $name);
        $stmt->execute();
        return;
    }

    if ($role === 'band') {
        $deleteMusician = $db->prepare("DELETE FROM musicians WHERE user_id = ?");
        $deleteMusician->bind_param("i", $userId);
        $deleteMusician->execute();

        $stmt = $db->prepare("
            INSERT INTO bands (user_id, band_name)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE band_name = VALUES(band_name)
        ");
        $stmt->bind_param("is", $userId, $name);
        $stmt->execute();
        return;
    }

    $deleteMusician = $db->prepare("DELETE FROM musicians WHERE user_id = ?");
    $deleteMusician->bind_param("i", $userId);
    $deleteMusician->execute();
    $deleteBand = $db->prepare("DELETE FROM bands WHERE user_id = ?");
    $deleteBand->bind_param("i", $userId);
    $deleteBand->execute();
}

function emailExists(mysqli $db, string $email, ?int $exceptUserId = null): bool {
    if ($exceptUserId) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $stmt->bind_param("si", $email, $exceptUserId);
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
    }
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create_user') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'musician';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($fullName === '') $errors[] = 'Nama user wajib diisi.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
        if (strlen($password) < 8) $errors[] = 'Password minimal 8 karakter.';
        if (!in_array($role, $validRoles, true)) $errors[] = 'Role tidak valid.';
        if (emailExists($db, $email)) $errors[] = 'Email sudah dipakai user lain.';

        if (!$errors) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (full_name, email, password_hash, role, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $fullName, $email, $hash, $role, $isActive);
            $stmt->execute();
            syncProfileForRole($db, (int)$db->insert_id, $role, $fullName);
            $_SESSION['flash_message'] = 'User baru berhasil dibuat.';
            $_SESSION['flash_type'] = 'success';
            header('Location: /pages/users.php');
            exit;
        }
    }

    if ($action === 'update_user') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'musician';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($userId <= 0) $errors[] = 'User tidak valid.';
        if ($fullName === '') $errors[] = 'Nama user wajib diisi.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
        if ($password !== '' && strlen($password) < 8) $errors[] = 'Password baru minimal 8 karakter.';
        if (!in_array($role, $validRoles, true)) $errors[] = 'Role tidak valid.';
        if ($userId === (int)$_SESSION['user_id'] && ($role !== 'admin' || !$isActive)) {
            $errors[] = 'Admin tidak boleh menonaktifkan atau menurunkan role akunnya sendiri.';
        }
        if (emailExists($db, $email, $userId)) $errors[] = 'Email sudah dipakai user lain.';

        if (!$errors) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET full_name=?, email=?, password_hash=?, role=?, is_active=? WHERE id=?");
                $stmt->bind_param("ssssii", $fullName, $email, $hash, $role, $isActive, $userId);
            } else {
                $stmt = $db->prepare("UPDATE users SET full_name=?, email=?, role=?, is_active=? WHERE id=?");
                $stmt->bind_param("sssii", $fullName, $email, $role, $isActive, $userId);
            }
            $stmt->execute();
            syncProfileForRole($db, $userId, $role, $fullName);

            if ($userId === (int)$_SESSION['user_id']) {
                $_SESSION['name'] = $fullName;
                $_SESSION['role'] = $role;
            }

            $_SESSION['flash_message'] = 'Data user berhasil diperbarui.';
            $_SESSION['flash_type'] = 'success';
            header('Location: /pages/users.php');
            exit;
        }
    }

    if ($action === 'delete_user') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId === (int)$_SESSION['user_id']) {
            $errors[] = 'Admin tidak boleh menghapus akunnya sendiri.';
        } elseif ($userId > 0) {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $_SESSION['flash_message'] = 'User berhasil dihapus.';
            $_SESSION['flash_type'] = 'success';
            header('Location: /pages/users.php');
            exit;
        }
    }
}

$users = $db->query("
    SELECT u.*
    FROM users u
    ORDER BY FIELD(u.role, 'admin', 'band', 'musician'), u.full_name, u.email
")->fetch_all(MYSQLI_ASSOC);

require_once '../includes/header.php';
?>

<div class="section-head">
    <h2 class="section-title"><i class="fas fa-users-cog me-2"></i>Manajemen User</h2>
    <span class="count"><?= count($users) ?> akun</span>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger">
    <ul class="mb-0 ps-3">
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card p-4 mb-4">
    <p class="kicker mb-3">Create User</p>
    <form method="POST" class="row g-3">
        <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
        <input type="hidden" name="action" value="create_user">
        <div class="col-md-3">
            <label class="form-label">Nama</label>
            <input type="text" name="full_name" class="form-control" required placeholder="Nama user">
        </div>
        <div class="col-md-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required placeholder="user@email.test">
        </div>
        <div class="col-md-2">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required placeholder="Minimal 8">
        </div>
        <div class="col-md-2">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <?php foreach ($roleLabels as $role => $label): ?>
                <option value="<?= $role ?>"><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end justify-content-between gap-3">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="is_active" id="newActive" checked>
                <label class="form-check-label" for="newActive">Aktif</label>
            </div>
            <button type="submit" class="btn btn-warning"><i class="fas fa-plus me-1"></i>Buat</button>
        </div>
    </form>
</div>

<div class="card p-4">
    <p class="kicker mb-3">Read / Update / Delete User</p>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Reset Password</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <?php $formId = 'edit-user-' . (int)$u['id']; ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td>
                        <input form="<?= $formId ?>" type="text" name="full_name" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($u['full_name'] ?? '') ?>" required>
                    </td>
                    <td>
                        <input form="<?= $formId ?>" type="email" name="email" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($u['email']) ?>" required>
                    </td>
                    <td>
                        <select form="<?= $formId ?>" name="role" class="form-select form-select-sm">
                            <?php foreach ($roleLabels as $role => $label): ?>
                            <option value="<?= $role ?>" <?= $u['role'] === $role ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <div class="form-check">
                            <input form="<?= $formId ?>" class="form-check-input" type="checkbox"
                                   name="is_active" id="active-<?= (int)$u['id'] ?>" <?= (int)$u['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="active-<?= (int)$u['id'] ?>">Aktif</label>
                        </div>
                    </td>
                    <td>
                        <input form="<?= $formId ?>" type="password" name="password" class="form-control form-control-sm"
                               placeholder="Kosongkan jika tetap">
                    </td>
                    <td class="d-flex gap-2">
                        <form method="POST" id="<?= $formId ?>">
                            <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="update_user">
                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-save me-1"></i>Simpan
                            </button>
                        </form>
                        <form method="POST" id="delete-user-<?= (int)$u['id'] ?>">
                            <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="konfirmasiHapusForm('delete-user-<?= (int)$u['id'] ?>','User ini akan dihapus permanen!')">
                                <i class="fas fa-trash me-1"></i>Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
