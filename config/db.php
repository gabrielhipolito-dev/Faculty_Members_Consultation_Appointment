<?php
// Database configuration for local XAMPP environment
// Update these values if you created a different DB user.
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'faculty_appointment';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    // During development it's helpful to see the error. In production, log instead.
    throw new RuntimeException('Database connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

?>
<?php
$host = "localhost";
$user = "root"; // default XAMPP
$pass = "";     // default XAMPP
$db   = "faculty_appointment"; // your database name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
