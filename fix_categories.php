<?php
include('includes/db.php');

echo "<h2>Database Fix Tool</h2>";

// Get category IDs
$categories = [];
$res = $conn->query("SELECT * FROM kategoriler ORDER BY id");
while($row = $res->fetch_assoc()) {
    $categories[strtolower($row['ad'])] = $row['id'];
}

echo "<h3>Categories found:</h3>";
foreach($categories as $name => $id) {
    echo "$name => $id<br>";
}

// Define the correct mappings (you'll need to adjust these based on your actual data)
$corrections = [
    // Cat breeds should be in cat category
    'kedi' => ['kategori' => 'kedi', 'breeds' => ['persian', 'siamese', 'british shorthair', 'maine coon', 'tekir', 'van kedisi', 'ankara kedisi']],
    // Dog breeds should be in dog category  
    'köpek' => ['kategori' => 'köpek', 'breeds' => ['golden retriever', 'labrador', 'german shepherd', 'bulldog', 'husky', 'kangal', 'akbash']],
    // Bird breeds should be in bird category
    'kuş' => ['kategori' => 'kuş', 'breeds' => ['papağan', 'kanarya', 'muhabbet kuşu', 'güvercin', 'kartal', 'baykuş']]
];

echo "<h3>Checking current breed associations:</h3>";
$res = $conn->query("SELECT c.id, c.ad as breed_name, c.kategori_id, k.ad as category_name 
                     FROM cinsler c 
                     LEFT JOIN kategoriler k ON c.kategori_id = k.id 
                     ORDER BY c.ad");

$fixes_needed = [];
while($row = $res->fetch_assoc()) {
    $breed_lower = strtolower($row['breed_name']);
    $category_lower = strtolower($row['category_name']);
    
    // Check if this breed should be in a different category
    foreach($corrections as $correct_category => $data) {
        foreach($data['breeds'] as $expected_breed) {
            if (strpos($breed_lower, $expected_breed) !== false || strpos($expected_breed, $breed_lower) !== false) {
                if ($category_lower !== $correct_category) {
                    $fixes_needed[] = [
                        'breed_id' => $row['id'],
                        'breed_name' => $row['breed_name'],
                        'current_category' => $row['category_name'],
                        'correct_category' => $correct_category,
                        'correct_category_id' => $categories[$correct_category]
                    ];
                }
                break 2;
            }
        }
    }
}

if (!empty($fixes_needed)) {
    echo "<h3>Fixes needed:</h3>";
    foreach($fixes_needed as $fix) {
        echo "<span style='color: red;'>'{$fix['breed_name']}' is in '{$fix['current_category']}' but should be in '{$fix['correct_category']}'</span><br>";
    }
    
    echo "<h3>Would you like to apply these fixes?</h3>";
    echo "<form method='post'>";
    echo "<input type='submit' name='apply_fixes' value='Apply Fixes' style='background: red; color: white; padding: 10px;'>";
    echo "</form>";
    
    if (isset($_POST['apply_fixes'])) {
        echo "<h3>Applying fixes...</h3>";
        foreach($fixes_needed as $fix) {
            $stmt = $conn->prepare("UPDATE cinsler SET kategori_id = ? WHERE id = ?");
            $stmt->bind_param("ii", $fix['correct_category_id'], $fix['breed_id']);
            if ($stmt->execute()) {
                echo "✓ Fixed: '{$fix['breed_name']}' moved to '{$fix['correct_category']}'<br>";
            } else {
                echo "✗ Error fixing '{$fix['breed_name']}': " . $stmt->error . "<br>";
            }
        }
        echo "<br><strong>Fixes applied! Please refresh the main page to test.</strong>";
    }
} else {
    echo "<span style='color: green;'>All breed associations look correct!</span>";
}
?>
