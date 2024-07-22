<?php
// Create (connect to) SQLite database in file
$db = new SQLite3('mandarake.db');

// Check if the connection is successful
if (!$db) {
    echo $db->lastErrorMsg();
} else {
    echo "Opened database successfully\n";
}
// Create a table
$sql = "CREATE TABLE IF NOT EXISTS items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key TEXT UNIQUE NOT NULL,
    title TEXT NOT NULL,
    price REAL NOT NULL,
    shipping REAL NOT NULL,
    total REAL NOT NULL,
    currency TEXT NOT NULL,
    price_eu REAL NOT NULL,
    shipping_eu REAL NOT NULL,
    total_eu REAL NOT NULL,
    size TEXT NOT NULL,
    weight REAL NOT NULL,
    full_url TEXT NOT NULL,
    images TEXT NOT NULL,
    item_code TEXT NOT NULL,
    store TEXT NOT NULL
);";

$ret = $db->exec($sql);
if (!$ret) {
    echo $db->lastErrorMsg();
} else {
    echo "Table created successfully\n";
}
?>