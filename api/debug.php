<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
echo "<pre>";
echo "COOKIE: "; var_dump($_COOKIE);
echo "sm_uid: " . ($_COOKIE['sm_uid'] ?? 'TIDAK ADA');
echo "</pre>";
