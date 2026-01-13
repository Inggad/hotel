<?php
require_once __DIR__ . '/../config/database.php';

class Room {
    private $db;
    private $table = 'Rooms';
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Search available rooms
     */
    public function searchRooms($checkInDate, $checkOutDate, $roomType = null, $maxPrice = null, $numGuests = null) {
        try {
            $sql = "SELECT r.*, h.hotel_name, h.city FROM {$this->table} r
                    JOIN Hotels h ON r.hotel_id = h.hotel_id
                    WHERE r.is_available = TRUE
                    AND r.room_id NOT IN (
                        SELECT DISTINCT room_id FROM Bookings 
                        WHERE booking_status != 'cancelled'
                        AND check_in_date < ? 
                        AND check_out_date > ?
                    )";
            
            $params = array($checkOutDate, $checkInDate);
            $types = "ss";
            
            if ($roomType) {
                $sql .= " AND r.room_type = ?";
                $params[] = $roomType;
                $types .= "s";
            }
            
            if ($maxPrice) {
                $sql .= " AND r.price_per_night <= ?";
                $params[] = $maxPrice;
                $types .= "d";
            }
            
            if ($numGuests) {
                $sql .= " AND r.capacity >= ?";
                $params[] = $numGuests;
                $types .= "i";
            }
            
            $sql .= " ORDER BY r.price_per_night ASC";
            
            $stmt = $this->db->prepare($sql);
            
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Room search error: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Get room by ID
     */
    public function getRoomById($roomId) {
        try {
            $stmt = $this->db->prepare("SELECT r.*, h.hotel_name FROM {$this->table} r 
                                        JOIN Hotels h ON r.hotel_id = h.hotel_id 
                                        WHERE r.room_id = ?");
            $stmt->bind_param("i", $roomId);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            error_log("Get room error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all rooms
     */
    public function getAllRooms() {
        try {
            $result = $this->db->query("SELECT r.*, h.hotel_name FROM {$this->table} r 
                                        JOIN Hotels h ON r.hotel_id = h.hotel_id 
                                        ORDER BY r.room_number ASC");
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Get all rooms error: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Add new room
     */
    public function addRoom($hotelId, $roomNumber, $roomType, $capacity, $pricePerNight, $amenities, $floorNumber) {
        try {
            $isAvailable = 1;
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (hotel_id, room_number, room_type, capacity, price_per_night, amenities, is_available, floor_number) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issiidsi", $hotelId, $roomNumber, $roomType, $capacity, $pricePerNight, $amenities, $isAvailable, $floorNumber);
            
            if ($stmt->execute()) {
                return array('success' => true, 'message' => 'Room added successfully', 'room_id' => $this->db->lastInsertId());
            } else {
                return array('success' => false, 'message' => 'Failed to add room');
            }
        } catch (Exception $e) {
            error_log("Add room error: " . $e->getMessage());
            return array('success' => false, 'message' => 'An error occurred');
        }
    }
    
    /**
     * Update room
     */
    public function updateRoom($roomId, $roomType, $capacity, $pricePerNight, $amenities, $isAvailable, $floorNumber) {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} 
                                        SET room_type = ?, capacity = ?, price_per_night = ?, amenities = ?, is_available = ?, floor_number = ?
                                        WHERE room_id = ?");
            $stmt->bind_param("siidii", $roomType, $capacity, $pricePerNight, $amenities, $isAvailable, $floorNumber, $roomId);
            
            if ($stmt->execute()) {
                return array('success' => true, 'message' => 'Room updated successfully');
            } else {
                return array('success' => false, 'message' => 'Failed to update room');
            }
        } catch (Exception $e) {
            error_log("Update room error: " . $e->getMessage());
            return array('success' => false, 'message' => 'An error occurred');
        }
    }
    
    /**
     * Check if room is available for dates
     */
    public function isRoomAvailable($roomId, $checkInDate, $checkOutDate) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM Bookings 
                                        WHERE room_id = ? 
                                        AND booking_status != 'cancelled'
                                        AND check_in_date < ? 
                                        AND check_out_date > ?");
            $stmt->bind_param("iss", $roomId, $checkOutDate, $checkInDate);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            return $result['count'] === 0;
        } catch (Exception $e) {
            error_log("Check availability error: " . $e->getMessage());
            return false;
        }
    }
}
?>
