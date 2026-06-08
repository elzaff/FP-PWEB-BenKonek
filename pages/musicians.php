<?php
$pageTitle = 'Direktori Musisi';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/header.php';

$db = getDB();

$filterInstrument = trim($_GET['instrument'] ?? '');
$filterCity       = trim($_GET['city']       ?? '');

$instrResult = $db->query("SELECT DISTINCT primary_instrument FROM musicians WHERE primary_instrument IS NOT NULL AND primary_instrument != '' ORDER BY primary_instrument");
$instrOptions = $instrResult ? $instrResult->fetch_all(MYSQLI_ASSOC) : [];

$where  = ['1=1'];
$params = [];
$types  = '';

if ($filterInstrument) {
    $where[]  = "m.primary_instrument = ?";
    $params[] = $filterInstrument;
    $types   .= 's';
}
if ($filterCity) {
    $where[]  = "m.location_city LIKE ?";
    $params[] = "%$filterCity%";
    $types   .= 's';
}

$whereSQL = implode(' AND ', $where);
$sql = "SELECT m.* FROM musicians m WHERE $whereSQL AND m.full_name IS NOT NULL ORDER BY m.full_name ASC";

if ($params) {
    $stmt = $db->prepare($sql); $stmt->bind_param($types, ...$params); $stmt->execute();
    $musicians = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $musicians = $db->query($sql)->fetch_all(MYSQLI_ASSOC);
}
?>

<div class="section-head">
    <h2 class="section-title"><i class="fas fa-users me-2"></i>Direktori Musisi</h2>
    <span class="count"><?= count($musicians) ?> musisi</span>
</div>

<div class="row">
    <div class="col-lg-3 mb-4">
        <div class="filter-sidebar">
            <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter</h5>
            <form method="GET">
                <div class="mb-3">
                    <label class="form-label">Instrumen</label>
                    <select name="instrument" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Instrumen</option>
                        <?php foreach ($instrOptions as $ins): ?>
                        <option value="<?= htmlspecialchars($ins['primary_instrument']) ?>"
                            <?= $filterInstrument === $ins['primary_instrument'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ins['primary_instrument']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kota</label>
                    <input type="text" name="city" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($filterCity) ?>" placeholder="Surabaya...">
                </div>
                <button type="submit" class="btn btn-warning btn-sm w-100">Terapkan</button>
                <?php if ($filterInstrument || $filterCity): ?>
                <a href="/pages/musicians.php" class="btn btn-outline-secondary btn-sm w-100 mt-2">
                    <i class="fas fa-times me-1"></i>Reset
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="col-lg-9">
        <?php if (empty($musicians)): ?>
        <div class="empty-state p-5 text-center">
            <i class="fas fa-guitar fa-3x mb-3"></i>
            <p class="text-muted mb-0">Belum ada musisi yang sesuai filter.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($musicians as $idx => $m): ?>
            <div class="col-md-6 col-xl-4">
                <article class="card h-100 taped" data-reveal="<?= ($idx % 6) * 50 ?>" style="transform: rotate(<?= ($idx % 2 == 0 ? '-1.5' : '1.5') ?>deg);">
                    <?php if (!empty($m['photo_profile'])): ?>
                    <img src="/assets/images/uploads/<?= htmlspecialchars($m['photo_profile']) ?>"
                         class="musician-card-img" alt="<?= htmlspecialchars($m['full_name']) ?>">
                    <?php else: ?>
                    <div class="musician-card-img d-flex align-items-center justify-content-center" style="background:var(--paper-3);">
                        <i class="fas fa-user fa-4x" style="color:var(--ink-faint);"></i>
                    </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h3 class="h5 card-title mb-2"><?= htmlspecialchars($m['full_name']) ?></h3>
                            <span class="catalog-no stamp border-0 p-0" style="font-size: 0.65rem; transform: rotate(10deg); opacity: 0.7;">
                                NO. <?= str_pad((string)$m['id'], 3, '0', STR_PAD_LEFT) ?>
                            </span>
                        </div>
                        </div>
                        <?php if ($m['primary_instrument']): ?>
                        <span class="badge-instrument mb-2">
                            <i class="fas fa-guitar me-1"></i><?= htmlspecialchars($m['primary_instrument']) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($m['location_city']): ?>
                        <p class="meta mb-1 mt-2">
                            <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($m['location_city']) ?>
                        </p>
                        <?php endif; ?>
                        <?php if ($m['experience_level']): ?>
                        <p class="meta mb-0">
                            <i class="fas fa-star me-1"></i><?= htmlspecialchars($m['experience_level']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php if ($m['whatsapp_number'] || $m['portfolio_url']): ?>
                    <div class="card-footer d-flex gap-2">
                        <?php if ($m['portfolio_url']): ?>
                        <a href="<?= htmlspecialchars($m['portfolio_url']) ?>" target="_blank" rel="noopener noreferrer"
                           class="btn btn-sm btn-outline-warning flex-grow-1">
                            <i class="fas fa-globe me-1"></i>Portfolio
                        </a>
                        <?php endif; ?>
                        <?php if ($m['whatsapp_number']): ?>
                        <button class="btn btn-sm btn-success flex-grow-1"
                                onclick="hubungiWhatsApp('<?= htmlspecialchars($m['whatsapp_number']) ?>','<?= htmlspecialchars(addslashes($m['full_name'])) ?>','Bergabung bersama')">
                            <i class="fab fa-whatsapp me-1"></i>WA
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
