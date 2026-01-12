<?php
session_start();
$conn = new mysqli("localhost", "root", "", "barberking_db");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil tanggal dari filter, jika tidak ada default ke hari ini
$tanggal_pilihan = $_GET['tanggal'] ?? date('Y-m-d');

// 1. AMBIL SEMUA DATA KAPSTER (Jika Anda masih butuh daftar kapster dari tabel kapster)
$kapsters = $conn->query("SELECT * FROM kapster");
$list_kapster = [];
if ($kapsters) {
    while($k = $kapsters->fetch_assoc()) {
        $list_kapster[] = $k;
    }
}

// 2. AMBIL JADWAL BOOKING
// Perbaikan: Kita ambil kolom 'kapster' langsung dari tabel bookings
$sql_jadwal = "SELECT * FROM bookings 
               WHERE tanggal = '$tanggal_pilihan' 
               ORDER BY jam ASC";
$result_jadwal = $conn->query($sql_jadwal);

// Kelompokkan data berdasarkan jam agar mudah ditampilkan di timeline
$jadwal_terisi = [];
if ($result_jadwal && $result_jadwal->num_rows > 0) {
    while($row = $result_jadwal->fetch_assoc()) {
        // Ambil 5 karakter pertama dari jam (misal 09:00:00 jadi 09:00)
        $jam_key = substr($row['jam'], 0, 5); 
        $jadwal_terisi[$jam_key][] = $row;
    }
}

// Daftar jam operasional (Sesuaikan jika ada jam lain)
$jam_operasional = [
    "09:00", "10:00", "11:00", "13:00", "14:00", 
    "15:00", "16:00", "17:00", "18:00", "19:00", "20:00",
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kapster - D'Cutss</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">

<div class="flex min-h-screen">
    <aside class="w-64 bg-gray-900 text-gray-100 flex flex-col shadow-lg sticky top-0 h-screen">
        <div class="px-6 py-6 text-2xl font-bold tracking-wider border-b border-gray-800">
            D'CUTSS <span class="text-blue-500">PRO</span>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 text-sm">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-400 hover:text-white">
                <i class="fas fa-chart-line w-5"></i> Statistik
            </a>
            <a href="dataBooking.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-400 hover:text-white">
                <i class="fas fa-calendar-check w-5"></i> Data Booking
            </a>
            <a href="Layanan.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-400 hover:text-white">
                <i class="fas fa-cut w-5"></i> Layanan
            </a>
            <a href="Kapster.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-400 hover:text-white">
                <i class="fas fa-user-friends w-5"></i> Kapster
            </a>
            <a href="jadwal.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-600 text-white shadow-lg shadow-blue-900/20">
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
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Jadwal Harian</h1>
                <p class="text-gray-500"><span class="font-bold text-blue-600"><?= date('d M Y', strtotime($tanggal_pilihan)) ?></span></p>
            </div>
            
            <form action="" method="GET" class="flex items-center gap-2 bg-white p-2 rounded-xl shadow-sm border">
                <input type="date" name="tanggal" value="<?= $tanggal_pilihan ?>" 
                       class="outline-none text-sm p-2 text-gray-700" onchange="this.form.submit()">
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="p-6 text-left text-xs font-bold text-gray-400 uppercase tracking-wider w-32">Waktu</th>
                            <th class="p-6 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Aktivitas & Kapster</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach($jam_operasional as $jam): ?>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="p-6 align-top">
                                <span class="text-lg font-bold text-gray-700"><?= $jam ?></span>
                                <p class="text-[10px] text-gray-400 font-medium">WIB</p>
                            </td>
                            <td class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <?php 
                                    if(isset($jadwal_terisi[$jam])): 
                                        foreach($jadwal_terisi[$jam] as $book):
                                            $status = strtolower($book['status']);
                                            $cardStyle = ($status === 'selesai') ? 'bg-green-50 border-green-100' : 'bg-blue-50 border-blue-100';
                                            $textColor = ($status === 'selesai') ? 'text-green-600' : 'text-blue-600';
                                    ?>
                                        <div class="<?= $cardStyle ?> border p-4 rounded-xl flex flex-col gap-2 relative group shadow-sm">
                                            <div class="flex justify-between items-start">
                                                <span class="text-xs font-bold <?= $textColor ?> uppercase tracking-tight">
                                                    <i class="fas fa-user-tie mr-1"></i> 
                                                    <?= htmlspecialchars($book['kapster'] ?? 'No Name') ?>
                                                </span>
                                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold <?= ($status === 'selesai') ? 'bg-green-200 text-green-800' : 'bg-blue-200 text-blue-800' ?>">
                                                    <?= strtoupper($book['status']) ?>
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($book['nama']) ?></p>
                                                <p class="text-xs text-gray-500 italic"><?= htmlspecialchars($book['layanan']) ?></p>
                                            </div>
                                        </div>
                                    <?php 
                                        endforeach;
                                    else: 
                                    ?>
                                        <div class="border border-dashed border-gray-200 p-4 rounded-xl flex items-center justify-center bg-gray-50/30">
                                            <span class="text-xs text-gray-400 italic">Slot Kosong</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>