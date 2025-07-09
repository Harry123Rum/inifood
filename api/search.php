<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = $_GET['q'] ?? '';
    
    if (strlen($query) < 2) {
        echo json_encode([]);
        exit();
    }
    
    // Search in recipes with approved status
    $searchQuery = "SELECT id, title, category_id, 
               (SELECT name FROM categories WHERE id = category_id) as category_name
        FROM recipes 
        WHERE status = 'approved' 
        AND title LIKE :query
        ORDER BY 
            CASE 
                WHEN title LIKE :exact_query THEN 1
                WHEN title LIKE :start_query THEN 2
                ELSE 3
            END,
            title ASC
        LIMIT 8";
    
    $stmt = $db->prepare($searchQuery);
    $likeQuery = "%$query%";
    $exactQuery = "$query%";
    $startQuery = "$query%";
    
    $stmt->bindParam(':query', $likeQuery);
    $stmt->bindParam(':exact_query', $exactQuery);
    $stmt->bindParam(':start_query', $startQuery);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
