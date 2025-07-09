<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

// Require admin access
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get statistics
    $statsQuery = "SELECT 
                    (SELECT COUNT(*) FROM recipes WHERE status = 'pending') as pending_recipes,
                    (SELECT COUNT(*) FROM recipes WHERE status = 'approved') as approved_recipes,
                    (SELECT COUNT(*) FROM recipes WHERE status = 'rejected') as rejected_recipes,
                    (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
                    (SELECT COUNT(*) FROM ratings) as total_ratings,
                    (SELECT COUNT(*) FROM comments) as total_comments";
    
    $statsStmt = $db->prepare($statsQuery);
    $statsStmt->execute();
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode($stats);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
