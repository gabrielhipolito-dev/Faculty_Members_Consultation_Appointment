<?php
/**
 * Dashboard Student - Database Handler
 * Centralizes all database queries and data processing for student dashboard
 * Handles: profile pictures, appointments, professors, statistics
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

/**
 * Resolve image path with robust fallback handling
 * Normalizes paths from database and verifies file existence
 */
function getStudentProfileImage($rawPath) {
    $default = '/uploads/profile_pics/default_image.png';
    
    if (!$rawPath || empty(trim($rawPath))) {
        return $default;
    }
    
    $root = dirname(__DIR__); // project root
    $path = trim(str_replace('\\', '/', $rawPath));
    
    // Ensure leading slash
    if (strpos($path, '/') !== 0) {
        $path = '/' . $path;
    }
    
    // Check file exists
    $fullPath = $root . $path;
    if (file_exists($fullPath) && is_file($fullPath)) {
        return $path;
    }
    
    // Try with just basename in uploads folder
    $baseName = basename($path);
    $altPath = '/uploads/profile_pics/' . $baseName;
    $altFullPath = $root . $altPath;
    
    if (file_exists($altFullPath) && is_file($altFullPath)) {
        return $altPath;
    }
    
    return $default;
}

/**
 * Get student's upcoming appointments
 */
function getStudentUpcomingAppointments($studentId, $limit = 5) {
    global $conn;
    
    try {
        $stmt = $conn->prepare('
            SELECT 
                a.appointment_id,
                a.appointment_date,
                a.status,
                av.start_time,
                av.end_time,
                av.day_of_week,
                u.name as professor_name,
                u.profile_picture
            FROM Appointments a
            INNER JOIN Availability av ON a.availability_id = av.availability_id
            INNER JOIN Faculty f ON a.faculty_id = f.faculty_id
            INNER JOIN Users u ON f.user_id = u.user_id
            WHERE a.student_id = ? 
                AND a.appointment_date >= CURDATE()
                AND a.status IN ("Pending", "Approved")
            ORDER BY a.appointment_date ASC, av.start_time ASC
            LIMIT ?
        ');
        
        $stmt->bind_param('ii', $studentId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $appointments = [];
        
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
        
        $stmt->close();
        return $appointments;
    } catch (Exception $e) {
        error_log('Error fetching appointments: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get featured/available professors
 */
function getStudentFeaturedProfessors($studentId, $limit = 6) {
    global $conn;
    
    try {
        $stmt = $conn->prepare('
            SELECT 
                u.user_id,
                u.name,
                u.email,
                u.profile_picture,
                f.department,
                f.specialization,
                (SELECT COUNT(*) FROM Appointments a 
                 INNER JOIN Student s ON a.student_id = s.student_id
                 WHERE a.faculty_id = f.faculty_id AND s.user_id = ?) as interaction_count
            FROM Users u
            INNER JOIN Faculty f ON u.user_id = f.user_id
            WHERE u.role = "Faculty" AND u.status = "Active"
            ORDER BY u.name ASC
            LIMIT ?
        ');
        
        $stmt->bind_param('ii', $studentId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $professors = [];
        
        while ($row = $result->fetch_assoc()) {
            $professors[] = $row;
        }
        
        $stmt->close();
        return $professors;
    } catch (Exception $e) {
        error_log('Error fetching professors: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get student dashboard statistics
 */
function getStudentStats($studentId) {
    global $conn;
    
    $stats = [
        'upcoming_count' => 0,
        'completed_count' => 0,
        'professors_available' => 0,
        'average_rating' => 4.8
    ];
    
    try {
        // Upcoming appointments
        $stmt = $conn->prepare('
            SELECT COUNT(*) as count FROM Appointments
            WHERE student_id = ? AND appointment_date >= CURDATE() 
            AND status IN ("Pending", "Approved")
        ');
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stats['upcoming_count'] = $row['count'] ?? 0;
        $stmt->close();
        
        // Completed appointments
        $stmt = $conn->prepare('
            SELECT COUNT(*) as count FROM Appointments
            WHERE student_id = ? AND status = "Approved" AND appointment_date < CURDATE()
        ');
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stats['completed_count'] = $row['count'] ?? 0;
        $stmt->close();
        
        // Available professors count
        $stmt = $conn->prepare('SELECT COUNT(*) as count FROM Users WHERE role = "faculty"');
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stats['professors_available'] = $row['count'] ?? 0;
        $stmt->close();
        
    } catch (Exception $e) {
        error_log('Error fetching stats: ' . $e->getMessage());
    }
    
    return $stats;
}

// Pre-compute commonly used data
if (isset($_SESSION['user_id'])) {
    $studentStats = getStudentStats($_SESSION['user_id']);
    $upcomingAppointments = getStudentUpcomingAppointments($_SESSION['user_id']);
    $featuredProfessors = getStudentFeaturedProfessors($_SESSION['user_id']);
    
    // Resolve profile image for current user
    if (isset($user) && isset($user['profile_picture'])) {
        $user['profile_picture'] = getStudentProfileImage($user['profile_picture']);
    }
}

?>
