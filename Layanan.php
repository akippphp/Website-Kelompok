<?php
session_start();
$conn = new mysqli("localhost", "root", "", "barberking_db");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// LOGIKA SIMPAN & EDIT
if (isset($_POST['simpan'])) {
    $id = $_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_layanan']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    // Proses Upload Foto
    $foto_nama = $_POST['foto_lama'] ?? ''; 
    if ($_FILES['foto']['name'] != "") {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true); 
        
        $foto_nama = time() . "_" . basename($_FILES["foto"]["name"]);
        $target_file = $target_dir . $foto_nama;
        move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file);
        
        // Hapus foto lama jika ganti foto baru
        if ($id != "" && $_POST['foto_lama'] != "" && file_exists("uploads/" . $_POST['foto_lama'])) {
            unlink("uploads/" . $_POST['foto_lama']);
        }
    }

    if ($id == "") {
        $sql = "INSERT INTO layanan (nama_layanan, harga, deskripsi, foto) VALUES ('$nama', '$harga', '$deskripsi', '$foto_nama')";
    } else {
        $sql = "UPDATE layanan SET nama_layanan='$nama', harga='$harga', deskripsi='$deskripsi', foto='$foto_nama' WHERE id=$id";
    }

    if ($conn->query($sql)) {
        header("Location: Layanan.php?pesan=berhasil");
        exit();
    }
}

// LOGIKA HAPUS
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $res = $conn->query("SELECT foto FROM layanan WHERE id=$id");
    $data = $res->fetch_assoc();
    if($data['foto'] && file_exists("uploads/".$data['foto'])) unlink("uploads/".$data['foto']);

    $conn->query("DELETE FROM layanan WHERE id=$id");
    header("Location: Layanan.php?pesan=hapus");
    exit();
}

// AMBIL DATA EDIT
$edit_data = ['id' => '', 'nama_layanan' => '', 'harga' => '', 'deskripsi' => '', 'foto' => ''];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM layanan WHERE id=$id");
    if ($result && $result->num_rows > 0) { 
        $edit_data = $result->fetch_assoc(); 
    }
}

$layanan_list = $conn->query("SELECT * FROM layanan ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Layanan - D'Cutss Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-900">

    <div class="flex min-h-screen">
        <aside class="w-64 bg-gray-900 text-gray-100 flex flex-col shadow-lg sticky top-0 h-screen">
            <div class="px-6 py-6 text-2xl font-bold tracking-wider border-b border-gray-800 text-white">
                D'CUTSS <span class="text-blue-500">PRO</span>
            </div>
            
            <nav class="flex-1 px-4 py-6 space-y-2 text-sm">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-400 hover:text-white">
                    <i class="fas fa-chart-line w-5"></i> Statistik
                </a>
                <a href="dataBooking.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-400 hover:text-white">
                    <i class="fas fa-calendar-check w-5"></i> Data Booking
                </a>
                <a href="Layanan.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-600 text-white shadow-lg shadow-blue-900/20">
                    <i class="fas fa-cut w-5"></i> Layanan
                </a>
                <a href="Kapster.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-400 hover:text-white">
                    <i class="fas fa-user-friends w-5"></i> Kapster
                </a>
                <a href="jadwal.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-400 hover:text-white">
                    <i class="fas fa-clock w-5"></i> Jadwal Kapster
                </a>
                <a href="inputData.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-400 hover:text-white">
                    <i class="fas fa-plus-circle w-5"></i> Booking Manual
                </a>
            </nav>

            <div class="px-4 py-4 border-t border-gray-800">
                <a href="logout.php" class="flex items-center gap-3 px-4 py-2 text-red-400 hover:bg-red-500/10 rounded-lg transition text-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </aside>

        <main class="flex-1 p-8">
            <div class="max-w-6xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800">Manajemen Layanan</h1>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 mb-10">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                        <i class="fas <?= $edit_data['id'] ? 'fa-pen-to-square text-orange-500' : 'fa-plus-square text-blue-500' ?>"></i>
                        <?= $edit_data['id'] ? 'Edit Layanan' : 'Tambah Layanan Baru' ?>
                    </h2>
                    
                    <form action="" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                        <input type="hidden" name="foto_lama" value="<?= $edit_data['foto'] ?>">
                        
                        <div class="flex flex-col">
                            <label class="text-sm font-semibold text-gray-600 mb-2">Nama Layanan</label>
                            <input type="text" name="nama_layanan" value="<?= $edit_data['nama_layanan'] ?>" placeholder="Contoh: Premium Haircut" class="border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                        </div>
                        
                        <div class="flex flex-col">
                            <label class="text-sm font-semibold text-gray-600 mb-2">Harga (Rp)</label>
                            <input type="number" name="harga" value="<?= $edit_data['harga'] ?>" placeholder="50000" class="border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                        </div>

                        <div class="flex flex-col md:col-span-2">
                            <label class="text-sm font-semibold text-gray-600 mb-2">Deskripsi Singkat</label>
                            <textarea name="deskripsi" rows="2" class="border border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition" required placeholder="Jelaskan apa saja yang didapat pelanggan..."><?= $edit_data['deskripsi'] ?></textarea>
                        </div>

                        <div class="flex flex-col md:col-span-2">
                            <label class="text-sm font-semibold text-gray-600 mb-2">Foto Layanan</label>
                            <input type="file" name="foto" class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 p-2 rounded-xl bg-gray-50" <?= $edit_data['id'] ? '' : 'required' ?>>
                            <?php if($edit_data['foto']): ?>
                                <p class="text-xs text-blue-500 mt-2 font-medium italic"><i class="fas fa-image mr-1"></i> File aktif: <?= $edit_data['foto'] ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="md:col-span-2 flex items-center gap-4 mt-2">
                            <button type="submit" name="simpan" class="bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200 font-semibold text-sm">
                                <i class="fas fa-save mr-2"></i> <?= $edit_data['id'] ? 'Simpan Perubahan' : 'Publish Layanan' ?>
                            </button>
                            <?php if($edit_data['id']): ?>
                                <a href="Layanan.php" class="text-gray-500 hover:text-gray-800 text-sm font-medium transition hover:underline">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                        <h3 class="font-bold text-gray-800">Daftar Layanan Tersedia</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-xs uppercase text-gray-400 font-bold border-b border-gray-100">
                                    <th class="px-6 py-4">Visual</th>
                                    <th class="px-6 py-4">Nama Layanan</th>
                                    <th class="px-6 py-4">Harga</th>
                                    <th class="px-6 py-4">Deskripsi</th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if($layanan_list->num_rows > 0): ?>
                                    <?php while($row = $layanan_list->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-6 py-4">
                                            <?php if($row['foto']): ?>
                                                <img src="uploads/<?= $row['foto'] ?>" class="w-16 h-12 object-cover rounded-xl border border-gray-100 shadow-sm">
                                            <?php else: ?>
                                                <div class="w-16 h-12 bg-gray-100 rounded-xl flex items-center justify-center text-gray-400 border border-gray-200">
                                                    <i class="fas fa-camera text-sm"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 font-bold text-gray-800"><?= htmlspecialchars($row['nama_layanan']) ?></td>
                                        <td class="px-6 py-4">
                                            <span class="text-green-600 font-bold">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 text-sm max-w-xs truncate"><?= htmlspecialchars($row['deskripsi']) ?></td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex justify-center gap-4">
                                                <a href="?edit=<?= $row['id'] ?>" class="text-blue-500 hover:text-blue-700 transition" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?hapus=<?= $row['id'] ?>" class="text-red-400 hover:text-red-600 transition" onclick="return confirm('Hapus layanan ini?')" title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-20 text-center">
                                            <div class="flex flex-col items-center opacity-30">
                                                <i class="fas fa-box-open text-5xl mb-3"></i>
                                                <p class="italic">Belum ada layanan yang ditambahkan.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>