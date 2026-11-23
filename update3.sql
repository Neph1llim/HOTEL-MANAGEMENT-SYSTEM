-- Run ONCE in phpMyAdmin (select the bellavista database, open the SQL tab, paste, Go).
-- Adds: unpaid-reservation expiry data, refund tracking, balance payments, walk-in bookings,
-- room on/off switch, and in-system guest notifications. Existing data is kept.
USE bellavista;

ALTER TABLE rooms ADD active TINYINT DEFAULT 1;

ALTER TABLE reservations ADD source ENUM ('online', 'walkin') DEFAULT 'online',
ADD cancel_reason VARCHAR(12) NULL,
ADD cancelled_at DATETIME NULL,
ADD refund_status ENUM ('none', 'pending', 'refunded') DEFAULT 'none',
ADD refund_amount DECIMAL(10, 2) DEFAULT 0,
ADD refund_note VARCHAR(120) NULL;

ALTER TABLE payments MODIFY proof VARCHAR(60) NULL,
ADD kind ENUM ('down', 'balance') DEFAULT 'down',
ADD method ENUM ('online', 'cash') DEFAULT 'online';

CREATE TABLE
    notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        guest_id INT,
        msg VARCHAR(255),
        is_read TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (guest_id, is_read),
        FOREIGN KEY (guest_id) REFERENCES guests (id)
    );

-- Cancelled reservations that already have a verified payment and were cancelled before this update
-- are not flagged for refund automatically; use Refund on the admin Reservations table if needed.