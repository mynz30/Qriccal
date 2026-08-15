<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_GET['format']) && $_GET['format'] === 'csv') {
    $rows = $pdo->query("
        SELECT nama, kategori, keterangan, jumlah_tamu, sudah_hadir, waktu_hadir
        FROM tamu ORDER BY kategori ASC, nama ASC
    ")->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=laporan_qriccal_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($output, ['Nama', 'Kategori', 'Keterangan', 'Jumlah Tamu', 'Status', 'Waktu Hadir']);

    foreach ($rows as $r) {
        fputcsv($output, [
            $r['nama'], $r['kategori'], $r['keterangan'], $r['jumlah_tamu'],
            $r['sudah_hadir'] ? 'Hadir' : 'Belum Hadir',
            $r['waktu_hadir'] ? format_tanggal_indo($r['waktu_hadir']) : '-',
        ]);
    }
    fclose($output);
    exit;
}

$acara = ambil_pengaturan_acara($pdo);
$total = $pdo->query("SELECT COUNT(*) FROM tamu")->fetchColumn();
$hadir = $pdo->query("SELECT COUNT(*) FROM tamu WHERE sudah_hadir = 1")->fetchColumn();
$rate  = $total > 0 ? round($hadir / $total * 100, 1) : 0;

$breakdown = $pdo->query("
    SELECT kategori, COUNT(*) as total, SUM(sudah_hadir) as hadir
    FROM tamu GROUP BY kategori ORDER BY total DESC
")->fetchAll();

$daftar = $pdo->query("SELECT * FROM tamu ORDER BY sudah_hadir DESC, waktu_hadir ASC, nama ASC")->fetchAll();

$pageTitle = 'Laporan - QRiccal';
require __DIR__ . '/../includes/header.php';
?>

<style>
    @media print { nav, .no-print { display: none !important; } body { background: white; } }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
  <h3>Laporan Kehadiran</h3>
  <div>
    <a href="?format=csv" class="btn btn-outline-dark">⬇ Download CSV</a>
    <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak / Simpan sebagai PDF</button>
  </div>
</div>

<div class="card p-4 mb-3">
  <h4 class="mb-1"><?= htmlspecialchars($acara['nama_acara']) ?></h4>
  <p class="text-muted">Dicetak pada: <?= format_tanggal_indo(date('Y-m-d H:i:s')) ?></p>

  <div class="row text-center my-3">
    <div class="col-4">
      <div class="fs-3 fw-bold"><?= $total ?></div>
      <div class="text-muted small">Total Terdaftar</div>
    </div>
    <div class="col-4">
      <div class="fs-3 fw-bold" style="color:#0047BB"><?= $hadir ?></div>
      <div class="text-muted small">Hadir</div>
    </div>
    <div class="col-4">
      <div class="fs-3 fw-bold"><?= $rate ?>%</div>
      <div class="text-muted small">Attendance Rate</div>
    </div>
  </div>

  <h6>Breakdown per Kategori</h6>
  <table class="table table-sm w-auto mb-4">
    <thead><tr><th>Kategori</th><th>Hadir</th><th>Total</th></tr></thead>
    <tbody>
      <?php foreach ($breakdown as $b): ?>
      <tr><td><?= htmlspecialchars($b['kategori']) ?></td><td><?= $b['hadir'] ?></td><td><?= $b['total'] ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <table class="table table-bordered table-sm">
    <thead class="table-light">
      <tr><th>No</th><th>Nama</th><th>Kategori</th><th>Keterangan</th><th>Status</th><th>Waktu Hadir</th></tr>
    </thead>
    <tbody>
      <?php foreach ($daftar as $i => $t): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= htmlspecialchars($t['nama']) ?></td>
        <td><?= htmlspecialchars($t['kategori']) ?></td>
        <td><?= htmlspecialchars($t['keterangan']) ?></td>
        <td><?= $t['sudah_hadir'] ? 'Hadir' : 'Belum Hadir' ?></td>
        <td><?= $t['waktu_hadir'] ? format_tanggal_indo($t['waktu_hadir']) : '-' ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
