<?php
// actions/get_professors.php - Database logic for retrieving professors and availability

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';

// Check if logged in and is a student
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php?error=Please login first");
    exit;
}

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
    header("Location: ../public/index.php?error=Only students can access this page");
    exit;
}

/**
 * Fetch all active professors with their availability
 * @return array Array of professor records with availability data
 */
function getProfessorsWithAvailability($conn) {
    $professors = [];
    
    $query = "
        SELECT 
            f.faculty_id, 
            f.department, 
            f.specialization,
            u.user_id,
            u.name, 
            u.email, 
            u.profile_picture
        FROM Faculty f
        INNER JOIN Users u ON u.user_id = f.user_id
        WHERE u.status = 'Active'
        ORDER BY u.name ASC
    ";
    
    $result = $conn->query($query);
    if (!$result) {
        return [];
    }
    
    while ($row = $result->fetch_assoc()) {
        // Get availability for this faculty
        $avail_stmt = $conn->prepare(
            "SELECT availability_id, day_of_week, start_time, end_time 
             FROM Availability 
             WHERE faculty_id = ? 
             ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')"
        );
        $avail_stmt->bind_param('i', $row['faculty_id']);
        $avail_stmt->execute();
        $avail_res = $avail_stmt->get_result();
        
        $availability = [];
        while ($avail_row = $avail_res->fetch_assoc()) {
            $availability[] = $avail_row;
        }
        $avail_stmt->close();
        
        $row['availability'] = $availability;
        $professors[] = $row;
    }
    
    return $professors;
}

// Get all professors
$professors = getProfessorsWithAvailability($conn);
?>
