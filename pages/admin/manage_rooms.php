<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Room.php';

$pageTitle = "Manage Rooms";

// Check if user is logged in as admin
if (!User::isLoggedIn() || !User::isAdmin()) {
    redirect('pages/guest/login.php');
}

$database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$roomObj = new Room($database);

$rooms = $roomObj->getAllRooms();
$error = '';
$success = '';

// Handle add/edit room
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = intval($_POST['room_id'] ?? 0);
    $roomType = trim($_POST['room_type'] ?? '');
    $capacity = intval($_POST['capacity'] ?? 0);
    $pricePerNight = floatval($_POST['price_per_night'] ?? 0);
    $amenities = trim($_POST['amenities'] ?? '');
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;
    $floorNumber = intval($_POST['floor_number'] ?? 0);
    
    // Validation
    if (empty($roomType) || $capacity <= 0 || $pricePerNight <= 0) {
        $error = 'Invalid input. Please check all required fields.';
    } else {
        if ($roomId) {
            // Update room
            $result = $roomObj->updateRoom($roomId, $roomType, $capacity, $pricePerNight, $amenities, $isAvailable, $floorNumber);
        } else {
            $error = 'Cannot add new rooms from this interface. Contact database administrator.';
        }
        
        if (isset($result)) {
            if ($result['success']) {
                $success = $result['message'];
                $rooms = $roomObj->getAllRooms(); // Refresh list
            } else {
                $error = $result['message'];
            }
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<h2 class="mb-4">Manage Rooms</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Room Number</th>
                <th>Type</th>
                <th>Capacity</th>
                <th>Price/Night</th>
                <th>Floor</th>
                <th>Available</th>
                <th>Amenities</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rooms as $room): ?>
                <tr>
                    <td><strong><?php echo $room['room_number']; ?></strong></td>
                    <td><?php echo ucfirst($room['room_type']); ?></td>
                    <td><?php echo $room['capacity']; ?></td>
                    <td><?php echo formatCurrency($room['price_per_night']); ?></td>
                    <td><?php echo $room['floor_number']; ?></td>
                    <td>
                        <span class="badge bg-<?php echo $room['is_available'] ? 'success' : 'danger'; ?>">
                            <?php echo $room['is_available'] ? 'Yes' : 'No'; ?>
                        </span>
                    </td>
                    <td><?php echo substr($room['amenities'], 0, 30); ?>...</td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editRoom<?php echo $room['room_id']; ?>">Edit</button>
                    </td>
                </tr>
                
                <!-- Edit Room Modal -->
                <div class="modal fade" id="editRoom<?php echo $room['room_id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Room <?php echo $room['room_number']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                    
                                    <div class="mb-3">
                                        <label for="room_type<?php echo $room['room_id']; ?>" class="form-label">Room Type</label>
                                        <select class="form-select" id="room_type<?php echo $room['room_id']; ?>" name="room_type" required>
                                            <option value="single" <?php echo $room['room_type'] === 'single' ? 'selected' : ''; ?>>Single</option>
                                            <option value="double" <?php echo $room['room_type'] === 'double' ? 'selected' : ''; ?>>Double</option>
                                            <option value="suite" <?php echo $room['room_type'] === 'suite' ? 'selected' : ''; ?>>Suite</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="capacity<?php echo $room['room_id']; ?>" class="form-label">Capacity</label>
                                        <input type="number" class="form-control" id="capacity<?php echo $room['room_id']; ?>" name="capacity" min="1" value="<?php echo $room['capacity']; ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="price<?php echo $room['room_id']; ?>" class="form-label">Price per Night</label>
                                        <input type="number" class="form-control" id="price<?php echo $room['room_id']; ?>" name="price_per_night" min="0" step="0.01" value="<?php echo $room['price_per_night']; ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="floor<?php echo $room['room_id']; ?>" class="form-label">Floor Number</label>
                                        <input type="number" class="form-control" id="floor<?php echo $room['room_id']; ?>" name="floor_number" min="0" value="<?php echo $room['floor_number']; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="amenities<?php echo $room['room_id']; ?>" class="form-label">Amenities</label>
                                        <textarea class="form-control" id="amenities<?php echo $room['room_id']; ?>" name="amenities" rows="3"><?php echo $room['amenities']; ?></textarea>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="available<?php echo $room['room_id']; ?>" name="is_available" <?php echo $room['is_available'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="available<?php echo $room['room_id']; ?>">
                                            Available for Booking
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
