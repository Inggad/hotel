<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';

$pageTitle = "Manage Availability";

// Check if user is logged in as admin
if (!User::isLoggedIn() || !User::isAdmin()) {
    redirect('pages/guest/login.php');
}

$database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Get all rooms
$result = $database->query("SELECT r.*, h.hotel_name FROM Rooms r JOIN Hotels h ON r.hotel_id = h.hotel_id ORDER BY r.room_number ASC");
$rooms = $result->fetch_all(MYSQLI_ASSOC);

$error = '';
$success = '';

// Handle availability management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = intval($_POST['room_id'] ?? 0);
    $dateFrom = trim($_POST['date_from'] ?? '');
    $dateTo = trim($_POST['date_to'] ?? '');
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;
    
    // Validation
    if (!$roomId || !$dateFrom || !$dateTo) {
        $error = 'Please fill all required fields.';
    } elseif (strtotime($dateFrom) > strtotime($dateTo)) {
        $error = 'Start date must be before end date.';
    } else {
        // Generate date range and insert/update availability
        $currentDate = new DateTime($dateFrom);
        $endDate = new DateTime($dateTo);
        
        $inserted = 0;
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            
            // Check if record exists
            $stmt = $database->prepare("SELECT availability_id FROM Room_Availability WHERE room_id = ? AND available_date = ?");
            $stmt->bind_param("is", $roomId, $dateStr);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update
                $stmt = $database->prepare("UPDATE Room_Availability SET is_available = ? WHERE room_id = ? AND available_date = ?");
                $stmt->bind_param("iis", $isAvailable, $roomId, $dateStr);
            } else {
                // Insert
                $stmt = $database->prepare("INSERT INTO Room_Availability (room_id, available_date, is_available) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $roomId, $dateStr, $isAvailable);
            }
            
            $stmt->execute();
            $inserted++;
            $currentDate->modify('+1 day');
        }
        
        $success = "Updated availability for {$inserted} days.";
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<h2 class="mb-4">Manage Room Availability</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Set Availability</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="room_id" class="form-label">Select Room *</label>
                        <select class="form-select" id="room_id" name="room_id" required>
                            <option value="">-- Select Room --</option>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo $room['room_id']; ?>"><?php echo $room['room_number']; ?> - <?php echo $room['hotel_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date_from" class="form-label">From Date *</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date_to" class="form-label">To Date *</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" required>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_available" name="is_available" checked>
                        <label class="form-check-label" for="is_available">
                            Available (uncheck to mark as unavailable)
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Update Availability</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Instructions</h5>
            </div>
            <div class="card-body">
                <p><strong>Preventing Overbooking:</strong></p>
                <ul>
                    <li>Select a room from the dropdown</li>
                    <li>Choose a date range</li>
                    <li>Check "Available" to allow bookings (default)</li>
                    <li>Uncheck "Available" to block bookings for that period</li>
                    <li>Click "Update Availability" to apply changes</li>
                </ul>
                <p class="mt-3"><strong>Use Cases:</strong></p>
                <ul>
                    <li>Block rooms for maintenance</li>
                    <li>Mark seasonal closures</li>
                    <li>Prevent overlapping bookings</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
