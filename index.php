<?php
require_once 'config/database.php';
require_once 'config/session.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Management - Kelola Resep Favoritmu</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/home.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <h1>IniFood</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link active">Home</a></li>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Temukan Resep Terbaik untuk Setiap Momen</h1>
            <p>Jelajahi ribuan resep lezat dari berbagai kategori. Dari makanan utama hingga hidangan penutup, semua ada di sini!</p>
            <div class="hero-buttons">
                <a href="recipes.php" class="btn btn-primary">Jelajahi Resep</a>
                <a href="share.php" class="btn btn-secondary">Bagikan Resep</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2>Mengapa Memilih IniFood?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🍳</div>
                    <h3>Resep Berkualitas</h3>
                    <p>Semua resep telah diverifikasi dan diuji untuk memastikan hasil yang sempurna</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⭐</div>
                    <h3>Rating & Review</h3>
                    <p>Sistem rating dan komentar membantu Anda menemukan resep terbaik</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⏱️</div>
                    <h3>Timer Terintegrasi</h3>
                    <p>Timer bawaan membantu Anda mengatur waktu memasak dengan tepat</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔍</div>
                    <h3>Pencarian Cerdas</h3>
                    <p>Temukan resep dengan mudah menggunakan fitur pencarian live search</p>
                </div>
            </div>
        </div>
    </section>

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
    <script src="assets/js/home.js"></script>
</body>
</html>
