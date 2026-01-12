<?php
// 1. Sertakan script koneksi
require 'db_connect.php'; 

// Mulai sesi (diperlukan untuk error handling)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Cek apakah request datang dari form POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil dan bersihkan data yang dikirim dari form
    $full_name = trim($_POST['name']); // Dari input id="registerName"
    $email = trim($_POST['email']); // Dari input id="registerEmail"
    $password = $_POST['password']; // Dari input id="registerPassword"

    // Validasi dasar (pastikan semua terisi)
    if (empty($full_name) || empty($email) || empty($password)) {
        // Arahkan kembali dengan pesan error menggunakan flash session (opsional, tapi lebih konsisten)
        // Saya memilih menggunakan GET parameter seperti sebelumnya karena lebih sederhana untuk notifikasi registrasi
        header("Location: index.php?status=registration_failed&error=empty_fields");
        exit();
    }

    // 3. Hash Password (SANGAT PENTING UNTUK KEAMANAN)
    // Gunakan algoritma yang kuat seperti PASSWORD_DEFAULT (saat ini bcrypt)
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // 4. Perintah SQL untuk memasukkan data (Menggunakan Prepared Statements untuk keamanan)
        $sql = "INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        // 5. Eksekusi perintah
        $stmt->execute([$full_name, $email, $password_hash]);

        // Arahkan ke halaman indeks setelah pendaftaran berhasil
        header("Location: index.php?status=registration_success");
        exit(); // PENTING: Menghentikan eksekusi script setelah redirect

    } catch (PDOException $e) {
        // Handle error (misalnya jika email sudah terdaftar)
        if ($e->getCode() == '23000') { // 23000 adalah kode error untuk duplikasi entri
            // Arahkan kembali dengan pesan error
            header("Location: index.php?status=registration_failed&error=email_exists");
            exit();
        } else {
            // Tampilkan error lain jika ada masalah koneksi/query
            // Menggunakan flash message error untuk kasus ini
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => "Terjadi kesalahan database saat mendaftar: " . $e->getMessage()
            ];
            header("Location: index.php");
            exit();
        }
    }
}
?>