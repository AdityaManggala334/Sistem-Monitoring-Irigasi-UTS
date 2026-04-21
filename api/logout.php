<?php

session_start();
session_unset();    // Hapus semua variabel session
session_destroy();
header("Location: login.php");
exit();
