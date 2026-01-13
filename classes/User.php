<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    private $table = 'Users';
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Register new user
     */
    public function register($fullName, $email, $phone, $password) {
        try {
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT user_id FROM {$this->table} WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return array('success' => false, 'message' => 'Email already registered');
            }
            
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $userType = 'guest';
            
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (full_name, email, phone, password_hash, user_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $fullName, $email, $phone, $passwordHash, $userType);
            
            if ($stmt->execute()) {
                return array('success' => true, 'message' => 'Registration successful', 'user_id' => $this->db->lastInsertId());
            } else {
                return array('success' => false, 'message' => 'Registration failed');
            }
        } catch (Exception $e) {
            error_log("User registration error: " . $e->getMessage());
            return array('success' => false, 'message' => 'An error occurred');
        }
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT user_id, full_name, email, password_hash, user_type, is_active FROM {$this->table} WHERE email = ? AND is_active = TRUE");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return array('success' => false, 'message' => 'Invalid email or password');
            }
            
            $user = $result->fetch_assoc();
            
            if (!password_verify($password, $user['password_hash'])) {
                return array('success' => false, 'message' => 'Invalid email or password');
            }
            
            // Set session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];
            
            return array('success' => true, 'message' => 'Login successful', 'user' => $user);
        } catch (Exception $e) {
            error_log("User login error: " . $e->getMessage());
            return array('success' => false, 'message' => 'An error occurred');
        }
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        return self::isLoggedIn() && $_SESSION['user_type'] === 'admin';
    }
    
    /**
     * Check if user is guest
     */
    public static function isGuest() {
        return self::isLoggedIn() && $_SESSION['user_type'] === 'guest';
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        session_destroy();
        header("Location: " . SITE_URL);
        exit;
    }
}
?>
