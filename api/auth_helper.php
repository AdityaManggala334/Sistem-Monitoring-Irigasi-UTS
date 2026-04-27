<?php
// Memulai output buffer untuk mengelola header (seperti redirect) sebelum konten dikirim
ob_start();

// Menampilkan semua error PHP untuk debugging (1 = tampilkan error)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Memuat file koneksi database
require_once 'koneksi.php';

// Memuat file helper autentikasi (untuk fungsi-fungsi pendukung)
require_once 'auth_helper.php';

// Memeriksa apakah variabel koneksi database ($conn) tersedia
if (!isset($conn)) {
    die("Koneksi database belum diinisialisasi.");
}

// Mengambil nilai UID dari cookie 'sm_uid' dan mengkonversinya ke integer
// Jika cookie tidak ada, nilai default 0
$uid = isset($_COOKIE['sm_uid']) ? (int)$_COOKIE['sm_uid'] : 0;

// Jika UID tidak valid (<=0), redirect ke halaman login
if ($uid <= 0) {
    header("Location: login.php");
    exit();
}

// Mempersiapkan query SQL untuk mengambil data user berdasarkan ID
$stmt = mysqli_prepare($conn,
    "SELECT id_users, nama_depan, nama_belakang, username, role
     FROM users WHERE id_users = ? LIMIT 1"
);

// Mengikat parameter ID user ke query (i = integer)
mysqli_stmt_bind_param($stmt, 'i', $uid);

// Mengeksekusi query
mysqli_stmt_execute($stmt);

// Mengambil hasil query sebagai array asosiatif
$res  = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);

// Menutup statement untuk membebaskan resource
mysqli_stmt_close($stmt);

// Jika user tidak ditemukan di database (cookie mungkin sudah tidak valid)
if (!$user) {
    // Hapus cookie 'sm_uid' dengan mengatur waktu kadaluarsa ke masa lalu
    setcookie('sm_uid', '', time() - 3600, '/');
    // Redirect ke halaman login
    header("Location: login.php");
    exit();
}

// Menyimpan data user ke dalam variabel yang lebih mudah digunakan
$user_id      = $user['id_users'];      // ID user
$username     = $user['username'];      // Username user
$nama_depan   = $user['nama_depan'];    // Nama depan user
$nama_belakang= $user['nama_belakang']; // Nama belakang user
$role         = $user['role'];          // Role user (administrator, petugas, petani)

// Memformat nama depan untuk ditampilkan di halaman (escape HTML untuk keamanan)
// Jika nama depan kosong, gunakan username sebagai pengganti
$namaDepan   = htmlspecialchars($nama_depan   ?: $username);

// Memformat nama belakang (escape HTML, jika kosong menjadi string kosong)
$namaBelakang = htmlspecialchars($nama_belakang ?? '');

// Memformat nama lengkap (escape HTML, jika kosong gunakan username)
// Trim untuk menghapus spasi berlebih di awal/akhir
$namaLengkap = htmlspecialchars(trim($nama_depan . ' ' . $nama_belakang) ?: $username);
