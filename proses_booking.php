<?php
session_start();
$conn = new mysqli("localhost", "root", "", "barberking_db");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = mysqli_real_escape_string($conn, $_POST['nama']);
    $phone     = mysqli_real_escape_string($conn, $_POST['phone']);
    $layanan   = mysqli_real_escape_string($conn, $_POST['layanan']);
    $kapster   = mysqli_real_escape_string($conn, $_POST['kapster']);
    $tanggal   = $_POST['tanggal'];
    $jam_input = $_POST['jam']; 

    // --- 1. AMBIL HARGA OTOMATIS DARI TABEL LAYANAN ---
    $sql_harga = "SELECT harga FROM layanan WHERE nama_layanan = ?";
    $stmt_h = $conn->prepare($sql_harga);
    $stmt_h->bind_param("s", $layanan);
    $stmt_h->execute();
    $result_h = $stmt_h->get_result();
    
    // Jika layanan ditemukan, ambil harganya. Jika tidak (misal input manual), default ke 0.
    $harga = 0;
    if ($row_h = $result_h->fetch_assoc()) {
        $harga = $row_h['harga'];
    }

    // --- 2. CEK TABRAKAN JADWAL (Validasi 60 Menit) ---
    $sql_cek = "SELECT jam FROM bookings WHERE kapster = ? AND tanggal = ?";
    $stmt_cek = $conn->prepare($sql_cek);
    $stmt_cek->bind_param("ss", $kapster, $tanggal);
    $stmt_cek->execute();
    $result = $stmt_cek->get_result();
    
    $is_clash = false;
    $timestamp_input = strtotime("$tanggal $jam_input");
    
    while ($row = $result->fetch_assoc()) {
        $timestamp_existing = strtotime("$tanggal " . $row['jam']);
        $selisih_menit = abs($timestamp_input - $timestamp_existing) / 60;
        if ($selisih_menit < 60) {
            $is_clash = true;
            break;
        }
    }

    if ($is_clash) {
        header("Location: index.php?status=booking_failed&error=jam_penuh#booking");
        exit();
    }

    // --- 3. SIMPAN DATA KE TABEL BOOKINGS ---
    // Pastikan kolom sesuai dengan gambar database Anda: nama, phone, layanan, kapster, tanggal, jam, harga, status
    $sql_ins = "INSERT INTO bookings (nama, phone, layanan, kapster, tanggal, jam, harga, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Menunggu')";
    $stmt_ins = $conn->prepare($sql_ins);

    if (!$stmt_ins) {
        die("Error Prepare: " . $conn->error);
    }

    // "ssssssi" -> 6 string (nama s/d jam) dan 1 integer (harga)
    $stmt_ins->bind_param("ssssssi", $nama, $phone, $layanan, $kapster, $tanggal, $jam_input, $harga);

    if ($stmt_ins->execute()) {
        header("Location: index.php?status=booking_success#booking");
    } else {
        header("Location: index.php?status=booking_failed#booking");
    }
}
?>