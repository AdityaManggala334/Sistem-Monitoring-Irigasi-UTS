<?php
/**
 * auth_helper.php
 * Sertakan file ini di setiap halaman yang membutuhkan login.
 * Membaca cookie sm_uid, query database, lalu isi variabel:
 *   $user_id, $username, $nama_depan, $nama_belakang, $role,
 *   $namaDepan, $namaBelakang, $namaLengkap
 */

// Pastikan koneksi sudah ada (require koneksi.php sebelum file ini)
if (!isset($conn)) {
    die("Koneksi database belum diinisialisasi.");
}

$uid = isset($_COOKIE['sm_uid']) ? (int)$_COOKIE['sm_uid'] : 0;

if ($uid <= 0) {
    header("Location: login.php");
    exit();
}

$stmt = mysqli_prepare($conn,
    "SELECT id_users, nama_depan, nama_belakang, username, role
     FROM users WHERE id_users = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$res  = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$user) {
    // Cookie ada tapi user tidak ditemukan — hapus cookie & redirect
    setcookie('sm_uid', '', time() - 3600, '/');
    header("Location: login.php");
    exit();
}

$user_id      = $user['id_users'];
$username     = $user['username'];
$nama_depan   = $user['nama_depan'];
$nama_belakang= $user['nama_belakang'];
$role         = $user['role'];

$namaDepan   = htmlspecialchars($nama_depan   ?: $username);
$namaBelakang= htmlspecialchars($nama_belakang ?? '');
$namaLengkap = htmlspecialchars(trim($nama_depan . ' ' . $nama_belakang) ?: $username);
