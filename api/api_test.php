<?php
echo "<h2>Debug Query String</h2>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "QUERY_STRING: " . $_SERVER['QUERY_STRING'] . "<br>";
echo "<pre>";
print_r($_GET);
echo "</pre>";
?>
