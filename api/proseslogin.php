<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'koneksi.php';

// Tolak akses langsung (tanpa form submit)
if (!isset($_POST['login'])) {
    header("Location: login.php");
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header("Location: login.php?error=kosong");
    exit();
}

// Cari user berdasarkan username ATAU email (prepared statement)
$stmt = mysqli_prepare($conn,
    "SELECT id_users, nama_depan, nama_belakang, username, email, password, role
     FROM   users
     WHERE  username = ? OR email = ?
     LIMIT  1"
);
mysqli_stmt_bind_param($stmt, 'ss', $username, $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    die(" USER TIDAK DITEMUKAN: " . htmlspecialchars($username));
}

if (!password_verify($password, $user['password'])) {
    die(" PASSWORD SALAH!<br>
        Input password: " . htmlspecialchars($password) . "<br>
        Hash di database: " . $user['password']);
}

    // Regenerate session ID untuk mencegah session fixation attack
    session_regenerate_id(true);

    // Simpan semua data penting ke session
    $_SESSION['user_id']       = $user['id_users'];
    $_SESSION['username']      = $user['username'];
    $_SESSION['nama_depan']    = $user['nama_depan'];
    $_SESSION['nama_belakang'] = $user['nama_belakang'];
    $_SESSION['role']          = $user['role'];

    // Redirect berdasarkan role
    if ($user['role'] === 'administrator') {
        header("Location: dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();

} else {
    // Gagal login — kembalikan ke halaman login dengan pesan error
    header("Location: login.php?error=salah");
    exit();
}
