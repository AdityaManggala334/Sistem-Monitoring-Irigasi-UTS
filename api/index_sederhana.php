<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once 'koneksi.php';
require_once 'auth_helper.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SM Irigasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-6">
        <div class="bg-white rounded-xl shadow p-6">
            <h1 class="text-2xl font-bold mb-4">Selamat Datang, <?= htmlspecialchars($namaDepan) ?>!</h1>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">User ID</p>
                    <p class="text-xl font-bold"><?= $user_id ?></p>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Username</p>
                    <p class="text-xl font-bold"><?= htmlspecialchars($username) ?></p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Role</p>
                    <p class="text-xl font-bold capitalize"><?= htmlspecialchars($role) ?></p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Nama Lengkap</p>
                    <p class="text-xl font-bold"><?= htmlspecialchars($namaLengkap) ?></p>
                </div>
            </div>

            <div class="flex gap-3">
                <a href="peta.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Peta Sensor</a>
                <a href="bps.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Data BPS</a>
                <a href="riwayat.php" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">Riwayat</a>
                <?php if ($role === 'administrator'): ?>
                <a href="dashboard.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>
