<?php
require_once 'db.php';
require_once 'auth.php';

if (isUserLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $pincode = trim($_POST['pincode']);

    if (empty($full_name) || empty($email) || empty($mobile) || empty($password) || empty($cpassword)) {
        $error = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($password !== $cpassword) {
        $error = "Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, mobile, password, address, city, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $full_name, $email, $mobile, $hashed_password, $address, $city, $state, $pincode);
            if ($stmt->execute()) {
                $success = "Registration successful! Redirecting to login...";
                echo "<script>setTimeout(()=>window.location.href='login.php', 2000);</script>";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="container py-5 d-flex justify-content-center">
    <div class="auth-container w-100" style="max-width: 700px; margin: 40px auto;">
        <div class="auth-form" style="padding: 40px;">
            <div class="text-center mb-4">
                <img src="assets/images/logo.png" alt="JR Jersey Shop" height="80" style="filter: invert(1); margin-bottom: 15px;">
                <h3 style="font-weight: 800; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">JR_jersey shop</h3>
            </div>
            <h4 class="mb-4 text-center">Create an Account</h4>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mobile Number *</label>
                        <input type="text" name="mobile" class="form-control" pattern="[0-9]{10}" title="10 digit mobile number" required value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="cpassword" class="form-control" required minlength="8">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($_POST['state'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Pincode</label>
                        <input type="text" name="pincode" class="form-control" value="<?= htmlspecialchars($_POST['pincode'] ?? '') ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-custom w-100 py-2 mt-3">CREATE ACCOUNT</button>
                <div class="text-center mt-3">
                    Already have an account? <a href="login.php" style="color: var(--primary-color);">Login here</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
