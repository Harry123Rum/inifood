<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Require admin access
requireAdmin();

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

// Get filter for recipes
$status_filter = $_GET['status'] ?? 'pending';
$search = $_GET['search'] ?? '';

// Build query based on filter
$recipeQuery = "SELECT r.*, c.name as category_name, u.username as author
                FROM recipes r 
                LEFT JOIN categories c ON r.category_id = c.id
                LEFT JOIN users u ON r.created_by = u.id
                WHERE 1=1";

$params = [];

if ($status_filter !== 'all') {
    $recipeQuery .= " AND r.status = :status";
    $params['status'] = $status_filter;
}

if ($search) {
    $recipeQuery .= " AND (r.title LIKE :search OR r.description LIKE :search)";
    $params['search'] = "%$search%";
}

$recipeQuery .= " ORDER BY r.created_at DESC";

$recipeStmt = $db->prepare($recipeQuery);
$recipeStmt->execute($params);
$recipes = $recipeStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle recipe actions
if ($_POST && isset($_POST['action']) && isset($_POST['recipe_id'])) {
    $action = $_POST['action'];
    $recipeId = (int)$_POST['recipe_id'];
    
    if (in_array($action, ['approve', 'reject'])) {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        
        $updateQuery = "UPDATE recipes SET status = :status WHERE id = :id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':status', $status);
        $updateStmt->bindParam(':id', $recipeId);
        
        if ($updateStmt->execute()) {
            header('Location: dashboard.php?success=1');
            exit();
        }
    } elseif ($action === 'delete') {
        // Only allow delete for approved or rejected recipes
        $checkQuery = "SELECT image_url, status FROM recipes WHERE id = :id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $recipeId);
        $checkStmt->execute();
        $recipe = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($recipe && in_array($recipe['status'], ['approved', 'rejected'])) {
            // Delete recipe from database
            $deleteQuery = "DELETE FROM recipes WHERE id = :id";
            $deleteStmt = $db->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $recipeId);
            
            if ($deleteStmt->execute()) {
                // Delete image file if exists
                if ($recipe['image_url'] && file_exists('../' . $recipe['image_url'])) {
                    unlink('../' . $recipe['image_url']);
                }
                header('Location: dashboard.php?deleted=1');
                exit();
            }
        } else {
            header('Location: dashboard.php?error=invalid_delete');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Recipe Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <h1>IniFood Admin</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link">Home</a></li>
                <li><a href="../recipes.php" class="nav-link">Resep</a></li>
                <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
            </ul>
            <div class="user-menu">
                <div class="user-icon" onclick="toggleUserMenu()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="user-dropdown" id="userDropdown">
                    <span class="user-greeting">Admin: <?php echo htmlspecialchars(getUsername()); ?></span>
                    <a href="../logout.php">Logout</a>
                </div>
            </div>
        </nav>
    </header>

    <main class="admin-main">
        <!-- Page Header -->
        <section class="admin-header">
            <div class="container">
                <h1>Dashboard Admin</h1>
                <p>Kelola resep dan monitor aktivitas platform</p>
                <div class="breadcrumb">
                    <a href="../index.php">Home</a> / <span>Admin Dashboard</span>
                </div>
            </div>
        </section>

        <!-- Statistics -->
        <section class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-card pending">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-info">
                            <h3><?php echo $stats['pending_recipes']; ?></h3>
                            <p>Resep Menunggu</p>
                        </div>
                    </div>
                    <div class="stat-card approved">
                        <div class="stat-icon">✅</div>
                        <div class="stat-info">
                            <h3><?php echo $stats['approved_recipes']; ?></h3>
                            <p>Resep Disetujui</p>
                        </div>
                    </div>
                    <div class="stat-card users">
                        <div class="stat-icon">👥</div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_users']; ?></h3>
                            <p>Total Pengguna</p>
                        </div>
                    </div>
                    <div class="stat-card ratings">
                        <div class="stat-icon">⭐</div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_ratings']; ?></h3>
                            <p>Total Rating</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filter Section -->
        <section class="filter-section">
            <div class="container">
                <div class="filter-controls">
                    <div class="status-filter">
                        <a href="?status=pending<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="filter-btn <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                            Menunggu (<?php echo $stats['pending_recipes']; ?>)
                        </a>
                        <a href="?status=approved<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="filter-btn <?php echo $status_filter === 'approved' ? 'active' : ''; ?>">
                            Disetujui (<?php echo $stats['approved_recipes']; ?>)
                        </a>
                        <a href="?status=rejected<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="filter-btn <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>">
                            Ditolak (<?php echo $stats['rejected_recipes']; ?>)
                        </a>
                        <a href="?status=all<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="filter-btn <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                            Semua
                        </a>
                    </div>
                    
                    <div class="search-filter">
                        <form method="GET" class="search-form">
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                            <input type="text" name="search" placeholder="Cari resep..." 
                                   value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recipes Section -->
        <section class="recipes-section">
            <div class="container">
                <h2>
                    <?php 
                    switch($status_filter) {
                        case 'pending': echo 'Resep Menunggu Persetujuan'; break;
                        case 'approved': echo 'Resep Disetujui'; break;
                        case 'rejected': echo 'Resep Ditolak'; break;
                        default: echo 'Semua Resep'; break;
                    }
                    ?>
                </h2>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        Resep berhasil diproses!
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success">
                        Resep berhasil dihapus!
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_delete'): ?>
                    <div class="alert alert-error">
                        Tidak dapat menghapus resep yang masih dalam tahap pengajuan!
                    </div>
                <?php endif; ?>

                <?php if (empty($recipes)): ?>
                    <div class="no-recipes">
                        <div class="no-recipes-icon">📝</div>
                        <h3>Tidak ada resep ditemukan</h3>
                        <p>Tidak ada resep yang sesuai dengan filter yang dipilih.</p>
                    </div>
                <?php else: ?>
                    <div class="recipes-table">
                        <?php foreach ($recipes as $recipe): ?>
                            <div class="recipe-row">
                                <div class="recipe-info">
                                    <div class="recipe-image">
                                        <?php 
                                        $imagePath = $recipe['image_url'];
                                        if ($imagePath) {
                                            $fullImagePath = '../' . $imagePath;
                                            if (file_exists($fullImagePath)) {
                                                echo '<img src="' . htmlspecialchars($fullImagePath) . '" alt="' . htmlspecialchars($recipe['title']) . '" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">';
                                            } else {
                                                echo '<img src="/placeholder.svg?height=80&width=80" alt="No image" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; background: #f0f0f0;">';
                                            }
                                        } else {
                                            echo '<img src="/placeholder.svg?height=80&width=80" alt="No image" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; background: #f0f0f0;">';
                                        }
                                        ?>
                                    </div>
                                    <div class="recipe-details">
                                        <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                        <p class="recipe-category"><?php echo htmlspecialchars($recipe['category_name']); ?></p>
                                        <p class="recipe-author">Oleh: <?php echo htmlspecialchars($recipe['author']); ?></p>
                                        <p class="recipe-date"><?php echo date('d M Y H:i', strtotime($recipe['created_at'])); ?></p>
                                        <span class="recipe-status status-<?php echo $recipe['status']; ?>">
                                            <?php 
                                            switch($recipe['status']) {
                                                case 'pending': echo 'Menunggu'; break;
                                                case 'approved': echo 'Disetujui'; break;
                                                case 'rejected': echo 'Ditolak'; break;
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="recipe-actions">
                                    <!-- Lihat Detail - tersedia untuk semua status -->
                                    <button class="btn btn-outline" onclick="viewRecipe(<?php echo $recipe['id']; ?>)">
                                        Lihat Detail
                                    </button>
                                    
                                    <?php if ($recipe['status'] === 'pending'): ?>
                                        <!-- Untuk resep pending: hanya setujui dan tolak -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-success">
                                                Setujui
                                            </button>
                                            <button type="submit" name="action" value="reject" class="btn btn-danger" 
                                                    onclick="return confirm('Yakin ingin menolak resep ini?')">
                                                Tolak
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <!-- Untuk resep yang sudah diproses: edit dan hapus -->
                                        <a href="edit-recipe.php?id=<?php echo $recipe['id']; ?>" class="btn btn-secondary">
                                            Edit
                                        </a>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                                            <button type="submit" name="action" value="delete" class="btn btn-danger" 
                                                    onclick="return confirm('Yakin ingin menghapus resep ini? Tindakan ini tidak dapat dibatalkan!')">
                                                Hapus
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Recipe Detail Modal -->
    <div class="modal" id="recipeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Detail Resep</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Recipe details will be loaded here -->
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
