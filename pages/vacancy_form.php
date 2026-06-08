<?php
$pageTitle = 'Lowongan';
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();
requireRole('band');

$db     = getDB();
$userId = $_SESSION['user_id'];

// Must have band profile first
$stmt = $db->prepare("SELECT id FROM bands WHERE user_id = ?");
$stmt->bind_param("i", $userId); $stmt->execute();
$band = $stmt->get_result()->fetch_assoc();

if (!$band) {
    $_SESSION['flash_message'] = 'Lengkapi profil band terlebih dahulu.';
    $_SESSION['flash_type']    = 'warning';
    header('Location: /pages/dashboard.php'); exit;
}

$bandId = $band['id'];
$editId = (int)($_GET['id'] ?? 0);
$vacancy = null;
$isEdit  = false;
$errors  = [];

// Handle delete via POST+CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_vacancy') {
    verifyCsrf();
    $delId = (int)($_POST['vacancy_id'] ?? 0);
    if ($delId) {
        $s = $db->prepare("DELETE FROM vacancies WHERE id=? AND band_id=?");
        $s->bind_param("ii", $delId, $bandId); $s->execute();
        $_SESSION['flash_message'] = 'Lowongan berhasil dihapus.';
        $_SESSION['flash_type']    = 'success';
    }
    header('Location: /pages/dashboard.php'); exit;
}

// Load vacancy for edit
if ($editId) {
    $s = $db->prepare("SELECT * FROM vacancies WHERE id=? AND band_id=?");
    $s->bind_param("ii", $editId, $bandId); $s->execute();
    $vacancy = $s->get_result()->fetch_assoc();
    if ($vacancy) {
        $isEdit    = true;
        $pageTitle = 'Edit Lowongan';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']             ?? '');
    $description = trim($_POST['description']       ?? '');
    $instrument  = trim($_POST['needed_instrument'] ?? '');
    $projectType = $_POST['project_type']           ?? 'Gig';

    $validTypes = ['Permanent','Session','Recording','Gig'];
    if (!in_array($projectType, $validTypes)) $projectType = 'Gig';

    if (empty($title))      $errors[] = 'Judul tidak boleh kosong.';
    if (empty($instrument)) $errors[] = 'Instrumen yang dibutuhkan wajib diisi.';

    if (empty($errors)) {
        if ($isEdit && $vacancy) {
            $s = $db->prepare("UPDATE vacancies SET title=?,description=?,needed_instrument=?,project_type=? WHERE id=? AND band_id=?");
            $s->bind_param("ssssii", $title, $description, $instrument, $projectType, $editId, $bandId);
            $msg = 'Lowongan berhasil diperbarui!';
        } else {
            $s = $db->prepare("INSERT INTO vacancies (band_id,title,description,needed_instrument,project_type) VALUES (?,?,?,?,?)");
            $s->bind_param("issss", $bandId, $title, $description, $instrument, $projectType);
            $msg = 'Lowongan berhasil dibuat!';
        }
        $s->execute();
        $_SESSION['flash_message'] = $msg;
        $_SESSION['flash_type']    = 'success';
        header('Location: /pages/dashboard.php'); exit;
    }
}

$formVal = function(string $field) use ($vacancy): string {
    return htmlspecialchars($_POST[$field] ?? $vacancy[$field] ?? '');
};

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-9 col-lg-7">
        <div class="card p-4 p-md-5">
            <p class="kicker mb-3"><i class="fas fa-bullhorn me-1"></i> <?= $isEdit ? 'Ubah Iklan' : 'Pasang Iklan' ?></p>
            <h1 class="h2 mb-4" style="text-transform:uppercase;letter-spacing:-0.03em;">
                <?= $isEdit ? 'Edit' : 'Buat' ?> Lowongan
            </h1>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Judul Lowongan</label>
                    <input type="text" name="title" class="form-control" required
                           value="<?= $formVal('title') ?>"
                           placeholder="Cari Gitaris untuk Rekaman EP">
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi <span class="text-muted">(opsional)</span></label>
                    <textarea name="description" class="form-control" rows="5"
                              placeholder="Ceritakan tentang band, kebutuhan, ekspektasi..."><?= $formVal('description') ?></textarea>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Instrumen Dibutuhkan</label>
                        <input type="text" name="needed_instrument" class="form-control" required
                               value="<?= $formVal('needed_instrument') ?>"
                               placeholder="Guitar, Bass, Drums, Keyboard...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipe Proyek</label>
                        <select name="project_type" class="form-select">
                            <?php
                            $currentType = $_POST['project_type'] ?? ($vacancy['project_type'] ?? 'Gig');
                            foreach (['Permanent','Session','Recording','Gig'] as $type):
                            ?>
                            <option value="<?= $type ?>" <?= $currentType === $type ? 'selected' : '' ?>>
                                <?= $type ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-2"></i><?= $isEdit ? 'Perbarui' : 'Publikasikan' ?>
                    </button>
                    <a href="/pages/dashboard.php" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
