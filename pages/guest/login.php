<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';

$pageTitle = "Login";
$database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$userObj = new User($database);

// If already logged in, redirect
if (User::isLoggedIn()) {
    if (User::isAdmin()) {
        redirect('pages/admin/dashboard.php');
    } else {
        redirect('pages/guest/search_rooms.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $result = $userObj->login($email, $password);
        if ($result['success']) {
            if (User::isAdmin()) {
                redirect('pages/admin/dashboard.php');
            } else {
                redirect('pages/guest/search_rooms.php');
            }
        } else {
            $error = $result['message'];
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Login</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <strong>Test Credentials:</strong><br>
                    Admin: admin@hotel.com / admin@123<br>
                    Guest: john@example.com / password123
                </div>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="<?php echo SITE_URL; ?>pages/guest/register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
