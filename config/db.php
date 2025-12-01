<?php
// config/db.php
// Single, clear mysqli connection for local XAMPP. Adjust if your env differs.
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'Faculty_Consultation'; // must match the database you created

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_errno) {
    // In development surface a helpful message. In production, replace with proper logging.
    throw new RuntimeException('Database connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?>
