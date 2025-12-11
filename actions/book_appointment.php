<?php
// actions/book_appointment.php - Process appointment booking
session_start();
require_once __DIR__ . '/../config/db.php';

// Check if logged in and is a student
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php?error=Please login first");
    exit;
}

// Verify student role
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

if ($currentUser['role'] !== 'Student') {
    header("Location: ../public/index.php?error=Only students can book appointments");
    exit;
}

// Process POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate form data
    $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
    $faculty_id = isset($_POST['faculty_id']) ? intval($_POST['faculty_id']) : 0;
    $availability_id = isset($_POST['availability_id']) ? intval($_POST['availability_id']) : 0;
    $appointment_date = isset($_POST['appointment_date']) ? trim($_POST['appointment_date']) : '';
    $topic = isset($_POST['topic']) ? trim($_POST['topic']) : '';
    $purpose = isset($_POST['purpose']) ? trim($_POST['purpose']) : '';

    // Validate required fields
    if (empty($student_id) || empty($faculty_id) || empty($availability_id) || empty($appointment_date) || empty($topic) || empty($purpose)) {
        header("Location: ../public/book_appointment.php?faculty_id=$faculty_id&error=All fields are required");
        exit;
    }

    // Validate date format and not in the past
    $dateObj = DateTime::createFromFormat('Y-m-d', $appointment_date);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $appointment_date) {
        header("Location: ../public/book_appointment.php?faculty_id=$faculty_id&error=Invalid date format");
        exit;
    }

    $today = new DateTime();
    $today->setTime(0, 0, 0);
    if ($dateObj < $today) {
        header("Location: ../public/book_appointment.php?faculty_id=$faculty_id&error=Appointment date cannot be in the past");
        exit;
    }

    // Verify the availability slot belongs to the faculty and get the day of week
    $stmt = $conn->prepare("
        SELECT day_of_week 
        FROM Availability 
        WHERE availability_id = ? AND faculty_id = ?
    ");
    $stmt->bind_param('ii', $availability_id, $faculty_id);
    $stmt->execute();
    $availResult = $stmt->get_result();
    
    if ($availResult->num_rows === 0) {
        $stmt->close();
        header("Location: ../public/book_appointment.php?faculty_id=$faculty_id&error=Invalid time slot selected");
        exit;
    }
    
    $availData = $availResult->fetch_assoc();
    $expectedDay = $availData['day_of_week'];
    $stmt->close();

    // Validate that the selected date matches the day of week
    $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $selectedDayIndex = $dateObj->format('w'); // 0 (Sunday) to 6 (Saturday)
    $selectedDay = $daysOfWeek[$selectedDayIndex];

    if ($selectedDay !== $expectedDay) {
        header("Location: ../public/book_appointment.php?faculty_id=$faculty_id&error=Selected date does not match the time slot's day of week. Please select a $expectedDay");
        exit;
    }

    // Check if this time slot is already booked for the selected date
    $stmt = $conn->prepare("
        SELECT appointment_id 
        FROM Appointments 
        WHERE availability_id = ? AND appointment_date = ?
    ");
    $stmt->bind_param('is', $availability_id, $appointment_date);
    $stmt->execute();
    $checkResult = $stmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $stmt->close();
        header("Location: ../public/book_appointment.php?faculty_id=$faculty_id&error=This time slot is already booked for the selected date. Please choose another date or time.");
        exit;
    }
    $stmt->close();

    // Insert the appointment
    $insertStmt = $conn->prepare("
        INSERT INTO Appointments (student_id, faculty_id, availability_id, appointment_date, topic, purpose, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'Pending')
    ");
    $insertStmt->bind_param('iiisss', $student_id, $faculty_id, $availability_id, $appointment_date, $topic, $purpose);
    
    if ($insertStmt->execute()) {
        $insertStmt->close();
        header("Location: ../public/dashboard_student.php?success=Appointment booked successfully! Waiting for faculty approval.");
        exit;
    } else {
        $insertStmt->close();
        header("Location: ../public/book_appointment.php?faculty_id=$faculty_id&error=Failed to book appointment. Please try again.");
        exit;
    }

} else {
    // Not a POST request
    header("Location: ../public/search_professors.php");
    exit;
}
?>
