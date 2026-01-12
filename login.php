<?php
// 1. Sertakan script koneksi database
require 'db_connect.php'; 

// 2. Cek apakah request datang dari form POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil dan bersihkan data yang dikirim
    $email = trim($_POST['email']); // Dari input id="loginEmail"
    $password = $_POST['password']; // Dari input id="loginPassword"

    // Validasi dasar
    if (empty($email) || empty($password)) {
        // Jika data kosong, redirect dengan status gagal
        header("Location: index.php?status=login_failed"); // Menggunakan status failed
        exit();
    }

    try {
        // 3. Cari pengguna di database berdasarkan email
        $sql = "SELECT id, password_hash, full_name FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        
        // Ambil data pengguna
        $user = $stmt->fetch();

        // 4. Verifikasi Hasil Pencarian dan Password
        if ($user) {
            // Pengguna ditemukan, kini verifikasi password
            if (password_verify($password, $user['password_hash'])) {
                
                // Login BERHASIL!
                // 5. Mulai Sesi (PENTING untuk menjaga status login)
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                // Simpan data penting pengguna ke dalam Sesi
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['logged_in'] = true;
                
                // 🎯 SOLUSI UTAMA: Mengganti Flash Message dengan URL Parameter
                header("Location: index.php?status=login_success");
                exit(); // PENTING: Menghentikan eksekusi script setelah redirect

            } else {
                // Password tidak cocok
                header("Location: index.php?status=login_failed");
                exit();
            }
        } else {
            // Pengguna tidak ditemukan
            header("Location: index.php?status=login_failed");
            exit();
        }

    } catch (PDOException $e) {
        // Handle error database
        // Jika terjadi kesalahan database, anggap login gagal
        header("Location: index.php?status=login_failed");
        exit();
    }
}
?>