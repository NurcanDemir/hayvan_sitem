<?php
include('includes/db.php');

echo "<h2>Database Check and Fix Tool</h2>";

// First, let's see all categories and their IDs
echo "<h3>All Categories:</h3>";
$res = $conn->query("SELECT * FROM kategoriler ORDER BY id");
$categories = [];
while($row = $res->fetch_assoc()) {
    $categories[$row['id']] = $row['ad'];
    echo "ID: " . $row['id'] . " - Name: " . $row['ad'] . "<br>";
}

// Now let's see all breeds and their category associations
echo "<h3>All Breeds with Category Associations:</h3>";
$res = $conn->query("SELECT c.id, c.ad as breed_name, c.kategori_id, k.ad as category_name 
                     FROM cinsler c 
                     LEFT JOIN kategoriler k ON c.kategori_id = k.id 
                     ORDER BY c.kategori_id, c.ad");
while($row = $res->fetch_assoc()) {
    echo "Breed: " . $row['breed_name'] . " - Category ID: " . $row['kategori_id'] . " - Category: " . $row['category_name'] . "<br>";
}

// Check for specific problematic cases
echo "<h3>Potential Issues:</h3>";
$res = $conn->query("SELECT c.id, c.ad as breed_name, c.kategori_id, k.ad as category_name 
                     FROM cinsler c 
                     LEFT JOIN kategoriler k ON c.kategori_id = k.id 
                     WHERE (c.ad LIKE '%kedi%' AND k.ad NOT LIKE '%kedi%') 
                     OR (c.ad LIKE '%köpek%' AND k.ad NOT LIKE '%köpek%')
                     OR (c.ad LIKE '%kuş%' AND k.ad NOT LIKE '%kuş%')");
if ($res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        echo "<span style='color: red;'>MISMATCH: Breed '" . $row['breed_name'] . "' is in category '" . $row['category_name'] . "'</span><br>";
    }
} else {
    echo "No obvious mismatches found.<br>";
}

// Show the JavaScript data structure that would be generated
echo "<h3>JavaScript Data Structure (cinsler):</h3>";
$cinsler = [];
$res = $conn->query("SELECT id, kategori_id, ad FROM cinsler ORDER BY kategori_id, ad ASC");
while($row = $res->fetch_assoc()) {
    $cinsler[$row['kategori_id']][] = $row;
}
echo "<pre>";
print_r($cinsler);
echo "</pre>";
?>
