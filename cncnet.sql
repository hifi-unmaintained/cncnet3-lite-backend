PRAGMA foreign_keys = ON;

CREATE TABLE games (
    id          INTEGER PRIMARY KEY,
    name        TEXT NOT NULL,
    protocol    TEXT NOT NULL
);

CREATE UNIQUE INDEX games_protocol ON games (protocol);

CREATE TABLE players (
    id          INTEGER PRIMARY KEY,
    game_id     INTEGER NOT NULL,
    ip          TEXT NOT NULL,
    port        INTEGER NOT NULL DEFAULT 8054,
    active      DATETIME NOT NULL,              -- last a message was received
    logout      DATETIME NULL,                  -- time of logout if ever

    FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX players_game_id ON players (game_id);
CREATE INDEX players_active ON players (active);
CREATE INDEX players_logout ON players (logout);
CREATE UNIQUE INDEX players_unique ON players (game_id, ip, port);

INSERT INTO games VALUES (NULL, 'Command & Conquer', 'cnc95');
INSERT INTO games VALUES (NULL, 'Red Alert', 'ra95');
INSERT INTO games VALUES (NULL, 'Tiberian Sun', 'ts');
INSERT INTO games VALUES (NULL, 'Red Alert 2', 'ra2');
INSERT INTO games VALUES (NULL, 'Red Alert 2: Yuri''s Revenge', 'ra2yr');
