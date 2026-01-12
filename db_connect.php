<?php
// Konfigurasi Database
$host = 'localhost'; // Biasanya 'localhost' jika diuji secara lokal
$db   = 'barberking_db'; // Ganti dengan nama database Anda
$user = 'root'; // Ganti dengan username database Anda (default XAMPP adalah 'root')
$pass = ''; // Ganti dengan password database Anda (default XAMPP adalah kosong)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mengaktifkan mode error yang mengeluarkan exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Mengambil hasil sebagai array asosiatif
    PDO::ATTR_EMULATE_PREPARES   => false, // Memastikan prepared statement digunakan
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     // echo "Koneksi database berhasil!"; // Anda bisa mengaktifkan ini untuk menguji
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Variabel $pdo sekarang memegang koneksi database yang siap digunakan.
?>