<?php
require_once 'config/database.php';
require_once 'config/session.php';

$database = new Database();
$db = $database->getConnection();

// Get recipe ID
$recipeId = $_GET['id'] ?? 0;

if (!$recipeId) {
    header('Location: recipes.php');
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
                WHERE r.id = :id AND r.status = 'approved'
                GROUP BY r.id";

$recipeStmt = $db->prepare($recipeQuery);
$recipeStmt->bindParam(':id', $recipeId);
$recipeStmt->execute();
$recipe = $recipeStmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    header('Location: recipes.php');
    exit();
}

// Get user's rating for this recipe
$userRating = 0;
if (isLoggedIn()) {
    $userRatingQuery = "SELECT rating FROM ratings WHERE recipe_id = :recipe_id AND user_id = :user_id";
    $userRatingStmt = $db->prepare($userRatingQuery);
    $userRatingStmt->bindParam(':recipe_id', $recipeId);
    $userRatingStmt->bindParam(':user_id', $_SESSION['user_id']);
    $userRatingStmt->execute();
    $userRatingResult = $userRatingStmt->fetch(PDO::FETCH_ASSOC);
    $userRating = $userRatingResult ? $userRatingResult['rating'] : 0;
}

// Get comments
$commentsQuery = "SELECT c.*, u.username 
                  FROM comments c 
                  LEFT JOIN users u ON c.user_id = u.id 
                  WHERE c.recipe_id = :recipe_id 
                  ORDER BY c.created_at DESC";
$commentsStmt = $db->prepare($commentsQuery);
$commentsStmt->bindParam(':recipe_id', $recipeId);
$commentsStmt->execute();
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle rating submission
if ($_POST && isset($_POST['rating']) && isLoggedIn()) {
    $rating = (int)$_POST['rating'];
    $userId = $_SESSION['user_id'];
    
    if ($rating >= 1 && $rating <= 5) {
        $insertRatingQuery = "INSERT INTO ratings (recipe_id, user_id, rating) 
                             VALUES (:recipe_id, :user_id, :rating)
                             ON DUPLICATE KEY UPDATE rating = :rating";
        $insertRatingStmt = $db->prepare($insertRatingQuery);
        $insertRatingStmt->bindParam(':recipe_id', $recipeId);
        $insertRatingStmt->bindParam(':user_id', $userId);
        $insertRatingStmt->bindParam(':rating', $rating);
        
        if ($insertRatingStmt->execute()) {
            header("Location: recipe-detail.php?id=$recipeId");
            exit();
        }
    }
}

// Handle comment submission
if ($_POST && isset($_POST['comment']) && isLoggedIn()) {
    $comment = trim($_POST['comment']);
    $userId = $_SESSION['user_id'];
    
    if (!empty($comment)) {
        $insertCommentQuery = "INSERT INTO comments (recipe_id, user_id, comment) 
                              VALUES (:recipe_id, :user_id, :comment)";
        $insertCommentStmt = $db->prepare($insertCommentQuery);
        $insertCommentStmt->bindParam(':recipe_id', $recipeId);
        $insertCommentStmt->bindParam(':user_id', $userId);
        $insertCommentStmt->bindParam(':comment', $comment);
        
        if ($insertCommentStmt->execute()) {
            header("Location: recipe-detail.php?id=$recipeId");
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
    <title><?php echo htmlspecialchars($recipe['title']); ?> - Recipe Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/recipe-detail.css">
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
        <!-- Breadcrumb -->
        <section class="breadcrumb">
            <div class="container">
                <nav class="breadcrumb-nav">
                    <a href="index.php">Home</a>
                    <span class="separator">›</span>
                    <a href="recipes.php">Resep</a>
                    <span class="separator">›</span>
                    <a href="recipes.php?category=<?php echo $recipe['category_slug']; ?>"><?php echo htmlspecialchars($recipe['category_name']); ?></a>
                    <span class="separator">›</span>
                    <span class="current"><?php echo htmlspecialchars($recipe['title']); ?></span>
                </nav>
            </div>
        </section>

        <!-- Recipe Header -->
        <section class="recipe-header">
            <div class="container">
                <div class="recipe-header-content">
                    <div class="recipe-image-section">
                        <img src="<?php echo $recipe['image_url'] ?: '/placeholder.svg?height=400&width=600'; ?>" 
                             alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="recipe-main-image">
                    </div>
                    <div class="recipe-info-section">
                        <div class="recipe-category-badge"><?php echo htmlspecialchars($recipe['category_name']); ?></div>
                        <h1 class="recipe-title"><?php echo htmlspecialchars($recipe['title']); ?></h1>
                        <p class="recipe-description"><?php echo htmlspecialchars($recipe['description']); ?></p>
                        
                        <div class="recipe-meta-info">
                            <div class="meta-item">
                                <span class="meta-label">Waktu Persiapan:</span>
                                <span class="meta-value"><?php echo $recipe['prep_time']; ?> menit</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Waktu Memasak:</span>
                                <span class="meta-value"><?php echo $recipe['cook_time']; ?> menit</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Porsi:</span>
                                <span class="meta-value"><?php echo $recipe['servings']; ?> orang</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Tingkat Kesulitan:</span>
                                <span class="meta-value difficulty-<?php echo strtolower($recipe['difficulty']); ?>">
                                    <?php echo $recipe['difficulty']; ?>
                                </span>
                            </div>
                        </div>

                        <div class="recipe-rating-section">
                            <div class="current-rating">
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?php echo $i <= round($recipe['avg_rating']) ? 'filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-text"><?php echo number_format($recipe['avg_rating'], 1); ?> (<?php echo $recipe['rating_count']; ?> rating)</span>
                            </div>
                            
                            <?php if (isLoggedIn()): ?>
                                <div class="user-rating">
                                    <span class="rating-label">Beri Rating:</span>
                                    <form method="POST" class="rating-form">
                                        <div class="rating interactive" data-rating="<?php echo $userRating; ?>">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star <?php echo $i <= $userRating ? 'filled' : ''; ?>" 
                                                      data-rating="<?php echo $i; ?>">★</span>
                                            <?php endfor; ?>
                                        </div>
                                        <input type="hidden" name="rating" id="ratingInput" value="<?php echo $userRating; ?>">
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="recipe-author">
                            <span>Dibuat oleh: <strong><?php echo htmlspecialchars($recipe['author']); ?></strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recipe Content -->
        <section class="recipe-content">
            <div class="container">
                <div class="recipe-content-grid">
                    <!-- Ingredients -->
                    <div class="ingredients-section">
                        <h2>Bahan-bahan</h2>
                        <div class="ingredients-list">
                            <?php 
                            $ingredients = explode("\n", $recipe['ingredients']);
                            foreach ($ingredients as $ingredient): 
                                if (trim($ingredient)):
                            ?>
                                <div class="ingredient-item">
                                    <input type="checkbox" class="ingredient-checkbox">
                                    <span><?php echo htmlspecialchars(trim($ingredient)); ?></span>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="instructions-section">
                        <h2>Cara Membuat</h2>
                        <div class="instructions-list">
                            <?php 
                            $instructions = explode("\n", $recipe['instructions']);
                            $stepNumber = 1;
                            foreach ($instructions as $instruction): 
                                if (trim($instruction)):
                            ?>
                                <div class="instruction-step">
                                    <div class="step-number"><?php echo $stepNumber++; ?></div>
                                    <div class="step-content"><?php echo htmlspecialchars(trim($instruction)); ?></div>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Timer Section -->
                <div class="timer-section">
                    <h3>Timer Memasak</h3>
                    <div class="timer" id="cookingTimer">
                        <div class="timer-controls">
                            <input type="number" class="timer-input" placeholder="Menit" min="1" max="999">
                            <button class="btn btn-primary timer-start">Mulai</button>
                            <button class="btn btn-secondary timer-pause" disabled>Pause</button>
                            <button class="btn btn-outline timer-reset">Reset</button>
                        </div>
                        <div class="timer-display">00:00</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Comments Section -->
        <section class="comments-section">
            <div class="container">
                <h2>Komentar (<?php echo count($comments); ?>)</h2>
                
                <?php if (isLoggedIn()): ?>
                    <form method="POST" class="comment-form">
                        <div class="form-group">
                            <textarea name="comment" class="form-textarea" placeholder="Tulis komentar Anda..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Kirim Komentar</button>
                    </form>
                <?php else: ?>
                    <p class="login-prompt">
                        <a href="login.php">Login</a> untuk memberikan komentar.
                    </p>
                <?php endif; ?>

                <div class="comments-list">
                    <?php if (empty($comments)): ?>
                        <p class="no-comments">Belum ada komentar. Jadilah yang pertama!</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item">
                                <div class="comment-header">
                                    <strong class="comment-author"><?php echo htmlspecialchars($comment['username']); ?></strong>
                                    <span class="comment-date"><?php echo date('d M Y H:i', strtotime($comment['created_at'])); ?></span>
                                </div>
                                <div class="comment-content">
                                    <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
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
    <script src="assets/js/recipe-detail.js"></script>
</body>
</html>
