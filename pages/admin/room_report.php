<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';

$pageTitle = "Room Status Report";

// Check if user is logged in as admin
if (!User::isLoggedIn() || !User::isAdmin()) {
    redirect('pages/guest/login.php');
}

$database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Get room report with current status and revenue
$result = $database->query("
    SELECT 
        r.room_id,
        r.room_number,
        r.room_type,
        r.capacity,
        h.hotel_name,
        r.price_per_night,
        CASE WHEN EXISTS (
            SELECT 1 FROM Bookings b 
            WHERE b.room_id = r.room_id 
            AND b.booking_status IN ('confirmed', 'completed')
            AND b.check_in_date <= CURDATE()
            AND b.check_out_date > CURDATE()
        ) THEN 'Occupied' ELSE 'Available' END as current_status,
        (SELECT u.full_name FROM Bookings b 
         JOIN Users u ON b.user_id = u.user_id
         WHERE b.room_id = r.room_id 
         AND b.booking_status IN ('confirmed', 'completed')
         AND b.check_in_date <= CURDATE()
         AND b.check_out_date > CURDATE()
         LIMIT 1) as current_guest,
        (SELECT DATE(b.check_out_date) FROM Bookings b
         WHERE b.room_id = r.room_id
         AND b.booking_status IN ('confirmed', 'completed')
         AND b.check_in_date > CURDATE()
         ORDER BY b.check_in_date ASC
         LIMIT 1) as next_booking_date,
        (SELECT COALESCE(SUM(b.total_price), 0) FROM Bookings b
         WHERE b.room_id = r.room_id
         AND b.booking_status IN ('confirmed', 'completed')
         AND MONTH(b.check_in_date) = MONTH(CURDATE())
         AND YEAR(b.check_in_date) = YEAR(CURDATE())) as revenue_this_month
    FROM Rooms r
    JOIN Hotels h ON r.hotel_id = h.hotel_id
    ORDER BY r.room_number ASC
");

$rooms = $result->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>

<h2 class="mb-4">Room Status Report</h2>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Room</th>
                <th>Type</th>
                <th>Hotel</th>
                <th>Capacity</th>
                <th>Price/Night</th>
                <th>Current Status</th>
                <th>Current Guest</th>
                <th>Next Booking</th>
                <th>Revenue (This Month)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rooms as $room): ?>
                <tr>
                    <td><strong><?php echo $room['room_number']; ?></strong></td>
                    <td><?php echo ucfirst($room['room_type']); ?></td>
                    <td><?php echo $room['hotel_name']; ?></td>
                    <td><?php echo $room['capacity']; ?> guests</td>
                    <td><?php echo formatCurrency($room['price_per_night']); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $room['current_status'] === 'Occupied' ? 'danger' : 'success'; ?>">
                            <?php echo $room['current_status']; ?>
                        </span>
                    </td>
                    <td><?php echo $room['current_guest'] ? $room['current_guest'] : '-'; ?></td>
                    <td><?php echo $room['next_booking_date'] ? formatDate($room['next_booking_date']) : 'No booking'; ?></td>
                    <td><?php echo formatCurrency($room['revenue_this_month']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>Total Rooms</h5>
                <h3><?php echo count($rooms); ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>Total Revenue (This Month)</h5>
                <h3><?php 
                    $totalRevenue = 0;
                    foreach ($rooms as $room) {
                        $totalRevenue += $room['revenue_this_month'];
                    }
                    echo formatCurrency($totalRevenue);
                ?></h3>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
