<?php
session_start();

// Konfigurasi database
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'barberking_db';

$error = '';
$success = '';

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        if ($conn->connect_error) {
            $error = 'Koneksi database gagal: ' . $conn->connect_error;
        } else {
            // Cek apakah email sudah terdaftar
            $stmt_check = $conn->prepare("SELECT email FROM pengguna WHERE email = ?");
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $error = 'Email sudah digunakan!';
            } else {
                // Hash password untuk keamanan
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert ke tabel pengguna
                $stmt = $conn->prepare("INSERT INTO pengguna (nama, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $nama, $email, $hashed_password);

                if ($stmt->execute()) {
                    $success = 'Registrasi berhasil! Silakan login.';
                } else {
                    $error = 'Terjadi kesalahan saat mendaftar.';
                }
                $stmt->close();
            }
            $stmt_check->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Admin D'Cutss</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <div class="bg-indigo-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Daftar Akun</h1>
            <p class="text-gray-500 mt-2">Buat akun admin baru</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm text-center">
                <?php echo $success; ?>
                <br>
                <a href="login.php" class="font-bold underline">Klik di sini untuk Login</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none transition" placeholder="Admin Name" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none transition" placeholder="admin@email.com" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none transition" placeholder="••••••••" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                <input type="password" name="confirm_password" class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none transition" placeholder="••••••••" required>
            </div>

            <button type="submit" name="register" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition duration-200 shadow-lg">
                Daftar Sekarang
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-600">
            Sudah punya akun? <a href="login.php" class="text-indigo-600 font-semibold hover:underline">Masuk</a>
        </div>
    </div>

</body>
</html>