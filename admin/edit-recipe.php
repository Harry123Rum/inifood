<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Require admin access
requireAdmin();

$database = new Database();
$db = $database->getConnection();

// Get recipe ID
$recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$recipeId) {
    header('Location: dashboard.php');
    exit();
}

// Get recipe data
$recipeQuery = "SELECT r.*, c.name as category_name, u.username as author
                FROM recipes r 
                LEFT JOIN categories c ON r.category_id = c.id
                LEFT JOIN users u ON r.created_by = u.id
                WHERE r.id = :id";
$recipeStmt = $db->prepare($recipeQuery);
$recipeStmt->bindParam(':id', $recipeId);
$recipeStmt->execute();
$recipe = $recipeStmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    header('Location: dashboard.php');
    exit();
}

// Only allow editing of approved or rejected recipes
if ($recipe['status'] === 'pending') {
    header('Location: dashboard.php?error=cannot_edit_pending');
    exit();
}

// Get categories
$categoriesQuery = "SELECT * FROM categories ORDER BY name";
$categoriesStmt = $db->prepare($categoriesQuery);
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

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
    $status = $_POST['status'];
    
    $errors = [];
    
    // Validation
    if (empty($title)) $errors[] = "Judul resep harus diisi";
    if (empty($description)) $errors[] = "Deskripsi harus diisi";
    if (empty($ingredients)) $errors[] = "Bahan-bahan harus diisi";
    if (empty($instructions)) $errors[] = "Cara membuat harus diisi";
    if ($prep_time <= 0) $errors[] = "Waktu persiapan harus lebih dari 0";
    if ($cook_time <= 0) $errors[] = "Waktu memasak harus lebih dari 0";
    if ($servings <= 0) $errors[] = "Jumlah porsi harus lebih dari 0";
    if (!in_array($difficulty, ['Easy', 'Medium', 'Hard'])) $errors[] = "Tingkat kesulitan tidak valid";
    if (!in_array($status, ['pending', 'approved', 'rejected'])) $errors[] = "Status tidak valid";
    
    $imagePath = $recipe['image_url']; // Keep existing image by default
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/recipes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileInfo = pathinfo($_FILES['image']['name']);
        $extension = strtolower($fileInfo['extension']);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = "Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.";
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = "Ukuran file terlalu besar. Maksimal 5MB.";
        } else {
            $fileName = 'recipe_' . $recipeId . '_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                // Delete old image if exists
                if ($recipe['image_url'] && file_exists('../' . $recipe['image_url'])) {
                    unlink('../' . $recipe['image_url']);
                }
                $imagePath = 'uploads/recipes/' . $fileName;
            } else {
                $errors[] = "Gagal mengupload gambar.";
            }
        }
    }
    
    if (empty($errors)) {
        $updateQuery = "UPDATE recipes SET 
                        title = :title,
                        description = :description,
                        ingredients = :ingredients,
                        instructions = :instructions,
                        prep_time = :prep_time,
                        cook_time = :cook_time,
                        servings = :servings,
                        difficulty = :difficulty,
                        category_id = :category_id,
                        status = :status,
                        image_url = :image_url,
                        updated_at = NOW()
                        WHERE id = :id";
        
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':title', $title);
        $updateStmt->bindParam(':description', $description);
        $updateStmt->bindParam(':ingredients', $ingredients);
        $updateStmt->bindParam(':instructions', $instructions);
        $updateStmt->bindParam(':prep_time', $prep_time);
        $updateStmt->bindParam(':cook_time', $cook_time);
        $updateStmt->bindParam(':servings', $servings);
        $updateStmt->bindParam(':difficulty', $difficulty);
        $updateStmt->bindParam(':category_id', $category_id);
        $updateStmt->bindParam(':status', $status);
        $updateStmt->bindParam(':image_url', $imagePath);
        $updateStmt->bindParam(':id', $recipeId);
        
        if ($updateStmt->execute()) {
            header('Location: dashboard.php?updated=1');
            exit();
        } else {
            $errors[] = "Gagal mengupdate resep.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resep - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/edit-recipe.css">
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
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
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

    <main class="edit-recipe-main">
        <div class="edit-recipe-container">
            <!-- Header Section -->
            <div class="edit-recipe-header">
                <h1>Edit Resep</h1>
                <p>Ubah dan perbarui informasi resep dengan mudah</p>
                <div class="recipe-info-badge">
                    Resep: <?php echo htmlspecialchars($recipe['title']); ?>
                </div>
                <div class="recipe-meta">
                    <div class="meta-item">
                        <strong>Dibuat oleh</strong>
                        <span><?php echo htmlspecialchars($recipe['author']); ?></span>
                    </div>
                    <div class="meta-item">
                        <strong>Kategori</strong>
                        <span><?php echo htmlspecialchars($recipe['category_name']); ?></span>
                    </div>
                    <div class="meta-item">
                        <strong>Status</strong>
                        <span class="status-badge status-<?php echo $recipe['status']; ?>">
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
            </div>

            <!-- Form Section -->
            <div class="edit-recipe-form">
                <?php if (!empty($errors)): ?>
                    <div class="form-section">
                        <div class="alert alert-error">
                            <strong>Terjadi kesalahan:</strong>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3>Informasi Dasar</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="title">Judul Resep <span class="required">*</span></label>
                                <input type="text" id="title" name="title" class="form-input" required 
                                       value="<?php echo htmlspecialchars($recipe['title']); ?>"
                                       placeholder="Masukkan judul resep yang menarik">
                            </div>

                            <div class="form-group">
                                <label for="category_id">Kategori <span class="required">*</span></label>
                                <select id="category_id" name="category_id" class="form-select" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo $recipe['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Deskripsi <span class="required">*</span></label>
                            <textarea id="description" name="description" class="form-textarea" required 
                                      placeholder="Ceritakan tentang resep ini..."><?php echo htmlspecialchars($recipe['description']); ?></textarea>
                            <small class="form-help">Jelaskan keunikan dan daya tarik resep Anda</small>
                        </div>

                        <div class="form-group">
                            <label for="status">Status Resep <span class="required">*</span></label>
                            <select id="status" name="status" class="form-select" required>
                                <option value="pending" <?php echo $recipe['status'] === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="approved" <?php echo $recipe['status'] === 'approved' ? 'selected' : ''; ?>>Disetujui</option>
                                <option value="rejected" <?php echo $recipe['status'] === 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                            </select>
                        </div>
                    </div>

                    <!-- Recipe Details -->
                    <div class="form-section">
                        <h3>Detail Memasak</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="prep_time">Waktu Persiapan (menit) <span class="required">*</span></label>
                                <input type="number" id="prep_time" name="prep_time" class="form-input" required min="1" 
                                       value="<?php echo $recipe['prep_time']; ?>" placeholder="15">
                            </div>

                            <div class="form-group">
                                <label for="cook_time">Waktu Memasak (menit) <span class="required">*</span></label>
                                <input type="number" id="cook_time" name="cook_time" class="form-input" required min="1" 
                                       value="<?php echo $recipe['cook_time']; ?>" placeholder="30">
                            </div>

                            <div class="form-group">
                                <label for="servings">Jumlah Porsi <span class="required">*</span></label>
                                <input type="number" id="servings" name="servings" class="form-input" required min="1" 
                                       value="<?php echo $recipe['servings']; ?>" placeholder="4">
                            </div>

                            <div class="form-group">
                                <label for="difficulty">Tingkat Kesulitan <span class="required">*</span></label>
                                <select id="difficulty" name="difficulty" class="form-select" required>
                                    <option value="">Pilih Kesulitan</option>
                                    <option value="Easy" <?php echo $recipe['difficulty'] === 'Easy' ? 'selected' : ''; ?>>Mudah</option>
                                    <option value="Medium" <?php echo $recipe['difficulty'] === 'Medium' ? 'selected' : ''; ?>>Sedang</option>
                                    <option value="Hard" <?php echo $recipe['difficulty'] === 'Hard' ? 'selected' : ''; ?>>Sulit</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Image Section -->
                    <div class="form-section">
                        <h3>Foto Resep</h3>
                        <div class="image-section">
                            <div class="current-image">
                                <strong>Foto Saat Ini:</strong>
                                <?php if ($recipe['image_url']): ?>
                                    <img src="../<?php echo htmlspecialchars($recipe['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                                <?php else: ?>
                                    <img src="/placeholder.svg?height=250&width=400" alt="No image" 
                                         style="background: #f0f0f0; border: 2px dashed #cbd5e0;">
                                <?php endif; ?>
                            </div>

                            <div class="image-upload-area">
                                <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                <div class="upload-placeholder">
                                    <h4>Ganti Foto Resep</h4>
                                    <p>Klik untuk memilih foto baru</p>
                                    <small class="form-help">Format: JPG, PNG, GIF, WebP. Maksimal 5MB</small>
                                </div>
                            </div>
                            <div id="imagePreview"></div>
                        </div>
                    </div>

                    <!-- Ingredients -->
                    <div class="form-section">
                        <h3>Bahan-bahan</h3>
                        <div class="form-group">
                            <label for="ingredients">Daftar Bahan <span class="required">*</span></label>
                            <textarea id="ingredients" name="ingredients" class="form-textarea" required rows="8" 
                                      placeholder="Tulis setiap bahan dalam baris terpisah, contoh:&#10;- 500g daging sapi&#10;- 2 buah bawang merah&#10;- 3 siung bawang putih"><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>
                            <small class="form-help">Tulis setiap bahan dalam baris terpisah dengan takaran yang jelas</small>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="form-section">
                        <h3>Cara Membuat</h3>
                        <div class="form-group">
                            <label for="instructions">Langkah-langkah <span class="required">*</span></label>
                            <textarea id="instructions" name="instructions" class="form-textarea" required rows="10" 
                                      placeholder="Tulis setiap langkah dalam baris terpisah, contoh:&#10;1. Potong daging menjadi kubus kecil&#10;2. Tumis bawang hingga harum&#10;3. Masukkan daging dan masak hingga berubah warna"><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>
                            <small class="form-help">Jelaskan setiap langkah dengan detail agar mudah diikuti</small>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="dashboard.php" class="btn btn-outline">
                            Kembali ke Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Update Resep
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
