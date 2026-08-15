<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Daftar Peserta & QR Code - QRiccal';
$pesan = '';

if (isset($_GET['generate_id'])) {
    $id = (int)$_GET['generate_id'];
    $stmt = $pdo->prepare("SELECT * FROM tamu WHERE id = ?");
    $stmt->execute([$id]);
    $tamu = $stmt->fetch();

    if ($tamu) {
        $filename = $tamu['kode_unik'] . '.png';
        $savePath = __DIR__ . '/../assets/qrcodes/' . $filename;

        if (generate_qr_image($tamu['kode_unik'], $savePath)) {
            $update = $pdo->prepare("UPDATE tamu SET qr_path = ? WHERE id = ?");
            $update->execute(['assets/qrcodes/' . $filename, $id]);
            $pesan = "QR untuk {$tamu['nama']} berhasil dibuat.";
        } else {
            $pesan = "Gagal membuat QR untuk {$tamu['nama']}. Cek koneksi internet.";
        }
    }
}

if (isset($_GET['generate_all'])) {
    $rows = $pdo->query("SELECT * FROM tamu WHERE qr_path IS NULL")->fetchAll();
    $count = 0;
    foreach ($rows as $tamu) {
        $filename = $tamu['kode_unik'] . '.png';
        $savePath = __DIR__ . '/../assets/qrcodes/' . $filename;
        if (generate_qr_image($tamu['kode_unik'], $savePath)) {
            $update = $pdo->prepare("UPDATE tamu SET qr_path = ? WHERE id = ?");
            $update->execute(['assets/qrcodes/' . $filename, $tamu['id']]);
            $count++;
        }
    }
    $pesan = "$count QR berhasil dibuat sekaligus.";
}

if (isset($_GET['kirim_email_id'])) {
    $id = (int)$_GET['kirim_email_id'];
    $stmt = $pdo->prepare("SELECT * FROM tamu WHERE id = ?");
    $stmt->execute([$id]);
    $tamu = $stmt->fetch();

    if ($tamu && $tamu['email'] && $tamu['qr_path']) {
        $hasil = kirim_email_qr($pdo, $tamu['nama'], $tamu['email'], $tamu['kategori'], __DIR__ . '/../' . $tamu['qr_path']);
        if ($hasil['success']) {
            $pdo->prepare("UPDATE tamu SET email_terkirim = 1 WHERE id = ?")->execute([$id]);
            $pesan = "Email berhasil dikirim ke {$tamu['nama']} ({$tamu['email']}).";
        } else {
            $pesan = "Gagal kirim email ke {$tamu['nama']}: " . $hasil['error'];
        }
    } else {
        $pesan = "Tidak bisa kirim: pastikan peserta punya email & QR sudah digenerate.";
    }
}

if (isset($_GET['kirim_email_semua'])) {
    $rows = $pdo->query("
        SELECT * FROM tamu
        WHERE email IS NOT NULL AND email != '' AND qr_path IS NOT NULL AND email_terkirim = 0
    ")->fetchAll();

    $sukses = 0;
    $gagal = 0;
    foreach ($rows as $i => $tamu) {
        $hasil = kirim_email_qr($pdo, $tamu['nama'], $tamu['email'], $tamu['kategori'], __DIR__ . '/../' . $tamu['qr_path']);
        if ($hasil['success']) {
            $pdo->prepare("UPDATE tamu SET email_terkirim = 1 WHERE id = ?")->execute([$tamu['id']]);
            $sukses++;
        } else {
            $gagal++;
        }
        // Jeda 2 detik antar email supaya tidak memicu deteksi spam Gmail
        // (penting terutama selama akun pengirim masih baru/"pemanasan").
        if ($i < count($rows) - 1) {
            sleep(2);
        }
    }
    $pesan = "$sukses email berhasil dikirim" . ($gagal > 0 ? ", $gagal gagal terkirim." : ".");
}

$daftar = $pdo->query("SELECT * FROM tamu ORDER BY kategori ASC, nama ASC")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Daftar Peserta & QR Code</h3>
  <div>
    <a href="?generate_all=1" class="btn btn-primary">⚡ Generate Semua QR</a>
    <a href="?kirim_email_semua=1" class="btn btn-outline-dark">📧 Kirim Semua Email</a>
  </div>
</div>

<?php if ($pesan): ?><div class="alert alert-info"><?= htmlspecialchars($pesan) ?></div><?php endif; ?>

<table class="table table-bordered bg-white align-middle">
  <thead class="table-light">
    <tr>
      <th>Nama</th><th>Kategori</th><th>Keterangan</th><th>Email</th><th>Status</th><th>QR Code</th><th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($daftar as $t): ?>
    <tr>
      <td><?= htmlspecialchars($t['nama']) ?></td>
      <td><span class="badge <?= kategori_badge_class($t['kategori']) ?>"><?= htmlspecialchars($t['kategori']) ?></span></td>
      <td><?= htmlspecialchars($t['keterangan']) ?></td>
      <td class="small">
        <?= $t['email'] ? htmlspecialchars($t['email']) : '<span class="text-muted">-</span>' ?>
        <?php if ($t['email_terkirim']): ?><br><span class="badge bg-info text-dark">✉ Terkirim</span><?php endif; ?>
      </td>
      <td>
        <?= $t['sudah_hadir']
            ? '<span class="badge bg-success">Hadir - ' . format_tanggal_indo($t['waktu_hadir']) . '</span>'
            : '<span class="badge bg-secondary">Belum Hadir</span>' ?>
      </td>
      <td>
        <?php if ($t['qr_path']): ?>
          <img src="/qriccal/<?= $t['qr_path'] ?>" width="70">
        <?php else: ?>
          <span class="text-muted small">Belum digenerate</span>
        <?php endif; ?>
      </td>
      <td>
        <?php if ($t['qr_path']): ?>
          <a href="/qriccal/<?= $t['qr_path'] ?>" download class="btn btn-sm btn-outline-dark mb-1">Download</a>
        <?php else: ?>
          <a href="?generate_id=<?= $t['id'] ?>" class="btn btn-sm btn-primary mb-1">Generate QR</a>
        <?php endif; ?>
        <?php if ($t['email'] && $t['qr_path'] && !$t['email_terkirim']): ?>
          <br><a href="?kirim_email_id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">Kirim Email</a>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
