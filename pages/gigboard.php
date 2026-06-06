<?php
$pageTitle = 'GigBoard';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/header.php';

$db = getDB();

$filterInstrument = trim($_GET['instrument'] ?? '');
$filterCity       = trim($_GET['city']       ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$where  = ["v.status = 'Open'"];
$params = [];
$types  = '';

if ($filterInstrument) {
    $where[]  = "v.needed_instrument = ?";
    $params[] = $filterInstrument;
    $types   .= 's';
}
if ($filterCity) {
    $where[]  = "b.basecamp_location LIKE ?";
    $params[] = "%$filterCity%";
    $types   .= 's';
}

$whereSQL = implode(' AND ', $where);

// Instruments for dropdown
$instrResult = $db->query("SELECT DISTINCT needed_instrument FROM vacancies WHERE status='Open' ORDER BY needed_instrument");
$instruments = $instrResult ? $instrResult->fetch_all(MYSQLI_ASSOC) : [];

// Count
$countSQL = "SELECT COUNT(*) FROM vacancies v JOIN bands b ON v.band_id=b.id WHERE $whereSQL";
if ($params) {
    $s = $db->prepare($countSQL); $s->bind_param($types, ...$params); $s->execute();
    $totalRows = $s->get_result()->fetch_row()[0];
} else {
    $totalRows = $db->query($countSQL)->fetch_row()[0];
}
$totalPages = (int)ceil($totalRows / $perPage);

// Fetch page
$allParams = array_merge($params, [$perPage, $offset]);
$allTypes  = $types . 'ii';
$sql = "SELECT v.*, b.band_name, b.basecamp_location, b.whatsapp_number, b.main_genre
        FROM vacancies v JOIN bands b ON v.band_id=b.id
        WHERE $whereSQL ORDER BY v.created_at DESC LIMIT ? OFFSET ?";
$stmt = $db->prepare($sql);
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$vacancies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageUrl = function(int $p) use ($filterInstrument, $filterCity): string {
    return '?page=' . $p . '&instrument=' . urlencode($filterInstrument) . '&city=' . urlencode($filterCity);
};
?>

<div class="row">
    <!-- Filter sidebar -->
    <div class="col-lg-3 mb-4">
        <div class="filter-sidebar">
            <h5 class="text-warning mb-3"><i class="fas fa-filter me-2"></i>Filter</h5>
            <form method="GET">
                <div class="mb-3">
                    <label class="form-label">Instrumen</label>
                    <select name="instrument" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Instrumen</option>
                        <?php foreach ($instruments as $ins): ?>
                        <option value="<?= htmlspecialchars($ins['needed_instrument']) ?>"
                            <?= $filterInstrument === $ins['needed_instrument'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ins['needed_instrument']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kota Band</label>
                    <input type="text" name="city" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($filterCity) ?>" placeholder="Surabaya...">
                </div>
                <button type="submit" class="btn btn-warning btn-sm w-100">Terapkan</button>
                <?php if ($filterInstrument || $filterCity): ?>
                <a href="/pages/gigboard.php" class="btn btn-outline-secondary btn-sm w-100 mt-2">
                    <i class="fas fa-times me-1"></i>Reset Filter
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Main -->
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-warning mb-0"><i class="fas fa-briefcase me-2"></i>GigBoard</h2>
            <span style="color:#9E9E9E;"><?= $totalRows ?> lowongan ditemukan</span>
        </div>

        <!-- Realtime search -->
        <div class="mb-3">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="searchInput" class="form-control"
                       placeholder="Cari judul atau deskripsi...">
            </div>
        </div>

        <?php if (empty($vacancies)): ?>
        <div class="card p-5 text-center">
            <i class="fas fa-search fa-3x mb-3 text-warning"></i>
            <p style="color:#9E9E9E;">Tidak ada lowongan yang sesuai filter.</p>
            <a href="/pages/gigboard.php" class="btn btn-outline-warning btn-sm">Lihat Semua</a>
        </div>
        <?php else: ?>

        <div id="vacancyList">
            <?php foreach ($vacancies as $v): ?>
            <div class="card mb-3 vacancy-card"
                 data-title="<?= htmlspecialchars(mb_strtolower($v['title'])) ?>"
                 data-desc="<?= htmlspecialchars(mb_strtolower($v['description'] ?? '')) ?>">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                <span class="badge bg-warning text-dark small">
                                    <?= htmlspecialchars($v['project_type']) ?>
                                </span>
                                <a href="/pages/vacancy_detail.php?id=<?= $v['id'] ?>"
                                   class="text-warning text-decoration-none fw-semibold fs-6">
                                    <?= htmlspecialchars($v['title']) ?>
                                </a>
                            </div>
                            <div class="d-flex flex-wrap gap-3 small mb-2" style="color:#9E9E9E;">
                                <span><i class="fas fa-drum me-1"></i><?= htmlspecialchars($v['band_name']) ?></span>
                                <span><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($v['basecamp_location']) ?></span>
                                <span><i class="fas fa-music me-1"></i><?= htmlspecialchars($v['main_genre']) ?></span>
                            </div>
                            <span class="badge-instrument">
                                <i class="fas fa-guitar me-1"></i><?= htmlspecialchars($v['needed_instrument']) ?>
                            </span>
                        </div>
                        <div class="d-flex flex-column gap-2 ms-3">
                            <a href="/pages/vacancy_detail.php?id=<?= $v['id'] ?>"
                               class="btn btn-sm btn-outline-warning">Detail</a>
                            <button class="btn btn-sm btn-success"
                                    onclick="hubungiWhatsApp('<?= htmlspecialchars($v['whatsapp_number']) ?>','<?= htmlspecialchars(addslashes($v['band_name'])) ?>','<?= htmlspecialchars(addslashes($v['title'])) ?>')">
                                <i class="fab fa-whatsapp me-1"></i>WA
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 d-flex justify-content-between">
                    <small style="color:#9E9E9E;"><i class="fas fa-clock me-1"></i><?= date('d M Y', strtotime($v['created_at'])) ?></small>
                    <?php if ($v['description']): ?>
                    <small style="color:#9E9E9E;" class="d-none d-md-block">
                        <?= htmlspecialchars(mb_substr($v['description'], 0, 80)) ?><?= mb_strlen($v['description']) > 80 ? '…' : '' ?>
                    </small>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="<?= $pageUrl($page-1) ?>"><i class="fas fa-chevron-left"></i></a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $pageUrl($i) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="<?= $pageUrl($page+1) ?>"><i class="fas fa-chevron-right"></i></a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('input', function () {
    var q = this.value.toLowerCase().trim();
    document.querySelectorAll('#vacancyList .vacancy-card').forEach(function (card) {
        var match = !q || card.dataset.title.includes(q) || card.dataset.desc.includes(q);
        card.style.display = match ? '' : 'none';
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
