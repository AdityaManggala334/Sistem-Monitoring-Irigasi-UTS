<?php
session_start();
$_SESSION['test'] = 'berhasil';
echo "Session test: " . $_SESSION['test'];
?>
