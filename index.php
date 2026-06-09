<?php
$pageTitle = 'Beranda';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/header.php';

$db = getDB();

$vacancies = [];
$result = $db->query("
    SELECT v.*, b.band_name, b.basecamp_location, b.whatsapp_number, b.main_genre
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

<section class="hero overflow-hidden">
    <div class="container position-relative">
        <div class="hero__layout">
            <div class="hero__content">
                <p class="hero__eyebrow">Est. <?= date('Y') ?> · Hidupkan Skena Musik Indonesia</p>
                <h1 class="hero__title">Cari Musisi.<br>Cari <span class="accent">Pesona.</span><br>Bikin karya.</h1>
                <hr class="hero__rule" aria-hidden="true">
                <div class="hero__lead taped p-3 bg-paper-2 mb-4 d-inline-block">
                    Jago main musik tapi bingung mau jamming siapa? Di sini tempatnya! Ayo berkarya.
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <a href="/pages/gigboard.php" class="btn btn-warning btn-lg shadow-ink">
                        <i class="fas fa-bullhorn me-2"></i>Lihat Lowongan
                    </a>
                    <a href="/pages/musicians.php" class="btn btn-outline-warning btn-lg shadow-ink">
                        <i class="fas fa-users me-2"></i>Cari Musisi
                    </a>
                    <div class="roundel d-none d-md-flex ms-1" aria-hidden="true">
                        <span class="roundel__main">Live</span>
                        <span class="roundel__sub">Scene</span>
                    </div>
                </div>
            </div>
            <div class="hero__vinyl d-none d-lg-flex" aria-hidden="true">
                <div class="vinyl">
                    <div class="vinyl__label-wrap">
                        <div class="vinyl__label-inner">
                            <span class="vinyl__lbl-name">BK</span>
                            <span class="vinyl__lbl-sub">BENKONEK</span>
                        </div>
                    </div>
                    <div class="vinyl__spindle"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="ticker" aria-label="Statistik BenKonek">
    <div class="ticker__track">
        <span><b><?= (int)$totalMusicians ?></b> Musisi Terdaftar</span><span><i>/</i></span>
        <span><b><?= (int)$totalBands ?></b> Band Aktif</span><span><i>/</i></span>
        <span><b><?= (int)$totalVacancies ?></b> Lowongan Terbuka</span><span><i>/</i></span>
        <span>Calling All Musicians</span><span><i>/</i></span>
    </div>
</div>

<div class="section-head">
    <h2 class="section-title"><i class="fas fa-fire me-2"></i>Lowongan Terbaru</h2>
    <span class="count"><?= count($vacancies) ?> baru</span>
</div>

<?php if (empty($vacancies)): ?>
<div class="empty-state text-center p-5">
    <i class="fas fa-record-vinyl fa-3x mb-3"></i>
    <p class="text-muted mb-0">Belum ada lowongan.
        <a href="/pages/register.php">Daftar sebagai band</a> dan pasang yang pertama.
    </p>
</div>
<?php else: ?>
<div class="row g-4 mb-4">
    <?php foreach ($vacancies as $i => $v): ?>
    <div class="col-md-6 col-lg-4">
        <article class="card h-100 vacancy-card" data-reveal="<?= $i * 60 ?>">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-warning"><?= htmlspecialchars($v['project_type']) ?></span>
                    <span class="catalog-no stamp border-0 p-0" style="font-size: 0.65rem; transform: rotate(10deg); opacity: 0.7;">
                        NO. <?= str_pad((string)$v['id'], 3, '0', STR_PAD_LEFT) ?>
                    </span>
                </div>
                <h3 class="h5 card-title mb-2"><?= htmlspecialchars($v['title']) ?></h3>
                <p class="meta mb-1"><i class="fas fa-drum me-2"></i><?= htmlspecialchars($v['band_name']) ?></p>
                <p class="meta mb-3"><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($v['basecamp_location']) ?></p>
                <span class="badge-instrument mt-auto"><i class="fas fa-guitar me-1"></i><?= htmlspecialchars($v['needed_instrument']) ?></span>
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="/pages/vacancy_detail.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-warning flex-grow-1">Detail</a>
                <button class="btn btn-sm btn-success" aria-label="Hubungi via WhatsApp"
                        onclick="hubungiWhatsApp('<?= htmlspecialchars($v['whatsapp_number']) ?>','<?= htmlspecialchars(addslashes($v['band_name'])) ?>','<?= htmlspecialchars(addslashes($v['title'])) ?>')">
                    <i class="fab fa-whatsapp"></i>
                </button>
            </div>
        </article>
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
