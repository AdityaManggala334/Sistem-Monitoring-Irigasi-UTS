<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'koneksi.php';
require_once 'auth_helper.php';
?>
<!DOCTYPE html>
<html>
<head><title>Test Minimal</title></head>
<body>
<h1>Test</h1>
<p>user_id: <?= $user_id ?? 'tidak ada' ?></p>
<p>username: <?= $username ?? 'tidak ada' ?></p>
<p>role: <?= $role ?? 'tidak ada' ?></p>
<p>Jika data di atas muncul, auth_helper.php BERHASIL!</p>
<a href="logout.php">Logout</a>
</body>
</html>
