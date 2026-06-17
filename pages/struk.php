<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireRole('band');

$db   = getDB();
$user = getCurrentUser();

$stmt = $db->prepare("SELECT * FROM bands WHERE user_id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$band = $stmt->get_result()->fetch_assoc();

if (!$band) {
    $_SESSION['flash_message'] = 'Lengkapi profil band terlebih dahulu.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: /pages/dashboard.php');
    exit;
}

$q = $db->prepare("
    SELECT c.id, c.message, c.status, c.created_at,
           m.full_name, m.primary_instrument, m.experience_level, m.location_city,
           m.bio, m.whatsapp_number, m.portfolio_url,
           v.title AS vacancy_title, v.project_type, v.needed_instrument
    FROM connections c
    JOIN musicians m ON c.musician_id = m.id
    JOIN vacancies v ON c.vacancy_id = v.id
    WHERE c.band_id = ?
    ORDER BY FIELD(c.status, 'Pending', 'Accepted', 'Rejected'), v.title, c.created_at DESC
");
$q->bind_param("i", $band['id']);
$q->execute();
$rows = $q->get_result()->fetch_all(MYSQLI_ASSOC);

$statusLabel = ['Pending' => 'MENUNGGU', 'Accepted' => 'DITERIMA', 'Rejected' => 'DITOLAK'];
$h = fn($v) => htmlspecialchars($v ?? '');
$noStruk = 'BK-LIST-' . str_pad((string)$band['id'], 3, '0', STR_PAD_LEFT) . '-' . date('Ymd');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Pendaftar <?= $h($band['band_name']) ?> - BenKonek</title>
<style>
    :root { --ink:#211c17; --paper:#efe7d3; --paper2:#e6dcc2; --rust:#cf3f17; --muted:#6b6151; --green:#2f7d52; }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        padding: 24px;
        background: #cdc4ad;
        color: var(--ink);
        font-family: "Courier New", ui-monospace, monospace;
    }
    .toolbar {
        max-width: 1100px;
        margin: 0 auto 16px;
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }
    .toolbar button, .toolbar a {
        font-family: inherit;
        font-size: 13px;
        cursor: pointer;
        border: 2px solid var(--ink);
        background: var(--paper);
        color: var(--ink);
        padding: 8px 16px;
        text-decoration: none;
        border-radius: 3px;
        font-weight: bold;
    }
    .toolbar button { background: var(--rust); color: #fff; }
    .sheet {
        max-width: 1100px;
        margin: 0 auto;
        background: var(--paper);
        border: 2px solid var(--ink);
        box-shadow: 8px 8px 0 var(--ink);
        padding: 28px;
    }
    .top {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 18px;
        border-bottom: 2px dashed var(--ink);
        padding-bottom: 18px;
        margin-bottom: 18px;
    }
    h1 {
        margin: 0;
        font-size: 34px;
        letter-spacing: 6px;
        line-height: 1;
    }
    .sub {
        margin: 8px 0 0;
        color: var(--muted);
        letter-spacing: 2px;
        font-size: 12px;
        text-transform: uppercase;
    }
    .meta {
        text-align: right;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.6;
        white-space: nowrap;
    }
    .meta .band-fit {
        --fit-size: 12px;
        display: inline-block;
        max-width: 300px;
        font-size: var(--fit-size);
        line-height: 1.15;
        white-space: normal;
        text-align: right;
        vertical-align: top;
    }
    .summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 18px;
    }
    .box {
        border: 2px solid var(--ink);
        padding: 10px;
        background: var(--paper2);
        height: 72px;
        overflow: hidden;
    }
    .box span {
        display: block;
        color: var(--muted);
        font-size: 11px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .box strong {
        display: block;
        margin-top: 6px;
        font-size: 24px;
        line-height: .95;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .box--band strong {
        --fit-size: 24px;
        font-size: var(--fit-size);
    }
    .fit-text {
        display: block;
        max-width: 100%;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    th {
        background: var(--ink);
        color: var(--paper);
        text-align: left;
        padding: 8px;
        border: 1px solid var(--ink);
        letter-spacing: 1px;
        text-transform: uppercase;
        font-size: 11px;
    }
    td {
        vertical-align: top;
        padding: 8px;
        border: 1px solid var(--ink);
    }
    tr:nth-child(even) td { background: rgba(255,255,255,.18); }
    .name { font-weight: bold; text-transform: uppercase; }
    .small { color: var(--muted); font-size: 11px; line-height: 1.45; }
    .status {
        display: inline-block;
        border: 1.5px solid var(--ink);
        padding: 2px 6px;
        font-weight: bold;
        letter-spacing: 1px;
        white-space: nowrap;
    }
    .status.accepted { background: var(--green); color: #fff; }
    .status.pending { background: #d99a1c; color: var(--ink); }
    .status.rejected { background: var(--paper2); color: var(--muted); }
    .empty {
        border: 2px dashed var(--ink);
        padding: 32px;
        text-align: center;
        color: var(--muted);
    }
    .foot {
        border-top: 2px dashed var(--ink);
        margin-top: 18px;
        padding-top: 14px;
        display: grid;
        grid-template-columns: 1fr 260px;
        gap: 20px;
        font-size: 11px;
        color: var(--muted);
        line-height: 1.6;
    }
    .sign {
        text-align: center;
        color: var(--ink);
    }
    .sign .line {
        border-top: 1px solid var(--ink);
        margin-top: 48px;
        padding-top: 4px;
    }
    @media print {
        body { background: #fff; padding: 0; }
        .toolbar { display: none; }
        .sheet {
            max-width: none;
            min-height: 100vh;
            box-shadow: none;
            border: none;
            padding: 10mm;
        }
        .box, th, td { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
    }
</style>
</head>
<body>
<div class="toolbar">
    <a href="/pages/connections.php">Kembali</a>
    <button onclick="window.print()">Cetak / Simpan PDF</button>
</div>

<main class="sheet">
    <header class="top">
        <div>
            <h1>BENKONEK</h1>
            <p class="sub">Daftar Pendaftar Lowongan Band</p>
        </div>
        <div class="meta">
            <div>No. <?= $h($noStruk) ?></div>
            <div><?= date('d/m/Y H:i') ?></div>
            <div><span class="fit-text band-fit" data-fit-text data-fit-min="8" data-fit-max="12"><?= $h($band['band_name']) ?></span></div>
        </div>
    </header>

    <section class="summary">
        <div class="box box--band"><span>Band</span><strong class="fit-text" data-fit-text data-fit-min="13" data-fit-max="30"><?= $h($band['band_name']) ?></strong></div>
        <div class="box"><span>Total Pendaftar</span><strong><?= count($rows) ?></strong></div>
        <div class="box"><span>Menunggu</span><strong><?= count(array_filter($rows, fn($r) => $r['status'] === 'Pending')) ?></strong></div>
        <div class="box"><span>Diterima</span><strong><?= count(array_filter($rows, fn($r) => $r['status'] === 'Accepted')) ?></strong></div>
    </section>

    <?php if (empty($rows)): ?>
    <div class="empty">Belum ada musisi yang mendaftar ke lowongan band ini.</div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th style="width:40px;">No</th>
                <th>Musisi</th>
                <th>Lowongan</th>
                <th>Kontak</th>
                <th>Pesan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $i => $row): ?>
            <?php $statusClass = strtolower($row['status']); ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td>
                    <div class="name"><?= $h($row['full_name']) ?></div>
                    <div class="small">
                        <?= $h($row['primary_instrument']) ?> / <?= $h($row['experience_level']) ?><br>
                        <?= $h($row['location_city']) ?>
                    </div>
                    <?php if (!empty($row['bio'])): ?>
                    <div class="small"><?= $h($row['bio']) ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <div><?= $h($row['vacancy_title']) ?></div>
                    <div class="small"><?= $h($row['project_type']) ?> / <?= $h($row['needed_instrument']) ?></div>
                    <div class="small"><?= date('d/m/Y', strtotime($row['created_at'])) ?></div>
                </td>
                <td>
                    <?= $h($row['whatsapp_number']) ?>
                    <?php if (!empty($row['portfolio_url'])): ?>
                    <div class="small"><?= $h($row['portfolio_url']) ?></div>
                    <?php endif; ?>
                </td>
                <td><?= $row['message'] ? $h($row['message']) : '<span class="small">-</span>' ?></td>
                <td><span class="status <?= $h($statusClass) ?>"><?= $h($statusLabel[$row['status']] ?? $row['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <footer class="foot">
        <div>
            Dokumen ini berisi daftar musisi yang sudah mendaftar ke lowongan band terkait.
            Data musisi ditampilkan untuk kebutuhan seleksi band.
        </div>
        <div class="sign">
            <div class="line">Tanda tangan penyeleksi</div>
        </div>
    </footer>
</main>

<script>
function fitTextToBox(element) {
    var min = Number(element.dataset.fitMin || 10);
    var max = Number(element.dataset.fitMax || 24);
    var parent = element.parentElement;
    var limit = parent ? parent.clientHeight : element.clientHeight;
    var label = parent ? parent.querySelector('span:not(.fit-text)') : null;

    if (label) {
        limit -= label.offsetHeight + 8;
    }

    var low = min;
    var high = max;
    var best = min;

    for (var i = 0; i < 14; i++) {
        var mid = (low + high) / 2;

        element.style.setProperty('--fit-size', mid + 'px');
        element.style.fontSize = mid + 'px';

        if (element.scrollWidth <= element.clientWidth + 1 && element.scrollHeight <= limit + 1) {
            best = mid;
            low = mid;
        } else {
            high = mid;
        }
    }

    element.style.setProperty('--fit-size', best + 'px');
    element.style.fontSize = best + 'px';
}

function fitPrintText() {
    document.querySelectorAll('[data-fit-text]').forEach(fitTextToBox);
}

window.addEventListener('load', function () {
    fitPrintText();
    if (!new URLSearchParams(location.search).has('noprint')) {
        setTimeout(function () {
            fitPrintText();
            window.print();
        }, 350);
    }
});
window.addEventListener('resize', fitPrintText);
window.addEventListener('beforeprint', fitPrintText);
</script>
</body>
</html>
