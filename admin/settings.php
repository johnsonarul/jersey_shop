<?php
require_once '../db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $store_name = trim($_POST['store_name']);
    $phone = trim($_POST['phone']);
    $whatsapp = trim($_POST['whatsapp']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    
    $stmt = $conn->prepare("UPDATE settings SET store_name=?, phone=?, whatsapp=?, email=?, address=? WHERE id=1");
    $stmt->bind_param("sssss", $store_name, $phone, $whatsapp, $email, $address);
    if ($stmt->execute()) {
        $success = "Settings updated successfully!";
    } else {
        $error = "Failed to update settings.";
    }
}

$res = $conn->query("SELECT * FROM settings WHERE id = 1");
$settings = $res->fetch_assoc();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Global Settings</h2>
</div>

<?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<div class="bg-card p-4 rounded shadow-sm max-w-800">
    <form method="POST" action="">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Store Name</label>
                <input type="text" name="store_name" class="form-control" value="<?= htmlspecialchars($settings['store_name'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($settings['email'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($settings['phone'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">WhatsApp Number</label>
                <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($settings['whatsapp'] ?? '') ?>" required>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label">Store Address</label>
                <textarea name="address" class="form-control" rows="3" required><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary px-5 py-2 mt-3">Save Settings</button>
    </form>
</div>

<?php include 'footer.php'; ?>
