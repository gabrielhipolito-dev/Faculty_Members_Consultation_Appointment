<?php
session_start();

// Load database connection
require_once __DIR__ . '/../config/db.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/login.php');
    exit;
}

// Get form input
$identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($identifier === '' || $password === '') {
    header('Location: ../public/login.php?error=' . urlencode('Enter username/email and password'));
    exit;
}

// Determine if identifier is email or username
$isEmail = strpos($identifier, '@') !== false;

// Prepare SQL query based on type
if ($isEmail) {
    $stmt = $conn->prepare("SELECT user_id, password, role, username, email, name FROM Users WHERE email = ? LIMIT 1");
} else {
    $stmt = $conn->prepare("SELECT user_id, password, role, username, email, name FROM Users WHERE username = ? LIMIT 1");
}

// Check for statement preparation errors
if (!$stmt) {
    die("SQL prepare error: " . $conn->error);
}

// Bind parameter and execute
$stmt->bind_param('s', $identifier);
$stmt->execute();
$result = $stmt->get_result();

// Fetch user data
$user = $result->fetch_assoc();
$storedPassword = $user['password'];

// Verify password
$valid = false;
if (strpos($storedPassword, '$2y$') === 0 || strpos($storedPassword, '$2a$') === 0 || strpos($storedPassword, '$argon2') === 0) {
    $valid = password_verify($password, $storedPassword);
} else {
    // Plain-text (not recommended)
    $valid = hash_equals((string)$storedPassword, (string)$password);
    
    // Upgrade to hashed password if correct
    if ($valid) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $up = $conn->prepare('UPDATE Users SET password = ? WHERE user_id = ?');
        if ($up) {
            $up->bind_param('si', $newHash, $user['user_id']);
            $up->execute();
            $up->close();
        }
    }
}

// Check result
if ($valid) {
    // Login success
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    // Redirect to role-specific dashboard wrappers
    $role = strtolower($user['role'] ?? '');
    if ($role === 'admin') {
        header('Location: ../public/dashboard_admin.php');
    } elseif ($role === 'faculty') {
        header('Location: ../public/dashboard_faculty.php');
    } elseif ($role === 'student') {
        header('Location: ../public/dashboard_student.php');
    } else {
        header('Location: ../public/dashboard.php');
    }
    exit;
} else {
    header('Location: ../public/login.php?error=' . urlencode('Invalid username/email or password'));
    exit;
}
