<?php
$pageTitle = 'Pendaftaran';
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$db   = getDB();
$user = getCurrentUser();
$role = $user['role'];

function statusBadge(string $status): string {
    $map = ['Pending' => 'bg-warning', 'Accepted' => 'bg-success', 'Rejected' => 'bg-secondary'];
    $label = ['Pending' => 'Menunggu', 'Accepted' => 'Diterima', 'Rejected' => 'Ditolak'];
    $cls = $map[$status] ?? 'bg-secondary';
    return '<span class="badge ' . $cls . '">' . htmlspecialchars($label[$status] ?? $status) . '</span>';
}

$rows = [];

if ($role === 'band') {
    $s = $db->prepare("SELECT id FROM bands WHERE user_id = ?");
    $s->bind_param("i", $user['id']); $s->execute();
    $bandId = $s->get_result()->fetch_assoc()['id'] ?? 0;
    if ($bandId) {
        $q = $db->prepare("
            SELECT c.*, m.full_name AS counterpart, m.primary_instrument, m.experience_level,
                   m.location_city, v.title AS vacancy_title
            FROM connections c
            JOIN musicians m ON c.musician_id = m.id
            JOIN vacancies v ON c.vacancy_id = v.id
            WHERE c.band_id = ?
            ORDER BY FIELD(c.status,'Pending','Accepted','Rejected'), c.created_at DESC");
        $q->bind_param("i", $bandId); $q->execute();
        $rows = $q->get_result()->fetch_all(MYSQLI_ASSOC);
    }
} elseif ($role === 'musician') {
    $s = $db->prepare("SELECT id FROM musicians WHERE user_id = ?");
    $s->bind_param("i", $user['id']); $s->execute();
    $musId = $s->get_result()->fetch_assoc()['id'] ?? 0;
    if ($musId) {
        $q = $db->prepare("
            SELECT c.*, b.band_name AS counterpart, b.main_genre, b.basecamp_location,
                   v.title AS vacancy_title
            FROM connections c
            JOIN bands b ON c.band_id = b.id
            JOIN vacancies v ON c.vacancy_id = v.id
            WHERE c.musician_id = ?
            ORDER BY c.created_at DESC");
        $q->bind_param("i", $musId); $q->execute();
        $rows = $q->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

require_once '../includes/header.php';
?>

<div class="section-head">
    <h2 class="section-title"><i class="fas fa-inbox me-2"></i>Pendaftaran</h2>
    <span class="count"><?= count($rows) ?> total</span>
</div>

<?php if ($role === 'admin'): ?>
<div class="card p-4">
    <p class="meta mb-0">Admin memantau seluruh data pendaftaran lewat database / phpMyAdmin. Halaman ini khusus alur musisi dan band.</p>
</div>
<?php require_once '../includes/footer.php'; exit; ?>
<?php endif; ?>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <h3 class="h5 mb-0">
            <i class="fas <?= $role === 'band' ? 'fa-users' : 'fa-paper-plane' ?> me-2"></i>
            <?= $role === 'band' ? 'Pendaftar Lowonganmu' : 'Lamaran yang Kamu Kirim' ?>
        </h3>
        <?php if ($role === 'band' && !empty($rows)): ?>
        <a href="/pages/struk.php" target="_blank" rel="noopener" class="btn btn-sm btn-outline-warning">
            <i class="fas fa-print me-1"></i>Cetak Daftar Pendaftar
        </a>
        <?php endif; ?>
    </div>
    <?php if (empty($rows)): ?>
    <div class="empty-state text-center p-4">
        <i class="fas fa-folder-open fa-2x mb-3"></i>
        <p class="meta mb-0">
            <?php if ($role === 'band'): ?>
            Belum ada musisi yang mendaftar. Pasang lowongan menarik di <a href="/pages/dashboard.php">dashboard</a>.
            <?php else: ?>
            Belum mendaftar lowongan. Lihat <a href="/pages/gigboard.php">GigBoard</a> dan daftar yang cocok.
            <?php endif; ?>
        </p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th><?= $role === 'band' ? 'Musisi' : 'Band' ?></th>
                    <th class="d-none d-md-table-cell">Lowongan</th>
                    <?php if ($role === 'band'): ?><th class="d-none d-lg-table-cell">Pesan</th><?php endif; ?>
                    <th>Status</th>
                    <?php if ($role === 'band'): ?><th>Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $c): ?>
                <tr>
                    <td>
                        <span class="fw-semibold"><?= htmlspecialchars($c['counterpart']) ?></span>
                        <?php if ($role === 'band'): ?>
                        <div class="meta" style="font-size:.72rem;">
                            <?= htmlspecialchars(trim(($c['primary_instrument'] ?? '') . ' · ' . ($c['experience_level'] ?? ''), ' ·')) ?>
                            <?php if (!empty($c['location_city'])): ?> · <?= htmlspecialchars($c['location_city']) ?><?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="meta" style="font-size:.72rem;"><?= htmlspecialchars(trim(($c['main_genre'] ?? '') . ' · ' . ($c['basecamp_location'] ?? ''), ' ·')) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="d-none d-md-table-cell meta" style="font-size:.78rem;"><?= htmlspecialchars($c['vacancy_title']) ?></td>
                    <?php if ($role === 'band'): ?>
                    <td class="d-none d-lg-table-cell meta" style="font-size:.78rem;max-width:240px;">
                        <?= $c['message'] ? htmlspecialchars($c['message']) : '<span class="text-muted">—</span>' ?>
                    </td>
                    <?php endif; ?>
                    <td><?= statusBadge($c['status']) ?></td>
                    <?php if ($role === 'band'): ?>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <?php if ($c['status'] === 'Pending'): ?>
                            <form method="POST" action="/pages/connect.php" class="d-inline">
                                <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
                                <input type="hidden" name="action" value="set_status">
                                <input type="hidden" name="connection_id" value="<?= (int)$c['id'] ?>">
                                <input type="hidden" name="status" value="Accepted">
                                <button class="btn btn-sm btn-success" title="Terima"><i class="fas fa-check"></i></button>
                            </form>
                            <form method="POST" action="/pages/connect.php" class="d-inline">
                                <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
                                <input type="hidden" name="action" value="set_status">
                                <input type="hidden" name="connection_id" value="<?= (int)$c['id'] ?>">
                                <input type="hidden" name="status" value="Rejected">
                                <button class="btn btn-sm btn-outline-danger" title="Tolak"><i class="fas fa-times"></i></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
