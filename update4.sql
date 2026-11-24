-- Optional: run ONCE in phpMyAdmin (select the bellavista database, SQL tab, paste, Go).
-- Adds the "deleted" flag used by Admin > Rooms & Rates > Delete Room. The system also adds it
-- automatically the first time any page is opened, so you only need this if you prefer to do it by hand.
USE bellavista;
ALTER TABLE rooms ADD deleted TINYINT DEFAULT 0;
