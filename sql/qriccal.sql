-- =========================================================
-- DATABASE: qriccal_db
-- QRiccal — Sistem Gateway Ticketing Berbasis QR Code
-- =========================================================

CREATE DATABASE IF NOT EXISTS qriccal_db;
USE qriccal_db;

-- Tabel pengaturan acara (cuma 1 baris aktif, id selalu = 1)
-- Data ini yang dipakai untuk mengisi konten email otomatis
CREATE TABLE IF NOT EXISTS pengaturan_acara (
    id INT PRIMARY KEY DEFAULT 1,
    nama_acara VARCHAR(150) NOT NULL DEFAULT 'Nama Acara Kamu',
    deskripsi_acara TEXT DEFAULT NULL,
    logo_url VARCHAR(500) DEFAULT NULL,
    lokasi VARCHAR(255) DEFAULT NULL,
    lokasi_gmaps_url VARCHAR(500) DEFAULT NULL,
    tanggal_acara DATE DEFAULT NULL,
    waktu_acara VARCHAR(50) DEFAULT NULL,
    template_email TINYINT NOT NULL DEFAULT 1  -- pilihan 1, 2, atau 3
);
INSERT INTO pengaturan_acara (id, nama_acara, deskripsi_acara)
VALUES (1, 'Gathering SBM 29', 'Acara gathering dan open recruitment panitia SBM ITB angkatan 29.');

-- Tabel utama data tamu/peserta
-- kategori sekarang bebas diisi teks apa saja (tidak lagi terkunci VIP/Reguler)
CREATE TABLE IF NOT EXISTS tamu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    kategori VARCHAR(50) NOT NULL DEFAULT 'Peserta',
    keterangan VARCHAR(255) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    jumlah_tamu INT NOT NULL DEFAULT 1,
    kode_unik VARCHAR(64) NOT NULL UNIQUE,
    qr_path VARCHAR(255) DEFAULT NULL,
    email_terkirim TINYINT(1) NOT NULL DEFAULT 0,
    sudah_hadir TINYINT(1) NOT NULL DEFAULT 0,
    waktu_hadir DATETIME DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Log setiap kejadian scan (audit trail)
CREATE TABLE IF NOT EXISTS scan_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tamu_id INT NOT NULL,
    hasil ENUM('BERHASIL','SUDAH_PERNAH','TIDAK_VALID') NOT NULL,
    waktu_scan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tamu_id) REFERENCES tamu(id)
);
