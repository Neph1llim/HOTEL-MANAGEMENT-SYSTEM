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
