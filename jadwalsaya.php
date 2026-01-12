<?php
session_start();
$conn = new mysqli("localhost", "root", "", "barberking_db");

// PERBAIKAN PROTEKSI: Menggunakan 'user_name' sesuai login.php Anda
if (!isset($_SESSION['user_name'])) {
    header("Location: index.php"); // Diarahkan ke index karena login menggunakan modal di sana
    exit();
}

$nama_user = $_SESSION['user_name'];

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// --- LOGIKA PEMBATALAN ---
if (isset($_POST['cancel_id'])) {
    $id_to_delete = $_POST['cancel_id'];
    
    // Keamanan: Hapus hanya jika ID cocok DAN nama cocok dengan session
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND nama = ?");
    $stmt->bind_param("is", $id_to_delete, $nama_user);
    
    if ($stmt->execute()) {
        $pesan_sukses = "Jadwal berhasil dibatalkan.";
    } else {
        $pesan_error = "Gagal membatalkan jadwal.";
    }
    $stmt->close();
}

// --- AMBIL DATA JADWAL SAYA ---
$sql_saya = "SELECT * FROM bookings 
             WHERE nama = ? 
             ORDER BY tanggal DESC, jam ASC";
$stmt_get = $conn->prepare($sql_saya);
$stmt_get->bind_param("s", $nama_user);
$stmt_get->execute();
$result_saya = $stmt_get->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Saya - D'Cutss</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">

<div class="flex min-h-screen">
    <aside class="w-64 bg-gray-900 text-gray-100 flex flex-col shadow-lg sticky top-0 h-screen">
        <div class="px-6 py-6 text-2xl font-bold tracking-wider border-b border-gray-800 text-blue-400">
            D'CUTSS
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 text-sm">
            <a href="jadwalBooking.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 transition">
                <i class="fas fa-calendar-alt w-5"></i> Jadwal Kapster
            </a>
            <a href="jadwalsaya.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-600 text-white shadow-lg">
                <i class="fas fa-user-clock w-5"></i> Jadwal Saya
            </a>
        </nav>
        <div class="px-4 py-4 border-t border-gray-800">
            <a href="index.php" class="flex items-center gap-3 px-4 py-2 text-red-400 hover:bg-red-500/10 rounded-lg transition text-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </aside>

    <main class="flex-1 p-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Jadwal Saya</h1>
            <p class="text-gray-500">Halo, <span class="font-semibold text-blue-600"><?= htmlspecialchars($nama_user) ?></span>! Ini adalah daftar pesanan Anda.</p>
        </div>

        <?php if(isset($pesan_sukses)): ?>
            <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 flex justify-between items-center rounded-r-lg">
                <span><i class="fas fa-check-circle mr-2"></i> <?= $pesan_sukses ?></span>
                <button onclick="this.parentElement.remove()" class="text-green-900 font-bold">&times;</button>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($result_saya->num_rows > 0): ?>
                <?php while($row = $result_saya->fetch_assoc()): 
                    $status = strtolower($row['status']);
                    $is_selesai = ($status === 'selesai');
                    $tgl_format = date('d M Y', strtotime($row['tanggal']));
                    $jam_format = substr($row['jam'], 0, 5);
                ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col transition-transform hover:scale-[1.02]">
                        <div class="p-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Booking Status</span>
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold <?= $is_selesai ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600' ?>">
                                <?= strtoupper($status) ?>
                            </span>
                        </div>
                        
                        <div class="p-5 flex-1 space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold">Tanggal & Jam</p>
                                    <p class="text-sm font-bold text-gray-800"><?= $tgl_format ?> | <?= $jam_format ?> WIB</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center text-purple-600">
                                    <i class="fas fa-cut"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold">Layanan & Kapster</p>
                                    <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($row['layanan']) ?></p>
                                    <p class="text-xs text-gray-500">Bersama: <span class="font-medium"><?= htmlspecialchars($row['kapster']) ?></span></p>
                                </div>
                            </div>
                        </div>

                        <?php if(!$is_selesai): ?>
                        <div class="p-4 bg-gray-50 border-t border-gray-100">
                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan booking ini?')">
                                <input type="hidden" name="cancel_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="w-full py-2 bg-white border border-red-200 text-red-500 rounded-xl text-xs font-bold hover:bg-red-500 hover:text-white transition-all duration-200 shadow-sm">
                                    BATALKAN BOOKING
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center bg-white rounded-3xl border-2 border-dashed border-gray-200">
                    <div class="text-gray-200 mb-4 text-6xl">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-700">Belum Ada Pesanan</h3>
                    <p class="text-gray-400">Anda tidak memiliki jadwal booking aktif atau riwayat saat ini.</p>
                    <a href="index.php" class="mt-6 inline-block px-8 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-200">Booking Sekarang</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>