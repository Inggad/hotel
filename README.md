# Hotel Booking System

A comprehensive PHP-based hotel booking system with a strong database foundation and role-based access control for guests and administrators.

## Project Overview

This system implements a complete hotel booking solution with:
- **7 Well-Designed Database Tables** with proper relationships, constraints, and data types
- **User Authentication** with role-based access (Guest/Admin)
- **Guest Features**: Room search, booking, payment, and booking management
- **Admin Features**: Dashboard, booking management, room management, availability control, and reporting
- **Security**: Prepared statements to prevent SQL injection, password hashing, session management
- **Professional UI**: Bootstrap 5-based responsive design

## Database Schema (7 Tables)

1. **Users**: Authentication and user management
2. **Hotels**: Hotel information
3. **Rooms**: Room inventory with pricing and amenities
4. **Bookings**: Guest bookings with status tracking
5. **Payments**: Payment records and processing
6. **Reviews**: Guest reviews and ratings
7. **Room_Availability**: Daily availability tracking

## Features Implemented

### Guest Features
✅ User Registration & Login with password hashing
✅ Search Rooms (by date, type, price, capacity)
✅ View Room Details & Amenities
✅ Create Booking with Special Requests
✅ Process Payment (Mock implementation)
✅ View Booking Confirmation (printable)
✅ View My Bookings History
✅ Cancel Bookings
✅ Logout

### Admin Features
✅ Dashboard with Key Metrics:
   - Total Bookings (Week/Month)
   - Total Revenue
   - Occupancy Rate %
   - Pending Payments Count
   - Average Booking Value

✅ View & Manage Bookings (filter by status/date)
✅ Manage Rooms (edit details, amenities, availability)
✅ Room Availability Management (prevent overbooking)
✅ Room Status Report (occupancy, revenue per room)

## Setup Instructions

1. **Import Database**
   ```bash
   mysql -u root -p < /path/to/hotel/database.sql
   ```

2. **Configure Database** (if needed)
   - Edit `config/database.php`
   - Update: DB_HOST, DB_USER, DB_PASS, DB_NAME

3. **Access Application**
   - Home: http://localhost/hotel/
   - Admin: http://localhost/hotel/pages/admin/dashboard.php

## Test Credentials

### Admin Account
- Email: `admin@hotel.com`
- Password: `admin@123`

### Guest Accounts
- Email: `john@example.com` / Password: `password123`
- Email: `jane@example.com` / Password: `password123`

## Database Queries & Security

### SQL Injection Prevention
- All queries use prepared statements (mysqli)
- Input parameters are bound with proper types

### Double-Booking Prevention
```sql
SELECT COUNT(*) FROM bookings WHERE room_id = ? 
AND booking_status != 'cancelled'
AND check_in_date < ? AND check_out_date > ?
```

### Form Validations
- **Client-Side**: Date range, email format, required fields
- **Server-Side**: Email uniqueness, password confirmation, numeric validation

## Output Pages

1. **Booking Confirmation**: Full details with print option
2. **Admin Dashboard**: Statistics and recent bookings
3. **My Bookings**: Booking history with cancel option
4. **Room Status Report**: Occupancy and revenue data

## Key Technical Highlights

✅ 7-table normalized database design
✅ OOP principles (Model classes for entities)
✅ Prepared statements for SQL safety
✅ Password hashing (bcrypt)
✅ Session-based authentication
✅ Role-based access control
✅ Bootstrap 5 responsive UI
✅ Error handling and logging
✅ Helper functions for formatting (currency, dates)
✅ UNIQUE and CHECK constraints

## Project Structure

```
hotel/
├── config/database.php          # DB configuration
├── classes/
│   ├── Database.php             # Abstraction layer
│   ├── User.php                 # User model
│   ├── Room.php                 # Room model
│   ├── Booking.php              # Booking model
│   └── Payment.php              # Payment model
├── pages/
│   ├── guest/
│   │   ├── register.php
│   │   ├── login.php
│   │   ├── search_rooms.php
│   │   ├── booking.php
│   │   ├── payment.php
│   │   ├── confirmation.php
│   │   └── my_bookings.php
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── view_bookings.php
│   │   ├── manage_rooms.php
│   │   ├── manage_availability.php
│   │   └── room_report.php
│   └── common/logout.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── assets/
│   ├── css/style.css
│   └── js/validation.js
├── index.php
└── database.sql
```

## Notes

- Payment system is mock implementation (auto-marked as completed)
- Occupancy calculated from current date
- Revenue calculated monthly per room
- All amounts in Indonesian Rupiah (Rp)
- Dates formatted as DD/MM/YYYY

---
**Version**: 1.0 | **Last Updated**: January 2026
