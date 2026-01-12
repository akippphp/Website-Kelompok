<?php
session_start();
$conn = new mysqli("localhost", "root", "", "barberking_db");

// PROSES SIMPAN / EDIT
if (isset($_POST['simpan'])) {
    $id = $_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kapster']);
    $spesialisasi = mysqli_real_escape_string($conn, $_POST['spesialisasi']);
    
    $foto_nama = $_POST['foto_lama']; 
    if ($_FILES['foto']['name'] != "") {
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        $target_dir = "uploads/";
        $ext = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
        $foto_nama = time() . "_" . str_replace(' ', '_', $nama) . "." . $ext;
        
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_dir . $foto_nama)) {
            if ($id != "" && $_POST['foto_lama'] != "" && file_exists("uploads/" . $_POST['foto_lama'])) {
                unlink("uploads/" . $_POST['foto_lama']);
            }
        }
    }

    if ($id == "") {
        $sql = "INSERT INTO kapster (nama_kapster, spesialisasi, foto) VALUES ('$nama', '$spesialisasi', '$foto_nama')";
    } else {
        $sql = "UPDATE kapster SET nama_kapster='$nama', spesialisasi='$spesialisasi', foto='$foto_nama' WHERE id=$id";
    }

    if ($conn->query($sql)) {
        header("Location: Kapster.php?pesan=berhasil");
    } else {
        die("Gagal simpan: " . $conn->error);
    }
}

// PROSES HAPUS
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $res = $conn->query("SELECT foto FROM kapster WHERE id=$id");
    $data = $res->fetch_assoc();
    if ($data['foto'] != "" && file_exists("uploads/" . $data['foto'])) { unlink("uploads/" . $data['foto']); }
    
    $conn->query("DELETE FROM kapster WHERE id=$id");
    header("Location: Kapster.php?pesan=hapus");
}
?>