<?php
require_once __DIR__ . '/../config/database.php';

class Payment {
    private $db;
    private $table = 'Payments';
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create new payment
     */
    public function createPayment($bookingId, $amount, $paymentMethod) {
        try {
            $paymentStatus = 'completed'; // Mock payment - directly mark as completed
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (booking_id, amount, payment_method, payment_status, payment_date)
                                        VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("idss", $bookingId, $amount, $paymentMethod, $paymentStatus);
            
            if ($stmt->execute()) {
                // Update booking status to confirmed
                $updateStmt = $this->db->prepare("UPDATE Bookings SET booking_status = 'confirmed' WHERE booking_id = ?");
                $updateStmt->bind_param("i", $bookingId);
                $updateStmt->execute();
                
                return array('success' => true, 'message' => 'Payment completed successfully', 'payment_id' => $this->db->lastInsertId());
            } else {
                return array('success' => false, 'message' => 'Payment failed');
            }
        } catch (Exception $e) {
            error_log("Create payment error: " . $e->getMessage());
            return array('success' => false, 'message' => 'An error occurred');
        }
    }
    
    /**
     * Get payment by ID
     */
    public function getPaymentById($paymentId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE payment_id = ?");
            $stmt->bind_param("i", $paymentId);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            error_log("Get payment error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get payment by booking ID
     */
    public function getPaymentByBookingId($bookingId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE booking_id = ?");
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            error_log("Get payment by booking error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all payments
     */
    public function getAllPayments($statusFilter = null) {
        try {
            $sql = "SELECT p.*, b.booking_id, b.booking_date, u.full_name, r.room_number
                    FROM {$this->table} p
                    JOIN Bookings b ON p.booking_id = b.booking_id
                    JOIN Users u ON b.user_id = u.user_id
                    JOIN Rooms r ON b.room_id = r.room_id";
            
            if ($statusFilter) {
                $sql .= " WHERE p.payment_status = ?";
            }
            
            $sql .= " ORDER BY p.transaction_date DESC";
            
            $stmt = $this->db->prepare($sql);
            
            if ($statusFilter) {
                $stmt->bind_param("s", $statusFilter);
            }
            
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Get all payments error: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Get payment statistics
     */
    public function getPaymentStats() {
        try {
            $stats = array();
            
            // Total completed payments
            $result = $this->db->query("SELECT SUM(amount) as total FROM {$this->table} 
                                        WHERE payment_status = 'completed'");
            $payment = $result->fetch_assoc();
            $stats['total_revenue'] = $payment['total'] ?: 0;
            
            // Pending payments
            $result = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} 
                                        WHERE payment_status = 'pending'");
            $stats['pending_count'] = $result->fetch_assoc()['count'];
            
            // Failed payments
            $result = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} 
                                        WHERE payment_status = 'failed'");
            $stats['failed_count'] = $result->fetch_assoc()['count'];
            
            // Average payment amount
            $result = $this->db->query("SELECT AVG(amount) as avg FROM {$this->table} 
                                        WHERE payment_status = 'completed'");
            $avg = $result->fetch_assoc();
            $stats['avg_amount'] = $avg['avg'] ?: 0;
            
            return $stats;
        } catch (Exception $e) {
            error_log("Get payment stats error: " . $e->getMessage());
            return array();
        }
    }
}
?>
