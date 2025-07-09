<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $recipeId = $_GET['id'] ?? 0;
    
    if (!$recipeId) {
        echo json_encode(['error' => 'Recipe ID is required']);
        exit();
    }
    
    // Get recipe details
    $recipeQuery = "SELECT r.*, c.name as category_name, c.slug as category_slug,
                           u.username as author,
                           COALESCE(AVG(rt.rating), 0) as avg_rating,
                           COUNT(rt.id) as rating_count
                    FROM recipes r 
                    LEFT JOIN categories c ON r.category_id = c.id
                    LEFT JOIN users u ON r.created_by = u.id
                    LEFT JOIN ratings rt ON r.id = rt.recipe_id
                    WHERE r.id = :id
                    GROUP BY r.id";
    
    $recipeStmt = $db->prepare($recipeQuery);
    $recipeStmt->bindParam(':id', $recipeId);
    $recipeStmt->execute();
    $recipe = $recipeStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$recipe) {
        echo json_encode(['error' => 'Recipe not found']);
        exit();
    }
    
    // Return recipe data as-is (path akan dihandle di JavaScript)
    echo json_encode($recipe);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
