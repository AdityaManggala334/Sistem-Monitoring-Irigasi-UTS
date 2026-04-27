<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'koneksi.php';

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

$stmt = mysqli_prepare($conn,
    "SELECT id_users, nama_depan, nama_belakang, username, email, password, role
     FROM users
     WHERE username = ? OR email = ?
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'ss', $username, $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user || !password_verify($password, $user['password'])) {
    header("Location: login.php?error=salah");
    exit();
}

// Simpan user_id ke cookie (berlaku 8 jam)
$expire = time() + (8 * 60 * 60);
setcookie('sm_uid',  (string)$user['id_users'], $expire, '/', '', false, true);

// Redirect ke index.php — tanpa parameter URL
header("Location: index.php");
exit();
