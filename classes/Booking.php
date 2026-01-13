<?php
require_once __DIR__ . '/../config/database.php';

class Booking {
    private $db;
    private $table = 'Bookings';
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create new booking
     */
    public function createBooking($userId, $roomId, $checkInDate, $checkOutDate, $numGuests, $totalPrice, $specialRequests = '') {
        try {
            $bookingStatus = 'pending';
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (user_id, room_id, check_in_date, check_out_date, num_guests, booking_status, total_price, special_requests)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissiids", $userId, $roomId, $checkInDate, $checkOutDate, $numGuests, $bookingStatus, $totalPrice, $specialRequests);
            
            if ($stmt->execute()) {
                return array('success' => true, 'message' => 'Booking created successfully', 'booking_id' => $this->db->lastInsertId());
            } else {
                return array('success' => false, 'message' => 'Failed to create booking');
            }
        } catch (Exception $e) {
            error_log("Create booking error: " . $e->getMessage());
            return array('success' => false, 'message' => 'An error occurred');
        }
    }
    
    /**
     * Get booking by ID
     */
    public function getBookingById($bookingId) {
        try {
            $stmt = $this->db->prepare("SELECT b.*, u.full_name, u.email, u.phone, r.room_number, r.room_type, h.hotel_name
                                        FROM {$this->table} b
                                        JOIN Users u ON b.user_id = u.user_id
                                        JOIN Rooms r ON b.room_id = r.room_id
                                        JOIN Hotels h ON r.hotel_id = h.hotel_id
                                        WHERE b.booking_id = ?");
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            error_log("Get booking error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get bookings by user ID
     */
    public function getBookingsByUserId($userId) {
        try {
            $stmt = $this->db->prepare("SELECT b.*, r.room_number, r.room_type, h.hotel_name
                                        FROM {$this->table} b
                                        JOIN Rooms r ON b.room_id = r.room_id
                                        JOIN Hotels h ON r.hotel_id = h.hotel_id
                                        WHERE b.user_id = ?
                                        ORDER BY b.booking_date DESC");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Get user bookings error: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Get all bookings (for admin)
     */
    public function getAllBookings($statusFilter = null, $dateFrom = null, $dateTo = null) {
        try {
            $sql = "SELECT b.*, u.full_name, u.email, r.room_number, r.room_type, h.hotel_name
                    FROM {$this->table} b
                    JOIN Users u ON b.user_id = u.user_id
                    JOIN Rooms r ON b.room_id = r.room_id
                    JOIN Hotels h ON r.hotel_id = h.hotel_id
                    WHERE 1=1";
            
            $params = array();
            $types = "";
            
            if ($statusFilter) {
                $sql .= " AND b.booking_status = ?";
                $params[] = $statusFilter;
                $types .= "s";
            }
            
            if ($dateFrom && $dateTo) {
                $sql .= " AND b.check_in_date >= ? AND b.check_in_date <= ?";
                $params[] = $dateFrom;
                $params[] = $dateTo;
                $types .= "ss";
            }
            
            $sql .= " ORDER BY b.booking_date DESC";
            
            $stmt = $this->db->prepare($sql);
            
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Get all bookings error: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Update booking status
     */
    public function updateBookingStatus($bookingId, $status) {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET booking_status = ? WHERE booking_id = ?");
            $stmt->bind_param("si", $status, $bookingId);
            
            if ($stmt->execute()) {
                return array('success' => true, 'message' => 'Booking status updated');
            } else {
                return array('success' => false, 'message' => 'Failed to update booking status');
            }
        } catch (Exception $e) {
            error_log("Update booking status error: " . $e->getMessage());
            return array('success' => false, 'message' => 'An error occurred');
        }
    }
    
    /**
     * Cancel booking
     */
    public function cancelBooking($bookingId) {
        try {
            return $this->updateBookingStatus($bookingId, 'cancelled');
        } catch (Exception $e) {
            error_log("Cancel booking error: " . $e->getMessage());
            return array('success' => false, 'message' => 'An error occurred');
        }
    }
    
    /**
     * Calculate total price
     */
    public static function calculateTotalPrice($pricePerNight, $checkInDate, $checkOutDate) {
        $startDate = new DateTime($checkInDate);
        $endDate = new DateTime($checkOutDate);
        $interval = $startDate->diff($endDate);
        $nights = $interval->days;
        
        return $nights * $pricePerNight;
    }
    
    /**
     * Get booking statistics
     */
    public function getBookingStats() {
        try {
            $stats = array();
            
            // Total bookings this week
            $result = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} 
                                        WHERE booking_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['bookings_this_week'] = $result->fetch_assoc()['count'];
            
            // Total bookings this month
            $result = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} 
                                        WHERE MONTH(booking_date) = MONTH(NOW()) AND YEAR(booking_date) = YEAR(NOW())");
            $stats['bookings_this_month'] = $result->fetch_assoc()['count'];
            
            // Pending payments
            $result = $this->db->query("SELECT COUNT(DISTINCT b.booking_id) as count FROM {$this->table} b
                                        JOIN Payments p ON b.booking_id = p.booking_id
                                        WHERE p.payment_status = 'pending'");
            $stats['pending_payments'] = $result->fetch_assoc()['count'];
            
            // Total revenue
            $result = $this->db->query("SELECT SUM(total_price) as revenue FROM {$this->table} 
                                        WHERE booking_status IN ('confirmed', 'completed')");
            $revenue = $result->fetch_assoc();
            $stats['total_revenue'] = $revenue['revenue'] ?: 0;
            
            // Average booking value
            $result = $this->db->query("SELECT AVG(total_price) as avg FROM {$this->table} 
                                        WHERE booking_status IN ('confirmed', 'completed')");
            $avg = $result->fetch_assoc();
            $stats['avg_booking_value'] = $avg['avg'] ?: 0;
            
            return $stats;
        } catch (Exception $e) {
            error_log("Get booking stats error: " . $e->getMessage());
            return array();
        }
    }
}
?>
