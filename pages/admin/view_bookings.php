<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Booking.php';

$pageTitle = "View Bookings";

// Check if user is logged in as admin
if (!User::isLoggedIn() || !User::isAdmin()) {
    redirect('pages/guest/login.php');
}

$database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$bookingObj = new Booking($database);

$statusFilter = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$bookings = $bookingObj->getAllBookings($statusFilter ?: null, $dateFrom ?: null, $dateTo ?: null);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = intval($_POST['booking_id'] ?? 0);
    $newStatus = trim($_POST['new_status'] ?? '');
    
    if ($bookingId && $newStatus) {
        $result = $bookingObj->updateBookingStatus($bookingId, $newStatus);
        setMessage($result['message'], $result['success'] ? 'success' : 'error');
        header("Refresh:0");
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<h2 class="mb-4">View Bookings</h2>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Bookings</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $dateFrom; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $dateTo; ?>">
            </div>
            
            <div class="col-md-3">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Bookings (<?php echo count($bookings); ?> found)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($bookings) > 0): ?>
                        <?php foreach ($bookings as $booking): ?>
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
                                <td>
                                    <?php if ($booking['booking_status'] !== 'cancelled' && $booking['booking_status'] !== 'completed'): ?>
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                            <select name="new_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="">Change Status</option>
                                                <option value="pending">Pending</option>
                                                <option value="confirmed">Confirmed</option>
                                                <option value="cancelled">Cancelled</option>
                                                <option value="completed">Completed</option>
                                            </select>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No bookings found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
