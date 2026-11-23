-- Run this once in phpMyAdmin if you already imported database.sql before the payment feature
USE bellavista;

CREATE TABLE
    payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reservation_id INT,
        amount DECIMAL(10, 2),
        ref_no VARCHAR(40),
        proof VARCHAR(60),
        status ENUM ('pending', 'verified', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (reservation_id) REFERENCES reservations (id)
    );