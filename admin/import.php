<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Import Peserta - QRiccal';
$pesan = '';
$errorRows = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_csv'])) {

    $tmpPath = $_FILES['file_csv']['tmp_name'];

    if ($_FILES['file_csv']['error'] !== UPLOAD_ERR_OK) {
        $pesan = 'Gagal upload file.';
    } else {
        $handle = fopen($tmpPath, 'r');
        $baris = 0;
        $berhasil = 0;

        $stmt = $pdo->prepare("
            INSERT INTO tamu (nama, kategori, keterangan, email, jumlah_tamu, kode_unik)
            VALUES (:nama, :kategori, :keterangan, :email, :jumlah, :kode)
        ");

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $baris++;
            if ($baris === 1) continue;

            // Format kolom CSV: Nama, Kategori, Keterangan, Email(opsional), JumlahTamu(opsional)
            $nama       = trim($data[0] ?? '');
            $kategori   = trim($data[1] ?? '') ?: 'Peserta';
            $keterangan = trim($data[2] ?? '');
            $email      = trim($data[3] ?? '');
            $jumlah     = isset($data[4]) && is_numeric($data[4]) ? (int)$data[4] : 1;

            if ($nama === '') {
                $errorRows[] = "Baris $baris dilewati: nama kosong";
                continue;
            }

            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorRows[] = "Baris $baris: email '$email' tidak valid, diimport tanpa email";
                $email = '';
            }

            $kode = generate_kode_unik();

            try {
                $stmt->execute([
                    ':nama' => $nama,
                    ':kategori' => $kategori,
                    ':keterangan' => $keterangan,
                    ':email' => $email !== '' ? $email : null,
                    ':jumlah' => $jumlah,
                    ':kode' => $kode,
                ]);
                $berhasil++;
            } catch (PDOException $e) {
                $errorRows[] = "Baris $baris gagal: " . $e->getMessage();
            }
        }
        fclose($handle);

        $pesan = "Import selesai. $berhasil peserta berhasil ditambahkan.";
    }
}

require __DIR__ . '/../includes/header.php';
?>

<h3>Import Data Peserta dari CSV</h3>
<p class="text-muted">
  Kolom <b>Kategori</b> sekarang bebas diisi teks apa saja (misal: "Peserta", "Panitia", "Pembicara", "VIP") — tidak lagi terbatas VIP/Reguler.
</p>
<table class="table table-bordered table-sm w-auto">
  <thead><tr><th>Nama</th><th>Kategori</th><th>Keterangan</th><th>Email (opsional)</th><th>JumlahTamu (opsional)</th></tr></thead>
  <tbody>
    <tr><td>Z Arsy</td><td>Panitia Divisi Acara</td><td>Koordinator Lapangan</td><td>zarsy@email.com</td><td>1</td></tr>
    <tr><td>Budi Santoso</td><td>Peserta</td><td>Pendaftar Umum</td><td>budi@email.com</td><td>1</td></tr>
  </tbody>
</table>

<?php if ($pesan): ?>
  <div class="alert alert-info"><?= htmlspecialchars($pesan) ?></div>
<?php endif; ?>

<?php if (!empty($errorRows)): ?>
  <div class="alert alert-warning">
    <b>Beberapa baris bermasalah:</b>
    <ul class="mb-0"><?php foreach ($errorRows as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="card p-3">
  <div class="mb-3">
    <label class="form-label">Pilih file CSV</label>
    <input type="file" name="file_csv" accept=".csv" class="form-control" required>
  </div>
  <button type="submit" class="btn btn-primary">Import Sekarang</button>
  <a href="/qriccal/admin/list_tamu.php" class="btn btn-outline-dark">Lihat Daftar Peserta &rarr;</a>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
