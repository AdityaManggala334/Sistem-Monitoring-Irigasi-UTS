<?php
// File: api/test_info.php
// Tujuan: Memeriksa apakah runtime PHP Vercel berfungsi.

echo "=== PHP Runtime Test ===";
echo "<br>Jika Anda melihat teks ini, PHP berjalan dengan baik.";
echo "<br>Versi PHP: " . phpversion();
echo "<br>Parameter GET yang diterima: <pre>";
print_r($_GET);
echo "</pre>";
?>
