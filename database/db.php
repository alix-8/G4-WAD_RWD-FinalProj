<?php
function get_db(): SQLite3
// Connection to 
{
    static $db = null;
    if ($db !== null) {
        return $db;
    }
    $dbPath = __DIR__ . '/../campusFind.db';
    $db = new SQLite3($dbPath);
    $db->enableExceptions(true);

    // Create users table if it does not exist
    $db->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'user', -- 'user', 'staff', 'admin'
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT 1
    )");

    // Create items table if it does not exist
    // $db->exec();

    return $db;
    echo("Successfully created : D"); //message para makita kung gumana

get_db(); //try ko lang i-call dito para makita kung gumana
}