<?php
/**
 * TEMPLATE konfigurasi email — file ini AMAN untuk di-push ke GitHub.
 *
 * Cara pakai:
 * 1. Copy file ini, rename jadi "mailer_config.php" (hapus ".example")
 * 2. Isi SMTP_USER dan SMTP_PASS dengan kredensial asli kamu
 * 3. File "mailer_config.php" (tanpa .example) TIDAK akan ikut ter-push ke GitHub
 *    karena sudah didaftarkan di .gitignore
 *
 * CARA DAPAT APP PASSWORD GMAIL:
 * 1. Buka https://myaccount.google.com/security, aktifkan 2-Step Verification
 * 2. Buka https://myaccount.google.com/apppasswords, buat App Password baru
 * 3. Copy 16 karakter yang muncul ke SMTP_PASS di bawah
 */

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'emailkamu@gmail.com');
define('SMTP_PASS', 'xxxx xxxx xxxx xxxx');
define('SMTP_FROM_NAME', 'QRiccal');
