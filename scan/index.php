<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Scan Peserta - QRiccal</title>
<script src="../assets/js/html5-qrcode.min.js"></script>
<style>
  * { box-sizing: border-box; }
  body {
    margin: 0; font-family: -apple-system, Segoe UI, Roboto, sans-serif;
    background: #0047BB; color: #fff; text-align: center;
  }
  h2 { padding: 14px 0 4px; margin: 0; font-size: 18px; }
  #reader { width: 100%; max-width: 480px; margin: 10px auto; }
  #result-box {
    margin: 12px auto; max-width: 480px; padding: 18px; border-radius: 14px;
    font-size: 18px; min-height: 90px; display:flex; flex-direction:column;
    justify-content:center; transition: background .2s;
  }
  .status-idle    { background: rgba(255,255,255,0.15); }
  .status-valid   { background:#16a34a; }
  .status-used    { background:#d97706; }
  .status-invalid { background:#dc2626; }
  .nama   { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
  .ket    { font-size: 14px; opacity: .9; }
  .badge-scan { background:#fff; color:#0047BB; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:bold; }
</style>
</head>
<body>

<h2>🎟️ QRiccal — Scan Peserta</h2>
<div id="reader"></div>

<div id="result-box" class="status-idle">
  Arahkan kamera ke QR Code peserta
</div>

<script>
const resultBox = document.getElementById('result-box');
let isProcessing = false;

function renderResult(data) {
  let html = '';
  resultBox.className = 'status-' + data.status;

  if (data.status === 'valid') {
    html = `<div class="nama">✅ ${data.nama}</div>
            <div class="ket"><span class="badge-scan">${data.kategori}</span> ${data.keterangan ?? ''}</div>
            <div class="ket">Silakan masuk 🙏</div>`;
  } else if (data.status === 'used') {
    html = `<div class="nama">⚠️ ${data.nama}</div>
            <div class="ket">Sudah check-in sebelumnya</div>`;
  } else {
    html = `<div class="nama">❌ QR Tidak Valid</div>
            <div class="ket">${data.message ?? ''}</div>`;
  }
  resultBox.innerHTML = html;
}

async function onScanSuccess(decodedText) {
  if (isProcessing) return;
  isProcessing = true;

  try {
    const res = await fetch('../api/verify_scan.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'kode=' + encodeURIComponent(decodedText)
    });
    const data = await res.json();
    renderResult(data);
  } catch (err) {
    resultBox.className = 'status-invalid';
    resultBox.innerHTML = `<div class="nama">⚠️ Gagal terhubung ke server</div><div class="ket">Cek koneksi WiFi ke laptop</div>`;
  }

  setTimeout(() => { isProcessing = false; }, 2500);
}

const html5QrCode = new Html5Qrcode("reader");
html5QrCode.start(
  { facingMode: "environment" },
  { fps: 10, qrbox: { width: 250, height: 250 } },
  onScanSuccess
).catch(err => {
  resultBox.className = 'status-invalid';
  resultBox.innerHTML = `<div class="nama">Kamera tidak bisa diakses</div><div class="ket">Izinkan akses kamera di browser HP kamu</div>`;
});
</script>

</body>
</html>
