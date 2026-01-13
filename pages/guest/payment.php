<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Booking.php';
require_once __DIR__ . '/../../classes/Payment.php';

$pageTitle = "Payment";

// Check if user is logged in as guest
if (!User::isLoggedIn() || !User::isGuest()) {
    redirect('pages/guest/login.php');
}

$database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$bookingObj = new Booking($database);
$paymentObj = new Payment($database);

$bookingId = $_GET['booking_id'] ?? '';
$booking = $bookingObj->getBookingById($bookingId);

if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
    redirect('pages/guest/search_rooms.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    
    // Validation
    if (empty($paymentMethod)) {
        $error = 'Please select a payment method.';
    } else {
        // Create payment
        $result = $paymentObj->createPayment($bookingId, $booking['total_price'], $paymentMethod);
        
        if ($result['success']) {
            // Redirect to confirmation
            header("Location: " . SITE_URL . "pages/guest/confirmation.php?booking_id=" . $bookingId);
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
                <h4 class="mb-0">Payment</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5>Booking Summary</h5>
                    <table class="table table-sm mb-0">
                        <tr>
                            <th>Booking ID:</th>
                            <td><?php echo $booking['booking_id']; ?></td>
                        </tr>
                        <tr>
                            <th>Room:</th>
                            <td><?php echo $booking['room_number']; ?> (<?php echo ucfirst($booking['room_type']); ?>)</td>
                        </tr>
                        <tr>
                            <th>Hotel:</th>
                            <td><?php echo $booking['hotel_name']; ?></td>
                        </tr>
                        <tr>
                            <th>Check-in:</th>
                            <td><?php echo formatDate($booking['check_in_date']); ?></td>
                        </tr>
                        <tr>
                            <th>Check-out:</th>
                            <td><?php echo formatDate($booking['check_out_date']); ?></td>
                        </tr>
                        <tr class="table-active">
                            <th>Total Amount to Pay:</th>
                            <td><strong><?php echo formatCurrency($booking['total_price']); ?></strong></td>
                        </tr>
                    </table>
                </div>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method *</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    
                    <div id="card_details" class="mb-3" style="display:none;">
                        <label for="card_number" class="form-label">Card Number</label>
                        <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                        
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label for="card_expiry" class="form-label">Expiry (MM/YY)</label>
                                <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="12/25" maxlength="5">
                            </div>
                            <div class="col-md-6">
                                <label for="card_cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" id="card_cvv" name="card_cvv" placeholder="123" maxlength="3">
                            </div>
                        </div>
                    </div>
                    
                    <div id="bank_details" class="mb-3" style="display:none;">
                        <label for="bank_account" class="form-label">Bank Account Number</label>
                        <input type="text" class="form-control" id="bank_account" name="bank_account" placeholder="Enter your bank account">
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirm_payment" required>
                        <label class="form-check-label" for="confirm_payment">
                            I confirm the payment of <?php echo formatCurrency($booking['total_price']); ?>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg w-100">Complete Payment</button>
                    <a href="<?php echo SITE_URL; ?>pages/guest/search_rooms.php" class="btn btn-secondary btn-lg w-100 mt-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('payment_method').addEventListener('change', function() {
    document.getElementById('card_details').style.display = this.value === 'credit_card' || this.value === 'debit_card' ? 'block' : 'none';
    document.getElementById('bank_details').style.display = this.value === 'bank_transfer' ? 'block' : 'none';
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
