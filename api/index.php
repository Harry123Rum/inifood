<?php
// API Directory Index
echo json_encode([
    'message' => 'Recipe Management API',
    'version' => '1.0',
    'endpoints' => [
        '/recipe-detail.php?id={id}' => 'Get recipe details',
        '/admin-stats.php' => 'Get admin statistics'
    ]
]);
?>
