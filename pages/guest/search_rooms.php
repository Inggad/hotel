<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Room.php';

$pageTitle = "Search Rooms";

// Check if user is logged in as guest
if (!User::isLoggedIn() || !User::isGuest()) {
    redirect('pages/guest/login.php');
}

$database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$roomObj = new Room($database);

$rooms = array();
$searched = false;
$checkInDate = '';
$checkOutDate = '';
$roomType = '';
$maxPrice = '';
$numGuests = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkInDate = trim($_POST['check_in_date'] ?? '');
    $checkOutDate = trim($_POST['check_out_date'] ?? '');
    $roomType = trim($_POST['room_type'] ?? '');
    $maxPrice = trim($_POST['max_price'] ?? '');
    $numGuests = trim($_POST['num_guests'] ?? '');
    
    $error = '';
    
    // Validation
    if (empty($checkInDate) || empty($checkOutDate)) {
        $error = 'Check-in and check-out dates are required.';
    } elseif (strtotime($checkInDate) >= strtotime($checkOutDate)) {
        $error = 'Check-out date must be after check-in date.';
    } elseif ($numGuests && $numGuests <= 0) {
        $error = 'Number of guests must be greater than 0.';
    } elseif ($maxPrice && $maxPrice < 0) {
        $error = 'Maximum price cannot be negative.';
    } else {
        $rooms = $roomObj->searchRooms($checkInDate, $checkOutDate, $roomType ?: null, $maxPrice ?: null, $numGuests ?: null);
        $searched = true;
    }
    
    if ($error) {
        setMessage($error, 'error');
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Search Rooms</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="check_in_date" class="form-label">Check-in Date *</label>
                        <input type="date" class="form-control" id="check_in_date" name="check_in_date" value="<?php echo $checkInDate; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="check_out_date" class="form-label">Check-out Date *</label>
                        <input type="date" class="form-control" id="check_out_date" name="check_out_date" value="<?php echo $checkOutDate; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="num_guests" class="form-label">Number of Guests</label>
                        <input type="number" class="form-control" id="num_guests" name="num_guests" min="1" value="<?php echo $numGuests; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="room_type" class="form-label">Room Type</label>
                        <select class="form-select" id="room_type" name="room_type">
                            <option value="">All Types</option>
                            <option value="single" <?php echo $roomType === 'single' ? 'selected' : ''; ?>>Single</option>
                            <option value="double" <?php echo $roomType === 'double' ? 'selected' : ''; ?>>Double</option>
                            <option value="suite" <?php echo $roomType === 'suite' ? 'selected' : ''; ?>>Suite</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="max_price" class="form-label">Maximum Price (Rp)</label>
                        <input type="number" class="form-control" id="max_price" name="max_price" min="0" value="<?php echo $maxPrice; ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Search Rooms</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <?php if ($searched): ?>
            <h4 class="mb-4">Available Rooms (<?php echo count($rooms); ?> found)</h4>
            
            <?php if (count($rooms) > 0): ?>
                <div class="row">
                    <?php foreach ($rooms as $room): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $room['room_type']; ?> Room - <?php echo $room['room_number']; ?></h5>
                                    <p class="text-muted"><?php echo $room['hotel_name']; ?></p>
                                    
                                    <div class="mb-3">
                                        <small><strong>Capacity:</strong> <?php echo $room['capacity']; ?> guests</small><br>
                                        <small><strong>Floor:</strong> <?php echo $room['floor_number']; ?></small><br>
                                        <small><strong>Price:</strong> <?php echo formatCurrency($room['price_per_night']); ?>/night</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Amenities:</strong>
                                        <p class="small"><?php echo $room['amenities']; ?></p>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <small><strong>Total Stay:</strong> <?php echo getDaysBetween($checkInDate, $checkOutDate); ?> nights<br>
                                        <strong>Total Cost:</strong> <?php echo formatCurrency($room['price_per_night'] * getDaysBetween($checkInDate, $checkOutDate)); ?></small>
                                    </div>
                                    
                                    <form method="POST" action="<?php echo SITE_URL; ?>pages/guest/booking.php">
                                        <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                        <input type="hidden" name="check_in_date" value="<?php echo $checkInDate; ?>">
                                        <input type="hidden" name="check_out_date" value="<?php echo $checkOutDate; ?>">
                                        <input type="hidden" name="num_guests" value="<?php echo $numGuests; ?>">
                                        <button type="submit" class="btn btn-success w-100">Book Now</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <strong>No rooms available</strong> for the selected dates and filters. Please try different criteria.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">
                Use the search form to find available rooms.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
