-- Optional: run ONCE in phpMyAdmin (select the bellavista database, SQL tab, paste, Go).
-- Adds the tables behind Admin > Rewards (points rate and the spin-wheel rewards), filled with the original defaults.
-- The system also creates them automatically the first time any page is opened, so you only need this if you prefer to do it by hand.
USE bellavista;

CREATE TABLE IF NOT EXISTS settings (
    k VARCHAR(40) PRIMARY KEY,
    v VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS wheel_prizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(60),
    kind VARCHAR(20),
    pct INT DEFAULT 0,
    chance DECIMAL(5, 2) DEFAULT 0,
    active TINYINT DEFAULT 1
);

-- Points rate (% of a completed stay's total awarded as points). Edit it in Admin > Rewards.
INSERT IGNORE INTO settings (k, v) VALUES ('points_percent', '1');

-- Default wheel rewards (only added when the table is empty). Add, edit or remove them in Admin > Rewards.
INSERT INTO wheel_prizes (label, kind, pct, chance, active)
SELECT * FROM (
    SELECT '20% discount voucher', 'pct', 20, 50, 1 UNION ALL
    SELECT '50% discount voucher', 'pct', 50, 30, 1 UNION ALL
    SELECT '75% discount voucher + meal stubs', 'pct', 75, 12.5, 1 UNION ALL
    SELECT 'Free Standard Room, 2 nights', 'free_standard', 0, 5, 1 UNION ALL
    SELECT 'Free Family Room, 2 nights', 'free_family', 0, 2.4, 1 UNION ALL
    SELECT 'VIP status (5% off every booking)', 'vip', 0, 0.1, 1
) AS default_prizes
WHERE NOT EXISTS (SELECT 1 FROM wheel_prizes);
