<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get categories
$categoryQuery = "SELECT * FROM categories ORDER BY name";
$categoryStmt = $db->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

$success = '';
$error = '';

// Handle form submission
if ($_POST) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $ingredients = trim($_POST['ingredients']);
    $instructions = trim($_POST['instructions']);
    $prep_time = (int)$_POST['prep_time'];
    $cook_time = (int)$_POST['cook_time'];
    $servings = (int)$_POST['servings'];
    $difficulty = $_POST['difficulty'];
    $category_id = (int)$_POST['category_id'];
    
    // Handle file upload
    $image_path = '';
    if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/recipes/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_info = pathinfo($_FILES['recipe_image']['name']);
        $file_extension = strtolower($file_info['extension']);
        
        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($file_extension, $allowed_types)) {
            $error = 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.';
        }
        
        // Validate file size (max 5MB)
        elseif ($_FILES['recipe_image']['size'] > 5 * 1024 * 1024) {
            $error = 'Ukuran file terlalu besar. Maksimal 5MB.';
        }
        
        else {
            // Generate unique filename
            $new_filename = uniqid('recipe_') . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['recipe_image']['tmp_name'], $upload_path)) {
                $image_path = $upload_path;
            } else {
                $error = 'Gagal mengupload gambar.';
            }
        }
    }
    
    // Validation
    if (!$error) {
        if (empty($title) || empty($description) || empty($ingredients) || empty($instructions)) {
            $error = 'Semua field wajib harus diisi.';
        } elseif ($prep_time <= 0 || $cook_time <= 0 || $servings <= 0) {
            $error = 'Waktu dan porsi harus lebih dari 0.';
        } elseif ($category_id <= 0) {
            $error = 'Pilih kategori yang valid.';
        } else {
            // Insert recipe
            $insertQuery = "INSERT INTO recipes (title, description, ingredients, instructions, prep_time, cook_time, servings, difficulty, category_id, image_url, status, created_by, created_at) 
                           VALUES (:title, :description, :ingredients, :instructions, :prep_time, :cook_time, :servings, :difficulty, :category_id, :image_url, 'pending', :created_by, NOW())";
            
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->bindParam(':title', $title);
            $insertStmt->bindParam(':description', $description);
            $insertStmt->bindParam(':ingredients', $ingredients);
            $insertStmt->bindParam(':instructions', $instructions);
            $insertStmt->bindParam(':prep_time', $prep_time);
            $insertStmt->bindParam(':cook_time', $cook_time);
            $insertStmt->bindParam(':servings', $servings);
            $insertStmt->bindParam(':difficulty', $difficulty);
            $insertStmt->bindParam(':category_id', $category_id);
            $insertStmt->bindParam(':image_url', $image_path);
            $insertStmt->bindParam(':created_by', $_SESSION['user_id']);
            
            if ($insertStmt->execute()) {
                $success = 'Resep berhasil dikirim! Menunggu persetujuan admin.';
                // Clear form data
                $_POST = array();
            } else {
                $error = 'Terjadi kesalahan saat menyimpan resep.';
                // Delete uploaded file if database insert failed
                if ($image_path && file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bagikan Resep - Recipe Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/share.css">
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
                <li><a href="share.php" class="nav-link active">Share</a></li>
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
                    <span class="user-greeting">Halo, <?php echo htmlspecialchars(getUsername()); ?>!</span>
                    <?php if (isAdmin()): ?>
                        <a href="admin/dashboard.php">Dashboard Admin</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <!-- Page Header -->
        <section class="page-header">
            <div class="container">
                <h1>Bagikan Resep Anda</h1>
                <p>Berbagi resep favorit Anda dengan komunitas IniFood</p>
            </div>
        </section>

        <!-- Form Section -->
        <section class="form-section">
            <div class="container">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="recipe-form" id="recipeForm">
                    <div class="form-section-group">
                        <h2>Informasi Dasar</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="title" class="form-label">Nama Resep *</label>
                                <input type="text" id="title" name="title" class="form-input" 
                                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="category_id" class="form-label">Kategori *</label>
                                <select id="category_id" name="category_id" class="form-select" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Deskripsi Resep *</label>
                            <textarea id="description" name="description" class="form-textarea" rows="3" 
                                      placeholder="Ceritakan tentang resep Anda..." required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="recipe_image" class="form-label">Foto Resep</label>
                            <div class="file-upload-container">
                                <input type="file" id="recipe_image" name="recipe_image" class="form-file" 
                                       accept="image/*" onchange="previewImage(this)">
                                <label for="recipe_image" class="file-upload-label">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21,15 16,10 5,21"></polyline>
                                    </svg>
                                    <span>Pilih Foto Resep</span>
                                </label>
                                <div id="imagePreview" class="image-preview-container"></div>
                            </div>
                            <small class="form-help">Format: JPG, PNG, GIF, WebP. Maksimal 5MB.</small>
                        </div>
                    </div>

                    <div class="form-section-group">
                        <h2>Detail Memasak</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="prep_time" class="form-label">Waktu Persiapan (menit) *</label>
                                <input type="number" id="prep_time" name="prep_time" class="form-input" 
                                       value="<?php echo htmlspecialchars($_POST['prep_time'] ?? ''); ?>" 
                                       min="1" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="cook_time" class="form-label">Waktu Memasak (menit) *</label>
                                <input type="number" id="cook_time" name="cook_time" class="form-input" 
                                       value="<?php echo htmlspecialchars($_POST['cook_time'] ?? ''); ?>" 
                                       min="1" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="servings" class="form-label">Porsi *</label>
                                <input type="number" id="servings" name="servings" class="form-input" 
                                       value="<?php echo htmlspecialchars($_POST['servings'] ?? ''); ?>" 
                                       min="1" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="difficulty" class="form-label">Tingkat Kesulitan *</label>
                                <select id="difficulty" name="difficulty" class="form-select" required>
                                    <option value="">Pilih Tingkat Kesulitan</option>
                                    <option value="Easy" <?php echo (($_POST['difficulty'] ?? '') == 'Easy') ? 'selected' : ''; ?>>Mudah</option>
                                    <option value="Medium" <?php echo (($_POST['difficulty'] ?? '') == 'Medium') ? 'selected' : ''; ?>>Sedang</option>
                                    <option value="Hard" <?php echo (($_POST['difficulty'] ?? '') == 'Hard') ? 'selected' : ''; ?>>Sulit</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section-group">
                        <h2>Bahan-bahan</h2>
                        <div class="form-group">
                            <label for="ingredients" class="form-label">Daftar Bahan *</label>
                            <textarea id="ingredients" name="ingredients" class="form-textarea" rows="8" 
                                      placeholder="Tulis setiap bahan dalam baris terpisah, contoh:&#10;Nasi putih 3 piring&#10;Telur 2 butir&#10;Bawang merah 5 siung" 
                                      required><?php echo htmlspecialchars($_POST['ingredients'] ?? ''); ?></textarea>
                            <small class="form-help">Tulis setiap bahan dalam baris terpisah</small>
                        </div>
                    </div>

                    <div class="form-section-group">
                        <h2>Cara Membuat</h2>
                        <div class="form-group">
                            <label for="instructions" class="form-label">Langkah-langkah *</label>
                            <textarea id="instructions" name="instructions" class="form-textarea" rows="10" 
                                      placeholder="Tulis setiap langkah dalam baris terpisah, contoh:&#10;1. Panaskan minyak dalam wajan&#10;2. Tumis bumbu halus hingga harum&#10;3. Masukkan telur, orak-arik" 
                                      required><?php echo htmlspecialchars($_POST['instructions'] ?? ''); ?></textarea>
                            <small class="form-help">Tulis setiap langkah dalam baris terpisah</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" onclick="resetForm()">Reset</button>
                        <button type="submit" class="btn btn-primary">Kirim Resep</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Tips Section -->
        <section class="tips-section">
            <div class="container">
                <h2>Tips Menulis Resep yang Baik</h2>
                <div class="tips-grid">
                    <div class="tip-card">
                        <div class="tip-icon">📝</div>
                        <h3>Judul yang Menarik</h3>
                        <p>Buat judul yang deskriptif dan menarik perhatian pembaca</p>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">📸</div>
                        <h3>Foto yang Menggugah</h3>
                        <p>Gunakan foto berkualitas tinggi yang menampilkan hasil akhir masakan</p>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">📋</div>
                        <h3>Bahan yang Jelas</h3>
                        <p>Tulis takaran dan jenis bahan dengan detail dan akurat</p>
                    </div>
                    <div class="tip-card">
                        <div class="tip-icon">👨‍🍳</div>
                        <h3>Langkah yang Detail</h3>
                        <p>Jelaskan setiap langkah dengan jelas dan mudah diikuti</p>
                    </div>
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
                    <p>Email: info@IniFood.com</p>
                    <p>Phone: +62 123 456 789</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 IniFood. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/share.js"></script>
</body>
</html>
