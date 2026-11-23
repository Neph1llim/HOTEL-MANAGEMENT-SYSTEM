-- Bella Vista RESET DEMO. One import restores the whole demo: phpMyAdmin > bellavista > Import > this file > Go.
-- WARNING: this DELETES every guest, reservation, payment, voucher and notification, restores the 7 default rooms and rates,
-- resets the admin login to admin / admin123, and then loads the sample data again. Do not run it on real data.
-- Demo logins: admin / admin123  |  maria@demo.com, juan@demo.com, carlo@demo.com, ana@demo.com, liza@demo.com / guest123
-- (This file = clear-all-data.sql + demo-data.sql. Keep all three in sync if you change one.)
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

-- Guests (password for every demo guest: guest123). Pedro Ramos is a walk-in guest with no online account.
INSERT INTO guests (id, name, email, phone, pass, points, vip) VALUES
    (1, 'Maria Santos', 'maria@demo.com', '09171110001', '$2y$10$W99YhPeaem5MncVs2EBWbehW47L8RwfA27Yw//kDeKVDwEh9OhwoK', 120, 0),
    (2, 'Juan Dela Cruz', 'juan@demo.com', '09171110002', '$2y$10$W99YhPeaem5MncVs2EBWbehW47L8RwfA27Yw//kDeKVDwEh9OhwoK', 95, 0),
    (3, 'Carlo Mendoza', 'carlo@demo.com', '09171110003', '$2y$10$W99YhPeaem5MncVs2EBWbehW47L8RwfA27Yw//kDeKVDwEh9OhwoK', 75, 1),
    (4, 'Ana Reyes', 'ana@demo.com', '09171110004', '$2y$10$W99YhPeaem5MncVs2EBWbehW47L8RwfA27Yw//kDeKVDwEh9OhwoK', 0, 0),
    (5, 'Liza Bautista', 'liza@demo.com', '09171110005', '$2y$10$W99YhPeaem5MncVs2EBWbehW47L8RwfA27Yw//kDeKVDwEh9OhwoK', 0, 0),
    (6, 'Pedro Ramos', NULL, '09171110006', NULL, 0, 0);

-- Vouchers: Maria has an unused 50% voucher; Juan's 75% voucher was already used on BVD0004.
INSERT INTO vouchers (id, guest_id, prize, kind, pct, status, created_at) VALUES
    (1, 1, '50% discount voucher', 'pct', 50, 'unused', DATE_SUB(NOW(), INTERVAL 40 DAY)),
    (2, 2, '75% discount voucher + meal stubs', 'pct', 75, 'used', DATE_SUB(NOW(), INTERVAL 30 DAY));

-- Reservations in every status. All dates are relative to the day you import this file.
--  BVD0001-0005 completed (points already awarded)
--  BVD0006 approved, guest is in the room today, balance unpaid  -> try "Collect balance"
--  BVD0007-0008 approved, upcoming
--  BVD0009 pending, payment proof waiting                        -> try "Verify"
--  BVD0010 pending, no payment yet (24-hour hold running)
--  BVD0011 pending, 23 hours old: auto-cancels about an hour after import (shows the expiry rule)
--  BVD0012 cancelled, refund pending                             -> try "Refund" > Refunded
--  BVD0013 cancelled, refunded
--  BVD0014 cancelled less than 3 days before check-in, non-refundable
--  BVD0015 cancelled automatically (no payment in 24 hours)
--  BVD0016 walk-in booking paid in cash
INSERT INTO reservations
    (id, ref, guest_id, room_id, checkin, checkout, pax, extra_fee, discount, total, downpayment, voucher_id,
     status, source, cancel_reason, cancelled_at, refund_status, refund_amount, refund_note, created_at)
VALUES
    (1, 'BVD0001', 1, 1, DATE_ADD(CURDATE(), INTERVAL -20 DAY), DATE_ADD(CURDATE(), INTERVAL -18 DAY), 2, 0, 0, 7000, 3500, NULL, 'completed', 'online', NULL, NULL, 'none', 0, NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
    (2, 'BVD0002', 1, 7, DATE_ADD(CURDATE(), INTERVAL -45 DAY), DATE_ADD(CURDATE(), INTERVAL -44 DAY), 30, 0, 0, 15000, 7500, NULL, 'completed', 'online', NULL, NULL, 'none', 0, NULL, DATE_SUB(NOW(), INTERVAL 55 DAY)),
    (3, 'BVD0003', 2, 7, DATE_ADD(CURDATE(), INTERVAL -60 DAY), DATE_ADD(CURDATE(), INTERVAL -59 DAY), 30, 0, 0, 15000, 7500, NULL, 'completed', 'online', NULL, NULL, 'none', 0, NULL, DATE_SUB(NOW(), INTERVAL 70 DAY)),
    (4, 'BVD0004', 2, 4, DATE_ADD(CURDATE(), INTERVAL -12 DAY), DATE_ADD(CURDATE(), INTERVAL -9 DAY), 5, 0, 13500, 4500, 2250, 2, 'completed', 'online', NULL, NULL, 'none', 0, NULL, DATE_SUB(NOW(), INTERVAL 20 DAY)),
    (5, 'BVD0005', 3, 7, DATE_ADD(CURDATE(), INTERVAL -30 DAY), DATE_ADD(CURDATE(), INTERVAL -29 DAY), 35, 2500, 0, 17500, 8750, NULL, 'completed', 'online', NULL, NULL, 'none', 0, NULL, DATE_SUB(NOW(), INTERVAL 40 DAY)),
    (6, 'BVD0006', 4, 2, DATE_ADD(CURDATE(), INTERVAL -1 DAY), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 3, 3000, 0, 13500, 6750, NULL, 'approved', 'online', NULL, NULL, 'none', 0, NULL, DATE_SUB(NOW(), INTERVAL 5 DAY)),
    (7, 'BVD0007', 1, 5, DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 8 DAY), 4, 0, 0, 18000, 9000, NULL, 'approved', 'online', NULL, NULL, 'none', 0, NULL, DATE_SUB(NOW(), INTERVAL 2 DAY)),
    (8, 'BVD0008', 2, 3, DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 12 DAY), 2, 0, 0, 7000, 3500, NULL, 'approved', 'online', NULL, NULL, 'none', 0, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),
    (9, 'BVD0009', 5, 1, DATE_ADD(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 4 DAY), 2, 0, 0, 3500, 1750, NULL, 'pending', 'online', NULL, NULL, 'none', 0, NULL, DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
    (10, 'BVD0010', 3, 6, DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 16 DAY), 5, 0, 600, 11400, 5700, NULL, 'pending', 'online', NULL, NULL, 'none', 0, NULL, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
    (11, 'BVD0011', 5, 3, DATE_ADD(CURDATE(), INTERVAL 25 DAY), DATE_ADD(CURDATE(), INTERVAL 26 DAY), 2, 0, 0, 3500, 1750, NULL, 'pending', 'online', NULL, NULL, 'none', 0, NULL, DATE_SUB(NOW(), INTERVAL 23 HOUR)),
    (12, 'BVD0012', 1, 1, DATE_ADD(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 17 DAY), 2, 0, 0, 7000, 3500, NULL, 'cancelled', 'online', 'guest', DATE_SUB(NOW(), INTERVAL 2 DAY), 'pending', 3500, NULL, DATE_SUB(NOW(), INTERVAL 6 DAY)),
    (13, 'BVD0013', 2, 2, DATE_ADD(CURDATE(), INTERVAL 8 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 2, 0, 0, 7000, 3500, NULL, 'cancelled', 'online', 'guest', DATE_SUB(NOW(), INTERVAL 4 DAY), 'refunded', 3500, 'GCash DEMO-REFUND-01', DATE_SUB(NOW(), INTERVAL 9 DAY)),
    (14, 'BVD0014', 4, 2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 2, 0, 0, 3500, 1750, NULL, 'cancelled', 'online', 'guest', DATE_SUB(NOW(), INTERVAL 1 DAY), 'none', 0, NULL, DATE_SUB(NOW(), INTERVAL 3 DAY)),
    (15, 'BVD0015', 3, 1, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 8 DAY), 2, 0, 0, 3500, 1750, NULL, 'cancelled', 'online', 'expired', DATE_SUB(NOW(), INTERVAL 2 DAY), 'none', 0, NULL, DATE_SUB(NOW(), INTERVAL 3 DAY)),
    (16, 'BVD0016', 6, 3, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 DAY), 2, 0, 0, 3500, 1750, NULL, 'approved', 'walkin', NULL, NULL, 'none', 0, NULL, DATE_SUB(NOW(), INTERVAL 1 HOUR));

-- Payments (proof image is a labelled placeholder in uploads/demo-proof.png)
INSERT INTO payments (id, reservation_id, amount, ref_no, proof, kind, method, status, created_at) VALUES
    (1, 1, 3500, 'DEMO-0001', 'demo-proof.png', 'down', 'online', 'verified', DATE_SUB(NOW(), INTERVAL 30 DAY)),
    (2, 1, 3500, 'BAL-BVD0001', NULL, 'balance', 'cash', 'verified', DATE_SUB(NOW(), INTERVAL 20 DAY)),
    (3, 2, 7500, 'DEMO-0002', 'demo-proof.png', 'down', 'online', 'verified', DATE_SUB(NOW(), INTERVAL 55 DAY)),
    (4, 2, 7500, 'BAL-BVD0002', NULL, 'balance', 'cash', 'verified', DATE_SUB(NOW(), INTERVAL 45 DAY)),
    (5, 3, 7500, 'DEMO-0003', 'demo-proof.png', 'down', 'online', 'verified', DATE_SUB(NOW(), INTERVAL 70 DAY)),
    (6, 3, 7500, 'BAL-BVD0003', NULL, 'balance', 'cash', 'verified', DATE_SUB(NOW(), INTERVAL 60 DAY)),
    (7, 4, 2250, 'DEMO-0004', 'demo-proof.png', 'down', 'online', 'verified', DATE_SUB(NOW(), INTERVAL 20 DAY)),
    (8, 4, 2250, 'BAL-BVD0004', NULL, 'balance', 'cash', 'verified', DATE_SUB(NOW(), INTERVAL 12 DAY)),
    (9, 5, 8750, 'DEMO-0005', 'demo-proof.png', 'down', 'online', 'verified', DATE_SUB(NOW(), INTERVAL 40 DAY)),
    (10, 5, 8750, 'BAL-BVD0005', NULL, 'balance', 'cash', 'verified', DATE_SUB(NOW(), INTERVAL 30 DAY)),
    (11, 6, 6750, 'DEMO-0006', 'demo-proof.png', 'down', 'online', 'verified', DATE_SUB(NOW(), INTERVAL 5 DAY)),
    (12, 7, 9000, 'DEMO-0007', 'demo-proof.png', 'down', 'online', 'verified', DATE_SUB(NOW(), INTERVAL 2 DAY)),
    (13, 8, 3500, 'DEMO-0008', 'demo-proof.png', 'down', 'online', 'verified', DATE_SUB(NOW(), INTERVAL 1 DAY)),
    (14, 9, 1750, 'DEMO-0009', 'demo-proof.png', 'down', 'online', 'pending', DATE_SUB(NOW(), INTERVAL 20 MINUTE)),
    (15, 12, 3500, 'DEMO-0012', 'demo-proof.png', 'down', 'online', 'verified', DATE_SUB(NOW(), INTERVAL 6 DAY)),
    (16, 13, 3500, 'DEMO-0013', 'demo-proof.png', 'down', 'online', 'verified', DATE_SUB(NOW(), INTERVAL 9 DAY)),
    (17, 14, 1750, 'DEMO-0014', 'demo-proof.png', 'down', 'online', 'verified', DATE_SUB(NOW(), INTERVAL 3 DAY)),
    (18, 16, 1750, 'WALK-IN', NULL, 'down', 'cash', 'verified', DATE_SUB(NOW(), INTERVAL 1 HOUR));

-- Guest notifications
INSERT INTO notifications (id, guest_id, msg, is_read, created_at) VALUES
    (1, 1, 'Payment verified. Reservation BVD0007 is approved. The balance of ₱9,000.00 is payable at check-in.', 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
    (2, 1, 'You cancelled reservation BVD0012. Your refund of ₱3,500.00 is pending. The hotel will contact you.', 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
    (3, 1, 'Thank you for staying with us! Reservation BVD0001 is completed and you earned 70 points.', 1, DATE_SUB(NOW(), INTERVAL 18 DAY)),
    (4, 2, 'Your refund of ₱3,500.00 for reservation BVD0013 has been processed.', 0, DATE_SUB(NOW(), INTERVAL 3 DAY)),
    (5, 2, 'Payment verified. Reservation BVD0008 is approved. The balance of ₱3,500.00 is payable at check-in.', 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
    (6, 3, 'Reservation BVD0010 received. Please pay the downpayment of ₱5,700.00 and upload your proof of payment within 24 hours, or the room will be released.', 0, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
    (7, 4, 'Payment verified. Reservation BVD0006 is approved. The balance of ₱6,750.00 is payable at check-in.', 1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
    (8, 5, 'Reservation BVD0011 received. Please pay the downpayment of ₱1,750.00 and upload your proof of payment within 24 hours, or the room will be released.', 0, DATE_SUB(NOW(), INTERVAL 23 HOUR)),
    (9, 5, 'Payment proof for BVD0009 submitted. The hotel will verify it shortly.', 0, DATE_SUB(NOW(), INTERVAL 20 MINUTE));
