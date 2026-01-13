<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Booking.php';

$pageTitle = "My Bookings";

// Check if user is logged in as guest
if (!User::isLoggedIn() || !User::isGuest()) {
    redirect('pages/guest/login.php');
}

$database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$bookingObj = new Booking($database);

$bookings = $bookingObj->getBookingsByUserId($_SESSION['user_id']);

// Handle cancel booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = intval($_POST['booking_id'] ?? 0);
    if ($bookingId) {
        $result = $bookingObj->cancelBooking($bookingId);
        setMessage($result['message'], $result['success'] ? 'success' : 'error');
        header("Refresh:0");
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<h2 class="mb-4">My Bookings</h2>

<?php if (count($bookings) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Booking ID</th>
                    <th>Room</th>
                    <th>Hotel</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Status</th>
                    <th>Total Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><strong><?php echo $booking['booking_id']; ?></strong></td>
                        <td><?php echo $booking['room_number']; ?> (<?php echo ucfirst($booking['room_type']); ?>)</td>
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
                        <td>
                            <a href="<?php echo SITE_URL; ?>pages/guest/confirmation.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-info">View</a>
                            
                            <?php if ($booking['booking_status'] === 'pending' || $booking['booking_status'] === 'confirmed'): ?>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <strong>No bookings yet.</strong> <a href="<?php echo SITE_URL; ?>pages/guest/search_rooms.php">Start by searching for rooms</a>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
