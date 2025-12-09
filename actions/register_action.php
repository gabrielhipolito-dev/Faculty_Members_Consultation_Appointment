<?php
session_start();
include '../config/db.php'; // your database connection

// 1. Get POST data and trim
$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$role = $_POST['role'] ?? '';
$birthdate = $_POST['birthdate'] ?? null;
$gender = $_POST['gender'] ?? null;
$contact_number = trim($_POST['contact_number'] ?? '');
$address = trim($_POST['address'] ?? '');
$profile_picture = $_FILES['profile_picture'] ?? null;

// Role-specific fields
$course = trim($_POST['course'] ?? '');
$year_level = $_POST['year_level'] ?? null;
$student_number = trim($_POST['student_number'] ?? '');

$department = trim($_POST['department'] ?? '');
$specialization = trim($_POST['specialization'] ?? '');
$faculty_number = trim($_POST['faculty_number'] ?? '');

// 2. Check required fields
if (!$name || !$username || !$email || !$password || !$role) {
    header("Location: ../public/register.php?error=Please fill in all required fields");
    exit;
}

// 3. Check for duplicates in Users table
$stmt = $conn->prepare("SELECT * FROM Users WHERE username = ? OR email = ? OR contact_number = ?");
$stmt->bind_param("sss", $username, $email, $contact_number);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    header("Location: ../public/register.php?error=Username, email, or contact number already exists");
    exit;
}

// 4. If Student, check student_number
if ($role === 'Student' && $student_number) {
    $stmt2 = $conn->prepare("SELECT * FROM Student WHERE student_number = ?");
    $stmt2->bind_param("s", $student_number);
    $stmt2->execute();
    if ($stmt2->get_result()->num_rows > 0) {
        header("Location: ../public/register.php?error=Student number already exists");
        exit;
    }
}

// 5. Handle profile picture upload
$profile_path = null;
if ($profile_picture && $profile_picture['tmp_name']) {
    $ext = pathinfo($profile_picture['name'], PATHINFO_EXTENSION);
    $new_name = uniqid('profile_') . "." . $ext;
    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    $destination = $upload_dir . $new_name;

    if (move_uploaded_file($profile_picture['tmp_name'], $destination)) {
        $profile_path = '/uploads/' . $new_name;
    }
}

// 6. Hash password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// 7. Insert into Users table (without password_plain)
$stmt3 = $conn->prepare("INSERT INTO Users 
(name, username, email, password, role, birthdate, gender, contact_number, address, profile_picture)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt3->bind_param(
    "ssssssssss",
    $name, $username, $email, $hashed_password, $role,
    $birthdate, $gender, $contact_number, $address, $profile_path
);

if (!$stmt3->execute()) {
    header("Location: ../public/register.php?error=Failed to create user");
    exit;
}

$user_id = $stmt3->insert_id;

// 8. Insert role-specific table
if ($role === 'Student') {
    $stmt4 = $conn->prepare("INSERT INTO Student (user_id, course, year_level, student_number) VALUES (?, ?, ?, ?)");
    $stmt4->bind_param("isss", $user_id, $course, $year_level, $student_number);
    $stmt4->execute();
}

if ($role === 'Faculty') {
    $stmt5 = $conn->prepare("INSERT INTO Faculty (user_id, department, specialization, faculty_number) VALUES (?, ?, ?, ?)");
    $stmt5->bind_param("isss", $user_id, $department, $specialization, $faculty_number);
    $stmt5->execute();
}

// 9. Success
header("Location: ../public/register.php?success=Account created successfully");
exit;
