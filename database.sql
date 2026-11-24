-- Bella Vista DATABASE. Fresh install: phpMyAdmin > Import > this file > Go.
-- Creates the database, all tables, the 7 default rooms and the default admin (admin / admin123).
-- Safe to run more than once: existing tables and rows are left untouched.
CREATE DATABASE IF NOT EXISTS bellavista CHARACTER SET utf8mb4;
USE bellavista;

CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30),
    type ENUM ('standard', 'family', 'hall'),
    rate INT,
    base_cap INT,
    max_cap INT,
    active TINYINT DEFAULT 1,
    deleted TINYINT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80),
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(30),
    pass VARCHAR(255),
    points INT DEFAULT 0,
    vip TINYINT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_id INT,
    prize VARCHAR(60),
    kind VARCHAR(20),
    pct INT DEFAULT 0,
    status ENUM ('unused', 'used') DEFAULT 'unused',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guest_id) REFERENCES guests (id)
);

CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ref VARCHAR(12) UNIQUE,
    guest_id INT,
    room_id INT,
    checkin DATE,
    checkout DATE,
    pax INT,
    extra_fee DECIMAL(10, 2),
    discount DECIMAL(10, 2),
    total DECIMAL(10, 2),
    downpayment DECIMAL(10, 2),
    voucher_id INT NULL,
    status ENUM ('pending', 'approved', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    source ENUM ('online', 'walkin') DEFAULT 'online',
    cancel_reason VARCHAR(12) NULL,
    cancelled_at DATETIME NULL,
    refund_status ENUM ('none', 'pending', 'refunded') DEFAULT 'none',
    refund_amount DECIMAL(10, 2) DEFAULT 0,
    refund_note VARCHAR(120) NULL,
    FOREIGN KEY (guest_id) REFERENCES guests (id),
    FOREIGN KEY (room_id) REFERENCES rooms (id)
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT,
    amount DECIMAL(10, 2),
    ref_no VARCHAR(40),
    proof VARCHAR(60) NULL,
    status ENUM ('pending', 'verified', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    kind ENUM ('down', 'balance') DEFAULT 'down',
    method ENUM ('online', 'cash') DEFAULT 'online',
    FOREIGN KEY (reservation_id) REFERENCES reservations (id)
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_id INT,
    msg VARCHAR(255),
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (guest_id, is_read),
    FOREIGN KEY (guest_id) REFERENCES guests (id)
);

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) UNIQUE,
    pass VARCHAR(255)
);

-- Default rooms (only added when the rooms table is empty)
INSERT INTO rooms (name, type, rate, base_cap, max_cap, active)
SELECT * FROM (
    SELECT 'Room A', 'standard', 3500, 2, 3, 1 UNION ALL
    SELECT 'Room B', 'standard', 3500, 2, 3, 1 UNION ALL
    SELECT 'Room C', 'standard', 3500, 2, 3, 1 UNION ALL
    SELECT 'FR1', 'family', 6000, 5, 5, 1 UNION ALL
    SELECT 'FR2', 'family', 6000, 5, 5, 1 UNION ALL
    SELECT 'FR3', 'family', 6000, 5, 5, 1 UNION ALL
    SELECT 'Event Hall', 'hall', 15000, 30, 40, 1
) AS default_rooms
WHERE NOT EXISTS (SELECT 1 FROM rooms);

-- Default admin: admin / admin123 (change the password after first login)
INSERT IGNORE INTO admins (username, pass)
VALUES ('admin', '$2y$10$nFrAvGcMwpHIGMI1Y2q35uEA1O2V5qjkKwI.M5WU8WofYmhoxZaTC');

-- Daily minigames: one claim per guest, per game, per calendar day (max 5 points each)
CREATE TABLE IF NOT EXISTS minigame_claims (
    guest_id INT NOT NULL,
    game ENUM ('toss', 'catch') NOT NULL,
    day DATE NOT NULL,
    points TINYINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (guest_id, game, day),
    CHECK (points BETWEEN 1 AND 5),
    FOREIGN KEY (guest_id) REFERENCES guests (id)
);
