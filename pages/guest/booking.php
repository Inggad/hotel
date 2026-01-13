<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Room.php';
require_once __DIR__ . '/../../classes/Booking.php';

$pageTitle = "Booking";

// Check if user is logged in as guest
if (!User::isLoggedIn() || !User::isGuest()) {
    redirect('pages/guest/login.php');
}

$database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$roomObj = new Room($database);
$bookingObj = new Booking($database);

// Get POST data
$roomId = $_POST['room_id'] ?? '';
$checkInDate = $_POST['check_in_date'] ?? '';
$checkOutDate = $_POST['check_out_date'] ?? '';
$numGuests = $_POST['num_guests'] ?? '';

// Get room details
$room = $roomObj->getRoomById($roomId);
if (!$room) {
    redirect('pages/guest/search_rooms.php');
}

$error = '';
$totalPrice = Booking::calculateTotalPrice($room['price_per_night'], $checkInDate, $checkOutDate);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specialRequests = trim($_POST['special_requests'] ?? '');
    $processedNumGuests = intval($_POST['num_guests'] ?? 1);
    
    // Validation
    if (empty($checkInDate) || empty($checkOutDate)) {
        $error = 'Invalid dates.';
    } elseif ($processedNumGuests <= 0) {
        $error = 'Invalid number of guests.';
    } else {
        // Create booking
        $result = $bookingObj->createBooking($_SESSION['user_id'], $roomId, $checkInDate, $checkOutDate, $processedNumGuests, $totalPrice, $specialRequests);
        
        if ($result['success']) {
            // Redirect to payment
            header("Location: " . SITE_URL . "pages/guest/payment.php?booking_id=" . $result['booking_id']);
            exit;
        } else {
            $error = $result['message'];
        }
    }
    
    if ($error) {
        setMessage($error, 'error');
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Complete Your Booking</h4>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Room Details</h5>
                        <table class="table table-sm">
                            <tr>
                                <th>Room Number:</th>
                                <td><?php echo $room['room_number']; ?></td>
                            </tr>
                            <tr>
                                <th>Room Type:</th>
                                <td><?php echo ucfirst($room['room_type']); ?></td>
                            </tr>
                            <tr>
                                <th>Capacity:</th>
                                <td><?php echo $room['capacity']; ?> guests</td>
                            </tr>
                            <tr>
                                <th>Hotel:</th>
                                <td><?php echo $room['hotel_name']; ?></td>
                            </tr>
                            <tr>
                                <th>Price per Night:</th>
                                <td><?php echo formatCurrency($room['price_per_night']); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Booking Details</h5>
                        <table class="table table-sm">
                            <tr>
                                <th>Check-in:</th>
                                <td><?php echo formatDate($checkInDate); ?></td>
                            </tr>
                            <tr>
                                <th>Check-out:</th>
                                <td><?php echo formatDate($checkOutDate); ?></td>
                            </tr>
                            <tr>
                                <th>Number of Nights:</th>
                                <td><?php echo getDaysBetween($checkInDate, $checkOutDate); ?></td>
                            </tr>
                            <tr>
                                <th>Number of Guests:</th>
                                <td><?php echo $numGuests; ?></td>
                            </tr>
                            <tr class="table-active">
                                <th>Total Price:</th>
                                <td><strong><?php echo formatCurrency($totalPrice); ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="room_id" value="<?php echo $roomId; ?>">
                    <input type="hidden" name="check_in_date" value="<?php echo $checkInDate; ?>">
                    <input type="hidden" name="check_out_date" value="<?php echo $checkOutDate; ?>">
                    
                    <div class="mb-3">
                        <label for="num_guests" class="form-label">Number of Guests *</label>
                        <input type="number" class="form-control" id="num_guests" name="num_guests" min="1" max="<?php echo $room['capacity']; ?>" value="<?php echo $numGuests; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="special_requests" class="form-label">Special Requests</label>
                        <textarea class="form-control" id="special_requests" name="special_requests" rows="3" placeholder="e.g., High floor, Non-smoking room, etc."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the booking terms and conditions
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg w-100">Proceed to Payment</button>
                    <a href="<?php echo SITE_URL; ?>pages/guest/search_rooms.php" class="btn btn-secondary btn-lg w-100 mt-2">Back to Search</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
