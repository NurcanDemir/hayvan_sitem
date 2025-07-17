<?php
// test_admin_auth.php - Test admin authentication
session_start();
include('includes/auth.php');

echo "<h2>Admin Authentication Test</h2>";

echo "<h3>Session Status:</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";

echo "<h3>Admin Session Variables:</h3>";
echo "admin_logged_in: " . (isset($_SESSION['admin_logged_in']) ? ($_SESSION['admin_logged_in'] ? 'true' : 'false') : 'not set') . "<br>";
echo "admin_id: " . (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'not set') . "<br>";
echo "admin_kullanici_adi: " . (isset($_SESSION['admin_kullanici_adi']) ? $_SESSION['admin_kullanici_adi'] : 'not set') . "<br>";

echo "<h3>isAdmin() Function Test:</h3>";
if (function_exists('isAdmin')) {
    echo "isAdmin() result: " . (isAdmin() ? 'true' : 'false') . "<br>";
} else {
    echo "isAdmin() function not found!<br>";
}

echo "<h3>All Session Variables:</h3>";
foreach ($_SESSION as $key => $value) {
    echo "$key: " . (is_array($value) ? print_r($value, true) : $value) . "<br>";
}
?>
