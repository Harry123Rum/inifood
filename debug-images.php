<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get all recipes with images
$query = "SELECT id, title, image_url FROM recipes ORDER BY id DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Debug Image Paths</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Title</th><th>Image URL (DB)</th><th>File Exists?</th><th>Full Path</th></tr>";

foreach ($recipes as $recipe) {
    $imageUrl = $recipe['image_url'];
    $fullPath = $imageUrl ? __DIR__ . '/' . $imageUrl : '';
    $fileExists = $imageUrl && file_exists($fullPath) ? 'YES' : 'NO';
    
    echo "<tr>";
    echo "<td>" . $recipe['id'] . "</td>";
    echo "<td>" . htmlspecialchars($recipe['title']) . "</td>";
    echo "<td>" . htmlspecialchars($imageUrl ?: 'NULL') . "</td>";
    echo "<td style='color: " . ($fileExists === 'YES' ? 'green' : 'red') . "'>" . $fileExists . "</td>";
    echo "<td>" . htmlspecialchars($fullPath) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Check if images folder exists
echo "<h3>Folder Check:</h3>";
echo "<p>Images folder exists: " . (is_dir(__DIR__ . '/images') ? 'YES' : 'NO') . "</p>";
echo "<p>Images folder path: " . __DIR__ . '/images</p>';

// List files in images folder
if (is_dir(__DIR__ . '/images')) {
    $files = scandir(__DIR__ . '/images');
    echo "<h3>Files in images folder:</h3>";
    echo "<ul>";
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "<li>" . $file . "</li>";
        }
    }
    echo "</ul>";
}
?>
