<?php

$host = getenv('DB_HOST') ?: 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com';
$port = getenv('DB_PORT') ?: '4000';
$user = getenv('DB_USER') ?: '44XY7Uq1LbkUX6u.root';
$pass = getenv('DB_PASS') ?: '1ifBBKYkZv9Zh4Bl';
$db   = getenv('DB_NAME') ?: 'db_irigasi';

// Inisialisasi koneksi
$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init gagal");
}

// Set SSL/TLS (WAJIB untuk TiDB Cloud)
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

// Koneksi dengan flag SSL
if (!mysqli_real_connect($conn, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
// Set charset
mysqli_set_charset($conn, 'utf8mb4');

// (Opsional) Verifikasi SSL aktif
$result = mysqli_query($conn, "SHOW STATUS LIKE 'Ssl_cipher'");
if ($result && $row = mysqli_fetch_assoc($result)) {
// SSL aktif, tidak perlu output apa pun
    error_log("SSL Connection OK - Cipher: " . $row['Value']);
}
?>
