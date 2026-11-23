-- Bella Vista CLEAR ALL DATA. Use this when the demo is over and you want an empty, ready-to-use system.
-- WARNING: deletes every guest, reservation, payment, voucher and notification,
-- restores the 7 default rooms and rates, and resets the admin to admin / admin123.
USE bellavista;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE notifications;
TRUNCATE TABLE payments;
TRUNCATE TABLE reservations;
TRUNCATE TABLE vouchers;
TRUNCATE TABLE guests;
TRUNCATE TABLE rooms;
TRUNCATE TABLE admins;
SET FOREIGN_KEY_CHECKS = 1;

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
