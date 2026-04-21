<?php

require_once 'koneksi.php';

// Tolak akses langsung (bukan dari form submit)
if (!isset($_POST['register'])) {
    header("Location: login.php");
    exit();
}

// ── Ambil & sanitasi input
$nama_depan    = trim($_POST['nama_depan']    ?? '');
$nama_belakang = trim($_POST['nama_belakang'] ?? '');
$username      = trim($_POST['username']      ?? '');
$email         = trim($_POST['email']         ?? '');
$password      = $_POST['password']           ?? '';
$konfirm       = $_POST['konfirm']            ?? '';

// ── Validasi: semua field wajib diisi
if (
    empty($nama_depan)    ||
    empty($nama_belakang) ||
    empty($username)      ||
    empty($email)         ||
    empty($password)      ||
    empty($konfirm)
) {
    header("Location: login.php?tab=register&reg_error=kosong");
    exit();
}

// ── Validasi: format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: login.php?tab=register&reg_error=email_invalid");
    exit();
}

// ── Validasi: panjang password minimal 6 karakter
if (strlen($password) < 6) {
    header("Location: login.php?tab=register&reg_error=pendek");
    exit();
}

// ── Validasi: konfirmasi password harus cocok
if ($password !== $konfirm) {
    header("Location: login.php?tab=register&reg_error=beda");
    exit();
}

// ── Cek duplikat username atau email (Prepared Statement)
$stmt = mysqli_prepare($conn,
    "SELECT id_users FROM users WHERE username = ? OR email = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    header("Location: login.php?tab=register&reg_error=duplikat");
    exit();
}
mysqli_stmt_close($stmt);

// ── Hash password & simpan ke database
// Role dikunci 'petani' — tidak bisa diubah dari form
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt2 = mysqli_prepare($conn,
    "INSERT INTO users (nama_depan, nama_belakang, username, email, password, role)
     VALUES (?, ?, ?, ?, ?, 'petani')"
);
mysqli_stmt_bind_param($stmt2, 'sssss',
    $nama_depan,
    $nama_belakang,
    $username,
    $email,
    $hash
);

if (mysqli_stmt_execute($stmt2)) {
    mysqli_stmt_close($stmt2);
    // Redirect ke halaman login dengan pesan sukses
    header("Location: login.php?sukses=register");
    exit();
} else {
    mysqli_stmt_close($stmt2);
    header("Location: login.php?tab=register&reg_error=gagal");
    exit();
}
