<?php
$conn = new mysqli("localhost", "root", "", "barberking_db");

if (isset($_POST['simpan_booking'])) {
    // 1. Tangkap data dari form (Pastikan atribut 'name' di HTML sesuai)
    // Gunakan mysqli_real_escape_string untuk keamanan data
    $nama    = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']); 
    $phone   = mysqli_real_escape_string($conn, $_POST['phone']);
    $layanan = mysqli_real_escape_string($conn, $_POST['layanan']);
    $kapster = mysqli_real_escape_string($conn, $_POST['kapster']);
    $harga   = mysqli_real_escape_string($conn, $_POST['harga']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $jam     = mysqli_real_escape_string($conn, $_POST['jam']);
    
    $status  = "Menunggu"; // Status default

    // 2. Query INSERT disesuaikan dengan kolom database Anda (nama, phone, layanan, kapster, tanggal, jam, status, harga)
    // Urutan kolom harus sama dengan urutan VALUES
    $sql = "INSERT INTO bookings (nama, phone, layanan, kapster, tanggal, jam, status, harga) 
            VALUES ('$nama', '$phone', '$layanan', '$kapster', '$tanggal', '$jam', '$status', '$harga')";

    if ($conn->query($sql)) {
        header("Location: dataBooking.php");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>