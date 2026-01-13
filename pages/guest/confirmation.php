<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Booking.php';
require_once __DIR__ . '/../../classes/Payment.php';

$pageTitle = "Booking Confirmation";

// Check if user is logged in as guest
if (!User::isLoggedIn() || !User::isGuest()) {
    redirect('pages/guest/login.php');
}

$database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$bookingObj = new Booking($database);
$paymentObj = new Payment($database);

$bookingId = $_GET['booking_id'] ?? '';
$booking = $bookingObj->getBookingById($bookingId);
$payment = $paymentObj->getPaymentByBookingId($bookingId);

if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
    redirect('pages/guest/search_rooms.php');
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow border-success">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">✓ Booking Confirmed</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    Thank you! Your booking has been confirmed successfully.
                </div>
                
                <h5 class="mb-3">Booking Details</h5>
                <table class="table">
                    <tr>
                        <th>Booking ID:</th>
                        <td><strong><?php echo $booking['booking_id']; ?></strong></td>
                    </tr>
                    <tr>
                        <th>Guest Name:</th>
                        <td><?php echo $booking['full_name']; ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo $booking['email']; ?></td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td><?php echo $booking['phone']; ?></td>
                    </tr>
                </table>
                
                <h5 class="mb-3">Room Information</h5>
                <table class="table">
                    <tr>
                        <th>Hotel:</th>
                        <td><?php echo $booking['hotel_name']; ?></td>
                    </tr>
                    <tr>
                        <th>Room Number:</th>
                        <td><?php echo $booking['room_number']; ?></td>
                    </tr>
                    <tr>
                        <th>Room Type:</th>
                        <td><?php echo ucfirst($booking['room_type']); ?></td>
                    </tr>
                </table>
                
                <h5 class="mb-3">Stay Details</h5>
                <table class="table">
                    <tr>
                        <th>Check-in Date:</th>
                        <td><?php echo formatDate($booking['check_in_date']); ?></td>
                    </tr>
                    <tr>
                        <th>Check-out Date:</th>
                        <td><?php echo formatDate($booking['check_out_date']); ?></td>
                    </tr>
                    <tr>
                        <th>Number of Nights:</th>
                        <td><?php echo getDaysBetween($booking['check_in_date'], $booking['check_out_date']); ?></td>
                    </tr>
                    <tr>
                        <th>Number of Guests:</th>
                        <td><?php echo $booking['num_guests']; ?></td>
                    </tr>
                    <?php if ($booking['special_requests']): ?>
                    <tr>
                        <th>Special Requests:</th>
                        <td><?php echo $booking['special_requests']; ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
                
                <h5 class="mb-3">Payment Information</h5>
                <table class="table">
                    <tr>
                        <th>Payment Method:</th>
                        <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                    </tr>
                    <tr>
                        <th>Payment Status:</th>
                        <td>
                            <span class="badge bg-success"><?php echo ucfirst($payment['payment_status']); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th>Payment Date:</th>
                        <td><?php echo formatDateTime($payment['payment_date']); ?></td>
                    </tr>
                    <tr class="table-active">
                        <th>Total Amount:</th>
                        <td><strong><?php echo formatCurrency($booking['total_price']); ?></strong></td>
                    </tr>
                </table>
                
                <h5 class="mb-3">Booking Status</h5>
                <table class="table">
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-info"><?php echo ucfirst($booking['booking_status']); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th>Booking Date:</th>
                        <td><?php echo formatDateTime($booking['booking_date']); ?></td>
                    </tr>
                </table>
                
                <div class="mt-4">
                    <button class="btn btn-primary" onclick="window.print()">Print Confirmation</button>
                    <a href="<?php echo SITE_URL; ?>pages/guest/my_bookings.php" class="btn btn-info">View My Bookings</a>
                    <a href="<?php echo SITE_URL; ?>pages/guest/search_rooms.php" class="btn btn-secondary">Book Another Room</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
