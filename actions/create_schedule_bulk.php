<?php
// actions/create_schedule_bulk.php - Create multiple availability slots at once
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
    $days = isset($_POST['days']) ? $_POST['days'] : [];
    $start_time = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
    $end_time = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';

    // Validate required fields
    if (empty($days) || empty($start_time) || empty($end_time)) {
        header("Location: ../public/manage_schedule.php?error=All fields are required");
        exit;
    }

    // Verify faculty_id matches
    if ($posted_faculty_id !== $faculty_id) {
        header("Location: ../public/manage_schedule.php?error=Invalid faculty ID");
        exit;
    }

    // Validate time format
    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $start_time) || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $end_time)) {
        header("Location: ../public/manage_schedule.php?error=Invalid time format");
        exit;
    }

    // Validate end time is after start time
    if (strtotime($end_time) <= strtotime($start_time)) {
        header("Location: ../public/manage_schedule.php?error=End time must be after start time");
        exit;
    }

    $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    $successCount = 0;
    $skippedDays = [];
    $errors = [];

    foreach ($days as $day_of_week) {
        // Validate day
        if (!in_array($day_of_week, $validDays)) {
            continue;
        }

        // Check for overlapping time slots on this day
        $checkStmt = $conn->prepare("
            SELECT availability_id, start_time, end_time 
            FROM Availability 
            WHERE faculty_id = ? AND day_of_week = ?
        ");
        $checkStmt->bind_param('is', $faculty_id, $day_of_week);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        $hasOverlap = false;
        while ($existing = $checkResult->fetch_assoc()) {
            $existing_start = strtotime($existing['start_time']);
            $existing_end = strtotime($existing['end_time']);
            $new_start = strtotime($start_time);
            $new_end = strtotime($end_time);
            
            // Check for overlap
            if (($new_start >= $existing_start && $new_start < $existing_end) ||
                ($new_end > $existing_start && $new_end <= $existing_end) ||
                ($new_start <= $existing_start && $new_end >= $existing_end)) {
                $hasOverlap = true;
                $skippedDays[] = $day_of_week . " (overlaps with " . date('h:i A', $existing_start) . " - " . date('h:i A', $existing_end) . ")";
                break;
            }
        }
        $checkStmt->close();

        if ($hasOverlap) {
            continue;
        }

        // Insert the availability
        $insertStmt = $conn->prepare("
            INSERT INTO Availability (faculty_id, day_of_week, start_time, end_time) 
            VALUES (?, ?, ?, ?)
        ");
        $insertStmt->bind_param('isss', $faculty_id, $day_of_week, $start_time, $end_time);
        
        if ($insertStmt->execute()) {
            $successCount++;
        } else {
            $errors[] = $day_of_week;
        }
        $insertStmt->close();
    }

    // Build success message
    $message = "";
    if ($successCount > 0) {
        $message .= "Successfully added availability for $successCount day(s). ";
    }
    if (!empty($skippedDays)) {
        $message .= "Skipped: " . implode(', ', $skippedDays) . ". ";
    }
    if (!empty($errors)) {
        $message .= "Failed to add: " . implode(', ', $errors) . ".";
    }

    if ($successCount > 0) {
        header("Location: ../public/manage_schedule.php?success=" . urlencode(trim($message)));
    } else {
        header("Location: ../public/manage_schedule.php?error=" . urlencode(trim($message)));
    }
    exit;

} else {
    header("Location: ../public/manage_schedule.php");
    exit;
}
?>
