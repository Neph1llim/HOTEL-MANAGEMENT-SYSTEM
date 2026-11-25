-- Optional: run ONCE in phpMyAdmin (select the bellavista database, SQL tab, paste, Go). Safe to run again.
-- Adds what Admin > Rewards needs for reward types and free-room rewards:
--   * the reward_types table (Discount voucher, Free room stay, VIP status, plus any you add),
--   * room_id / room_type / nights on wheel_prizes and vouchers (which room a free stay is for, and for how many nights),
--   * merges the old "Free Standard Room" and "Free Family Room" kinds into one "Free room stay" type.
--     Your existing rewards and vouchers keep working: "Free Standard Room, 2 nights" becomes a Free room stay for any Standard room, 2 nights.
-- The system also does all of this automatically the first time any page is opened, so you only need this if you prefer to do it by hand.
USE bellavista;

CREATE TABLE IF NOT EXISTS reward_types (
    code VARCHAR(20) PRIMARY KEY,
    label VARCHAR(60),
    base VARCHAR(10),
    builtin TINYINT DEFAULT 0
);

INSERT IGNORE INTO reward_types (code, label, base, builtin) VALUES
    ('pct', 'Discount voucher (% off)', 'pct', 1),
    ('free_room', 'Free room stay', 'free_room', 1),
    ('vip', 'VIP status (5% off every booking)', 'vip', 1);

-- add the three columns to wheel_prizes and vouchers only when they are missing (works on MySQL and MariaDB)
SET @q = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'wheel_prizes' AND COLUMN_NAME = 'nights') = 0,
    'ALTER TABLE wheel_prizes ADD room_id INT NULL, ADD room_type VARCHAR(10) NULL, ADD nights TINYINT DEFAULT 2', 'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;

SET @q = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vouchers' AND COLUMN_NAME = 'nights') = 0,
    'ALTER TABLE vouchers ADD room_id INT NULL, ADD room_type VARCHAR(10) NULL, ADD nights TINYINT DEFAULT 2', 'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;

-- merge the two old free-room kinds into the one "free_room" type
UPDATE wheel_prizes SET kind = 'free_room', room_type = 'standard', nights = 2 WHERE kind = 'free_standard';
UPDATE wheel_prizes SET kind = 'free_room', room_type = 'family', nights = 2 WHERE kind = 'free_family';
UPDATE vouchers SET kind = 'free_room', room_type = 'standard', nights = 2 WHERE kind = 'free_standard';
UPDATE vouchers SET kind = 'free_room', room_type = 'family', nights = 2 WHERE kind = 'free_family';
