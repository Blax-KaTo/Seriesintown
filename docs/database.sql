CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firstname TEXT NOT NULL,
    lastname TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    gender TEXT NOT NULL,
    country TEXT NOT NULL,
    password TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);


-- SQLite schema for `town` table

CREATE TABLE IF NOT EXISTS town (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    town TEXT NOT NULL,
    coordinates TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE movies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    api_source TEXT NOT NULL,
    api_id TEXT NOT NULL,
    name TEXT NOT NULL,
    type TEXT NOT NULL,  -- New type column added after name
    quality TEXT NOT NULL,
    price REAL NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create index for better query performance
CREATE INDEX idx_movies_user_id ON movies(user_id);
CREATE INDEX idx_movies_api_id ON movies(api_id);

-- Software table creation
CREATE TABLE software (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    api_source TEXT NOT NULL,
    api_id TEXT NOT NULL,
    name TEXT NOT NULL,
    price REAL NOT NULL,
    platform TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create index for better query performance
CREATE INDEX idx_software_user_id ON software(user_id);
CREATE INDEX idx_software_api_id ON software(api_id);