<?php
require_once 'db.php';
require_once 'auth.php';

if (isUserLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter email/username and password.";
    } else {
        // 1. Check if it's an Admin
        $stmt_admin = $conn->prepare("SELECT id, password FROM admins WHERE username = ? OR email = ?");
        $stmt_admin->bind_param("ss", $email, $email);
        $stmt_admin->execute();
        $res_admin = $stmt_admin->get_result();

        if ($row_admin = $res_admin->fetch_assoc()) {
            if (password_verify($password, $row_admin['password'])) {
                $_SESSION['admin_id'] = $row_admin['id'];
                header("Location: admin/dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            // 2. Not an Admin, check if it's a Customer
            $stmt_user = $conn->prepare("SELECT id, full_name, password, status FROM users WHERE email = ?");
            $stmt_user->bind_param("s", $email);
            $stmt_user->execute();
            $res_user = $stmt_user->get_result();

            if ($row_user = $res_user->fetch_assoc()) {
                if ($row_user['status'] === 'inactive') {
                    $error = "Your account has been disabled. Please contact support.";
                } elseif (password_verify($password, $row_user['password'])) {
                    $_SESSION['user_id'] = $row_user['id'];
                    $_SESSION['user_name'] = $row_user['full_name'];
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Invalid email or password.";
            }
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="container py-5 d-flex justify-content-center">
    <div class="auth-container w-100" style="max-width: 500px; margin: 40px auto;">
        <div class="auth-form" style="padding: 40px;">
            <div class="text-center mb-4">
                <img src="assets/images/logo.png" alt="JR Jersey Shop" height="80" style="filter: invert(1); margin-bottom: 15px;">
                <h3 style="font-weight: 800; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">JR_jersey shop</h3>
            </div>
            <h4 class="mb-2 text-center">Welcome Back!</h4>
            <p class="text-secondary mb-4 text-center">Login to continue shopping.</p>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Email / Username</label>
                    <input type="text" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember Me</label>
                    </div>
                    <a href="#" style="color: var(--primary-color); font-size: 0.9rem;">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-custom w-100 py-2">LOGIN</button>
                
                <div class="text-center mt-4">
                    Don't have an account? <a href="register.php" style="color: var(--primary-color);">CREATE ACCOUNT</a>
                </div>
                <div class="text-center mt-3">
                    <a href="index.php" class="text-secondary">Continue as Guest</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
