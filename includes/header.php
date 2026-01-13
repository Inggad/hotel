<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">🏨 <?php echo SITE_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (\User::isLoggedIn()): ?>
                        <?php if (\User::isAdmin()): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>pages/admin/dashboard.php">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>pages/admin/view_bookings.php">Bookings</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>pages/admin/manage_rooms.php">Manage Rooms</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>pages/guest/search_rooms.php">Search Rooms</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>pages/guest/my_bookings.php">My Bookings</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>pages/common/logout.php">Logout (<?php echo $_SESSION['full_name']; ?>)</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>pages/guest/login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>pages/guest/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <main class="py-4">
        <div class="container">
            <?php
            $msg = getMessage();
            if ($msg):
            ?>
                <div class="alert alert-<?php echo $msg['type'] === 'success' ? 'success' : ($msg['type'] === 'error' ? 'danger' : 'info'); ?> alert-dismissible fade show" role="alert">
                    <?php echo $msg['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
