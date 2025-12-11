<?php
// actions/update_appointment.php - Handle appointment approval/rejection
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
    header("Location: ../public/index.php?error=Only faculty can update appointments");
    exit;
}

// Get faculty_id
$stmt = $conn->prepare("SELECT faculty_id FROM Faculty WHERE user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$facultyRes = $stmt->get_result();
if (!$facultyRes || $facultyRes->num_rows === 0) {
    header("Location: ../public/dashboard_faculty.php?error=Faculty profile not found");
    exit;
}
$faculty = $facultyRes->fetch_assoc();
$faculty_id = $faculty['faculty_id'];
$stmt->close();

// Process POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';

    // Validate
    if (empty($appointment_id) || empty($status)) {
        header("Location: ../public/dashboard_faculty.php?error=Invalid request");
        exit;
    }

    // Validate status
    if (!in_array($status, ['Approved', 'Rejected'])) {
        header("Location: ../public/dashboard_faculty.php?error=Invalid status");
        exit;
    }

    // Verify this appointment belongs to this faculty
    $stmt = $conn->prepare("SELECT appointment_id FROM Appointments WHERE appointment_id = ? AND faculty_id = ?");
    $stmt->bind_param('ii', $appointment_id, $faculty_id);
    $stmt->execute();
    $checkResult = $stmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        $stmt->close();
        header("Location: ../public/dashboard_faculty.php?error=Appointment not found or access denied");
        exit;
    }
    $stmt->close();

    // Update the appointment status
    $updateStmt = $conn->prepare("UPDATE Appointments SET status = ? WHERE appointment_id = ?");
    $updateStmt->bind_param('si', $status, $appointment_id);
    
    if ($updateStmt->execute()) {
        $updateStmt->close();
        $message = $status === 'Approved' ? 'Appointment approved successfully' : 'Appointment rejected';
        header("Location: ../public/dashboard_faculty.php?success=" . urlencode($message));
        exit;
    } else {
        $updateStmt->close();
        header("Location: ../public/dashboard_faculty.php?error=Failed to update appointment");
        exit;
    }

} else {
    header("Location: ../public/dashboard_faculty.php");
    exit;
}
?>
