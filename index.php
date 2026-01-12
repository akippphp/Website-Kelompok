<?php
// Mulai sesi jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. KONEKSI DATABASE
$conn = new mysqli("localhost", "root", "", "barberking_db");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// 2. AMBIL DATA DARI DATABASE
$query_kapster = $conn->query("SELECT * FROM kapster ORDER BY id DESC");
$kapsters = [];
$spesialisasi_unik = []; // Array bantu untuk mengecek duplikasi jika diperlukan

// 2. AMBIL DATA DARI DATABASE
$query_kapster = $conn->query("SELECT * FROM kapster ORDER BY id DESC");
$kapsters = [];
if ($query_kapster && $query_kapster->num_rows > 0) {
    while ($row = $query_kapster->fetch_assoc()) {
        // Ambil data spesialisasi
        $spesialis = $row['spesialisasi'];

        $kapsters[] = [
            'id' => $row['id'],
            'nama' => $row['nama_kapster'],
            'spesialis' => $spesialis,
            'foto' => $row['foto'],
            // AGAR TIDAK DOUBLE: Cukup gunakan isi kolom spesialisasi saja tanpa tambahan teks manual
            'desc' => $spesialis 
        ];
    }
}

// Data Layanan
$query_layanan = $conn->query("SELECT * FROM layanan ORDER BY nama_layanan ASC");
$services = [];
if ($query_layanan && $query_layanan->num_rows > 0) {
    while ($row = $query_layanan->fetch_assoc()) {
        $services[] = [
            'nama' => $row['nama_layanan'],
            'harga' => 'Rp ' . number_format($row['harga'], 0, ',', '.'),
            'deskripsi' => $row['deskripsi'],
            'foto' => $row['foto'] // Tambahkan baris ini
        ];
    }
}

// Logika Status Login & Data Pengguna
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$user_id = $is_logged_in ? ($_SESSION['user_id'] ?? null) : null;
$user_name = $is_logged_in ? ($_SESSION['user_name'] ?? 'Pengguna') : 'Tamu';
$user_email = $is_logged_in ? ($_SESSION['user_email'] ?? '') : '';
$user_phone = $is_logged_in ? ($_SESSION['user_phone'] ?? '') : ''; // Asumsi nomor HP disimpan di sesi

// Logika Notifikasi
$notification = null;
$notification_class = 'bg-green-600 border-green-700 text-white'; 
$status = ''; // <--- TAMBAHKAN BARIS INI (Nilai default kosong)

if (isset($_GET['status'])) {
    $status = $_GET['status']; // Variabel diisi jika ada di URL
    $error = $_GET['error'] ?? '';

    switch ($status) {
        case 'registration_success':
            $notification = 'Pendaftaran berhasil! Silakan login.';
            break;
        case 'login_success':
            $notification = 'Login berhasil! Selamat datang, ' . htmlspecialchars($user_name) . '.'; 
            break; 
        case 'registration_failed':
            $notification_class = 'bg-red-600 border-red-700 text-white';
            if ($error === 'email_exists') {
                $notification = 'Pendaftaran gagal! Email sudah terdaftar.';
            } else {
                $notification = 'Pendaftaran gagal! Terjadi kesalahan.';
            }
            break;
        case 'login_failed':
            $notification_class = 'bg-red-600 border-red-700 text-white';
            $notification = 'Login gagal! Email atau password salah.';
            break;
        case 'logout_success':
            $notification = 'Anda berhasil keluar. Sampai jumpa lagi!';
            break;
        case 'not_logged_in':
            $notification_class = 'bg-yellow-600 border-yellow-700 text-black';
            $notification = 'Anda harus login untuk melakukan pemesanan.';
            break;
        case 'booking_success':
            $notification = 'Booking berhasil! Kami tunggu kedatangan Anda.';
            break;
        case 'booking_failed':
    $notification_class = 'bg-red-600 border-red-700 text-white';
    if ($_GET['error'] === 'jam_penuh') {
        $notification = 'Maaf, Kapster tersebut sudah memiliki jadwal. Silakan pilih jam lain.';
    } else {
        $notification = 'Booking gagal! Pastikan semua data terisi dengan benar.';
    }
    break;
    }
}

// Logika untuk menampilkan modal secara otomatis jika ada error login/daftar
$open_modal_on_load = ($notification && (strpos($status, 'login_failed') !== false || strpos($status, 'registration_failed') !== false || $status === 'registration_success'));
$default_form = ($status === 'registration_success' || (isset($_GET['form']) && $_GET['form'] === 'register')) ? 'register' : 'login';

// Mendapatkan nama file saat ini untuk redirect yang tepat
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>D'Cutss Barbershop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
    /* CSS sebelumnya tetap sama */
    .min-h-screen-extra {
        min-height: 200vh;
    }
    .modal {
        transition: opacity 0.3s ease-in-out;
    }
    .modal-active {
        opacity: 1;
        pointer-events: auto;
    }
    /* Tambahan untuk transisi konten modal */
    .modal-content {
        transition: transform 0.3s ease-in-out;
        transform: scale(0.95);
    }
    .modal-active .modal-content {
        transform: scale(1);
    }

    /* CSS Tambahan untuk Mengelola Tampilan Form */
    .form-hidden {
        display: none;
    }
    /* Notifikasi */
    .alert-notification {
        top: 6rem;
        z-index: 60; /* Di atas navbar dan modal */
    }
    </style>
</head>

<body class="bg-black text-white min-h-screen-extra">

    <?php if ($notification): ?>
        <div class="fixed alert-notification left-0 right-0 max-w-xl mx-auto px-4 transition-opacity duration-500" id="globalAlert">
            <div class="p-4 border-l-4 rounded-lg shadow-md <?php echo $notification_class; ?>" role="alert">
                <p class="font-bold">Informasi</p>
                <p><?php echo $notification; ?></p>
            </div>
        </div>
    <?php endif; ?>

    <nav class="w-full bg-black py-6 px-10 flex justify-between items-center sticky top-0 z-50 shadow-lg">
        <h1 class="text-3xl font-bold italic text-yellow-500">D'Cutss</h1>

        <ul class="flex gap-10 text-lg font-semibold">
            <li><a href="#" class="hover:text-yellow-400">HOME</a></li>
            <li><a href="#service" class="hover:text-yellow-400">SERVICE</a></li>
            <li><a href="#kapster" class="hover:text-yellow-400">KAPSTER</a></li>
            <li><a href="#booking" class="hover:text-yellow-400">BOOKING</a></li>
        </ul>

        <?php if ($is_logged_in): ?>
            <div class="flex items-center gap-4">
                <span class="text-white font-medium">Hai, <?php echo htmlspecialchars($user_name); ?></span>
                <a 
                    href="logout.php" 
                    class="bg-red-600 px-6 py-2 rounded-full font-semibold text-white hover:bg-red-500 transition duration-150"
                >
                    Logout
                </a>
            </div>
        <?php else: ?>
            <button
                id="openLoginModal"
                class="bg-yellow-500 px-6 py-2 rounded-full font-semibold text-black hover:bg-yellow-400 transition duration-150"
            >
                Login
            </button>
        <?php endif; ?>
    </nav>

    <div 
    id="loginModal" 
    class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50 modal opacity-0 pointer-events-none"
>
    <div id="modalContent" class="bg-gray-900 w-full max-w-md mx-auto rounded-xl shadow-2xl p-8 transform transition-all duration-300 scale-95 modal-content">
        
        <div class="flex justify-between items-center border-b border-gray-700 pb-4 mb-6">
            <h3 id="modalTitle" class="text-3xl font-bold text-yellow-500 italic">
                <?php echo ($default_form === 'register' ? "Daftar Akun D'Cutss" : "Login D'Cutss"); ?>
            </h3>
            <button id="closeLoginModal" class="text-gray-400 hover:text-white text-3xl leading-none">
                &times;
            </button>
        </div>

        <form 
            id="loginForm" 
            data-form="login" 
            action="login.php" 
            method="POST" 
            class="<?php echo ($default_form === 'register' ? 'form-hidden' : ''); ?>"
        >
            <input type="hidden" name="redirect" value="<?php echo $current_page; ?>">
            <div class="mb-5">
                <label for="login-email" class="block text-sm font-medium mb-2">Email</label>
                <input
                    type="email"
                    id="login-email"
                    name="email"
                    class="w-full p-3 rounded-lg bg-gray-800 border border-gray-700 focus:border-yellow-500 outline-none text-white"
                    placeholder="emailanda@contoh.com"
                    required
                />
            </div>

            <div class="mb-8">
                <label for="login-password" class="block text-sm font-medium mb-2">Password</label>
                <input
                    type="password"
                    id="login-password"
                    name="password"
                    class="w-full p-3 rounded-lg bg-gray-800 border border-gray-700 focus:border-yellow-500 outline-none text-white"
                    placeholder="Masukkan password Anda"
                    required
                />
            </div>

            <button
                type="submit"
                class="w-full bg-yellow-500 text-black font-bold text-lg px-6 py-3 rounded-full hover:bg-yellow-400 transition duration-150"
            >
                Masuk
            </button>

            <p class="text-center text-sm text-gray-400 mt-5">
                Belum punya akun? 
                <a href="#" id="switchToRegister" class="text-yellow-500 hover:text-yellow-400 font-semibold">Daftar Sekarang</a>
            </p>
        </form>
        
        <form 
            id="registerForm" 
            data-form="register" 
            action="register.php" 
            method="POST" 
            class="<?php echo ($default_form === 'login' ? 'form-hidden' : ''); ?>"
        >
            <input type="hidden" name="redirect" value="<?php echo $current_page; ?>">
            <div class="mb-5">
                <label for="register-name" class="block text-sm font-medium mb-2">Nama Lengkap</label>
                <input
                    type="text"
                    id="register-name"
                    name="name"
                    class="w-full p-3 rounded-lg bg-gray-800 border border-gray-700 focus:border-yellow-500 outline-none text-white"
                    placeholder="Nama Lengkap Anda"
                    required
                />
            </div>
            <div class="mb-5">
                <label for="register-email" class="block text-sm font-medium mb-2">Email</label>
                <input
                    type="email"
                    id="register-email"
                    name="email"
                    class="w-full p-3 rounded-lg bg-gray-800 border border-gray-700 focus:border-yellow-500 outline-none text-white"
                    placeholder="emailanda@contoh.com"
                    required
                />
            </div>

            <div class="mb-8">
                <label for="register-password" class="block text-sm font-medium mb-2">Password</label>
                <input
                    type="password"
                    id="register-password"
                    name="password"
                    class="w-full p-3 rounded-lg bg-gray-800 border border-gray-700 focus:border-yellow-500 outline-none text-white"
                    placeholder="Buat password (min 6 karakter)"
                    required
                />
            </div>

            <button
                type="submit"
                class="w-full bg-yellow-500 text-black font-bold text-lg px-6 py-3 rounded-full hover:bg-yellow-400 transition duration-150"
            >
                Daftar
            </button>

            <p class="text-center text-sm text-gray-400 mt-5">
                Sudah punya akun? 
                <a href="#" id="switchToLogin" class="text-yellow-500 hover:text-yellow-400 font-semibold">Masuk Sekarang</a>
            </p>
        </form>

    </div>
</div>

    <section class="w-full flex flex-col lg:flex-row items-center px-10 py-10">

        <div class="lg:w-1/2 w-full pr-8">
            <h2 class="text-5xl font-bold italic mb-6">Barbershop Service</h2>

            <p class="text-gray-300 mb-6 leading-relaxed">
                Menawarkan potongan rambut ahli, cukur tradisional, trim janggut, dan styling modern.
                Para tukang cukur kami yang berpengalaman menyediakan perawatan diri yang dipersonalisasi
                dalam suasana klasik yang santai dan nyaman.
            </p>

            <a
                href="#booking"
                class="mt-4 inline-block bg-yellow-500 text-black font-bold text-lg px-8 py-3 rounded-full hover:bg-yellow-400"
            >
                Booking Now
            </a>
        </div>

        <div class="lg:w-1/2 w-full mt-10 lg:mt-0">
            <img
                src="images/WhatsApp Image 2025-12-11 at 14.49.14_d7eef825.jpg"
                alt="Barbershop interior"
                class="rounded-lg shadow-lg w-full h-[450px] object-cover"
            />
        </div>
    </section>

    <section id="service" class="px-10 py-20 bg-black text-white">
    <h2 class="text-4xl font-bold text-center mb-10 italic">Paket Layanan</h2>

    <div class="grid md:grid-cols-3 gap-10">
        <?php if (!empty($services)): ?>
            <?php foreach ($services as $service): ?>
            <div class="bg-gray-900 rounded-xl overflow-hidden shadow-lg border border-gray-700 hover:border-yellow-500 transition group">
                <div class="bg-gray-800 flex justify-center p-0 overflow-hidden h-52"> 
                    <?php 
                        // Cek apakah ada file foto di database dan foldernya
                        $foto_path = "uploads/" . $service['foto'];
                        if (!empty($service['foto']) && file_exists($foto_path)) {
                            $display_img = $foto_path;
                        } else {
                            $display_img = "images/default-service.jpg"; // Gambar cadangan
                        }
                    ?>
                    <img src="<?= $display_img ?>" 
                         alt="<?= htmlspecialchars($service['nama']); ?>"
                         class="w-full h-full object-cover group-hover:scale-110 transition duration-500" />
                </div>
                <div class="p-7">
                    <h3 class="text-2xl font-bold mb-2 text-yellow-500"><?= htmlspecialchars($service['nama']); ?></h3>
                    <p class="text-gray-300 mb-5 text-sm leading-relaxed">
                        <?= htmlspecialchars($service['deskripsi']); ?>
                    </p>
                    <div class="flex justify-between items-center border-t border-gray-800 pt-4">
                        <span class="text-white text-lg font-bold"><?= $service['harga']; ?></span>
                        <a href="#booking" class="text-yellow-500 text-sm font-bold hover:underline">Pilih Paket</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="col-span-3 text-center text-gray-500">Layanan belum tersedia.</p>
        <?php endif; ?>
    </div>
</section>

    <section id="kapster" class="px-10 py-20 bg-black text-white">
    <h2 class="text-4xl font-bold text-center mb-14 italic">Kapster Profesional</h2>

    <div class="grid md:grid-cols-3 gap-12">
        <?php foreach ($kapsters as $kapster): ?>
        <div class="bg-gray-900 p-8 rounded-xl text-center border border-gray-700 hover:border-yellow-500 transition shadow-lg group">
            
            <?php 
                $foto_path = "uploads/" . $kapster['foto'];
                if (!empty($kapster['foto']) && file_exists($foto_path)) {
                    $img_src = $foto_path;
                } else {
                    $img_src = "images/default-kapster.jpg"; // Sediakan gambar default jika tidak ada foto
                }
            ?>
            
            <img src="<?php echo $img_src; ?>"
                 alt="<?php echo htmlspecialchars($kapster['nama']); ?>"
                 class="w-40 h-40 mx-auto rounded-full object-cover mb-5 shadow-lg border-4 border-yellow-500 group-hover:scale-105 transition duration-300" />
            
            <h3 class="text-2xl font-bold mb-2 text-yellow-500"><?php echo htmlspecialchars($kapster['nama']); ?></h3>
            
            <p class="text-gray-400 text-sm leading-relaxed">
                <?php echo htmlspecialchars($kapster['desc']); ?>
            </p>
        </div>
        <?php endforeach; ?>
    </div>
</section>
    
   <section id="booking" class="px-10 py-20 bg-black text-white">
        <h2 class="text-4xl font-bold text-center mb-12 italic">Booking Sekarang</h2>

        <div class="max-w-3xl mx-auto bg-gray-900 p-10 rounded-2xl shadow-xl border border-gray-700">

            <form class="grid grid-cols-1 md:grid-cols-2 gap-7" action="proses_booking.php" method="POST">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id ?? ''); ?>">

                <div class="flex flex-col">
                    <label class="font-semibold mb-2" for="booking-name">Nama Lengkap</label>
                    <input
                        type="text"
                        id="booking-name"
                        name="nama"
                        class="p-3 rounded-lg bg-gray-800 border border-gray-700 focus:border-yellow-500 outline-none <?php echo $is_logged_in ? 'text-gray-400' : 'text-white'; ?>"
                        placeholder="Masukkan nama"
                        value="<?php echo $is_logged_in ? htmlspecialchars($user_name) : ''; ?>"
                        <?php echo $is_logged_in ? 'readonly' : 'required'; ?>
                    />
                </div>

                <div class="flex flex-col">
                    <label class="font-semibold mb-2" for="booking-phone">Nomor WhatsApp</label>
                    <input
                        type="text"
                        id="booking-phone"
                        name="phone"
                        class="p-3 rounded-lg bg-gray-800 border border-gray-700 focus:border-yellow-500 outline-none text-white"
                        placeholder="08xxxx"
                        value="<?php echo htmlspecialchars($user_phone); ?>"
                        required
                    />
                </div>

                <div class="flex flex-col">
                    <label class="font-semibold mb-2" for="booking-kapster">Pilih Kapster</label>
                    <select
                        id="booking-kapster"
                        name="kapster"
                        class="p-3 rounded-lg bg-gray-800 border border-gray-700 focus:border-yellow-500 outline-none text-white"
                        required
                    >
                        <option value="" disabled selected>Pilih Kapster</option>
                        <?php foreach ($kapsters as $kapster): ?>
                            <option value="<?php echo htmlspecialchars($kapster['nama']); ?>">
                                <?php echo htmlspecialchars($kapster['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="font-semibold mb-2" for="booking-service">Pilih Layanan</label>
                    <select
                        id="booking-service"
                        name="layanan"
                        class="p-3 rounded-lg bg-gray-800 border border-gray-700 focus:border-yellow-500 outline-none text-white"
                        required
                    >
                        <option value="" disabled selected>Pilih Layanan</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?php echo htmlspecialchars($service['nama']); ?>">
                                <?php echo htmlspecialchars($service['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="font-semibold mb-2" for="booking-date">Tanggal</label>
                    <input
                        type="date"
                        id="booking-date"
                        name="tanggal"
                        class="p-3 rounded-lg bg-gray-800 border border-gray-700 focus:border-yellow-500 outline-none text-white"
                        required
                    />
                </div>

                <div class="flex flex-col">
                    <label class="font-semibold mb-2" for="booking-time">Jam</label>
                    <input
                        type="time"
                        id="booking-time"
                        name="jam"
                        min="09:00"
                        max="20:00"
                        step="3600"
                        class="p-3 rounded-lg bg-gray-800 border border-gray-700 focus:border-yellow-500 outline-none text-white"
                        required
                    />
                    <p class="text-xs text-gray-400 mt-1">*Jam operasional 09:00 - 20:00</p>
                </div>

                <div class="md:col-span-2 flex flex-col sm:flex-row justify-center items-center gap-4 mt-8">
                    <?php if ($is_logged_in): ?>
                        <button
                            type="submit"
                            class="w-full sm:w-auto bg-yellow-500 text-black font-bold text-lg px-10 py-3 rounded-full hover:bg-yellow-400 transition shadow-lg flex items-center justify-center gap-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                            </svg>
                            Booking Sekarang
                        </button>
                        
                        <a
                            href="jadwalBooking.php"
                            class="w-full sm:w-auto bg-transparent border-2 border-yellow-500 text-yellow-500 font-bold text-lg px-10 py-3 rounded-full hover:bg-yellow-500 hover:text-black transition flex items-center justify-center gap-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                            </svg>
                            Lihat Jadwal
                        </a>
                    <?php else: ?>
                        <button
                            type="button"
                            id="openLoginModalFromBooking"
                            class="w-full sm:w-auto bg-red-500 text-white font-bold text-lg px-10 py-3 rounded-full hover:bg-red-600 transition"
                        >
                            Login untuk Memesan
                        </button>
                    <?php endif; ?>
                </div>

            </form>
        </div>
    </section>

    <script>
        // Validasi Jam Booking
const timeInput = document.getElementById('booking-time');

timeInput.addEventListener('input', function() {
    const timeVal = this.value; // format: "HH:mm"
    const hour = timeVal.split(':')[0];
    const minute = timeVal.split(':')[1];

    // 1. Validasi Menit harus 00 (Tidak boleh ada lewat menit)
    if (minute !== "00") {
        alert("Mohon pilih jam bulat (Contoh: 09:00, 10:00).");
        this.value = hour + ":00";
    }

    // 2. Validasi Jam Istirahat (12:00)
    if (hour === "12") {
        alert("Jam 12:00 adalah waktu istirahat. Silakan pilih jam lain.");
        this.value = "";
    }

    // 3. Validasi Batas Jam (09:00 - 20:00)
    if (parseInt(hour) < 9 || parseInt(hour) > 20) {
        alert("Jam operasional kami adalah pukul 09:00 sampai 20:00.");
        this.value = "";
    }
});
    document.addEventListener('DOMContentLoaded', () => {
        const openButton = document.getElementById('openLoginModal');
        const closeButton = document.getElementById('closeLoginModal');
        const modal = document.getElementById('loginModal');
        const openBookingModalButton = document.getElementById('openLoginModalFromBooking');
        
        // Elemen-elemen baru untuk mengelola form
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const modalTitle = document.getElementById('modalTitle');
        const switchToRegisterButton = document.getElementById('switchToRegister');
        const switchToLoginButton = document.getElementById('switchToLogin');

        // Fungsi untuk menampilkan modal
        const showModal = (e) => {
            if(e) e.preventDefault();
            modal.classList.add('modal-active');
            modal.classList.remove('opacity-0', 'pointer-events-none');
        }

        // Fungsi untuk menyembunyikan modal
        const hideModal = () => {
            modal.classList.remove('modal-active');
            modal.classList.add('opacity-0', 'pointer-events-none');
            // Reset form ke login saat ditutup (opsional)
            showLoginForm();
        };

        // Fungsi untuk menampilkan form Login
        const showLoginForm = (e) => {
            if(e) e.preventDefault(); // Mencegah link pindah halaman
            loginForm.classList.remove('form-hidden');
            registerForm.classList.add('form-hidden');
            modalTitle.textContent = "Login D'Cutss";
            // Kosongkan formulir register saat beralih ke login (opsional)
            registerForm.reset(); 
        }

        // Fungsi untuk menampilkan form Register
        const showRegisterForm = (e) => {
            if(e) e.preventDefault();
            loginForm.classList.add('form-hidden');
            registerForm.classList.remove('form-hidden');
            modalTitle.textContent = "Daftar Akun D'Cutss";
            // Kosongkan formulir login saat beralih ke register (opsional)
            loginForm.reset(); 
        }

        // Event Listeners
        if (openButton) openButton.addEventListener('click', showModal);
        if (openBookingModalButton) openBookingModalButton.addEventListener('click', (e) => {
            showModal(e);
            showLoginForm(); // Pastikan defaultnya Login saat dipicu dari tombol Booking
        });
        if (closeButton) closeButton.addEventListener('click', hideModal);
        if (switchToRegisterButton) switchToRegisterButton.addEventListener('click', showRegisterForm);
        if (switchToLoginButton) switchToLoginButton.addEventListener('click', showLoginForm);

        // Sembunyikan modal ketika mengklik di luar konten modal (background overlay)
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                hideModal();
            }
        });

        // Logika untuk menampilkan modal secara otomatis jika ada notifikasi error/sukses register
        const openModalOnLoad = <?php echo $open_modal_on_load ? 'true' : 'false'; ?>;
        if (openModalOnLoad) {
            showModal();
            // Form default sudah diatur di PHP, jadi tidak perlu panggil showLoginForm/showRegisterForm
        }

        

        // Sembunyikan notifikasi setelah beberapa detik
        const alertBox = document.getElementById('globalAlert');
        if (alertBox) {
            setTimeout(() => {
                alertBox.classList.add('opacity-0');
                setTimeout(() => {
                    alertBox.remove(); // Hapus dari DOM setelah transisi
                }, 500);
            }, 5000); // Tampil selama 5 detik
        }
    });
</script>
</body>
</html>