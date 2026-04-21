<?php

$host     = 'localhost';
$user     = 'root';
$password = '';
$database = 'db_irigasi';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {

    error_log("Koneksi DB gagal: " . mysqli_connect_error());
    die("Koneksi database gagal. Silakan coba lagi nanti.");
}

// Set charset UTF-8 untuk menghindari masalah encoding
mysqli_set_charset($conn, 'utf8mb4');
