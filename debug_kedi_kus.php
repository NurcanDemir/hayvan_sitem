<?php
include('includes/db.php');

// Check what happens when we select "Kedi" (cat) category
echo "<h2>Checking for 'Kedi' category:</h2>";
$res = $conn->query("SELECT * FROM kategoriler WHERE ad LIKE '%kedi%' OR ad LIKE '%Kedi%'");
if ($res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        echo "Found category: ID=" . $row['id'] . ", Name=" . $row['ad'] . "<br>";
        
        // Now check breeds for this category
        echo "<h3>Breeds for category ID " . $row['id'] . ":</h3>";
        $breed_res = $conn->query("SELECT * FROM cinsler WHERE kategori_id = " . $row['id']);
        if ($breed_res->num_rows > 0) {
            while($breed = $breed_res->fetch_assoc()) {
                echo "- " . $breed['ad'] . " (ID: " . $breed['id'] . ")<br>";
            }
        } else {
            echo "No breeds found for this category<br>";
        }
    }
} else {
    echo "No 'Kedi' category found<br>";
}

// Check what happens when we select "Kuş" (bird) category
echo "<h2>Checking for 'Kuş' category:</h2>";
$res = $conn->query("SELECT * FROM kategoriler WHERE ad LIKE '%kuş%' OR ad LIKE '%Kuş%'");
if ($res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        echo "Found category: ID=" . $row['id'] . ", Name=" . $row['ad'] . "<br>";
        
        // Now check breeds for this category
        echo "<h3>Breeds for category ID " . $row['id'] . ":</h3>";
        $breed_res = $conn->query("SELECT * FROM cinsler WHERE kategori_id = " . $row['id']);
        if ($breed_res->num_rows > 0) {
            while($breed = $breed_res->fetch_assoc()) {
                echo "- " . $breed['ad'] . " (ID: " . $breed['id'] . ")<br>";
            }
        } else {
            echo "No breeds found for this category<br>";
        }
    }
} else {
    echo "No 'Kuş' category found<br>";
}
?>
