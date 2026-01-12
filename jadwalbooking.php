<?php
session_start();
$conn = new mysqli("localhost", "root", "", "barberking_db");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// PERBAIKAN: Menggunakan 'user_name' sesuai dengan file login.php kamu
$nama_login = $_SESSION['user_name'] ?? ''; 

// --- LOGIKA PEMBATALAN ---
if (isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    
    // Keamanan: Pastikan yang menghapus adalah pemilik jadwal
    // Menggunakan prepare statement untuk keamanan
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND nama = ?");
    $stmt->bind_param("is", $booking_id, $nama_login);
    
    if ($stmt->execute()) {
        echo "<script>alert('Jadwal berhasil dibatalkan'); window.location.href='jadwalBooking.php';</script>";
    }
    $stmt->close();
}

// Ambil tanggal dari filter
$tanggal_pilihan = $_GET['tanggal'] ?? date('Y-m-d');

// AMBIL JADWAL BOOKING
// Menggunakan real_escape_string untuk menghindari SQL Injection pada filter tanggal
$tgl_safe = $conn->real_escape_string($tanggal_pilihan);
$sql_jadwal = "SELECT * FROM bookings WHERE tanggal = '$tgl_safe' ORDER BY jam ASC";
$result_jadwal = $conn->query($sql_jadwal);

$jadwal_terisi = [];
if ($result_jadwal && $result_jadwal->num_rows > 0) {
    while($row = $result_jadwal->fetch_assoc()) {
        $jam_key = substr($row['jam'], 0, 5); 
        $jadwal_terisi[$jam_key][] = $row;
    }
}

$jam_operasional = ["09:00", "10:00", "11:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00", "20:00"];
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
        <div class="px-6 py-6 text-2xl font-bold tracking-wider border-b border-gray-800 text-blue-400">
            D'CUTSS
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 text-sm">
            <a href="jadwalBooking.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-600 text-white shadow-lg">
                <i class="fas fa-calendar-alt w-5"></i> Jadwal Kapster
            </a>
            <a href="jadwalsaya.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
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
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Jadwal Harian</h1>
                <p class="text-gray-500">Menampilkan jadwal pada <span class="font-bold text-blue-600"><?= date('d M Y', strtotime($tanggal_pilihan)) ?></span></p>
            </div>
            
            <form action="" method="GET" class="flex items-center gap-2 bg-white p-2 rounded-xl shadow-sm border">
                <i class="fas fa-calendar-day text-gray-400 ml-2"></i>
                <input type="date" name="tanggal" value="<?= $tanggal_pilihan ?>" 
                       class="outline-none text-sm p-2 text-gray-700" onchange="this.form.submit()">
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
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
                            <p class="text-[10px] text-gray-400 font-medium tracking-widest">WIB</p>
                        </td>
                        <td class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <?php 
                                if(isset($jadwal_terisi[$jam])): 
                                    foreach($jadwal_terisi[$jam] as $book):
                                        $status = strtolower($book['status']);
                                        // Cek apakah ini booking milik user yang sedang login
                                        $is_mine = (!empty($nama_login) && strtolower($nama_login) === strtolower($book['nama']));
                                        
                                        $cardStyle = $is_mine ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-100' : ($status === 'selesai' ? 'bg-green-50 border-green-100' : 'bg-white border-gray-100');
                                        $textColor = $is_mine ? 'text-blue-700' : ($status === 'selesai' ? 'text-green-600' : 'text-gray-600');
                                ?>
                                    <div class="border <?= $cardStyle ?> p-4 rounded-xl flex flex-col gap-2 shadow-sm relative group transition-all">
                                        <div class="flex justify-between items-start">
                                            <span class="text-[10px] font-bold <?= $textColor ?> uppercase tracking-tighter">
                                                <i class="fas fa-user-tie mr-1"></i> <?= htmlspecialchars($book['kapster']) ?>
                                            </span>
                                            <span class="text-[9px] px-2 py-0.5 rounded-full font-bold <?= $is_mine ? 'bg-blue-600 text-white' : ($status === 'selesai' ? 'bg-green-200 text-green-800' : 'bg-gray-100 text-gray-600') ?>">
                                                <?= $is_mine ? 'JADWAL SAYA' : strtoupper($status) ?>
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($book['nama']) ?></p>
                                            <p class="text-xs text-gray-500 italic"><?= htmlspecialchars($book['layanan']) ?></p>
                                        </div>

                                        <?php if($is_mine && $status !== 'selesai'): ?>
                                        <form method="POST" class="mt-2" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan jadwal ini?')">
                                            <input type="hidden" name="booking_id" value="<?= $book['id'] ?>">
                                            <button type="submit" name="cancel_booking" class="w-full py-1.5 text-[10px] bg-white border border-red-200 text-red-500 font-bold rounded-lg hover:bg-red-500 hover:text-white transition-colors shadow-sm">
                                                BATALKAN PESANAN
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                <?php 
                                    endforeach;
                                else: ?>
                                    <div class="border border-dashed border-gray-200 p-4 rounded-xl flex items-center justify-center bg-gray-50/30">
                                        <span class="text-xs text-gray-400 italic font-medium">Tersedia</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>