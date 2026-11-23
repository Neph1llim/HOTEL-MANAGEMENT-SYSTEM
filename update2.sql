-- Run once in phpMyAdmin if you already imported the earlier database.sql
USE bellavista;

ALTER TABLE guests ADD pass VARCHAR(255) NULL;