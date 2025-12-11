<?php
// actions/create_schedule.php - Create faculty availability schedule
session_start();
require_once __DIR__ . '/../config/db.php';

// Check if logged in and is faculty
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php?error=Please login first");
    exit;
}

// Verify faculty role
$stmt = $conn->prepare("SELECT u.user_id, u.role FROM Users u WHERE u.user_id = ? LIMIT 1");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    header("Location: ../public/login.php");
    exit;
}
$currentUser = $res->fetch_assoc();
$stmt->close();

if ($currentUser['role'] !== 'Faculty') {
    header("Location: ../public/index.php?error=Only faculty can manage schedules");
    exit;
}

// Get faculty_id
$stmt = $conn->prepare("SELECT faculty_id FROM Faculty WHERE user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$facultyRes = $stmt->get_result();
if (!$facultyRes || $facultyRes->num_rows === 0) {
    header("Location: ../public/manage_schedule.php?error=Faculty profile not found");
    exit;
}
$faculty = $facultyRes->fetch_assoc();
$faculty_id = $faculty['faculty_id'];
$stmt->close();

// Process POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_faculty_id = isset($_POST['faculty_id']) ? intval($_POST['faculty_id']) : 0;
    $day_of_week = isset($_POST['day_of_week']) ? trim($_POST['day_of_week']) : '';
    $start_time = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
    $end_time = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';

    // Validate required fields
    if (empty($day_of_week) || empty($start_time) || empty($end_time)) {
        header("Location: ../public/manage_schedule.php?error=All fields are required");
        exit;
    }

    // Verify faculty_id matches
    if ($posted_faculty_id !== $faculty_id) {
        header("Location: ../public/manage_schedule.php?error=Invalid faculty ID");
        exit;
    }

    // Validate day of week
    $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    if (!in_array($day_of_week, $validDays)) {
        header("Location: ../public/manage_schedule.php?error=Invalid day of week");
        exit;
    }

    // Validate time format (HH:MM or HH:MM:SS)
    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $start_time) || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $end_time)) {
        header("Location: ../public/manage_schedule.php?error=Invalid time format");
        exit;
    }

    // Validate end time is after start time
    if (strtotime($end_time) <= strtotime($start_time)) {
        header("Location: ../public/manage_schedule.php?error=End time must be after start time");
        exit;
    }

    // Check if this day already has availability
    $checkStmt = $conn->prepare("SELECT availability_id FROM Availability WHERE faculty_id = ? AND day_of_week = ?");
    $checkStmt->bind_param('is', $faculty_id, $day_of_week);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        header("Location: ../public/manage_schedule.php?error=You already have availability set for $day_of_week. Please delete it first to update.");
        exit;
    }
    $checkStmt->close();

    // Insert the availability
    $insertStmt = $conn->prepare("
        INSERT INTO Availability (faculty_id, day_of_week, start_time, end_time) 
        VALUES (?, ?, ?, ?)
    ");
    $insertStmt->bind_param('isss', $faculty_id, $day_of_week, $start_time, $end_time);
    
    if ($insertStmt->execute()) {
        $insertStmt->close();
        header("Location: ../public/manage_schedule.php?success=Availability added successfully for $day_of_week");
        exit;
    } else {
        $insertStmt->close();
        header("Location: ../public/manage_schedule.php?error=Failed to add availability. Please try again.");
        exit;
    }

} else {
    header("Location: ../public/manage_schedule.php");
    exit;
}
?>
