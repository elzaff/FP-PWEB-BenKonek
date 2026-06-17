<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/connections.php'); exit;
}
verifyCsrf();

$db     = getDB();
$user   = getCurrentUser();
$action = $_POST['action'] ?? '';
$back   = $_SERVER['HTTP_REFERER'] ?? '/pages/connections.php';

function flash_back(string $msg, string $type, string $back): void {
    $_SESSION['flash_message'] = $msg;
    $_SESSION['flash_type']    = $type;
    header('Location: ' . $back);
    exit;
}

function myMusicianId(mysqli $db, int $userId): ?int {
    $s = $db->prepare("SELECT id FROM musicians WHERE user_id = ?");
    $s->bind_param("i", $userId); $s->execute();
    $row = $s->get_result()->fetch_assoc();
    return $row ? (int)$row['id'] : null;
}
function myBandId(mysqli $db, int $userId): ?int {
    $s = $db->prepare("SELECT id FROM bands WHERE user_id = ?");
    $s->bind_param("i", $userId); $s->execute();
    $row = $s->get_result()->fetch_assoc();
    return $row ? (int)$row['id'] : null;
}

if ($action === 'apply') {
    if ($user['role'] !== 'musician') flash_back('Hanya akun musisi yang bisa mendaftar lowongan.', 'error', $back);
    $musicianId = myMusicianId($db, (int)$user['id']);
    if (!$musicianId) flash_back('Lengkapi profil musisi dulu sebelum mendaftar.', 'warning', '/pages/dashboard.php');

    $vacancyId = (int)($_POST['vacancy_id'] ?? 0);
    $message   = trim($_POST['message'] ?? '');
    if (mb_strlen($message) > 500) $message = mb_substr($message, 0, 500);

    $s = $db->prepare("SELECT v.id, v.band_id, v.status FROM vacancies v WHERE v.id = ?");
    $s->bind_param("i", $vacancyId); $s->execute();
    $vac = $s->get_result()->fetch_assoc();
    if (!$vac)                       flash_back('Lowongan tidak ditemukan.', 'error', $back);
    if ($vac['status'] !== 'Open')   flash_back('Lowongan ini sudah ditutup.', 'warning', $back);

    $bandId = (int)$vac['band_id'];
    $chk = $db->prepare("SELECT id FROM connections WHERE musician_id=? AND vacancy_id=?");
    $chk->bind_param("ii", $musicianId, $vacancyId); $chk->execute();
    if ($chk->get_result()->fetch_assoc()) flash_back('Kamu sudah mendaftar di lowongan ini.', 'warning', $back);

    $ins = $db->prepare("INSERT INTO connections (musician_id, band_id, vacancy_id, message) VALUES (?, ?, ?, ?)");
    $msgParam = $message !== '' ? $message : null;
    $ins->bind_param("iiis", $musicianId, $bandId, $vacancyId, $msgParam);
    $ins->execute();
    flash_back('Lamaran terkirim! Band bisa melihatmu di daftar pendaftar.', 'success', $back);
}

if ($action === 'set_status') {
    $connId   = (int)($_POST['connection_id'] ?? 0);
    $newState = $_POST['status'] ?? '';
    if (!in_array($newState, ['Accepted','Rejected'], true)) flash_back('Status tidak valid.', 'error', $back);

    $s = $db->prepare("SELECT * FROM connections WHERE id = ?");
    $s->bind_param("i", $connId); $s->execute();
    $conn = $s->get_result()->fetch_assoc();
    if (!$conn) flash_back('Data pendaftaran tidak ditemukan.', 'error', $back);

    $allowed = ($user['role'] === 'band')
        && ((int)$conn['band_id'] === (int)myBandId($db, (int)$user['id']));
    if (!$allowed) flash_back('Kamu tidak berhak mengubah status ini.', 'error', $back);

    $u = $db->prepare("UPDATE connections SET status = ? WHERE id = ?");
    $u->bind_param("si", $newState, $connId); $u->execute();
    $label = $newState === 'Accepted' ? 'diterima' : 'ditolak';
    flash_back("Berhasil — kandidat $label.", 'success', $back);
}

flash_back('Aksi tidak dikenali.', 'error', $back);
