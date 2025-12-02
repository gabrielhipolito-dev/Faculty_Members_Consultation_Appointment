<?php
// actions/register_action.php
// Simple registration handler. Inserts into Users and into Faculty or Student when needed.
// Expects POST values as provided by `public/register.php`.

try {
    require_once __DIR__ . '/../config/db.php';
    // `config/db.php` initializes `$conn` or throws on failure
} catch (Exception $e) {
    header('Location: ../public/register.php?error=' . urlencode('Database error'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/register.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'Student';
$contact = trim($_POST['contact_number'] ?? '');
$address = trim($_POST['address'] ?? '');
// optional fields
$birthdate = trim($_POST['birthdate'] ?? '');
$gender = trim($_POST['gender'] ?? '');

if ($name === '' || $username === '' || $email === '' || $password === '') {
    header('Location: ../public/register.php?error=' . urlencode('Please fill required fields'));
    exit;
}

// Enforce @gmail.com email addresses
if (!preg_match('/@gmail\.com$/i', $email)) {
    header('Location: ../public/register.php?error=' . urlencode('Email must be a @gmail.com address'));
    exit;
}

// Validate birthdate if provided (YYYY-MM-DD)
if ($birthdate !== '') {
    $d = DateTime::createFromFormat('Y-m-d', $birthdate);
    if (!$d || $d->format('Y-m-d') !== $birthdate) {
        header('Location: ../public/register.php?error=' . urlencode('Invalid birthdate format'));
        exit;
    }
}

// Validate gender (optional)
$allowedGenders = ['Male','Female','Other',''];
if (!in_array($gender, $allowedGenders, true)) {
    header('Location: ../public/register.php?error=' . urlencode('Invalid gender selection'));
    exit;
}

// Basic uniqueness check for username/email
$check = $conn->prepare('SELECT user_id FROM Users WHERE username = ? OR email = ? LIMIT 1');
if (!$check) {
    header('Location: ../public/register.php?error=' . urlencode('Server error'));
    exit;
}
$check->bind_param('ss', $username, $email);
$check->execute();
$res = $check->get_result();
if ($res && $res->num_rows > 0) {
    header('Location: ../public/register.php?error=' . urlencode('Username or email already taken'));
    exit;
}

// Hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert into Users
$ins = $conn->prepare('INSERT INTO Users (name, username, email, password, password_plain, role, birthdate, gender, contact_number, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
if (!$ins) {
    header('Location: ../public/register.php?error=' . urlencode('Server error'));
    exit;
}
$plain = $password; // NOTE: stored for testing only (db has `password_plain` column)
// If birthdate or gender are empty strings, convert to NULL for DB insert
$birthdate_param = ($birthdate === '') ? null : $birthdate;
$gender_param = ($gender === '') ? null : $gender;
$ins->bind_param('ssssssssss', $name, $username, $email, $hash, $plain, $role, $birthdate_param, $gender_param, $contact, $address);
if (!$ins->execute()) {
    header('Location: ../public/register.php?error=' . urlencode('Could not create user'));
    exit;
}
$user_id = $conn->insert_id;
$ins->close();

// Handle profile picture upload (optional). We'll save the file after we have $user_id.
$profile_picture_path = null;
if (isset($_FILES['profile_picture']) && isset($_FILES['profile_picture']['error']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['profile_picture'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Basic validation
        $maxBytes = 2 * 1024 * 1024; // 2MB
        if ($file['size'] <= $maxBytes) {
            $tmp = $file['tmp_name'];
            $info = @getimagesize($tmp);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
            if ($info && isset($allowed[$info['mime']])) {
                $ext = $allowed[$info['mime']];
                $uploadDir = __DIR__ . '/../uploads/profile_pics/';
                if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
                // unique filename
                $saved = $user_id . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $dest = $uploadDir . $saved;
                if (@move_uploaded_file($tmp, $dest)) {
                    // store relative path for DB
                    $profile_picture_path = 'uploads/profile_pics/' . $saved;
                }
            }
        }
    }
}

// Role-specific inserts
if ($role === 'Student') {
    $course = trim($_POST['course'] ?? '');
    $year = trim($_POST['year_level'] ?? '1');
    $student_number = trim($_POST['student_number'] ?? '');

    if ($profile_picture_path !== null) {
        $s = $conn->prepare('INSERT INTO Student (user_id, course, year_level, student_number, profile_picture) VALUES (?, ?, ?, ?, ?)');
        if ($s) {
            $s->bind_param('issss', $user_id, $course, $year, $student_number, $profile_picture_path);
            $s->execute();
            $s->close();
        }
    } else {
        $s = $conn->prepare('INSERT INTO Student (user_id, course, year_level, student_number) VALUES (?, ?, ?, ?)');
        if ($s) {
            $s->bind_param('isss', $user_id, $course, $year, $student_number);
            $s->execute();
            $s->close();
        }
    }
} elseif ($role === 'Faculty') {
    $department = trim($_POST['department'] ?? '');
    $spec = trim($_POST['specialization'] ?? '');
    $faculty_number = trim($_POST['faculty_number'] ?? '');
    if ($profile_picture_path !== null) {
        $f = $conn->prepare('INSERT INTO Faculty (user_id, department, specialization, faculty_number, profile_picture) VALUES (?, ?, ?, ?, ?)');
        if ($f) {
            $f->bind_param('issss', $user_id, $department, $spec, $faculty_number, $profile_picture_path);
            $f->execute();
            $f->close();
        }
    } else {
        $f = $conn->prepare('INSERT INTO Faculty (user_id, department, specialization, faculty_number) VALUES (?, ?, ?, ?)');
        if ($f) {
            $f->bind_param('isss', $user_id, $department, $spec, $faculty_number);
            $f->execute();
            $f->close();
        }
    }
}

// Success
header('Location: ../public/login.php?success=' . urlencode('Account created. You can log in now.'));
exit;
