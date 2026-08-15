<?php
/**
 * TEMPLATE koneksi database — file ini AMAN untuk di-push ke GitHub.
 *
 * Cara pakai:
 * 1. Copy file ini, rename jadi "database.php" (hapus ".example")
 * 2. Sesuaikan kredensial kalau beda dari default XAMPP
 * 3. File "database.php" (tanpa .example) TIDAK ikut ter-push ke GitHub
 */

$DB_HOST = 'localhost';
$DB_NAME = 'qriccal_db';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage() .
        '<br>Pastikan MySQL di XAMPP sudah menyala dan database "qriccal_db" sudah dibuat.');
}
