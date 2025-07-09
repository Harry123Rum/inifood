<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';
$showRegister = false;

// Handle login
if ($_POST && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi.';
    } else {
        try {
            $query = "SELECT id, username, email, password, role FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    header('Location: index.php');
                    exit();
                } else {
                    $error = 'Email atau password salah.';
                }
            } else {
                $error = 'Email atau password salah.';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Handle registration
if ($_POST && isset($_POST['register'])) {
    $showRegister = true; // Show register form if there's an error
    
    $username = trim($_POST['reg_username']);
    $email = trim($_POST['reg_email']);
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['reg_confirm_password'];
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Semua field harus diisi.';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak sama.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        try {
            // Check if email already exists
            $checkQuery = "SELECT id FROM users WHERE email = :email OR username = :username";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':email', $email);
            $checkStmt->bindParam(':username', $username);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $error = 'Email atau username sudah terdaftar.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $insertQuery = "INSERT INTO users (username, email, password, role, created_at) VALUES (:username, :email, :password, 'user', NOW())";
                $insertStmt = $db->prepare($insertQuery);
                $insertStmt->bindParam(':username', $username);
                $insertStmt->bindParam(':email', $email);
                $insertStmt->bindParam(':password', $hashedPassword);
                
                if ($insertStmt->execute()) {
                    $success = 'Registrasi berhasil! Silakan login dengan akun baru Anda.';
                    $showRegister = false; // Show login form after successful registration
                } else {
                    $error = 'Terjadi kesalahan saat menyimpan data: ' . implode(', ', $insertStmt->errorInfo());
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Recipe Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
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
        </nav>
    </header>

    <main class="auth-main">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Selamat Datang</h1>
                    <p>Masuk ke akun Anda atau daftar untuk memulai</p>
                </div>

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

                <div class="auth-tabs">
                    <button class="auth-tab <?php echo !$showRegister ? 'active' : ''; ?>" onclick="showLoginForm()">Masuk</button>
                    <button class="auth-tab <?php echo $showRegister ? 'active' : ''; ?>" onclick="showRegisterForm()">Daftar</button>
                </div>

                <!-- Login Form -->
                <form method="POST" class="auth-form <?php echo $showRegister ? 'hidden' : ''; ?>" id="loginForm">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-input" required>
                    </div>
                    
                    <button type="submit" name="login" class="btn btn-primary btn-full">Masuk</button>
                </form>

                <!-- Register Form -->
                <form method="POST" class="auth-form <?php echo !$showRegister ? 'hidden' : ''; ?>" id="registerForm">
                    <div class="form-group">
                        <label for="reg_username" class="form-label">Username</label>
                        <input type="text" id="reg_username" name="reg_username" class="form-input" required 
                               value="<?php echo isset($_POST['reg_username']) ? htmlspecialchars($_POST['reg_username']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_email" class="form-label">Email</label>
                        <input type="email" id="reg_email" name="reg_email" class="form-input" required
                               value="<?php echo isset($_POST['reg_email']) ? htmlspecialchars($_POST['reg_email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_password" class="form-label">Password</label>
                        <input type="password" id="reg_password" name="reg_password" class="form-input" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_confirm_password" class="form-label">Konfirmasi Password</label>
                        <input type="password" id="reg_confirm_password" name="reg_confirm_password" class="form-input" required minlength="6">
                    </div>
                    
                    <button type="submit" name="register" class="btn btn-primary btn-full">Daftar</button>
                </form>

                <div class="auth-footer">
                    <p>Admin? Login dengan email: admin@recipe.com, password: password</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        function showLoginForm() {
            document.getElementById('loginForm').classList.remove('hidden');
            document.getElementById('registerForm').classList.add('hidden');
            document.querySelectorAll('.auth-tab')[0].classList.add('active');
            document.querySelectorAll('.auth-tab')[1].classList.remove('active');
        }

        function showRegisterForm() {
            document.getElementById('loginForm').classList.add('hidden');
            document.getElementById('registerForm').classList.remove('hidden');
            document.querySelectorAll('.auth-tab')[0].classList.remove('active');
            document.querySelectorAll('.auth-tab')[1].classList.add('active');
        }

        // Show register form if there was a registration error
        <?php if ($showRegister): ?>
        showRegisterForm();
        <?php endif; ?>
    </script>
</body>
</html>
