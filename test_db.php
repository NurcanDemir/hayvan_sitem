<?php
// test_db.php - Database structure test
include('includes/db.php');

echo "<h2>Database Structure Test</h2>";

// Check sahiplenme_istekleri table structure
echo "<h3>sahiplenme_istekleri Table Structure:</h3>";
$result = $conn->query("DESCRIBE sahiplenme_istekleri");
if ($result) {
    while($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Null'] . " - " . $row['Key'] . " - " . $row['Default'] . "<br>";
    }
} else {
    echo "Error: " . $conn->error;
}

// Check if there are any test records
echo "<h3>Sample Records:</h3>";
$result = $conn->query("SELECT * FROM sahiplenme_istekleri LIMIT 5");
if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . " - Durum: " . $row['durum'] . " - Talep Tarihi: " . $row['talep_tarihi'] . "<br>";
        }
    } else {
        echo "No records found in sahiplenme_istekleri table.";
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
