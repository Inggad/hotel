<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/User.php';

$pageTitle = "Home";

include __DIR__ . '/includes/header.php';
?>

<div class="row align-items-center mb-5">
    <div class="col-md-8">
        <h1 class="display-4 mb-4">Welcome to Luxury Paradise Hotel</h1>
        <p class="lead mb-4">
            Book your perfect stay with us. Choose from our wide selection of luxurious rooms 
            and enjoy world-class amenities and services.
        </p>
        
        <?php if (!User::isLoggedIn()): ?>
            <div class="btn-group" role="group">
                <a href="<?php echo SITE_URL; ?>pages/guest/login.php" class="btn btn-primary btn-lg">Login</a>
                <a href="<?php echo SITE_URL; ?>pages/guest/register.php" class="btn btn-success btn-lg">Register Now</a>
            </div>
        <?php else: ?>
            <?php if (User::isAdmin()): ?>
                <a href="<?php echo SITE_URL; ?>pages/admin/dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>pages/guest/search_rooms.php" class="btn btn-primary btn-lg">Search Rooms</a>
                <a href="<?php echo SITE_URL; ?>pages/guest/my_bookings.php" class="btn btn-info btn-lg">My Bookings</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h3 class="card-title">🏨 Hotel Paradise</h3>
                <p class="card-text">
                    <strong>20 Rooms</strong><br>
                    Single, Double & Suite<br>
                    ⭐⭐⭐⭐☆ (4.5/5)
                </p>
                <p class="text-muted">
                    123 Main Street, Jakarta
                </p>
            </div>
        </div>
    </div>
</div>

<hr>

<h2 class="mb-4">Our Services</h2>

<div class="row mb-5">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3>🌟 Easy Booking</h3>
                <p class="card-text">Simple and secure online booking system. Find and book your perfect room in minutes.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3>💳 Secure Payment</h3>
                <p class="card-text">Multiple payment options including credit card, debit card, and bank transfer.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3>✨ Premium Rooms</h3>
                <p class="card-text">Luxurious rooms with modern amenities, WiFi, air conditioning, and more.</p>
            </div>
        </div>
    </div>
</div>

<h2 class="mb-4">Room Types</h2>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Single Room</h5>
            </div>
            <div class="card-body">
                <p><strong>Capacity:</strong> 1 guest</p>
                <p><strong>Starting from:</strong> <strong>Rp 500,000/night</strong></p>
                <p class="text-muted">Perfect for solo travelers. Includes WiFi, Air Conditioning, Flat TV, Mini Bar.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Double Room</h5>
            </div>
            <div class="card-body">
                <p><strong>Capacity:</strong> 2 guests</p>
                <p><strong>Starting from:</strong> <strong>Rp 800,000/night</strong></p>
                <p class="text-muted">Great for couples. Includes WiFi, Air Conditioning, King Bed, Mini Bar.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Suite Room</h5>
            </div>
            <div class="card-body">
                <p><strong>Capacity:</strong> 4 guests</p>
                <p><strong>Starting from:</strong> <strong>Rp 1,500,000/night</strong></p>
                <p class="text-muted">Luxury living. Includes Living Room, Kitchen, City View, Balcony.</p>
            </div>
        </div>
    </div>
</div>

<div class="mt-5 p-4 bg-primary text-white rounded">
    <h4>Test Account Credentials</h4>
    <p>Admin: <strong>admin@hotel.com</strong> / <strong>admin@123</strong></p>
    <p>Guest: <strong>john@example.com</strong> / <strong>password123</strong></p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
