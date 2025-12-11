<?php
// actions/delete_all_schedule.php - Delete all faculty availability slots
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

// Check if there are any pending or approved appointments
$appointmentCheckStmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM Appointments a
    INNER JOIN Availability av ON a.availability_id = av.availability_id
    WHERE av.faculty_id = ? 
        AND a.status IN ('Pending', 'Approved')
        AND a.appointment_date >= CURDATE()
");
$appointmentCheckStmt->bind_param('i', $faculty_id);
$appointmentCheckStmt->execute();
$appointmentCheckResult = $appointmentCheckStmt->get_result();
$appointmentData = $appointmentCheckResult->fetch_assoc();
$appointmentCheckStmt->close();

if ($appointmentData['count'] > 0) {
    header("Location: ../public/manage_schedule.php?error=Cannot delete all slots. There are {$appointmentData['count']} pending or approved appointment(s) scheduled.");
    exit;
}

// Delete all availability slots for this faculty
$deleteStmt = $conn->prepare("DELETE FROM Availability WHERE faculty_id = ?");
$deleteStmt->bind_param('i', $faculty_id);

if ($deleteStmt->execute()) {
    $affectedRows = $deleteStmt->affected_rows;
    $deleteStmt->close();
    header("Location: ../public/manage_schedule.php?success=Successfully deleted all $affectedRows availability slot(s)");
    exit;
} else {
    $deleteStmt->close();
    header("Location: ../public/manage_schedule.php?error=Failed to delete schedules. Please try again.");
    exit;
}
?>
