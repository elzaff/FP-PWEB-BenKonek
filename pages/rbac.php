<?php
$pageTitle = 'RBAC';
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$db = getDB();

$modules = $db->query("SELECT * FROM app_modules ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$roles = $db->query("SELECT * FROM roles ORDER BY FIELD(code, 'guest', 'musician', 'band', 'admin')")->fetch_all(MYSQLI_ASSOC);
$rules = $db->query("
    SELECT r.label AS role_label, p.permission_key, m.module_name, p.action, p.description
    FROM role_permissions rp
    JOIN roles r ON rp.role_code = r.code
    JOIN permissions p ON rp.permission_id = p.id
    JOIN app_modules m ON p.module_key = m.module_key
    ORDER BY FIELD(r.code, 'guest', 'musician', 'band', 'admin'), m.id, p.action, p.permission_key
")->fetch_all(MYSQLI_ASSOC);
$matrix = $db->query("
    SELECT m.module_name, r.label AS role_label, c.can_create, c.can_read, c.can_update, c.can_delete, c.scope_note
    FROM crud_security_matrix c
    JOIN app_modules m ON c.module_key = m.module_key
    JOIN roles r ON c.role_code = r.code
    ORDER BY m.id, FIELD(r.code, 'guest', 'musician', 'band', 'admin')
")->fetch_all(MYSQLI_ASSOC);

$yesNo = function ($value): string {
    return $value ? '<span class="badge bg-success">Ya</span>' : '<span class="badge bg-secondary">Tidak</span>';
};

require_once '../includes/header.php';
?>

<div class="section-head">
    <h2 class="section-title"><i class="fas fa-shield-alt me-2"></i>RBAC & CRUD Security</h2>
    <span class="count">PWEB Bagian 4</span>
</div>

<div class="card p-4 mb-4">
    <p class="kicker mb-3">1. Modul / Fitur Aplikasi</p>
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr><th>Modul</th><th>Keterangan</th></tr>
            </thead>
            <tbody>
                <?php foreach ($modules as $module): ?>
                <tr>
                    <td><?= htmlspecialchars($module['module_name']) ?></td>
                    <td><?= htmlspecialchars($module['description']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card p-4 mb-4">
    <p class="kicker mb-3">2. Jenis User</p>
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr><th>Role</th><th>Keterangan</th></tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                <tr>
                    <td><?= htmlspecialchars($role['label']) ?></td>
                    <td><?= htmlspecialchars($role['description']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card p-4 mb-4">
    <p class="kicker mb-3">3. Tabel RBAC</p>
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr><th>Role</th><th>Permission</th><th>Modul</th><th>Aksi</th><th>Keterangan</th></tr>
            </thead>
            <tbody>
                <?php foreach ($rules as $rule): ?>
                <tr>
                    <td><?= htmlspecialchars($rule['role_label']) ?></td>
                    <td><span class="badge-instrument"><?= htmlspecialchars($rule['permission_key']) ?></span></td>
                    <td><?= htmlspecialchars($rule['module_name']) ?></td>
                    <td><?= htmlspecialchars(strtoupper($rule['action'])) ?></td>
                    <td><?= htmlspecialchars($rule['description']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card p-4">
    <p class="kicker mb-3">4. Metrik Security Tabel CRUD</p>
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th>Modul</th>
                    <th>Role</th>
                    <th>Create</th>
                    <th>Read</th>
                    <th>Update</th>
                    <th>Delete</th>
                    <th>Scope / Rule</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matrix as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['module_name']) ?></td>
                    <td><?= htmlspecialchars($row['role_label']) ?></td>
                    <td><?= $yesNo($row['can_create']) ?></td>
                    <td><?= $yesNo($row['can_read']) ?></td>
                    <td><?= $yesNo($row['can_update']) ?></td>
                    <td><?= $yesNo($row['can_delete']) ?></td>
                    <td><?= htmlspecialchars($row['scope_note']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
