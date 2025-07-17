<?php
// simple_test.php - Simple test to check database and admin functionality
session_start();
include('includes/db.php');

echo "<h2>Simple Database Test</h2>";

// Test 1: Check if sahiplenme_istekleri table exists
echo "<h3>1. Table Structure:</h3>";
$result = $conn->query("SHOW TABLES LIKE 'sahiplenme_istekleri'");
if ($result->num_rows > 0) {
    echo "✓ sahiplenme_istekleri table exists<br>";
    
    // Show column info
    $result = $conn->query("SHOW COLUMNS FROM sahiplenme_istekleri");
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
} else {
    echo "✗ sahiplenme_istekleri table does not exist<br>";
}

// Test 2: Check if there are any records
echo "<h3>2. Records Count:</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM sahiplenme_istekleri");
$row = $result->fetch_assoc();
echo "Total records: " . $row['count'] . "<br>";

// Test 3: Show sample records
echo "<h3>3. Sample Records:</h3>";
$result = $conn->query("SELECT id, durum, talep_tarihi FROM sahiplenme_istekleri LIMIT 5");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", Status: " . $row['durum'] . ", Date: " . $row['talep_tarihi'] . "<br>";
    }
} else {
    echo "No records found<br>";
}

// Test 4: Check admin login status
echo "<h3>4. Admin Session Status:</h3>";
echo "admin_logged_in: " . (isset($_SESSION['admin_logged_in']) ? ($_SESSION['admin_logged_in'] ? 'true' : 'false') : 'not set') . "<br>";
echo "admin_id: " . (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'not set') . "<br>";

// Test 5: Test manual admin login (just for testing)
echo "<h3>5. Manual Admin Login Test:</h3>";
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;
$_SESSION['admin_kullanici_adi'] = 'test_admin';
echo "Admin session manually set for testing<br>";

$conn->close();
?>
