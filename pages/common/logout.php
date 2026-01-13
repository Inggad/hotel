<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';

// Check if user is logged in as admin
if (!User::isLoggedIn() || !User::isAdmin()) {
    redirect('pages/guest/login.php');
}

// Logout
session_destroy();
header("Location: " . SITE_URL);
exit;
?>
