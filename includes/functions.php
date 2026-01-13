<?php
require_once __DIR__ . '/../config/database.php';

// Initialize database
$database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Helper function to format currency
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Helper function to format date
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

// Helper function to format datetime
function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

// Helper function to get days between dates
function getDaysBetween($checkInDate, $checkOutDate) {
    $start = new DateTime($checkInDate);
    $end = new DateTime($checkOutDate);
    $interval = $start->diff($end);
    return $interval->days;
}

// Helper function to redirect
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit;
}

// Helper function to set message
function setMessage($message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

// Helper function to get message
function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return array('message' => $message, 'type' => $type);
    }
    return null;
}
?>
