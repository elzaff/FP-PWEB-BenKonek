<?php
$pageTitle = 'Beranda';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/header.php';

$db = getDB();

$vacancies = [];
$result = $db->query("
    SELECT v.*, b.band_name, b.basecamp_location, b.whatsapp_number
    FROM vacancies v
    JOIN bands b ON v.band_id = b.id
    WHERE v.status = 'Open'
    ORDER BY v.created_at DESC
    LIMIT 6
");
if ($result) $vacancies = $result->fetch_all(MYSQLI_ASSOC);

$totalMusicians = $db->query("SELECT COUNT(*) FROM musicians")->fetch_row()[0] ?? 0;
$totalBands     = $db->query("SELECT COUNT(*) FROM bands")->fetch_row()[0] ?? 0;
$totalVacancies = $db->query("SELECT COUNT(*) FROM vacancies WHERE status='Open'")->fetch_row()[0] ?? 0;
?>

<div class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">
            <span class="text-warning">Ben</span>Konek
        </h1>
        <p class="lead mb-4" style="color:#9E9E9E;">
            Platform matchmaking musisi – temukan band, temukan musisi, buat karya bersama.
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="/pages/gigboard.php" class="btn btn-warning btn-lg px-4">
                <i class="fas fa-guitar me-2"></i>Lihat Lowongan
            </a>
            <a href="/pages/musicians.php" class="btn btn-outline-warning btn-lg px-4">
                <i class="fas fa-users me-2"></i>Cari Musisi
            </a>
        </div>
        <div class="row justify-content-center mt-5 g-4">
            <div class="col-auto text-center">
                <div class="display-6 fw-bold text-warning"><?= $totalMusicians ?></div>
                <small style="color:#9E9E9E;">Musisi Terdaftar</small>
            </div>
            <div class="col-auto text-center px-5">
                <div class="display-6 fw-bold text-warning"><?= $totalBands ?></div>
                <small style="color:#9E9E9E;">Band Aktif</small>
            </div>
            <div class="col-auto text-center">
                <div class="display-6 fw-bold text-warning"><?= $totalVacancies ?></div>
                <small style="color:#9E9E9E;">Lowongan Terbuka</small>
            </div>
        </div>
    </div>
</div>

<h2 class="section-title"><i class="fas fa-fire me-2"></i>Lowongan Terbaru</h2>

<?php if (empty($vacancies)): ?>
<div class="text-center py-5 card p-5">
    <i class="fas fa-music fa-3x mb-3 text-warning"></i>
    <p style="color:#9E9E9E;">Belum ada lowongan.
        <a href="/pages/register.php" class="text-warning">Daftar sebagai band</a> dan buat lowongan pertamamu!
    </p>
</div>
<?php else: ?>
<div class="row g-4 mb-4">
    <?php foreach ($vacancies as $v): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 vacancy-card">
            <div class="card-body">
                <span class="badge bg-warning text-dark mb-2"><?= htmlspecialchars($v['project_type']) ?></span>
                <h5 class="card-title text-warning"><?= htmlspecialchars($v['title']) ?></h5>
                <p style="color:#9E9E9E;" class="mb-1"><i class="fas fa-drum me-1"></i><?= htmlspecialchars($v['band_name']) ?></p>
                <p style="color:#9E9E9E;" class="mb-2"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($v['basecamp_location']) ?></p>
                <span class="badge-instrument"><i class="fas fa-guitar me-1"></i><?= htmlspecialchars($v['needed_instrument']) ?></span>
            </div>
            <div class="card-footer bg-transparent border-0 d-flex gap-2">
                <a href="/pages/vacancy_detail.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-warning flex-grow-1">Detail</a>
                <button class="btn btn-sm btn-success" onclick="hubungiWhatsApp('<?= htmlspecialchars($v['whatsapp_number']) ?>','<?= htmlspecialchars(addslashes($v['band_name'])) ?>','<?= htmlspecialchars(addslashes($v['title'])) ?>')">
                    <i class="fab fa-whatsapp"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<div class="text-center">
    <a href="/pages/gigboard.php" class="btn btn-outline-warning">
        Lihat Semua Lowongan <i class="fas fa-arrow-right ms-2"></i>
    </a>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
