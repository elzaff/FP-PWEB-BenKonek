<?php
require_once '../config/session.php';

$_SESSION['flash_message'] = 'Profil musisi hanya bisa dilihat band setelah musisi mendaftar lowongan.';
$_SESSION['flash_type'] = 'info';
header('Location: /pages/gigboard.php');
exit;
