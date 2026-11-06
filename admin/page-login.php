
<?php
session_start();

require_once __DIR__ . "/config/database.php";

$error = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM penjual WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if ($data && $password === $data['password']) {
        $_SESSION['username'] = $data['username'];

        header("Location: index.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    
    <!-- SEO Optimization -->
    <meta name="description" content="Login Screen - Spark Admin Premium Bootstrap 5 Admin Dashboard Template">
    <meta name="author" content="Spark Admin Team">
    
    <!-- Favicon -->
    <link rel="icon" type="/image/png" href="../img/logo.png">
    
    <!-- Local Third-Party Libraries (100% Offline Compatible) -->
    <link rel="stylesheet" href="assets/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/libs/bootstrap-icons/bootstrap-icons.css">
    
    <!-- Main Design System & Custom Stylesheet -->
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

    <!-- ==========================================
         START: Authentication Container & Login Card
         ========================================== -->
    <div class="login-wrapper">
        <!-- Glowing background shapes for modern visual appearance -->
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>
        
        <!-- Main centered login card -->
        <div class="login-card">
            
            <!-- Brand Identity -->
            <div class="login-brand text-decoration-none">
                <i class="bi bi-asterisk"></i>
                <span>Radja Game</span>
            </div>
        <div class="login-down">
            <div class="login-brand text-decoration-none">
                <!-- <i class="bi bi-asterisk"></i> -->
                <span>Login</span>
            </div>
        </div>
            
            
            <!-- <p class="login-subtitle">Please sign in to access your dashboard</p> -->
            
            <!-- Login Form -->
            <form method="POST" id="loginForm" class="needs-validation" novalidate>
                
                <!-- Email Input Group -->
                <div class="login-form-group">
                    <label for="email" class="login-form-label">Username</label>
                    <div class="login-input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="text" name="username" id="email" class="login-input" placeholder="Masukan Username" required>
                    </div>
                </div>
                
                <!-- Password Input Group -->
                <div class="login-form-group">
                    <label for="password" class="login-form-label">Password</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" name="password" id="password" class="login-input login-input-password" placeholder="••••••••" required>
                        <button type="button" class="password-toggle-btn" id="toggle-password" aria-label="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Options (Remember me & Forgot Password) -->
                <!-- <div class="login-options">
                    <label class="custom-control-label">
                        <input type="checkbox" class="custom-checkbox-input" id="rememberMe">
                        <span>Remember Me</span>
                    </label>
                    <a href="#" class="forgot-password-link">Forgot Password?</a>
                </div> -->
                
                <!-- Submit Button -->
                <button type="submit" name="login" class="btn-login">
                    <span>Login</span>
                    <!-- <i class="bi bi-arrow-right"></i> -->
                </button>
            </form> <br>
            
            <!-- Divider -->
            <!-- <div class="login-divider">Or sign in with</div> -->
            
            <!-- Social Logins -->
            <!-- <div class="social-login-grid">
                <button class="btn-social" type="button" id="btn-google">
                    <i class="bi bi-google text-danger"></i>
                    <span>Google</span>
                </button>
                <button class="btn-social" type="button" id="btn-github">
                    <i class="bi bi-github"></i>
                    <span>GitHub</span>
                </button>
            </div> -->
            
            <!-- Footer Link -->
            <p class="login-footer-text">
                <!-- Belum Punya Akun? <a href="page-regist.php" id="link-register">Daftar Sekarang</a> -->
            </p>
            
        </div>
    </div>
    <!-- END: Authentication Container -->

    <!-- Local Bootstrap bundle -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Authentication interactions script -->
    <script src="assets/js/auth.js"></script>
</body>
</html>
