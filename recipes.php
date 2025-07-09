<?php
require_once 'config/database.php';
require_once 'config/session.php';

$database = new Database();
$db = $database->getConnection();

// Get categories
$categoryQuery = "SELECT * FROM categories ORDER BY name";
$categoryStmt = $db->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected category
$selectedCategory = $_GET['category'] ?? 'makanan-utama';
$searchQuery = $_GET['search'] ?? '';

// Build recipe query
$recipeQuery = "SELECT r.*, c.name as category_name, c.slug as category_slug,
                       COALESCE(AVG(rt.rating), 0) as avg_rating,
                       COUNT(rt.id) as rating_count,
                       u.username as author
                FROM recipes r 
                LEFT JOIN categories c ON r.category_id = c.id
                LEFT JOIN ratings rt ON r.id = rt.recipe_id
                LEFT JOIN users u ON r.created_by = u.id
                WHERE r.status = 'approved'";

$params = [];

if ($selectedCategory !== 'all') {
    $recipeQuery .= " AND c.slug = :category";
    $params['category'] = $selectedCategory;
}

if ($searchQuery) {
    $recipeQuery .= " AND r.title LIKE :search";
    $params['search'] = "%$searchQuery%";
}

$recipeQuery .= " GROUP BY r.id ORDER BY r.created_at DESC";

$recipeStmt = $db->prepare($recipeQuery);
$recipeStmt->execute($params);
$recipes = $recipeStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resep - Recipe Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/recipes.css">
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
                <li><a href="recipes.php" class="nav-link active">Resep</a></li>
                <li><a href="ratings.php" class="nav-link">Rating</a></li>
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
                <h1>Koleksi Resep</h1>
                <p>Temukan resep favorit Anda dari berbagai kategori</p>
            </div>
        </section>

        <!-- Search Section -->
        <section class="search-section">
            <div class="container">
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Cari resep..." 
                           value="<?php echo htmlspecialchars($searchQuery); ?>" id="searchInput">
            <div class="search-suggestions" id="searchSuggestions"></div>
        </div>
    </div>
</section>

        <!-- Category Filter -->
        <section class="category-filter">
            <div class="container">
                <div class="category-tabs">
                    <a href="?category=all<?php echo $searchQuery ? '&search=' . urlencode($searchQuery) : ''; ?>" 
                       class="category-tab <?php echo $selectedCategory === 'all' ? 'active' : ''; ?>">
                        Semua
                    </a>
                    <?php foreach ($categories as $category): ?>
                        <a href="?category=<?php echo $category['slug']; ?><?php echo $searchQuery ? '&search=' . urlencode($searchQuery) : ''; ?>" 
                           class="category-tab <?php echo $selectedCategory === $category['slug'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Recipes Grid -->
        <section class="recipes-section">
            <div class="container">
                <?php if (empty($recipes)): ?>
                    <div class="no-results">
                        <h3>Tidak ada resep ditemukan</h3>
                        <p>Coba ubah kata kunci pencarian atau pilih kategori lain.</p>
                    </div>
                <?php else: ?>
                    <div class="recipes-grid">
                        <?php foreach ($recipes as $recipe): ?>
                            <div class="recipe-card" onclick="window.location.href='recipe-detail.php?id=<?php echo $recipe['id']; ?>'">
                                <div class="recipe-image-container">
                                    <img src="<?php echo $recipe['image_url'] ?: '/placeholder.svg?height=200&width=300'; ?>" 
                                         alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="recipe-image">
                                    <div class="recipe-category"><?php echo htmlspecialchars($recipe['category_name']); ?></div>
                                    <div class="recipe-difficulty difficulty-<?php echo strtolower($recipe['difficulty']); ?>">
                                        <?php echo $recipe['difficulty']; ?>
                                    </div>
                                </div>
                                <div class="recipe-info">
                                    <h3 class="recipe-title"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                    <p class="recipe-description"><?php echo htmlspecialchars(substr($recipe['description'], 0, 100)); ?>...</p>
                                    <div class="recipe-meta">
                                        <div class="recipe-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star <?php echo $i <= round($recipe['avg_rating']) ? 'filled' : ''; ?>">★</span>
                                            <?php endfor; ?>
                                            <span class="rating-text">(<?php echo $recipe['rating_count']; ?>)</span>
                                        </div>
                                        <div class="recipe-time">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12,6 12,12 16,14"></polyline>
                                            </svg>
                                            <?php echo $recipe['prep_time'] + $recipe['cook_time']; ?> min
                                        </div>
                                    </div>
                                    <div class="recipe-author">
                                        Oleh: <?php echo htmlspecialchars($recipe['author']); ?>
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
    <script src="assets/js/recipes.js"></script>
</body>
</html>
