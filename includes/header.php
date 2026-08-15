<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $pageTitle ?? 'QRiccal' ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    :root {
        --qr-blue: #0047BB;
        --qr-white: #F2EFE9;
        --qr-gray-light: #E4E4E7;
        --qr-gray-dark: #52525B;
    }
    body { background: var(--qr-white); font-family: 'Segoe UI', Arial, sans-serif; }
    .navbar-brand { font-weight: 800; letter-spacing: 0.5px; }
    .navbar-qriccal { background: var(--qr-blue) !important; }
    .navbar-qriccal .nav-link { color: #dce6ff !important; }
    .navbar-qriccal .nav-link:hover { color: #fff !important; }
    .card { border-radius: 14px; border: none; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
    .btn-primary { background: var(--qr-blue); border-color: var(--qr-blue); }
    .btn-primary:hover { background: #003a99; border-color: #003a99; }
    .btn-outline-primary { color: var(--qr-blue); border-color: var(--qr-blue); }
    .btn-outline-primary:hover { background: var(--qr-blue); border-color: var(--qr-blue); }
    .btn-secondary, .btn-dark { background: var(--qr-gray-dark); border-color: var(--qr-gray-dark); }
    .btn-outline-dark { color: var(--qr-gray-dark); border-color: var(--qr-gray-dark); }
    .btn-outline-dark:hover { background: var(--qr-gray-dark); border-color: var(--qr-gray-dark); }
    .badge-highlight { background: var(--qr-blue); color: #fff; }
    .badge-default { background: var(--qr-gray-light); color: var(--qr-gray-dark); }
    .table-light { background: var(--qr-gray-light) !important; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-qriccal">
  <div class="container">
    <a class="navbar-brand text-white" href="/qriccal/index.php">🎟️ QRiccal</a>
    <div class="navbar-nav">
      <a class="nav-link" href="/qriccal/index.php">Dashboard</a>
      <a class="nav-link" href="/qriccal/admin/import.php">Import Peserta</a>
      <a class="nav-link" href="/qriccal/admin/list_tamu.php">Daftar Peserta & QR</a>
      <a class="nav-link" href="/qriccal/scan/index.php" target="_blank">Scan QR</a>
      <a class="nav-link" href="/qriccal/admin/export_laporan.php">Laporan</a>
      <a class="nav-link" href="/qriccal/admin/pengaturan.php">⚙️ Pengaturan Acara</a>
    </div>
  </div>
</nav>
<div class="container my-4">
