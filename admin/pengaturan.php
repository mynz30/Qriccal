<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        UPDATE pengaturan_acara SET
            nama_acara = :nama_acara,
            deskripsi_acara = :deskripsi_acara,
            logo_url = :logo_url,
            lokasi = :lokasi,
            lokasi_gmaps_url = :lokasi_gmaps_url,
            tanggal_acara = :tanggal_acara,
            waktu_acara = :waktu_acara,
            template_email = :template_email
        WHERE id = 1
    ");
    $stmt->execute([
        ':nama_acara' => trim($_POST['nama_acara']),
        ':deskripsi_acara' => trim($_POST['deskripsi_acara']),
        ':logo_url' => trim($_POST['logo_url']),
        ':lokasi' => trim($_POST['lokasi']),
        ':lokasi_gmaps_url' => trim($_POST['lokasi_gmaps_url']),
        ':tanggal_acara' => $_POST['tanggal_acara'] ?: null,
        ':waktu_acara' => trim($_POST['waktu_acara']),
        ':template_email' => (int)$_POST['template_email'],
    ]);
    $pesan = 'Pengaturan acara berhasil disimpan.';
}

$acara = ambil_pengaturan_acara($pdo);

// Data contoh untuk preview template email
$contohTamu = ['nama' => 'Nama Peserta Contoh', 'kategori' => 'VIP'];

$pageTitle = 'Pengaturan Acara - QRiccal';
require __DIR__ . '/../includes/header.php';
?>

<h3 class="mb-3">⚙️ Pengaturan Acara</h3>
<p class="text-muted">Data di sini otomatis dipakai untuk mengisi konten email e-tiket yang dikirim ke peserta.</p>

<?php if ($pesan): ?><div class="alert alert-success"><?= $pesan ?></div><?php endif; ?>

<div class="row g-4">
  <div class="col-md-6">
    <form method="post" class="card p-4">
      <div class="mb-3">
        <label class="form-label fw-bold">Nama Acara</label>
        <input type="text" name="nama_acara" class="form-control" value="<?= htmlspecialchars($acara['nama_acara']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label fw-bold">Deskripsi Acara</label>
        <textarea name="deskripsi_acara" class="form-control" rows="3"><?= htmlspecialchars($acara['deskripsi_acara']) ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label fw-bold">URL Logo Acara</label>
        <input type="text" name="logo_url" class="form-control" placeholder="https://.../logo.png" value="<?= htmlspecialchars($acara['logo_url'] ?? '') ?>">
        <div class="form-text">Upload logo ke Google Drive/Imgur dulu, lalu paste link gambarnya di sini.</div>
      </div>
      <div class="mb-3">
        <label class="form-label fw-bold">Lokasi (nama tempat)</label>
        <input type="text" name="lokasi" class="form-control" value="<?= htmlspecialchars($acara['lokasi'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label fw-bold">Link Google Maps</label>
        <input type="text" name="lokasi_gmaps_url" class="form-control" placeholder="https://maps.app.goo.gl/..." value="<?= htmlspecialchars($acara['lokasi_gmaps_url'] ?? '') ?>">
      </div>
      <div class="row">
        <div class="col-6 mb-3">
          <label class="form-label fw-bold">Tanggal Acara</label>
          <input type="date" name="tanggal_acara" class="form-control" value="<?= htmlspecialchars($acara['tanggal_acara'] ?? '') ?>">
        </div>
        <div class="col-6 mb-3">
          <label class="form-label fw-bold">Waktu</label>
          <input type="text" name="waktu_acara" class="form-control" placeholder="09.00 - selesai" value="<?= htmlspecialchars($acara['waktu_acara'] ?? '') ?>">
        </div>
      </div>

      <hr>
      <label class="form-label fw-bold">Pilih Template Email</label>
      <div class="mb-3">
        <?php foreach ([1 => 'Simple & Clean', 2 => 'Bold Banner', 3 => 'Elegant Card'] as $no => $label): ?>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="template_email" value="<?= $no ?>" id="tpl<?= $no ?>"
                 <?= (int)$acara['template_email'] === $no ? 'checked' : '' ?>>
          <label class="form-check-label" for="tpl<?= $no ?>">Template <?= $no ?> — <?= $label ?></label>
        </div>
        <?php endforeach; ?>
      </div>

      <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
    </form>
  </div>

  <div class="col-md-6">
    <h6 class="mb-2">Preview Email (Template <?= (int)$acara['template_email'] ?>)</h6>
    <div class="card p-3" style="background:#fff;">
      <?= bangun_email_html((int)$acara['template_email'], $contohTamu, $acara) ?>
    </div>
    <p class="text-muted small mt-2">Preview ini pakai data contoh. Simpan dulu perubahan di kiri untuk update preview.</p>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
