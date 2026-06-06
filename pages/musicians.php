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

<h2 class="text-warning mb-4"><i class="fas fa-users me-2"></i>Direktori Musisi</h2>

<div class="row">
    <div class="col-lg-3 mb-4">
        <div class="filter-sidebar">
            <h5 class="text-warning mb-3"><i class="fas fa-filter me-2"></i>Filter</h5>
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
        <p class="text-center mt-3 small" style="color:#9E9E9E;"><?= count($musicians) ?> musisi ditemukan</p>
    </div>

    <div class="col-lg-9">
        <?php if (empty($musicians)): ?>
        <div class="card p-5 text-center">
            <i class="fas fa-guitar fa-3x mb-3 text-warning"></i>
            <p style="color:#9E9E9E;">Belum ada musisi yang sesuai filter.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($musicians as $m): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <?php if (!empty($m['photo_profile'])): ?>
                    <img src="/assets/images/uploads/<?= htmlspecialchars($m['photo_profile']) ?>"
                         class="musician-card-img rounded-top" alt="<?= htmlspecialchars($m['full_name']) ?>">
                    <?php else: ?>
                    <div class="musician-card-img rounded-top bg-secondary d-flex align-items-center justify-content-center">
                        <i class="fas fa-user fa-4x" style="color:#555;"></i>
                    </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title text-warning mb-1"><?= htmlspecialchars($m['full_name']) ?></h5>
                        <?php if ($m['primary_instrument']): ?>
                        <span class="badge-instrument mb-2">
                            <i class="fas fa-guitar me-1"></i><?= htmlspecialchars($m['primary_instrument']) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($m['location_city']): ?>
                        <p class="small mb-1" style="color:#9E9E9E;">
                            <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($m['location_city']) ?>
                        </p>
                        <?php endif; ?>
                        <?php if ($m['experience_level']): ?>
                        <p class="small mb-0" style="color:#9E9E9E;">
                            <i class="fas fa-star me-1"></i><?= htmlspecialchars($m['experience_level']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php if ($m['whatsapp_number'] || $m['portfolio_url']): ?>
                    <div class="card-footer bg-transparent border-0 d-flex gap-2">
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
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
