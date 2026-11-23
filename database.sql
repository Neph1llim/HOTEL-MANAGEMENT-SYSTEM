CREATE DATABASE IF NOT EXISTS bellavista CHARACTER
SET
    utf8mb4;

USE bellavista;

CREATE TABLE
    rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(30),
        type ENUM ('standard', 'family', 'hall'),
        rate INT,
        base_cap INT,
        max_cap INT,
        active TINYINT DEFAULT 1
    );

CREATE TABLE
    guests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(80),
        email VARCHAR(100) UNIQUE,
        phone VARCHAR(30),
        pass VARCHAR(255),
        points INT DEFAULT 0,
        vip TINYINT DEFAULT 0
    );

CREATE TABLE
    vouchers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        guest_id INT,
        prize VARCHAR(60),
        kind VARCHAR(20),
        pct INT DEFAULT 0,
        status ENUM ('unused', 'used') DEFAULT 'unused',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (guest_id) REFERENCES guests (id)
    );

CREATE TABLE
    reservations (
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

CREATE TABLE
    payments (
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

CREATE TABLE
    admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) UNIQUE,
        pass VARCHAR(255)
    );

INSERT INTO
    rooms (name, type, rate, base_cap, max_cap)
VALUES
    ('Room A', 'standard', 3500, 2, 3),
    ('Room B', 'standard', 3500, 2, 3),
    ('Room C', 'standard', 3500, 2, 3),
    ('FR1', 'family', 6000, 5, 5),
    ('FR2', 'family', 6000, 5, 5),
    ('FR3', 'family', 6000, 5, 5),
    ('Event Hall', 'hall', 15000, 30, 40);