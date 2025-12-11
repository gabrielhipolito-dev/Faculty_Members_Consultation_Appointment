<?php
session_start();
include '../config/db.php';

function redirect_with($type, $message, $anchor = false)
{
    $suffix = $anchor ? '#manage' : '';
    header('Location: ../public/register.php?' . $type . '=' . urlencode($message) . $suffix);
    exit;
}

$action = $_POST['action'] ?? 'create';
$action = in_array($action, ['create', 'update', 'delete'], true) ? $action : 'create';

// Identify acting user role (if logged in)
$actingRole = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare('SELECT role FROM Users WHERE user_id = ? LIMIT 1');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $actingRole = $res->fetch_assoc()['role'];
    }
    $stmt->close();
}
$isAdmin = ($actingRole === 'Admin');

// Admin-only for update/delete
if (($action === 'update' || $action === 'delete') && !$isAdmin) {
    redirect_with('error', 'Only admins can update or delete accounts', true);
}

// Delete flow
if ($action === 'delete') {
    $targetId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    if (!$targetId) {
        redirect_with('error', 'Missing account id', true);
    }
    if (isset($_SESSION['user_id']) && $targetId === (int) $_SESSION['user_id']) {
        redirect_with('error', 'You cannot delete your own account while logged in', true);
    }

    $stmt = $conn->prepare('DELETE FROM Users WHERE user_id = ?');
    $stmt->bind_param('i', $targetId);
    if ($stmt->execute()) {
        redirect_with('success', 'Account deleted successfully', true);
    }
    redirect_with('error', 'Failed to delete account', true);
}

// Shared input
$userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : null;
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
$current_profile_picture = $_POST['current_profile_picture'] ?? null;

// Role-specific fields
$course = trim($_POST['course'] ?? '');
$year_level = $_POST['year_level'] ?? null;
$student_number = trim($_POST['student_number'] ?? '');

$department = trim($_POST['department'] ?? '');
$specialization = trim($_POST['specialization'] ?? '');
$faculty_number = trim($_POST['faculty_number'] ?? '');

// Basic validation
if (!$name || !$username || !$email || !$role) {
    redirect_with('error', 'Please fill in all required fields');
}

if ($action === 'create' && !$password) {
    redirect_with('error', 'Password is required for new accounts');
}

// Load existing user when updating
$existingUser = null;
if ($action === 'update') {
    if (!$userId) {
        redirect_with('error', 'Missing account id for update', true);
    }
    $stmt = $conn->prepare('SELECT * FROM Users WHERE user_id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows !== 1) {
        redirect_with('error', 'Account not found', true);
    }
    $existingUser = $res->fetch_assoc();
    $stmt->close();
}

// Check for duplicates (ignore self when updating)
if ($action === 'create') {
    $stmt = $conn->prepare('SELECT user_id FROM Users WHERE username = ? OR email = ? OR contact_number = ?');
    $stmt->bind_param('sss', $username, $email, $contact_number);
} else {
    $stmt = $conn->prepare('SELECT user_id FROM Users WHERE (username = ? OR email = ? OR contact_number = ?) AND user_id <> ?');
    $stmt->bind_param('sssi', $username, $email, $contact_number, $userId);
}
$stmt->execute();
$dupRes = $stmt->get_result();
if ($dupRes && $dupRes->num_rows > 0) {
    redirect_with('error', 'Username, email, or contact number already exists', $isAdmin);
}
$stmt->close();

// Student number uniqueness when creating/updating students
if ($role === 'Student' && $student_number) {
    if ($action === 'create') {
        $stmt2 = $conn->prepare('SELECT student_id FROM Student WHERE student_number = ?');
        $stmt2->bind_param('s', $student_number);
    } else {
        $stmt2 = $conn->prepare('SELECT student_id FROM Student WHERE student_number = ? AND user_id <> ?');
        $stmt2->bind_param('si', $student_number, $userId);
    }
    $stmt2->execute();
    $snRes = $stmt2->get_result();
    if ($snRes && $snRes->num_rows > 0) {
        redirect_with('error', 'Student number already exists', $isAdmin);
    }
    $stmt2->close();
}

// Handle profile picture upload / default preservation
$profile_path = $current_profile_picture ?: ($existingUser['profile_picture'] ?? null);
if ($profile_picture && isset($profile_picture['tmp_name']) && $profile_picture['tmp_name']) {
    $ext = strtolower(pathinfo($profile_picture['name'], PATHINFO_EXTENSION));
    $new_name = uniqid('profile_') . '.' . $ext;
    $upload_dir = __DIR__ . '/../uploads/profile_pics/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    $destination = $upload_dir . $new_name;
    if (move_uploaded_file($profile_picture['tmp_name'], $destination)) {
        $profile_path = '/uploads/profile_pics/' . $new_name;
    }
} elseif (!$profile_path) {
    // Only set default if no file was chosen and no existing picture
    $profile_path = '/uploads/profile_pics/default_image.png';
}

// Password handling
if ($action === 'create') {
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
} else {
    $hashed_password = $password ? password_hash($password, PASSWORD_BCRYPT) : ($existingUser['password'] ?? null);
}

// Persist user + role data inside a transaction
$conn->begin_transaction();

try {
    if ($action === 'create') {
        $stmt = $conn->prepare('INSERT INTO Users 
            (name, username, email, password, role, birthdate, gender, contact_number, address, profile_picture)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param(
            'ssssssssss',
            $name, $username, $email, $hashed_password, $role,
            $birthdate, $gender, $contact_number, $address, $profile_path
        );
        if (!$stmt->execute()) {
            throw new RuntimeException('Failed to create user');
        }
        $userId = $stmt->insert_id;
        $stmt->close();
    } else { // update
        $stmt = $conn->prepare('UPDATE Users SET name = ?, username = ?, email = ?, password = ?, role = ?, birthdate = ?, gender = ?, contact_number = ?, address = ?, profile_picture = ? WHERE user_id = ?');
        $stmt->bind_param(
            'ssssssssssi',
            $name, $username, $email, $hashed_password, $role,
            $birthdate, $gender, $contact_number, $address, $profile_path, $userId
        );
        if (!$stmt->execute()) {
            throw new RuntimeException('Failed to update user');
        }
        $stmt->close();
    }

    // Role-specific persistence
    if ($role === 'Student') {
        // Upsert student
        $check = $conn->prepare('SELECT student_id FROM Student WHERE user_id = ? LIMIT 1');
        $check->bind_param('i', $userId);
        $check->execute();
        $hasStudent = $check->get_result()->num_rows === 1;
        $check->close();

        if ($hasStudent) {
            $stmt = $conn->prepare('UPDATE Student SET course = ?, year_level = ?, student_number = ? WHERE user_id = ?');
            $stmt->bind_param('sssi', $course, $year_level, $student_number, $userId);
        } else {
            $stmt = $conn->prepare('INSERT INTO Student (user_id, course, year_level, student_number) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('isss', $userId, $course, $year_level, $student_number);
        }
        if (!$stmt->execute()) {
            throw new RuntimeException('Failed to save student details');
        }
        $stmt->close();

        // Remove faculty row if switching roles
        $stmt = $conn->prepare('DELETE FROM Faculty WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    } elseif ($role === 'Faculty') {
        $check = $conn->prepare('SELECT faculty_id FROM Faculty WHERE user_id = ? LIMIT 1');
        $check->bind_param('i', $userId);
        $check->execute();
        $hasFaculty = $check->get_result()->num_rows === 1;
        $check->close();

        if ($hasFaculty) {
            $stmt = $conn->prepare('UPDATE Faculty SET department = ?, specialization = ?, faculty_number = ? WHERE user_id = ?');
            $stmt->bind_param('sssi', $department, $specialization, $faculty_number, $userId);
        } else {
            $stmt = $conn->prepare('INSERT INTO Faculty (user_id, department, specialization, faculty_number) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('isss', $userId, $department, $specialization, $faculty_number);
        }
        if (!$stmt->execute()) {
            throw new RuntimeException('Failed to save faculty details');
        }
        $stmt->close();

        $stmt = $conn->prepare('DELETE FROM Student WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    } else { // Admin role: remove role-specific rows
        $stmt = $conn->prepare('DELETE FROM Student WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare('DELETE FROM Faculty WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();

    if ($action === 'create') {
        redirect_with('success', 'Account created successfully', $isAdmin);
    }
    redirect_with('success', 'Account updated successfully', true);
} catch (Throwable $e) {
    $conn->rollback();
    redirect_with('error', $e->getMessage(), $isAdmin);
}
