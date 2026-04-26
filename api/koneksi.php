<?php
// api/koneksi.php
$host = getenv('DB_HOST') ?: 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com';
$port = getenv('DB_PORT') ?: '4000';
$user = getenv('DB_USER') ?: '44XY7Uq1LbkUX6u.root';
$pass = getenv('DB_PASS') ?: '1ifBBKYkZv9Zh4Bl';
$db   = getenv('DB_NAME') ?: 'db_irigasi';

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_real_connect($conn, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>
