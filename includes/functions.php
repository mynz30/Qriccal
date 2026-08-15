<?php
/**
 * Kumpulan fungsi bantu QRiccal.
 */

function generate_kode_unik(): string
{
    return strtoupper(substr(bin2hex(random_bytes(8)), 0, 12));
}

function generate_qr_image(string $data, string $savePath): bool
{
    $url = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=' . urlencode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $imageContent = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($imageContent === false || $imageContent === '') {
        file_put_contents(__DIR__ . '/../qr_error_log.txt', date('Y-m-d H:i:s') . ' - ' . $error . "\n", FILE_APPEND);
        return false;
    }

    return file_put_contents($savePath, $imageContent) !== false;
}

function format_tanggal_indo(?string $datetime): string
{
    if (!$datetime) return '-';
    $bulan = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $ts = strtotime($datetime);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y H:i', $ts);
}

function format_tanggal_saja(?string $date): string
{
    if (!$date) return '-';
    $bulan = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $ts = strtotime($date);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * Kelas badge untuk kategori tamu yang sekarang bebas teks.
 * Kategori yang mengandung kata "vip"/"prioritas"/"panitia inti" disorot warna biru,
 * kategori lain pakai abu-abu netral.
 */
function kategori_badge_class(string $kategori): string
{
    $highlight = ['vip', 'prioritas', 'panitia inti', 'inti'];
    foreach ($highlight as $h) {
        if (stripos($kategori, $h) !== false) return 'badge-highlight';
    }
    return 'badge-default';
}

/**
 * Ambil data pengaturan acara aktif (selalu 1 baris, id = 1).
 */
function ambil_pengaturan_acara(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT * FROM pengaturan_acara WHERE id = 1");
    $data = $stmt->fetch();
    return $data ?: [
        'nama_acara' => 'Nama Acara Kamu',
        'deskripsi_acara' => '',
        'logo_url' => '',
        'lokasi' => '',
        'lokasi_gmaps_url' => '',
        'tanggal_acara' => null,
        'waktu_acara' => '',
        'template_email' => 1,
    ];
}

/**
 * Bangun isi HTML email berdasarkan template yang dipilih (1, 2, atau 3).
 * $tamu: array data tamu (nama, kategori, keterangan)
 * $acara: array data pengaturan acara
 */
function bangun_email_html(int $templateNo, array $tamu, array $acara): string
{
    $nama       = htmlspecialchars($tamu['nama']);
    $kategori   = htmlspecialchars($tamu['kategori']);
    $namaAcara  = htmlspecialchars($acara['nama_acara'] ?? 'Acara');
    $deskripsi  = nl2br(htmlspecialchars($acara['deskripsi_acara'] ?? ''));
    $logo       = htmlspecialchars($acara['logo_url'] ?? '');
    $lokasi     = htmlspecialchars($acara['lokasi'] ?? '');
    $gmapsUrl   = htmlspecialchars($acara['lokasi_gmaps_url'] ?? '');
    $tanggal    = format_tanggal_saja($acara['tanggal_acara'] ?? null);
    $waktu      = htmlspecialchars($acara['waktu_acara'] ?? '');

    $BIRU = '#0047BB';
    $PUTIH = '#F2EFE9';
    $ABU_TUA = '#52525B';
    $ABU_MUDA = '#E4E4E7';

    $logoHtml = $logo
        ? "<img src=\"{$logo}\" alt=\"Logo\" style=\"max-height:60px;margin-bottom:12px;\">"
        : '';

    $gmapsButton = $gmapsUrl
        ? "<a href=\"{$gmapsUrl}\" style=\"display:inline-block;background:{$BIRU};color:#fff;text-decoration:none;padding:10px 20px;border-radius:6px;font-weight:bold;margin-top:12px;\">📍 Lihat Lokasi di Google Maps</a>"
        : '';

    // ================= TEMPLATE 1: Simple & Clean =================
    if ($templateNo === 1) {
        return "
        <div style=\"font-family:Arial,sans-serif;max-width:520px;margin:0 auto;background:#fff;\">
            <div style=\"background:{$BIRU};padding:24px;text-align:center;\">
                {$logoHtml}
                <h2 style=\"color:#fff;margin:0;font-size:20px;\">{$namaAcara}</h2>
            </div>
            <div style=\"padding:24px;color:{$ABU_TUA};\">
                <p>Halo <b>{$nama}</b>,</p>
                <p>E-tiket kamu untuk kategori <b>{$kategori}</b> sudah siap. Mohon tunjukkan QR Code terlampir saat check-in di lokasi acara.</p>
                <table style=\"width:100%;background:{$ABU_MUDA};border-radius:8px;padding:12px;margin:16px 0;border-collapse:collapse;\">
                    <tr><td style=\"padding:6px 12px;\">📅 Tanggal</td><td style=\"padding:6px 12px;\"><b>{$tanggal}</b></td></tr>
                    <tr><td style=\"padding:6px 12px;\">🕒 Waktu</td><td style=\"padding:6px 12px;\"><b>{$waktu}</b></td></tr>
                    <tr><td style=\"padding:6px 12px;\">📍 Lokasi</td><td style=\"padding:6px 12px;\"><b>{$lokasi}</b></td></tr>
                </table>
                {$gmapsButton}
                <p style=\"margin-top:24px;font-size:13px;color:#999;\">Sampai jumpa di acara! — QRiccal</p>
            </div>
        </div>";
    }

    // ================= TEMPLATE 2: Bold Banner =================
    if ($templateNo === 2) {
        return "
        <div style=\"font-family:Arial,sans-serif;max-width:520px;margin:0 auto;background:{$PUTIH};border-radius:10px;overflow:hidden;\">
            <div style=\"background:{$BIRU};padding:32px 24px;text-align:center;\">
                {$logoHtml}
                <h1 style=\"color:#fff;margin:0;font-size:24px;\">{$namaAcara}</h1>
                <p style=\"color:#dce6ff;margin-top:6px;font-size:14px;\">{$tanggal} • {$waktu}</p>
            </div>
            <div style=\"padding:28px 24px;\">
                <p style=\"color:{$ABU_TUA};\">Halo <b>{$nama}</b> 👋</p>
                <p style=\"color:{$ABU_TUA};\">{$deskripsi}</p>
                <div style=\"background:#fff;border:2px solid {$BIRU};border-radius:8px;padding:14px;margin:18px 0;text-align:center;\">
                    <div style=\"font-size:12px;color:#888;\">KATEGORI TIKET</div>
                    <div style=\"font-size:20px;font-weight:bold;color:{$BIRU};\">{$kategori}</div>
                </div>
                <p style=\"color:{$ABU_TUA};font-size:14px;\">📍 <b>{$lokasi}</b></p>
                {$gmapsButton}
                <p style=\"margin-top:20px;font-size:13px;color:#999;\">Tunjukkan QR Code terlampir saat check-in. Sampai jumpa! — QRiccal</p>
            </div>
        </div>";
    }

    // ================= TEMPLATE 3: Elegant Card =================
    return "
    <div style=\"font-family:Arial,sans-serif;max-width:520px;margin:0 auto;background:{$ABU_MUDA};padding:24px;\">
        <div style=\"background:#fff;border-radius:14px;padding:28px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.08);\">
            {$logoHtml}
            <h2 style=\"color:{$BIRU};margin:8px 0 4px;\">{$namaAcara}</h2>
            <p style=\"color:#888;font-size:13px;margin-bottom:20px;\">E-Tiket Digital</p>
            <div style=\"border-top:1px dashed {$ABU_TUA};border-bottom:1px dashed {$ABU_TUA};padding:16px 0;margin:16px 0;\">
                <p style=\"margin:4px 0;color:{$ABU_TUA};\">Nama: <b>{$nama}</b></p>
                <p style=\"margin:4px 0;color:{$ABU_TUA};\">Kategori: <b style=\"color:{$BIRU};\">{$kategori}</b></p>
            </div>
            <p style=\"color:{$ABU_TUA};font-size:14px;\">📅 {$tanggal} &nbsp; 🕒 {$waktu}</p>
            <p style=\"color:{$ABU_TUA};font-size:14px;\">📍 {$lokasi}</p>
            {$gmapsButton}
            <p style=\"margin-top:24px;font-size:12px;color:#aaa;\">Ditenagai oleh QRiccal — Gateway Ticketing System</p>
        </div>
    </div>";
}

/**
 * Kirim e-tiket (QR code) ke email tamu, memakai template & data acara yang aktif.
 */
function kirim_email_qr(PDO $pdo, string $namaTujuan, string $emailTujuan, string $kategori, string $qrFilePath): array
{
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../config/mailer_config.php';

    $acara = ambil_pengaturan_acara($pdo);
    $templateNo = (int)($acara['template_email'] ?? 1);
    $bodyHtml = bangun_email_html($templateNo, ['nama' => $namaTujuan, 'kategori' => $kategori], $acara);

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
        $mail->addAddress($emailTujuan, $namaTujuan);

        if (file_exists($qrFilePath)) {
            $mail->addAttachment($qrFilePath, 'e-tiket-' . str_replace(' ', '-', $namaTujuan) . '.png');
        }

        $mail->isHTML(true);
        $mail->Subject = 'E-Tiket — ' . ($acara['nama_acara'] ?? 'Acara Kamu');
        $mail->Body    = $bodyHtml;

        $mail->send();
        return ['success' => true, 'error' => null];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}
