<?php
$pageTitle = 'Detail Lowongan';
require_once '../config/database.php';
require_once '../config/session.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: /pages/gigboard.php'); exit; }

$db   = getDB();
$stmt = $db->prepare("
    SELECT v.*, b.band_name, b.formation_year, b.main_genre, b.basecamp_location,
           b.whatsapp_number, b.photo_profile, b.user_id AS band_user_id, b.id AS band_id
    FROM vacancies v
    JOIN bands b ON v.band_id = b.id
    WHERE v.id = ?
");
$stmt->bind_param("i", $id); $stmt->execute();
$vacancy = $stmt->get_result()->fetch_assoc();

if (!$vacancy) {
    $_SESSION['flash_message'] = 'Lowongan tidak ditemukan.';
    $_SESSION['flash_type']    = 'error';
    header('Location: /pages/gigboard.php'); exit;
}

$isOwner = isLoggedIn()
    && $_SESSION['role'] === 'band'
    && (int)$_SESSION['user_id'] === (int)$vacancy['band_user_id'];

if ($isOwner) {
    if (isset($_GET['close'])) {
        $s = $db->prepare("UPDATE vacancies SET status='Closed' WHERE id=?");
        $s->bind_param("i", $id); $s->execute();
        $_SESSION['flash_message'] = 'Lowongan ditutup.';
        $_SESSION['flash_type']    = 'success';
        header('Location: /pages/vacancy_detail.php?id=' . $id); exit;
    }
    if (isset($_GET['reopen'])) {
        $s = $db->prepare("UPDATE vacancies SET status='Open' WHERE id=?");
        $s->bind_param("i", $id); $s->execute();
        $_SESSION['flash_message'] = 'Lowongan dibuka kembali.';
        $_SESSION['flash_type']    = 'success';
        header('Location: /pages/vacancy_detail.php?id=' . $id); exit;
    }
    if (isset($_GET['delete'])) {
        $s = $db->prepare("DELETE FROM vacancies WHERE id=? AND band_id=?");
        $s->bind_param("ii", $id, $vacancy['band_id']); $s->execute();
        $_SESSION['flash_message'] = 'Lowongan dihapus.';
        $_SESSION['flash_type']    = 'success';
        header('Location: /pages/dashboard.php'); exit;
    }
}

require_once '../includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-warning text-dark"><?= htmlspecialchars($vacancy['project_type']) ?></span>
                <span class="badge <?= $vacancy['status'] === 'Open' ? 'bg-success' : 'bg-secondary' ?>">
                    <?= $vacancy['status'] ?>
                </span>
            </div>

            <h2 class="text-warning"><?= htmlspecialchars($vacancy['title']) ?></h2>

            <div class="d-flex flex-wrap gap-3 mb-4 small" style="color:#9E9E9E;">
                <span><i class="fas fa-drum me-1"></i><?= htmlspecialchars($vacancy['band_name']) ?></span>
                <span><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($vacancy['basecamp_location']) ?></span>
                <span><i class="fas fa-calendar me-1"></i><?= date('d M Y', strtotime($vacancy['created_at'])) ?></span>
            </div>

            <h6 class="text-warning">Instrumen Dibutuhkan</h6>
            <span class="badge-instrument mb-4 fs-6">
                <i class="fas fa-guitar me-2"></i><?= htmlspecialchars($vacancy['needed_instrument']) ?>
            </span>

            <?php if ($vacancy['description']): ?>
            <h6 class="text-warning mt-3">Deskripsi</h6>
            <p style="color:#C0C0C0;white-space:pre-wrap;"><?= htmlspecialchars($vacancy['description']) ?></p>
            <?php endif; ?>

            <?php if ($vacancy['status'] === 'Open'): ?>
            <button class="btn btn-success btn-lg w-100 mt-2"
                    onclick="hubungiWhatsApp('<?= htmlspecialchars($vacancy['whatsapp_number']) ?>','<?= htmlspecialchars(addslashes($vacancy['band_name'])) ?>','<?= htmlspecialchars(addslashes($vacancy['title'])) ?>')">
                <i class="fab fa-whatsapp me-2"></i>Hubungi via WhatsApp
            </button>
            <?php endif; ?>

            <?php if ($isOwner): ?>
            <div class="d-flex flex-wrap gap-2 mt-3 pt-3" style="border-top:1px solid #2A2A2A;">
                <a href="/pages/vacancy_form.php?id=<?= $id ?>" class="btn btn-outline-warning btn-sm">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
                <?php if ($vacancy['status'] === 'Open'): ?>
                <a href="?id=<?= $id ?>&close=1" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-lock me-1"></i>Tutup
                </a>
                <?php else: ?>
                <a href="?id=<?= $id ?>&reopen=1" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-lock-open me-1"></i>Buka Kembali
                </a>
                <?php endif; ?>
                <button class="btn btn-outline-danger btn-sm"
                        onclick="konfirmasiHapus('/pages/vacancy_detail.php?id=<?= $id ?>&delete=1','Lowongan ini akan dihapus permanen!')">
                    <i class="fas fa-trash me-1"></i>Hapus
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card p-4 mb-3">
            <h5 class="text-warning mb-3"><i class="fas fa-drum me-2"></i>Tentang Band</h5>
            <?php if (!empty($vacancy['photo_profile'])): ?>
            <img src="/assets/images/uploads/<?= htmlspecialchars($vacancy['photo_profile']) ?>"
                 class="img-fluid rounded mb-3" style="height:140px;width:100%;object-fit:cover;"
                 alt="<?= htmlspecialchars($vacancy['band_name']) ?>">
            <?php endif; ?>
            <h6 class="mb-1"><?= htmlspecialchars($vacancy['band_name']) ?></h6>
            <p class="small mb-1" style="color:#9E9E9E;"><i class="fas fa-music me-1"></i><?= htmlspecialchars($vacancy['main_genre']) ?></p>
            <p class="small mb-1" style="color:#9E9E9E;"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($vacancy['basecamp_location']) ?></p>
            <?php if ($vacancy['formation_year']): ?>
            <p class="small mb-0" style="color:#9E9E9E;"><i class="fas fa-calendar me-1"></i>Berdiri <?= (int)$vacancy['formation_year'] ?></p>
            <?php endif; ?>
        </div>
        <a href="/pages/gigboard.php" class="btn btn-outline-warning w-100">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke GigBoard
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
