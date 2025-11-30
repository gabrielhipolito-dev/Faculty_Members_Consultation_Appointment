<?php
session_start();

header('Content-Type: text/html; charset=utf-8');

// Load DB config if available
$dbConfigPath = __DIR__ . '/../config/db.php';
if (file_exists($dbConfigPath)) {
    require_once $dbConfigPath;
}

// Fallback DB connection if $conn not provided
if (!isset($conn) || !($conn instanceof mysqli)) {
    $host = '127.0.0.1';
    $user = 'root';
    $pass = '';
    $db   = 'faculty_appointment';

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_errno) {
        // Can't connect to DB — show friendly message
        header('Location: ../public/login.php?error=' . urlencode('Database connection error'));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/login.php');
    exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($username === '' || $password === '') {
    header('Location: ../public/login.php?error=' . urlencode('Provide username and password'));
    exit;
}

// Lookup user by username
$stmt = $conn->prepare('SELECT id, name, username, password, role_id FROM users WHERE username = ? LIMIT 1');
if ($stmt === false) {
    header('Location: ../public/login.php?error=' . urlencode('Server error'));
    exit;
}

$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        // Successful login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['username'] = $user['username'];

        header('Location: ../public/dashboard.php');
        exit;
    }
}

// If we get here, authentication failed
header('Location: ../public/login.php?error=' . urlencode('Invalid username or password'));
exit;

?>
