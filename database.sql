-- Hotel Booking System Database Schema
-- Created: 2026-01-13

CREATE DATABASE IF NOT EXISTS hotel_booking;
USE hotel_booking;

-- Users Table
CREATE TABLE IF NOT EXISTS Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('guest', 'admin') NOT NULL DEFAULT 'guest',
    date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hotels Table
CREATE TABLE IF NOT EXISTS Hotels (
    hotel_id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_name VARCHAR(150) NOT NULL,
    location VARCHAR(200) NOT NULL,
    city VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    rating DECIMAL(2,1) DEFAULT 0,
    total_rooms INT,
    description TEXT,
    INDEX idx_city (city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rooms Table
CREATE TABLE IF NOT EXISTS Rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    room_number VARCHAR(10) NOT NULL,
    room_type ENUM('single', 'double', 'suite') NOT NULL,
    capacity INT NOT NULL CHECK (capacity > 0),
    price_per_night DECIMAL(10,2) NOT NULL CHECK (price_per_night > 0),
    amenities TEXT,
    is_available BOOLEAN DEFAULT TRUE,
    floor_number INT,
    FOREIGN KEY (hotel_id) REFERENCES Hotels(hotel_id) ON DELETE CASCADE,
    UNIQUE KEY unique_room_per_hotel (hotel_id, room_number),
    INDEX idx_room_type (room_type),
    INDEX idx_availability (is_available)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings Table
CREATE TABLE IF NOT EXISTS Bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    num_guests INT NOT NULL CHECK (num_guests > 0),
    booking_status ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
    total_price DECIMAL(10,2) NOT NULL CHECK (total_price >= 0),
    booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    special_requests TEXT,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES Rooms(room_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_room_id (room_id),
    INDEX idx_booking_status (booking_status),
    INDEX idx_check_in_date (check_in_date),
    INDEX idx_booking_date (booking_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments Table
CREATE TABLE IF NOT EXISTS Payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL CHECK (amount > 0),
    payment_method ENUM('credit_card', 'debit_card', 'bank_transfer') NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    payment_date DATETIME,
    FOREIGN KEY (booking_id) REFERENCES Bookings(booking_id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_transaction_date (transaction_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews Table
CREATE TABLE IF NOT EXISTS Reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    review_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES Bookings(booking_id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Room_Availability Table
CREATE TABLE IF NOT EXISTS Room_Availability (
    availability_id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    available_date DATE NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (room_id) REFERENCES Rooms(room_id) ON DELETE CASCADE,
    UNIQUE KEY unique_availability (room_id, available_date),
    INDEX idx_room_id (room_id),
    INDEX idx_available_date (available_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============= SEED DATA =============

-- Insert Sample Hotel
INSERT INTO Hotels (hotel_name, location, city, phone, email, rating, total_rooms, description) VALUES
('Luxury Paradise Hotel', '123 Main Street', 'Jakarta', '+62-21-123456', 'info@luxuryparadise.com', 4.5, 20, 'A luxurious 5-star hotel with world-class amenities and services');

-- Insert Sample Rooms (5 Singles, 8 Doubles, 7 Suites = 20 total)
INSERT INTO Rooms (hotel_id, room_number, room_type, capacity, price_per_night, amenities, is_available, floor_number) VALUES
-- Singles
(1, '101', 'single', 1, 500000, 'WiFi, Air Conditioning, Flat TV, Mini Bar', TRUE, 1),
(1, '102', 'single', 1, 500000, 'WiFi, Air Conditioning, Flat TV, Mini Bar', TRUE, 1),
(1, '201', 'single', 1, 550000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, City View', TRUE, 2),
(1, '202', 'single', 1, 550000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, City View', TRUE, 2),
(1, '301', 'single', 1, 600000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, City View, Balcony', TRUE, 3),
-- Doubles
(1, '103', 'double', 2, 800000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, King Bed', TRUE, 1),
(1, '104', 'double', 2, 800000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, King Bed', TRUE, 1),
(1, '203', 'double', 2, 850000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, King Bed, City View', TRUE, 2),
(1, '204', 'double', 2, 850000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, King Bed, City View', TRUE, 2),
(1, '302', 'double', 2, 900000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, King Bed, City View, Balcony', TRUE, 3),
(1, '303', 'double', 2, 900000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, King Bed, City View, Balcony', TRUE, 3),
(1, '402', 'double', 2, 950000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, King Bed, City View, Balcony, Jacuzzi', TRUE, 4),
(1, '403', 'double', 2, 950000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, King Bed, City View, Balcony, Jacuzzi', TRUE, 4),
-- Suites
(1, '105', 'suite', 4, 1500000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, Living Room, Kitchen', TRUE, 1),
(1, '205', 'suite', 4, 1600000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, Living Room, Kitchen, City View', TRUE, 2),
(1, '304', 'suite', 4, 1700000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, Living Room, Kitchen, City View, Balcony', TRUE, 3),
(1, '305', 'suite', 4, 1700000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, Living Room, Kitchen, City View, Balcony', TRUE, 3),
(1, '404', 'suite', 4, 1800000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, Living Room, Kitchen, City View, Balcony, Jacuzzi', TRUE, 4),
(1, '405', 'suite', 4, 1800000, 'WiFi, Air Conditioning, Flat TV, Mini Bar, Living Room, Kitchen, City View, Balcony, Jacuzzi', TRUE, 4),
(1, '501', 'suite', 4, 2000000, 'Penthouse Suite: WiFi, Air Conditioning, Flat TV, Mini Bar, Living Room, Kitchen, City View, Balcony, Jacuzzi, Gym Access', TRUE, 5);

-- Insert Sample Users (test admin and test guest)
INSERT INTO Users (full_name, email, phone, password_hash, user_type, date_created, is_active) VALUES
('Admin User', 'admin@hotel.com', '+62-21-999999', '$2y$10$YQv8uLpv2kRvOJvSm7L/.e8OKSaAHyQ7m8vJ5rLmCZbE/wWx4qD5K', 'admin', NOW(), TRUE),
('John Doe', 'john@example.com', '+62-812-1234567', '$2y$10$4wD8p6kJ3nL2mO8pR5qS/.e8OKSaAHyQ7m8vJ5rLmCZbE/wWx4qD5K', 'guest', NOW(), TRUE),
('Jane Smith', 'jane@example.com', '+62-812-7654321', '$2y$10$5xE9q7lK4oM3nP9qS6rT/.e8OKSaAHyQ7m8vJ5rLmCZbE/wWx4qD5K', 'guest', NOW(), TRUE);
-- Passwords: admin@123, password123, password123

-- Insert sample Room Availability for next 30 days
SET @start_date = CURDATE();
SET @end_date = DATE_ADD(@start_date, INTERVAL 30 DAY);

INSERT INTO Room_Availability (room_id, available_date, is_available)
SELECT r.room_id, DATE_ADD(@start_date, INTERVAL n DAY), TRUE
FROM Rooms r
CROSS JOIN (
    SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
    UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11
    UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17
    UNION SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23
    UNION SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
) AS dates
WHERE DATE_ADD(@start_date, INTERVAL n DAY) <= @end_date;

-- Insert Sample Bookings (test data)
INSERT INTO Bookings (user_id, room_id, check_in_date, check_out_date, num_guests, booking_status, total_price, booking_date, special_requests) VALUES
(2, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 4 DAY), 1, 'confirmed', 1000000, NOW(), 'High floor preferred'),
(3, 6, DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 8 DAY), 2, 'pending', 2400000, NOW(), 'Anniversary celebration');

-- Insert Sample Payments
INSERT INTO Payments (booking_id, amount, payment_method, payment_status, payment_date) VALUES
(1, 1000000, 'credit_card', 'completed', NOW()),
(2, 2400000, 'bank_transfer', 'pending', NULL);

-- Insert Sample Reviews
INSERT INTO Reviews (booking_id, rating, review_text, review_date) VALUES
(1, 5, 'Excellent service and comfortable room. Highly recommended!', NOW());
