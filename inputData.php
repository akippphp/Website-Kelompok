<?php
session_start();
$conn = new mysqli("localhost", "root", "", "barberking_db");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// 1. AMBIL DATA KAPSTER
$query_kapster = $conn->query("SELECT id, nama_kapster FROM kapster ORDER BY nama_kapster ASC");

// 2. AMBIL DATA LAYANAN
$query_layanan = $conn->query("SELECT id, nama_layanan, harga FROM layanan ORDER BY nama_layanan ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Manual - D'Cutss Admin</title>
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
            <a href="Layanan.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-400 hover:text-white">
                <i class="fas fa-cut w-5"></i> Layanan
            </a>
            <a href="Kapster.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-400 hover:text-white">
                <i class="fas fa-user-friends w-5"></i> Kapster
            </a>
            <a href="jadwal.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-400 hover:text-white">
                <i class="fas fa-clock w-5"></i> Jadwal Kapster
            </a>
            <a href="inputData.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-600 text-white shadow-lg shadow-blue-900/20">
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
        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Booking Manual</h1>
                <p class="text-gray-500 mt-1">Input data pelanggan yang datang langsung (Offline).</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-8">
                <div class="flex items-center gap-3 mb-6 border-b pb-4">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Formulir Booking Baru</h3>
                </div>
                
                <form action="proses-input.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <i class="fas fa-user text-gray-400"></i> Nama Pelanggan *
                            </label>
                            <input type="text" name="nama_pelanggan" required placeholder="Nama Lengkap" 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition bg-gray-50/50">
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <i class="fab fa-whatsapp text-gray-400"></i> Nomor WhatsApp *
                            </label>
                            <input type="text" name="phone" required placeholder="08xxxx" 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition bg-gray-50/50">
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <i class="fas fa-user-tag text-gray-400"></i> Pilih Kapster *
                            </label>
                            <select name="kapster" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-white transition">
                                <option value="" disabled selected>Pilih Kapster</option>
                                <?php while($k = $query_kapster->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($k['nama_kapster']) ?>"><?= htmlspecialchars($k['nama_kapster']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <i class="fas fa-concierge-bell text-gray-400"></i> Pilih Layanan *
                            </label>
                            <select name="layanan" id="layanan_select" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-white transition">
                                <option value="" disabled selected>Pilih Layanan</option>
                                <?php while($l = $query_layanan->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($l['nama_layanan']) ?>" data-harga="<?= $l['harga'] ?>">
                                        <?= htmlspecialchars($l['nama_layanan']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <i class="fas fa-tag text-gray-400"></i> Harga (Rp)
                            </label>
                            <input type="number" id="harga_input" name="harga" readonly 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-100 text-gray-600 outline-none font-bold" placeholder="0">
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <i class="fas fa-calendar-alt text-gray-400"></i> Tanggal *
                            </label>
                            <input type="date" name="tanggal" id="booking-date" required 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition bg-gray-50/50">
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <i class="fas fa-clock text-gray-400"></i> Jam *
                            </label>
                            <input type="time" name="jam" id="booking-time" min="09:00" max="20:00" step="3600" required 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition bg-gray-50/50">
                            <p class="text-xs text-gray-400 mt-1">*09:00 - 20:00 (Jam bulat)</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 pt-6 border-t border-gray-100">
                        <button type="reset" class="px-6 py-3 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition font-medium">
                            Reset
                        </button>
                        <button type="submit" name="simpan_booking" class="px-8 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition shadow-lg font-bold">
                            <i class="fas fa-save mr-2"></i> Simpan Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
    // 1. Logika Update Harga Otomatis
    const layananSelect = document.getElementById('layanan_select');
    const hargaInput = document.getElementById('harga_input');

    layananSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const harga = selectedOption.getAttribute('data-harga');
        hargaInput.value = harga ? harga : '';
    });

    // 2. Validasi Jam (Sama dengan form user)
    const timeInput = document.getElementById('booking-time');
    timeInput.addEventListener('input', function() {
        const timeVal = this.value; 
        const hour = timeVal.split(':')[0];
        const minute = timeVal.split(':')[1];

        if (minute !== "00") {
            alert("Mohon pilih jam bulat (Contoh: 09:00, 10:00).");
            this.value = hour + ":00";
        }

        if (hour === "12") {
            alert("Jam 12:00 adalah waktu istirahat.");
            this.value = "";
        }

        if (parseInt(hour) < 9 || parseInt(hour) > 20) {
            alert("Jam operasional 09:00 sampai 20:00.");
            this.value = "";
        }
    });

    // 3. Set minimal tanggal hari ini
    const dateInput = document.getElementById('booking-date');
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
</script>

</body>
</html>