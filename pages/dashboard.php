<?php
$pageTitle = 'Dashboard';
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$db     = getDB();
$user   = getCurrentUser();
$userId = $user['id'];

// ── Handle POST ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_musician' && $user['role'] === 'musician') {
        $fullName   = trim($_POST['full_name']           ?? '');
        $bio        = trim($_POST['bio']                 ?? '');
        $city       = trim($_POST['location_city']       ?? '');
        $instrument = trim($_POST['primary_instrument']  ?? '');
        $level      = $_POST['experience_level']         ?? 'Beginner';
        $portfolio  = trim($_POST['portfolio_url']       ?? '');
        $wa         = trim($_POST['whatsapp_number']     ?? '');

        $validLevels = ['Beginner','Intermediate','Advanced','Professional'];
        if (!in_array($level, $validLevels)) $level = 'Beginner';

        $photoFile = _handlePhotoUpload('mus_');
        if ($photoFile === false) { /* flash already set */ goto redirect_dash; }

        if ($photoFile) {
            $stmt = $db->prepare("
                INSERT INTO musicians (user_id,full_name,bio,location_city,primary_instrument,experience_level,portfolio_url,whatsapp_number,photo_profile)
                VALUES (?,?,?,?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE
                  full_name=VALUES(full_name),bio=VALUES(bio),location_city=VALUES(location_city),
                  primary_instrument=VALUES(primary_instrument),experience_level=VALUES(experience_level),
                  portfolio_url=VALUES(portfolio_url),whatsapp_number=VALUES(whatsapp_number),
                  photo_profile=VALUES(photo_profile)");
            $stmt->bind_param("issssssss",$userId,$fullName,$bio,$city,$instrument,$level,$portfolio,$wa,$photoFile);
        } else {
            $stmt = $db->prepare("
                INSERT INTO musicians (user_id,full_name,bio,location_city,primary_instrument,experience_level,portfolio_url,whatsapp_number)
                VALUES (?,?,?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE
                  full_name=VALUES(full_name),bio=VALUES(bio),location_city=VALUES(location_city),
                  primary_instrument=VALUES(primary_instrument),experience_level=VALUES(experience_level),
                  portfolio_url=VALUES(portfolio_url),whatsapp_number=VALUES(whatsapp_number)");
            $stmt->bind_param("isssssss",$userId,$fullName,$bio,$city,$instrument,$level,$portfolio,$wa);
        }
        $stmt->execute();
        $_SESSION['name'] = $fullName;
        $_SESSION['flash_message'] = 'Profil berhasil disimpan!';
        $_SESSION['flash_type']    = 'success';
        goto redirect_dash;
    }

    if ($action === 'save_band' && $user['role'] === 'band') {
        $bandName = trim($_POST['band_name']          ?? '');
        $year     = (int)($_POST['formation_year']    ?? 0);
        $genre    = trim($_POST['main_genre']         ?? '');
        $location = trim($_POST['basecamp_location']  ?? '');
        $wa       = trim($_POST['whatsapp_number']    ?? '');

        $photoFile = _handlePhotoUpload('band_');
        if ($photoFile === false) { goto redirect_dash; }

        if ($photoFile) {
            $stmt = $db->prepare("
                INSERT INTO bands (user_id,band_name,formation_year,main_genre,basecamp_location,whatsapp_number,photo_profile)
                VALUES (?,?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE
                  band_name=VALUES(band_name),formation_year=VALUES(formation_year),
                  main_genre=VALUES(main_genre),basecamp_location=VALUES(basecamp_location),
                  whatsapp_number=VALUES(whatsapp_number),photo_profile=VALUES(photo_profile)");
            $stmt->bind_param("isissss",$userId,$bandName,$year,$genre,$location,$wa,$photoFile);
        } else {
            $stmt = $db->prepare("
                INSERT INTO bands (user_id,band_name,formation_year,main_genre,basecamp_location,whatsapp_number)
                VALUES (?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE
                  band_name=VALUES(band_name),formation_year=VALUES(formation_year),
                  main_genre=VALUES(main_genre),basecamp_location=VALUES(basecamp_location),
                  whatsapp_number=VALUES(whatsapp_number)");
            $stmt->bind_param("isisss",$userId,$bandName,$year,$genre,$location,$wa);
        }
        $stmt->execute();
        $_SESSION['name'] = $bandName;
        $_SESSION['flash_message'] = 'Profil band berhasil disimpan!';
        $_SESSION['flash_type']    = 'success';
        goto redirect_dash;
    }

    redirect_dash:
    header('Location: /pages/dashboard.php'); exit;
}

function _handlePhotoUpload(string $prefix): string|false|null {
    if (empty($_FILES['photo']['name'])) return null;
    $allowed = ['image/jpeg','image/png','image/webp'];
    $maxSize = 2 * 1024 * 1024;
    if (!in_array($_FILES['photo']['type'], $allowed)) {
        $_SESSION['flash_message'] = 'Format foto tidak valid (JPG/PNG/WebP).';
        $_SESSION['flash_type']    = 'error';
        return false;
    }
    if ($_FILES['photo']['size'] > $maxSize) {
        $_SESSION['flash_message'] = 'Ukuran foto maksimal 2MB.';
        $_SESSION['flash_type']    = 'error';
        return false;
    }
    $ext     = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $newName = $prefix . uniqid('', true) . '.' . $ext;
    $dest    = __DIR__ . '/../assets/images/uploads/' . $newName;
    return move_uploaded_file($_FILES['photo']['tmp_name'], $dest) ? $newName : null;
}

// ── Fetch profile ────────────────────────────────────────────────────────────
$profile   = [];
$vacancies = [];
$stats     = [];

if ($user['role'] === 'musician') {
    $stmt = $db->prepare("SELECT * FROM musicians WHERE user_id = ?");
    $stmt->bind_param("i", $userId); $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc() ?? [];
} elseif ($user['role'] === 'band') {
    $stmt = $db->prepare("SELECT * FROM bands WHERE user_id = ?");
    $stmt->bind_param("i", $userId); $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc() ?? [];
    if (!empty($profile['id'])) {
        $bid  = $profile['id'];
        $stmt2 = $db->prepare("SELECT * FROM vacancies WHERE band_id = ? ORDER BY created_at DESC");
        $stmt2->bind_param("i", $bid); $stmt2->execute();
        $vacancies = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
    }
} elseif ($user['role'] === 'admin') {
    $stats = [
        'users'     => $db->query("SELECT COUNT(*) FROM users")->fetch_row()[0],
        'musicians' => $db->query("SELECT COUNT(*) FROM musicians")->fetch_row()[0],
        'bands'     => $db->query("SELECT COUNT(*) FROM bands")->fetch_row()[0],
        'vacancies' => $db->query("SELECT COUNT(*) FROM vacancies WHERE status='Open'")->fetch_row()[0],
    ];
}

require_once '../includes/header.php';
?>

<div class="d-flex align-items-center mb-4">
    <h2 class="text-warning mb-0"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
    <span class="ms-3 badge bg-warning text-dark">
        <?= $user['role'] === 'musician' ? 'Musisi' : ($user['role'] === 'band' ? 'Band' : 'Admin') ?>
    </span>
</div>

<?php if ($user['role'] === 'musician'): ?>
<!-- ── MUSICIAN ── -->
<div class="row g-4">
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <?php if (!empty($profile['photo_profile'])): ?>
            <img src="/assets/images/uploads/<?= htmlspecialchars($profile['photo_profile']) ?>"
                 class="musician-avatar mx-auto d-block mb-3" alt="Foto Profil">
            <?php else: ?>
            <div class="bg-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                 style="width:120px;height:120px;border:3px solid #FFC107;">
                <i class="fas fa-user fa-3x" style="color:#9E9E9E;"></i>
            </div>
            <?php endif; ?>
            <h6 class="text-warning"><?= htmlspecialchars($profile['full_name'] ?? $user['name']) ?></h6>
            <small style="color:#9E9E9E;"><?= htmlspecialchars($profile['primary_instrument'] ?? 'Belum diisi') ?></small>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card p-4">
            <h5 class="text-warning mb-3"><i class="fas fa-edit me-2"></i>Edit Profil Musisi</h5>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save_musician">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="full_name" class="form-control" required
                               value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kota</label>
                        <input type="text" name="location_city" class="form-control"
                               value="<?= htmlspecialchars($profile['location_city'] ?? '') ?>"
                               placeholder="Surabaya, Jakarta...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Instrumen Utama</label>
                        <input type="text" name="primary_instrument" class="form-control"
                               value="<?= htmlspecialchars($profile['primary_instrument'] ?? '') ?>"
                               placeholder="Guitar, Bass, Drums...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Level Pengalaman</label>
                        <select name="experience_level" class="form-select">
                            <?php foreach (['Beginner','Intermediate','Advanced','Professional'] as $lvl): ?>
                            <option value="<?= $lvl ?>"
                                <?= ($profile['experience_level'] ?? 'Beginner') === $lvl ? 'selected' : '' ?>>
                                <?= $lvl ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="3"
                                  placeholder="Ceritakan tentang dirimu..."><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nomor WhatsApp</label>
                        <input type="text" name="whatsapp_number" class="form-control"
                               value="<?= htmlspecialchars($profile['whatsapp_number'] ?? '') ?>"
                               placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">URL Portfolio</label>
                        <input type="url" name="portfolio_url" class="form-control"
                               value="<?= htmlspecialchars($profile['portfolio_url'] ?? '') ?>"
                               placeholder="https://soundcloud.com/...">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Foto Profil (JPG/PNG/WebP, maks 2MB)</label>
                        <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-warning fw-semibold">
                            <i class="fas fa-save me-2"></i>Simpan Profil
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php elseif ($user['role'] === 'band'): ?>
<!-- ── BAND ── -->
<div class="row g-4">
    <div class="col-md-3">
        <div class="card p-3 text-center">
            <?php if (!empty($profile['photo_profile'])): ?>
            <img src="/assets/images/uploads/<?= htmlspecialchars($profile['photo_profile']) ?>"
                 class="musician-avatar mx-auto d-block mb-3" alt="Foto Band">
            <?php else: ?>
            <div class="bg-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                 style="width:120px;height:120px;border:3px solid #FFC107;">
                <i class="fas fa-users fa-3x" style="color:#9E9E9E;"></i>
            </div>
            <?php endif; ?>
            <h6 class="text-warning"><?= htmlspecialchars($profile['band_name'] ?? $user['name']) ?></h6>
            <small style="color:#9E9E9E;"><?= htmlspecialchars($profile['main_genre'] ?? 'Belum diisi') ?></small>
            <?php if (!empty($profile['id'])): ?>
            <a href="/pages/vacancy_form.php" class="btn btn-warning btn-sm mt-3 w-100">
                <i class="fas fa-plus me-1"></i>Buat Lowongan
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card p-4 mb-4">
            <h5 class="text-warning mb-3"><i class="fas fa-edit me-2"></i>Edit Profil Band</h5>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save_band">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Band</label>
                        <input type="text" name="band_name" class="form-control" required
                               value="<?= htmlspecialchars($profile['band_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tahun Terbentuk</label>
                        <input type="number" name="formation_year" class="form-control"
                               value="<?= htmlspecialchars($profile['formation_year'] ?? '') ?>"
                               min="1900" max="<?= date('Y') ?>" placeholder="<?= date('Y') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Genre Utama</label>
                        <input type="text" name="main_genre" class="form-control"
                               value="<?= htmlspecialchars($profile['main_genre'] ?? '') ?>"
                               placeholder="Rock, Jazz, Pop...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kota Basecamp</label>
                        <input type="text" name="basecamp_location" class="form-control"
                               value="<?= htmlspecialchars($profile['basecamp_location'] ?? '') ?>"
                               placeholder="Surabaya">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nomor WhatsApp</label>
                        <input type="text" name="whatsapp_number" class="form-control"
                               value="<?= htmlspecialchars($profile['whatsapp_number'] ?? '') ?>"
                               placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Foto Band (JPG/PNG/WebP, maks 2MB)</label>
                        <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-warning fw-semibold">
                            <i class="fas fa-save me-2"></i>Simpan Profil
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Vacancy list -->
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-warning mb-0"><i class="fas fa-list me-2"></i>Lowongan Band</h5>
                <?php if (!empty($profile['id'])): ?>
                <a href="/pages/vacancy_form.php" class="btn btn-sm btn-warning">
                    <i class="fas fa-plus me-1"></i>Buat Baru
                </a>
                <?php endif; ?>
            </div>
            <?php if (empty($vacancies)): ?>
            <p class="text-center py-3" style="color:#9E9E9E;">
                Belum ada lowongan.
                <?php if (!empty($profile['id'])): ?>
                <a href="/pages/vacancy_form.php" class="text-warning">Buat sekarang!</a>
                <?php endif; ?>
            </p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr><th>Judul</th><th>Instrumen</th><th>Tipe</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vacancies as $v): ?>
                        <tr>
                            <td><?= htmlspecialchars($v['title']) ?></td>
                            <td><span class="badge-instrument"><?= htmlspecialchars($v['needed_instrument']) ?></span></td>
                            <td><?= htmlspecialchars($v['project_type']) ?></td>
                            <td>
                                <span class="badge <?= $v['status'] === 'Open' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $v['status'] ?>
                                </span>
                            </td>
                            <td class="d-flex gap-1">
                                <a href="/pages/vacancy_form.php?id=<?= $v['id'] ?>"
                                   class="btn btn-sm btn-outline-warning">Edit</a>
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="konfirmasiHapus('/pages/vacancy_form.php?delete=<?= $v['id'] ?>')">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php elseif ($user['role'] === 'admin'): ?>
<!-- ── ADMIN ── -->
<div class="row g-4">
    <?php
    $statItems = [
        ['label'=>'Total User',     'icon'=>'fas fa-users',    'val'=>$stats['users']],
        ['label'=>'Musisi',         'icon'=>'fas fa-guitar',   'val'=>$stats['musicians']],
        ['label'=>'Band',           'icon'=>'fas fa-drum',     'val'=>$stats['bands']],
        ['label'=>'Lowongan Aktif', 'icon'=>'fas fa-briefcase','val'=>$stats['vacancies']],
    ];
    foreach ($statItems as $si):
    ?>
    <div class="col-md-3 col-6">
        <div class="card stat-card p-4 text-center">
            <i class="<?= $si['icon'] ?> fa-2x text-warning mb-2"></i>
            <div class="display-6 fw-bold text-warning"><?= $si['val'] ?></div>
            <small style="color:#9E9E9E;"><?= $si['label'] ?></small>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
