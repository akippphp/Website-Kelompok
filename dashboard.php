<?php
// dashboard.php
session_start();

// 1. KONEKSI KE DATABASE
$conn = new mysqli("localhost", "root", "", "barberking_db");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$user = $_SESSION['user_name'] ?? 'Admin';

// 2. AMBIL TOTAL ORDERS
$sql_total = "SELECT COUNT(*) as total FROM bookings";
$result_total = $conn->query($sql_total);
$row_total = $result_total->fetch_assoc();
$total_orders = $row_total['total'];

// 3. AMBIL TOTAL PENDAPATAN
$sql_income = "SELECT SUM(harga) as total_pendapatan FROM bookings WHERE status = 'Selesai'";
$result_income = $conn->query($sql_income);
$row_income = $result_income->fetch_assoc();
$total_pendapatan = $row_income['total_pendapatan'] ?? 0;

// 4. AMBIL TOTAL KAPSTER
$sql_kapster = "SELECT COUNT(*) as total_kapster FROM kapster"; 
$result_kapster = $conn->query($sql_kapster);
$total_kapster = ($result_kapster) ? $result_kapster->fetch_assoc()['total_kapster'] : 0;

// 5. AMBIL 5 TRANSAKSI TERBARU (Sesuai kolom 'nama')
$sql_recent = "SELECT * FROM bookings ORDER BY id DESC LIMIT 5";
$recent_bookings = $conn->query($sql_recent);

// 6. LOGIKA DATA GRAFIK (7 Hari Terakhir) - Disesuaikan ke kolom 'tanggal'
$hari_indo = [
    'Sunday' => 'Min', 'Monday' => 'Sen', 'Tuesday' => 'Sel', 
    'Wednesday' => 'Rab', 'Thursday' => 'Kam', 'Friday' => 'Jum', 'Saturday' => 'Sab'
];

$grafik_labels = [];
$grafik_data = [];

// Query diperbaiki: Menggunakan kolom 'tanggal' (bukan tanggal_jam)
$sql_grafik = "SELECT DAYNAME(tanggal) as nama_hari, COUNT(*) as jumlah 
               FROM bookings 
               WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
               GROUP BY tanggal, nama_hari
               ORDER BY tanggal ASC";

$result_grafik = $conn->query($sql_grafik);

if ($result_grafik && $result_grafik->num_rows > 0) {
    while($row_g = $result_grafik->fetch_assoc()) {
        $grafik_labels[] = $hari_indo[$row_g['nama_hari']] ?? $row_g['nama_hari'];
        $grafik_data[] = $row_g['jumlah'];
    }
}

$json_labels = json_encode($grafik_labels);
$json_data = json_encode($grafik_data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - D'Cutss Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans">

<div class="flex min-h-screen">
    <aside class="w-64 bg-gray-900 text-gray-100 flex flex-col shadow-lg sticky top-0 h-screen">
        <div class="px-6 py-6 text-2xl font-bold tracking-wider border-b border-gray-800">
            D'CUTSS <span class="text-blue-500">PRO</span>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 text-sm">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-600 text-white shadow-lg shadow-blue-900/20">
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
            <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-5">
                <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-2xl">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium tracking-wide uppercase">Total Orders</p>
                    <h2 class="text-2xl font-bold text-gray-800"><?= number_format($total_orders, 0, ',', '.') ?></h2>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-5">
                <div class="w-14 h-14 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-2xl">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium tracking-wide uppercase">Total Revenue</p>
                    <h2 class="text-2xl font-bold text-gray-800">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h2>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-5">
                <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-2xl">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium tracking-wide uppercase">Active Kapster</p>
                    <h2 class="text-2xl font-bold text-gray-800"><?= $total_kapster ?></h2>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Transaksi Terbaru</h3>
                    <a href="dataBooking.php" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-400 text-xs uppercase font-semibold">
                            <tr>
                                <th class="px-6 py-4">No</th>
                                <th class="px-6 py-4">Nama Pelanggan</th>
                                <th class="px-6 py-4">Layanan</th>
                                <th class="px-6 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-sm text-gray-600">
                            <?php 
                            $no = 1;
                            while($row = $recent_bookings->fetch_assoc()): 
                                $status = $row['status'] ?? 'Menunggu';
                                $statusClass = (strtolower($status) == 'selesai') ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700';
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900"><?= $no++ ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['nama'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['layanan'] ?? '-') ?></td>
                                <td class="px-6 py-4">
                                    <span class="<?= $statusClass ?> px-3 py-1 rounded-full text-xs font-medium">
                                        <?= strtoupper($status) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-800 mb-4">Grafik Mingguan</h3>
                <div class="relative h-[250px]">
                    <canvas id="myChart"></canvas>
                </div>
                <p class="text-xs text-gray-400 mt-4 italic">* Menampilkan data pesanan 7 hari terakhir</p>
            </div>
        </div>
    </main>
</div>

<script>
    const ctx = document.getElementById('myChart').getContext('2d');
    const labelsPhp = <?php echo $json_labels; ?>;
    const dataPhp = <?php echo $json_data; ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labelsPhp.length > 0 ? labelsPhp : ['No Data'],
            datasets: [{
                label: 'Jumlah Pesanan',
                data: dataPhp.length > 0 ? dataPhp : [0],
                borderColor: '#3b82f6',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                pointBackgroundColor: '#3b82f6',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false }
            },
            scales: { 
                y: { 
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                } 
            }
        }
    });
</script>
</body>
</html>