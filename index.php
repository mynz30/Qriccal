<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Dashboard - QRiccal';
$acara = ambil_pengaturan_acara($pdo);

$total = $pdo->query("SELECT COUNT(*) FROM tamu")->fetchColumn();
$hadir = $pdo->query("SELECT COUNT(*) FROM tamu WHERE sudah_hadir = 1")->fetchColumn();
$rate  = $total > 0 ? round($hadir / $total * 100, 1) : 0;

// Breakdown dinamis per kategori (tidak lagi hardcode VIP/Reguler)
$breakdown = $pdo->query("
    SELECT kategori,
           COUNT(*) as total,
           SUM(sudah_hadir) as hadir
    FROM tamu
    GROUP BY kategori
    ORDER BY total DESC
")->fetchAll();

$terbaru = $pdo->query("
    SELECT nama, kategori, keterangan, waktu_hadir
    FROM tamu
    WHERE sudah_hadir = 1
    ORDER BY waktu_hadir DESC
    LIMIT 10
")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
  <h3 class="mb-1"><?= htmlspecialchars($acara['nama_acara']) ?></h3>
  <p class="text-muted small mb-0">QRiccal adalah sistem gateway ticketing berbasis QR Code yang dirancang untuk mempercepat dan mempermudah akses masuk pengunjung secara praktis, aman, dan serba otomatis.</p>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card p-3 text-center">
      <div class="text-muted small">Total Peserta Terdaftar</div>
      <div class="fs-2 fw-bold"><?= $total ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-3 text-center">
      <div class="text-muted small">Sudah Check-in</div>
      <div class="fs-2 fw-bold" style="color:#0047BB"><?= $hadir ?> / <?= $total ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-3 text-center">
      <div class="text-muted small">Attendance Rate</div>
      <div class="fs-2 fw-bold"><?= $rate ?>%</div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-5">
    <div class="card p-3">
      <h6 class="mb-3">Breakdown per Kategori</h6>
      <?php foreach ($breakdown as $b): ?>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="badge <?= kategori_badge_class($b['kategori']) ?>"><?= htmlspecialchars($b['kategori']) ?></span>
          <span class="small text-muted"><?= $b['hadir'] ?> / <?= $b['total'] ?> hadir</span>
        </div>
      <?php endforeach; ?>
      <?php if (empty($breakdown)): ?>
        <p class="text-muted small mb-0">Belum ada data peserta.</p>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-md-7">
    <div class="card p-3">
      <h6 class="mb-2">Live Feed — Peserta yang Baru Check-in</h6>
      <table class="table table-sm mb-0">
        <thead><tr><th>Nama</th><th>Kategori</th><th>Jam Hadir</th></tr></thead>
        <tbody>
          <?php foreach ($terbaru as $t): ?>
          <tr>
            <td><?= htmlspecialchars($t['nama']) ?></td>
            <td><span class="badge <?= kategori_badge_class($t['kategori']) ?>"><?= htmlspecialchars($t['kategori']) ?></span></td>
            <td><?= format_tanggal_indo($t['waktu_hadir']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($terbaru)): ?>
          <tr><td colspan="3" class="text-center text-muted">Belum ada peserta yang check-in</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
setTimeout(() => location.reload(), 5000);
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
