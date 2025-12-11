<?php
// actions/delete_schedule.php - Delete faculty availability schedule
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
    $availability_id = isset($_POST['availability_id']) ? intval($_POST['availability_id']) : 0;

    // Validate
    if (empty($availability_id)) {
        header("Location: ../public/manage_schedule.php?error=Invalid request");
        exit;
    }

    // Verify this availability belongs to this faculty
    $checkStmt = $conn->prepare("SELECT availability_id, day_of_week FROM Availability WHERE availability_id = ? AND faculty_id = ?");
    $checkStmt->bind_param('ii', $availability_id, $faculty_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        $checkStmt->close();
        header("Location: ../public/manage_schedule.php?error=Schedule not found or access denied");
        exit;
    }
    
    $availData = $checkResult->fetch_assoc();
    $day_of_week = $availData['day_of_week'];
    $checkStmt->close();

    // Check if there are any pending or approved appointments for this availability
    $appointmentCheckStmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM Appointments 
        WHERE availability_id = ? 
            AND status IN ('Pending', 'Approved')
            AND appointment_date >= CURDATE()
    ");
    $appointmentCheckStmt->bind_param('i', $availability_id);
    $appointmentCheckStmt->execute();
    $appointmentCheckResult = $appointmentCheckStmt->get_result();
    $appointmentData = $appointmentCheckResult->fetch_assoc();
    $appointmentCheckStmt->close();

    if ($appointmentData['count'] > 0) {
        header("Location: ../public/manage_schedule.php?error=Cannot delete this time slot. There are {$appointmentData['count']} pending or approved appointment(s) scheduled.");
        exit;
    }

    // Delete the availability
    $deleteStmt = $conn->prepare("DELETE FROM Availability WHERE availability_id = ?");
    $deleteStmt->bind_param('i', $availability_id);
    
    if ($deleteStmt->execute()) {
        $deleteStmt->close();
        header("Location: ../public/manage_schedule.php?success=Time slot for $day_of_week deleted successfully");
        exit;
    } else {
        $deleteStmt->close();
        header("Location: ../public/manage_schedule.php?error=Failed to delete schedule. Please try again.");
        exit;
    }

} else {
    header("Location: ../public/manage_schedule.php");
    exit;
}
?>
