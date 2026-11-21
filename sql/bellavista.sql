-- Bella Vista Hotel & Events Place — import this in phpMyAdmin (XAMPP)
CREATE DATABASE IF NOT EXISTS bellavista_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bellavista_db;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  phone VARCHAR(20) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  points INT NOT NULL DEFAULT 0,
  total_spent DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_days INT NOT NULL DEFAULT 0,
  is_vip TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL,
  type ENUM('standard','family','event') NOT NULL,
  name VARCHAR(60) NOT NULL,
  min_cap INT NOT NULL,
  max_cap INT NOT NULL,
  base_price DECIMAL(10,2) NOT NULL,
  description VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- NOTE: the PDF says Family Rooms max 5 pax, your instructions say 6. Change max_cap below if needed.
INSERT INTO rooms (code,type,name,min_cap,max_cap,base_price,description) VALUES
('A','standard','Room A',2,3,3500,'Cozy standard room with garden view. P3,500 for 2 guests, +P1,000 for a 3rd guest.'),
('B','standard','Room B',2,3,3500,'Warm wood interiors and a quiet balcony. P3,500 for 2 guests, +P1,000 for a 3rd guest.'),
('C','standard','Room C',2,3,3500,'Bright standard room facing the mountains. P3,500 for 2 guests, +P1,000 for a 3rd guest.'),
('FR1','family','Family Room 1',4,6,6000,'Spacious family suite. Fixed P6,000 per night for up to 6 guests.'),
('FR2','family','Family Room 2',4,6,6000,'Two sleeping areas and a sitting nook. Fixed P6,000 per night for up to 6 guests.'),
('FR3','family','Family Room 3',4,6,6000,'Our largest family room with mountain views. Fixed P6,000 per night for up to 6 guests.'),
('EH','event','Event Hall',30,40,15000,'Hall for celebrations and corporate events. P15,000 per night for up to 30 guests, +P500 per extra guest up to 40.');

CREATE TABLE vouchers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  kind ENUM('discount','free_standard','free_family','meal') NOT NULL,
  label VARCHAR(120) NOT NULL,
  pct INT NOT NULL DEFAULT 0,
  status ENUM('unused','used') NOT NULL DEFAULT 'unused',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  room_id INT NOT NULL,
  checkin DATE NOT NULL,
  checkout DATE NOT NULL,
  nights INT NOT NULL,
  guests INT NOT NULL,
  base_amount DECIMAL(10,2) NOT NULL,
  extra_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
  voucher_id INT NULL,
  voucher_discount DECIMAL(10,2) NOT NULL DEFAULT 0,
  vip_discount DECIMAL(10,2) NOT NULL DEFAULT 0,
  total DECIMAL(10,2) NOT NULL,
  downpayment DECIMAL(10,2) NOT NULL,
  payment_ref VARCHAR(60) NOT NULL,
  proof_path VARCHAR(255) NULL,
  status ENUM('pending','approved','checked_in','completed','cancelled') NOT NULL DEFAULT 'pending',
  refundable TINYINT(1) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (room_id,checkin,checkout),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (room_id) REFERENCES rooms(id)
) ENGINE=InnoDB;

CREATE TABLE qr_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  qr_date DATE NOT NULL UNIQUE,
  path VARCHAR(255) NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- The preset admin account is created automatically by config.php on first run:
--   email: admin@bellavista.local   password: Admin@123   (change it after first login)
