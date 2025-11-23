<!-- TO DOs
 1. fix the limiting numbers sa VARCHAR (SQLite does not support that)
 2. fix the connection between the categories and items table (newly added lang yung categories table kasi) -->

<?php
function get_db(): SQLite3
{
    static $db = null;
    if ($db !== null) {
        return $db;
    }
    $dbPath = __DIR__ . '/../database/campusFind.db';
    $db = new SQLite3($dbPath);
    $db->enableExceptions(true);

    // Create users table if it does not exist
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'user',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Create items table if it does not exist
    $db->exec( "CREATE TABLE IF NOT EXISTS items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        item_type VARCHAR(10) NOT NULL, -- 'lost' or 'found'
        category_id VARCHAR(50), -- 'electronics', 'books', 'clothing', 'accessories', 'other'
        location_found VARCHAR(255),
        location_details TEXT,
        date_lost_or_found DATE,
        status VARCHAR(20) DEFAULT 'open', -- 'open', 'claimed', 'closed'
        contact_info VARCHAR(255),
        image_path VARCHAR(500),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        claimed_by_user_id INTEGER,
        claimed_at DATETIME,
        
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (claimed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )");

    // Create categories table if it does not exist

    echo("Successfully created : D"); //message para makita kung gumana
    
    return $db;

}

function add_admin(): SQLite3
{
    // Insert admin (if not exists??)

    echo("Successfully created and adminnn : D"); //message para makita kung gumana
}

get_db(); //try ko lang i-call dito para makita kung gumana