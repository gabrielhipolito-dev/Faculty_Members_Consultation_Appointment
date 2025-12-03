<?php
// Centralized session and current-user loader
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first");
    exit();
}

require_once __DIR__ . '/../config/db.php';
$user = null;
try {
    $stmt = $conn->prepare('SELECT user_id, name, username, email, role, birthdate, gender, contact_number, address, profile_picture FROM Users WHERE user_id = ? LIMIT 1');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $user = $res->fetch_assoc();
    }
    $stmt->close();
} catch (Exception $e) {
    $user = null;
}
