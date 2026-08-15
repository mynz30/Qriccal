<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$kode = trim($_POST['kode'] ?? $_GET['kode'] ?? '');

if ($kode === '') {
    echo json_encode(['status' => 'invalid', 'message' => 'Kode QR kosong.']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM tamu WHERE kode_unik = ?");
$stmt->execute([$kode]);
$tamu = $stmt->fetch();

if (!$tamu) {
    echo json_encode(['status' => 'invalid', 'message' => 'QR tidak dikenali.']);
    exit;
}

if ((int)$tamu['sudah_hadir'] === 1) {
    $log = $pdo->prepare("INSERT INTO scan_log (tamu_id, hasil) VALUES (?, 'SUDAH_PERNAH')");
    $log->execute([$tamu['id']]);

    echo json_encode([
        'status' => 'used',
        'message' => 'Peserta ini SUDAH check-in sebelumnya.',
        'nama' => $tamu['nama'],
        'kategori' => $tamu['kategori'],
        'keterangan' => $tamu['keterangan'],
        'waktu_hadir' => $tamu['waktu_hadir'],
    ]);
    exit;
}

$update = $pdo->prepare("UPDATE tamu SET sudah_hadir = 1, waktu_hadir = NOW() WHERE id = ?");
$update->execute([$tamu['id']]);

$log = $pdo->prepare("INSERT INTO scan_log (tamu_id, hasil) VALUES (?, 'BERHASIL')");
$log->execute([$tamu['id']]);

echo json_encode([
    'status' => 'valid',
    'message' => 'Selamat datang!',
    'nama' => $tamu['nama'],
    'kategori' => $tamu['kategori'],
    'keterangan' => $tamu['keterangan'],
    'jumlah_tamu' => $tamu['jumlah_tamu'],
]);
