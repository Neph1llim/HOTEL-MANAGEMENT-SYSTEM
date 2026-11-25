-- Bella Vista CLEAR ALL DATA. Use this when the demo is over and you want an empty, ready-to-use system.
-- WARNING: deletes every guest, reservation, payment, voucher, minigame claim and notification,
-- restores the 7 default rooms and rates, and resets the admin to admin / admin123.
-- Reward settings (points rate, wheel rewards, reward types) are left as they are.
USE bellavista;

-- Minigame table (created here too, so this script works even if it was not created yet)
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

-- Clear child tables first so no foreign key blocks the deletes (works with or without FOREIGN_KEY_CHECKS)
DELETE FROM minigame_claims;
DELETE FROM notifications;
DELETE FROM payments;
DELETE FROM reservations;
DELETE FROM vouchers;
DELETE FROM guests;
DELETE FROM rooms;
DELETE FROM admins;
ALTER TABLE reservations AUTO_INCREMENT = 1;
ALTER TABLE notifications AUTO_INCREMENT = 1;
ALTER TABLE payments AUTO_INCREMENT = 1;
ALTER TABLE vouchers AUTO_INCREMENT = 1;
ALTER TABLE guests AUTO_INCREMENT = 1;
ALTER TABLE rooms AUTO_INCREMENT = 1;
ALTER TABLE admins AUTO_INCREMENT = 1;

INSERT INTO rooms (id, name, type, rate, base_cap, max_cap, active) VALUES
    (1, 'Room A', 'standard', 3500, 2, 3, 1),
    (2, 'Room B', 'standard', 3500, 2, 3, 1),
    (3, 'Room C', 'standard', 3500, 2, 3, 1),
    (4, 'FR1', 'family', 6000, 5, 5, 1),
    (5, 'FR2', 'family', 6000, 5, 5, 1),
    (6, 'FR3', 'family', 6000, 5, 5, 1),
    (7, 'Event Hall', 'hall', 15000, 30, 40, 1);

INSERT INTO admins (id, username, pass) VALUES
    (1, 'admin', '$2y$10$nFrAvGcMwpHIGMI1Y2q35uEA1O2V5qjkKwI.M5WU8WofYmhoxZaTC');
