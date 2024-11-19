<?php
// db.php

function getDatabaseConnection() {
    $dbPath = 'louxor.db'; // Adjust this path if needed
    $db = new SQLite3($dbPath);
    return $db;
}
?>
