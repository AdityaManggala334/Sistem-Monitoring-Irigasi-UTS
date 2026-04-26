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

if (!$user) {
    die("USER TIDAK DITEMUKAN: " . htmlspecialchars($username));
}

if (!password_verify($password, $user['password'])) {
    die("PASSWORD SALAH!<br>Input: " . htmlspecialchars($password) . "<br>Hash DB: " . $user['password']);
}

// Redirect dengan data user di URL (karena session tidak jalan)
$redirect = "/api/index.php?user_id=" . $user['id_users'] . 
            "&username=" . urlencode($user['username']) . 
            "&nama_depan=" . urlencode($user['nama_depan']) . 
            "&nama_belakang=" . urlencode($user['nama_belakang']) . 
            "&role=" . $user['role'];

header("Location: " . $redirect);
exit();
?>
