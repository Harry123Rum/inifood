<?php
require_once 'config/database.php';
require_once 'config/session.php';

$database = new Database();
$db = $database->getConnection();

// Get filter parameters
$filter = $_GET['filter'] ?? 'star';
$category = $_GET['category'] ?? 'all';

// Get categories for filter
$categoryQuery = "SELECT * FROM categories ORDER BY name";
$categoryStmt = $db->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Build query based on filter
if ($filter === 'star') {
    // Top rated recipes (4+ stars)
    $recipeQuery = "SELECT r.*, c.name as category_name, c.slug as category_slug,
                           u.username as author,
                           COALESCE(AVG(rt.rating), 0) as avg_rating,
                           COUNT(rt.id) as rating_count
                    FROM recipes r 
                    LEFT JOIN categories c ON r.category_id = c.id
                    LEFT JOIN users u ON r.created_by = u.id
                    LEFT JOIN ratings rt ON r.id = rt.recipe_id
                    WHERE r.status = 'approved'";
    
    if ($category !== 'all') {
        $recipeQuery .= " AND c.slug = :category";
    }
    
    $recipeQuery .= " GROUP BY r.id 
                     HAVING avg_rating >= 4 
                     ORDER BY avg_rating DESC, rating_count DESC";
} else {
    // Popular recipes (most rated)
    $recipeQuery = "SELECT r.*, c.name as category_name, c.slug as category_slug,
                           u.username as author,
                           COALESCE(AVG(rt.rating), 0) as avg_rating,
                           COUNT(rt.id) as rating_count
                    FROM recipes r 
                    LEFT JOIN categories c ON r.category_id = c.id
                    LEFT JOIN users u ON r.created_by = u.id
                    LEFT JOIN ratings rt ON r.id = rt.recipe_id
                    WHERE r.status = 'approved'";
    
    if ($category !== 'all') {
        $recipeQuery .= " AND c.slug = :category";
    }
    
    $recipeQuery .= " GROUP BY r.id 
                     HAVING rating_count > 0 
                     ORDER BY rating_count DESC, avg_rating DESC";
}

$recipeStmt = $db->prepare($recipeQuery);
if ($category !== 'all') {
    $recipeStmt->bindParam(':category', $category);
}
$recipeStmt->execute();
$recipes = $recipeStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rating Resep - Recipe Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/ratings.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <h1>IniFood</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="recipes.php" class="nav-link">Resep</a></li>
                <li><a href="ratings.php" class="nav-link active">Rating</a></li>
                <li><a href="share.php" class="nav-link">Share</a></li>
                <li><a href="about.php" class="nav-link">About Us</a></li>
            </ul>
            <div class="user-menu">
                <div class="user-icon" onclick="toggleUserMenu()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="user-dropdown" id="userDropdown">
                    <?php if (isLoggedIn()): ?>
                        <span class="user-greeting">Halo, <?php echo htmlspecialchars(getUsername()); ?>!</span>
                        <?php if (isAdmin()): ?>
                            <a href="admin/dashboard.php">Dashboard Admin</a>
                        <?php endif; ?>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <!-- Page Header -->
        <section class="page-header">
            <div class="container">
                <h1>Rating Resep</h1>
                <p>Temukan resep terbaik berdasarkan rating dan popularitas</p>
            </div>
        </section>

        <!-- Filter Section -->
        <section class="filter-section">
            <div class="container">
                <div class="filter-controls">
                    <div class="rating-filter">
                        <a href="?filter=star<?php echo $category !== 'all' ? '&category=' . $category : ''; ?>" 
                           class="filter-btn <?php echo $filter === 'star' ? 'active' : ''; ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
                            </svg>
                            Resep Terbaik (4+ ⭐)
                        </a>
                        <a href="?filter=popular<?php echo $category !== 'all' ? '&category=' . $category : ''; ?>" 
                           class="filter-btn <?php echo $filter === 'popular' ? 'active' : ''; ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                                <line x1="20" y1="8" x2="20" y2="14"></line>
                                <line x1="23" y1="11" x2="17" y2="11"></line>
                            </svg>
                            Paling Populer
                        </a>
                    </div>

                    <div class="category-filter">
                        <select class="category-select" onchange="filterByCategory(this.value)">
                            <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['slug']; ?>" <?php echo $category === $cat['slug'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </section>

        <!-- Results Section -->
        <section class="results-section">
            <div class="container">
                <div class="results-header">
                    <h2>
                        <?php if ($filter === 'star'): ?>
                            Resep Terbaik (Rating 4+ Bintang)
                        <?php else: ?>
                            Resep Paling Populer
                        <?php endif; ?>
                    </h2>
                    <p class="results-count"><?php echo count($recipes); ?> resep ditemukan</p>
                </div>

                <?php if (empty($recipes)): ?>
                    <div class="no-results">
                        <div class="no-results-icon">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </div>
                        <h3>Tidak ada resep ditemukan</h3>
                        <p>Coba ubah filter atau kategori untuk melihat resep lainnya.</p>
                    </div>
                <?php else: ?>
                    <div class="recipes-grid">
                        <?php foreach ($recipes as $index => $recipe): ?>
                            <div class="recipe-card" onclick="window.location.href='recipe-detail.php?id=<?php echo $recipe['id']; ?>'">
                                <div class="recipe-rank">#<?php echo $index + 1; ?></div>
                                <div class="recipe-image-container">
                                    <img src="<?php echo $recipe['image_url'] ?: '/placeholder.svg?height=200&width=300'; ?>" 
                                         alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="recipe-image">
                                    <div class="recipe-category"><?php echo htmlspecialchars($recipe['category_name']); ?></div>
                                </div>
                                <div class="recipe-info">
                                    <h3 class="recipe-title"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                    <p class="recipe-description"><?php echo htmlspecialchars(substr($recipe['description'], 0, 100)); ?>...</p>
                                    
                                    <div class="recipe-stats">
                                        <div class="rating-display">
                                            <div class="stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <span class="star <?php echo $i <= round($recipe['avg_rating']) ? 'filled' : ''; ?>">★</span>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="rating-text"><?php echo number_format($recipe['avg_rating'], 1); ?></span>
                                        </div>
                                        <div class="rating-count">
                                            <?php echo $recipe['rating_count']; ?> rating
                                        </div>
                                    </div>

                                    <div class="recipe-meta">
                                        <div class="recipe-time">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12,6 12,12 16,14"></polyline>
                                            </svg>
                                            <?php echo $recipe['prep_time'] + $recipe['cook_time']; ?> min
                                        </div>
                                        <div class="recipe-author">
                                            Oleh: <?php echo htmlspecialchars($recipe['author']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>IniFood</h3>
                    <p>Platform terbaik untuk berbagi dan menemukan resep lezat dari seluruh Indonesia.</p>
                </div>
                <div class="footer-section">
                    <h4>Menu</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="recipes.php">Resep</a></li>
                        <li><a href="ratings.php">Rating</a></li>
                        <li><a href="share.php">Share</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Kontak</h4>
                    <p>Email: IniFood@gmail.com</p>
                    <p>Phone: +62 123 456 789</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 IniFood. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/ratings.js"></script>
</body>
</html>
