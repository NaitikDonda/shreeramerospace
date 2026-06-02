<?php
// Database setup script
$dbFile = __DIR__ . '/../database.sqlite';

// Remove existing database if exists
if (file_exists($dbFile)) {
    unlink($dbFile);
}

try {
    $db = new \PDO('sqlite:' . $dbFile);
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    // Create users table for authentication
    $db->exec('
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ');

    // Create submissions table
    $db->exec('
        CREATE TABLE IF NOT EXISTS submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            unique_id TEXT UNIQUE NOT NULL,
            name TEXT,
            email TEXT,
            phone TEXT,
            company TEXT,
            subject TEXT,
            message TEXT,
            industry TEXT,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ');

    // Create files table to store file information
    $db->exec('
        CREATE TABLE IF NOT EXISTS files (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            submission_id INTEGER NOT NULL,
            file_name TEXT NOT NULL,
            file_path TEXT NOT NULL,
            file_size INTEGER,
            file_type TEXT,
            uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE
        )
    ');

    // Create sessions table for secure session management
    $db->exec('
        CREATE TABLE IF NOT EXISTS sessions (
            id TEXT PRIMARY KEY,
            user_id INTEGER NOT NULL,
            data TEXT,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ');

    // Insert default admin user (password: admin123)
    $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
    $stmt->execute(['admin', $passwordHash]);

    echo "Database setup completed successfully!\n";
    echo "Default admin user created (username: admin, password: admin123)\n";

} catch (PDOException $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
}
