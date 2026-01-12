<?php
session_start();

// 1. KONEKSI KE DATABASE
$conn = new mysqli("localhost", "root", "", "barberking_db");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// --- LOGIKA PENGHAPUSAN DATA ---
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    // Menggunakan prepared statement untuk keamanan
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id_hapus);
    $stmt->execute();
    header("Location: dataBooking.php");
    exit();
}

// --- LOGIKA PERUBAHAN STATUS ---
if (isset($_GET['toggle_status']) && isset($_GET['current'])) {
    $id_status = $_GET['toggle_status'];
    $current = strtolower(trim($_GET['current'])); 
    $new_status = ($current === 'menunggu') ? 'Selesai' : 'Menunggu';
    
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $id_status);
    $stmt->execute();
    header("Location: dataBooking.php");
    exit();
}

// 2. AMBIL DATA (Sesuai kolom di DB: nama, tanggal, jam)
$query = $conn->query("SELECT * FROM bookings ORDER BY tanggal DESC, jam DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Booking - D'Cutss Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">

<div class="flex min-h-screen">
    <aside class="w-64 bg-gray-900 text-gray-100 flex flex-col shadow-lg sticky top-0 h-screen">
        <div class="px-6 py-6 text-2xl font-bold tracking-wider border-b border-gray-800">
            D'CUTSS <span class="text-blue-500">PRO</span>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 text-sm">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-400 hover:text-white">
                <i class="fas fa-chart-line w-5"></i> Statistik
            </a>
            <a href="dataBooking.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-600 text-white shadow-lg shadow-blue-900/20">
                <i class="fas fa-calendar-check w-5"></i> Data Booking
            </a>
            <a href="Layanan.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-400 hover:text-white">
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
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Data Booking</h1>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">No</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Nama</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Phone</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Layanan</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Kapster</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Waktu</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php 
                        $no = 1;
                        if (!$query || $query->num_rows == 0): 
                        ?>
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-gray-400 italic">Belum ada data di database.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($item = $query->fetch_assoc()): 
                                $status = !empty($item['status']) ? $item['status'] : 'Menunggu';
                                $statusColor = (strtolower($status) === 'selesai') ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700';
                            ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-sm text-gray-600"><?= $no++ ?></td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-800"><?= htmlspecialchars($item['nama']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($item['phone']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($item['layanan']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($item['kapster']) ?></td>
                                    <td class="px-6 py-4 text-xs text-gray-500">
                                        <span class="block font-bold"><?= $item['tanggal'] ?></span>
                                        <span><?= $item['jam'] ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="?toggle_status=<?= $item['id'] ?>&current=<?= $status ?>" 
                                           class="px-3 py-1 text-xs font-bold rounded-full <?= $statusColor ?> cursor-pointer hover:opacity-80 transition inline-block">
                                            <?= strtoupper($status) ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="?hapus=<?= $item['id'] ?>" 
                                           onclick="return confirm('Hapus data booking ini?')"
                                           class="text-red-500 hover:text-red-700 text-sm font-medium transition">
                                            <i class="fas fa-trash-alt mr-1"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>