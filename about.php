<?php
require_once 'config/database.php';
require_once 'config/session.php';

$database = new Database();
$db = $database->getConnection();

// Get team members from database
$teamQuery = "SELECT * FROM team_members WHERE is_active = TRUE ORDER BY display_order ASC";
$teamStmt = $db->prepare($teamQuery);
$teamStmt->execute();
$teamMembers = $teamStmt->fetchAll(PDO::FETCH_ASSOC);

// Hardcoded Vision and Mission
$visionMission = [
    [
        'title' => 'Visi Kami',
        'content' => 'Menjadi platform kuliner digital terdepan di Indonesia yang menghubungkan pecinta masakan dari seluruh nusantara, melestarikan warisan kuliner tradisional, dan menginspirasi inovasi dalam dunia masak-memasak.',
        'icon' => 'eye'
    ],
    [
        'title' => 'Misi Kami',
        'content' => 'Menyediakan platform yang mudah digunakan untuk berbagi resep berkualitas, membangun komunitas kuliner yang solid, memastikan setiap resep telah diuji dan terverifikasi, serta mendukung pelestarian kekayaan kuliner Indonesia untuk generasi mendatang.',
        'icon' => 'star'
    ]
];

// Hardcoded Company Values
$values = [
    [
        'title' => 'Kualitas Terjamin',
        'content' => 'Setiap resep melalui proses validasi ketat untuk memastikan hasil yang konsisten dan memuaskan.',
        'icon' => 'check-circle'
    ],
    [
        'title' => 'Komunitas Solid',
        'content' => 'Membangun komunitas yang saling mendukung dan berbagi passion terhadap kuliner Indonesia.',
        'icon' => 'users'
    ],
    [
        'title' => 'Inovasi Berkelanjutan',
        'content' => 'Terus berinovasi dalam teknologi dan konten untuk memberikan pengalaman terbaik bagi pengguna.',
        'icon' => 'star'
    ],
    [
        'title' => 'Passion untuk Kuliner',
        'content' => 'Kecintaan mendalam terhadap kuliner Indonesia menjadi motivasi utama dalam setiap karya kami.',
        'icon' => 'heart'
    ]
];

// Icon mapping for values (remains the same as it's for SVG paths)
$iconMap = [
    'check-circle' => '<path d="M9 12l2 2 4-4"></path><path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3"></path><path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3"></path><path d="M12 3c0 1-1 3-3 3s-3-2-3-3 1-3 3-3 3 2 3 3"></path><path d="M12 21c0-1 1-3 3-3s3 2 3 3-1 3-3 3-3-2-3-3"></path>',
    'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
    'star' => '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>',
    'heart' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>',
    'eye' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - IniFood</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/about.css">
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
                <li><a href="about.php" class="nav-link active">About Us</a></li>
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
        <!-- Hero Section -->
        <section class="about-hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Tentang IniFood</h1>
                    <p class="hero-subtitle">Platform terpercaya untuk berbagi dan menemukan resep terbaik Indonesia</p>
                    <div class="hero-description">
                        <p>Kami adalah tim yang berdedikasi untuk menghadirkan pengalaman kuliner terbaik melalui platform digital. Dengan kombinasi keahlian kuliner, teknologi, dan passion terhadap makanan Indonesia, kami menciptakan ruang di mana setiap orang dapat berbagi dan menemukan resep yang menginspirasi.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Vision Mission Section -->
        <section class="vision-mission">
            <div class="container">
                <div class="vm-grid">
                    <?php foreach ($visionMission as $vm): ?>
                        <div class="vm-card">
                            <div class="vm-icon">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <?php echo $iconMap[$vm['icon']] ?? ''; ?>
                                </svg>
                            </div>
                            <h3><?php echo htmlspecialchars($vm['title']); ?></h3>
                            <p><?php echo htmlspecialchars($vm['content']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="team-section">
            <div class="container">
                <div class="section-header">
                    <h2>Tim Kami</h2>
                    <p>Bertemu dengan orang-orang hebat di balik IniFood</p>
                </div>
                
                <div class="team-grid">
                    <?php foreach ($teamMembers as $member): ?>
                        <div class="team-card">
                            <div class="team-image">
                                <img src="<?php echo htmlspecialchars($member['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($member['name']); ?>"
                                     onerror="this.src='/placeholder.svg?height=300&width=300'">
                            </div>
                            <div class="team-info">
                                <h3 class="team-name"><?php echo htmlspecialchars($member['name']); ?></h3>
                                <p class="team-role"><?php echo htmlspecialchars($member['role']); ?></p>
                                <p class="team-description"><?php echo htmlspecialchars($member['description']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Values Section -->
        <section class="values-section">
            <div class="container">
                <div class="section-header">
                    <h2>Nilai-Nilai Kami</h2>
                    <p>Prinsip yang memandu setiap langkah kami</p>
                </div>
                
                <div class="values-grid">
                    <?php foreach ($values as $value): ?>
                        <div class="value-card">
                            <div class="value-icon">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <?php echo $iconMap[$value['icon']] ?? ''; ?>
                                </svg>
                            </div>
                            <h4><?php echo htmlspecialchars($value['title']); ?></h4>
                            <p><?php echo htmlspecialchars($value['content']); ?></p>
                        </div>
                    <?php endforeach; ?>
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
                    <p>Email: hello@inifood.com</p>
                    <p>Phone: +62 123 456 789</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 IniFood. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/about.js"></script>
</body>
</html>
