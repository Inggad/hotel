<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Booking.php';
require_once __DIR__ . '/../../classes/Room.php';

$pageTitle = "Admin Dashboard";

// Check if user is logged in as admin
if (!User::isLoggedIn() || !User::isAdmin()) {
    redirect('pages/guest/login.php');
}

$database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$bookingObj = new Booking($database);
$roomObj = new Room($database);

$stats = $bookingObj->getBookingStats();

// Get occupancy rate
$result = $database->query("SELECT COUNT(*) as count FROM Rooms");
$totalRooms = $result->fetch_assoc()['count'];

$result = $database->query("SELECT COUNT(DISTINCT room_id) as count FROM Bookings 
                            WHERE booking_status IN ('confirmed', 'completed')
                            AND check_in_date <= CURDATE()
                            AND check_out_date > CURDATE()");
$occupiedRooms = $result->fetch_assoc()['count'];
$occupancyRate = $totalRooms > 0 ? ($occupiedRooms / $totalRooms) * 100 : 0;

// Get recent bookings
$result = $database->query("SELECT b.*, u.full_name, r.room_number, h.hotel_name
                            FROM Bookings b
                            JOIN Users u ON b.user_id = u.user_id
                            JOIN Rooms r ON b.room_id = r.room_id
                            JOIN Hotels h ON r.hotel_id = h.hotel_id
                            ORDER BY b.booking_date DESC
                            LIMIT 10");
$recentBookings = $result->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>

<h2 class="mb-4">Admin Dashboard</h2>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Bookings (This Month)</h5>
                <h2><?php echo $stats['bookings_this_month']; ?></h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <h2><?php echo formatCurrency($stats['total_revenue']); ?></h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Occupancy Rate</h5>
                <h2><?php echo number_format($occupancyRate, 1); ?>%</h2>
                <small><?php echo $occupiedRooms; ?> of <?php echo $totalRooms; ?> rooms</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Pending Payments</h5>
                <h2><?php echo $stats['pending_payments']; ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>Average Booking Value</h5>
                <h3><?php echo formatCurrency($stats['avg_booking_value']); ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>Bookings This Week</h5>
                <h3><?php echo $stats['bookings_this_week']; ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Recent Bookings</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Booking ID</th>
                        <th>Guest Name</th>
                        <th>Room</th>
                        <th>Hotel</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBookings as $booking): ?>
                        <tr>
                            <td><strong><?php echo $booking['booking_id']; ?></strong></td>
                            <td><?php echo $booking['full_name']; ?></td>
                            <td><?php echo $booking['room_number']; ?></td>
                            <td><?php echo $booking['hotel_name']; ?></td>
                            <td><?php echo formatDate($booking['check_in_date']); ?></td>
                            <td><?php echo formatDate($booking['check_out_date']); ?></td>
                            <td>
                                <?php
                                $statusColor = 'secondary';
                                if ($booking['booking_status'] === 'confirmed') $statusColor = 'success';
                                elseif ($booking['booking_status'] === 'pending') $statusColor = 'warning';
                                elseif ($booking['booking_status'] === 'cancelled') $statusColor = 'danger';
                                elseif ($booking['booking_status'] === 'completed') $statusColor = 'info';
                                ?>
                                <span class="badge bg-<?php echo $statusColor; ?>"><?php echo ucfirst($booking['booking_status']); ?></span>
                            </td>
                            <td><?php echo formatCurrency($booking['total_price']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="text-center mt-4">
    <a href="<?php echo SITE_URL; ?>pages/admin/view_bookings.php" class="btn btn-primary">View All Bookings</a>
    <a href="<?php echo SITE_URL; ?>pages/admin/manage_rooms.php" class="btn btn-info">Manage Rooms</a>
    <a href="<?php echo SITE_URL; ?>pages/admin/room_report.php" class="btn btn-secondary">Room Report</a>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
