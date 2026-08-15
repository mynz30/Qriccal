# QRiccal — Sistem Gateway Ticketing Berbasis QR Code

> QRiccal adalah sistem gateway ticketing berbasis QR Code yang dirancang untuk mempercepat dan mempermudah akses masuk pengunjung secara praktis, aman, dan serba otomatis.

Ini adalah versi rebrand & rombakan total dari project sebelumnya ("wedding-ticketing"). Project lama TIDAK disentuh — ini instalasi baru yang terpisah.

## 1. Setup dari Nol

1. **Hapus/abaikan folder lama** `wedding-ticketing` (boleh dibiarkan saja di htdocs kalau mau, tidak akan bentrok karena nama folder beda).
2. Extract folder `qriccal` ini, taruh di:
   `/Applications/XAMPP/xamppfiles/htdocs/qriccal`
3. Buka `http://localhost/phpmyadmin`, tab **SQL**, jalankan isi file `sql/qriccal.sql`. Ini akan membuat database `qriccal_db` baru (terpisah dari `wedding_ticketing` yang lama).
4. Copy folder `assets/js/html5-qrcode.min.js` dari project lama (`wedding-ticketing/assets/js/`) ke `qriccal/assets/js/` — supaya tidak perlu download ulang.
5. Copy juga folder `vendor/` (isi PHPMailer) dari project lama ke `qriccal/vendor/` — atau jalankan ulang `composer require phpmailer/phpmailer` di dalam folder `qriccal` lewat Terminal.
6. Edit `config/mailer_config.php`, isi ulang `SMTP_USER` dan `SMTP_PASS` (App Password Gmail) — sama seperti yang kamu pakai di project lama.
7. Buka `http://localhost/qriccal/` — harus muncul dashboard QRiccal dengan warna biru.

## 2. Yang Berubah dari Versi Sebelumnya

- **Nama & branding**: "Wedding Ticketing" → **QRiccal**, warna biru `#0047BB` + putih `#F2EFE9` + abu-abu.
- **Kategori bebas**: kolom kategori sekarang bisa diisi teks apa saja (bukan cuma VIP/Reguler) — cocok untuk konteks acara kampus (Peserta, Panitia, Pembicara, dll).
- **Pengaturan Acara** (menu baru ⚙️): admin bisa isi nama acara, deskripsi, logo (via URL), lokasi, link Google Maps, tanggal & waktu.
- **3 Template Email**: pilih salah satu dari halaman Pengaturan Acara, otomatis dipakai untuk semua email yang dikirim.
- **Dashboard & Laporan**: breakdown kategori sekarang dinamis (bukan hardcode VIP/Reguler).

## 3. Cara Pakai untuk Gathering SBM 29 (Contoh Alur)

1. Buka menu **Pengaturan Acara**, isi: nama acara "Gathering SBM 29", deskripsi, lokasi, link gmaps, tanggal, dan pilih template email favorit.
2. Siapkan data pendaftar (misal dari Google Form oprec panitia) → export ke CSV dengan kolom: Nama, Kategori (misal "Pendaftar Divisi Acara"), Keterangan, Email, JumlahTamu.
3. Import CSV di menu **Import Peserta**.
4. Generate QR untuk semua peserta.
5. Kirim email e-tiket (otomatis pakai template & info acara yang sudah diisi).
6. Hari-H: buka **Scan QR** di HP, sambungkan ke jaringan yang sama dengan laptop.

## 4. Struktur Folder

```
qriccal/
├── config/database.php, mailer_config.php
├── includes/functions.php   → termasuk 3 fungsi template email
├── admin/pengaturan.php     → BARU: setting acara + pilih template email
├── admin/import.php, list_tamu.php, export_laporan.php
├── scan/index.php, api/verify_scan.php
├── assets/qrcodes/, assets/js/
└── sql/qriccal.sql
```
