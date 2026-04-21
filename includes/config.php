<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change to your MySQL username
define('DB_PASS', '');           // Change to your MySQL password
define('DB_NAME', 'edutrack');

function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("<div style='font-family:sans-serif;padding:20px;background:#fee;color:#c00;border:1px solid #c00;border-radius:8px;margin:20px'>
            <strong>Database Connection Failed:</strong> " . $conn->connect_error . "<br><br>
            <small>Make sure XAMPP/MySQL is running and you've imported <code>database.sql</code>.</small>
        </div>");
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
