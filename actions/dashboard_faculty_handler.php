<?php
// actions/dashboard_faculty_handler.php - Faculty dashboard data handler
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';

/**
 * Get faculty appointments statistics
 */
function getFacultyAppointmentStats($faculty_id) {
    global $conn;
    
    $stats = [
        'pending' => 0,
        'approved' => 0,
        'rejected' => 0,
        'total' => 0,
        'today' => 0,
        'upcoming' => 0
    ];
    
    // Count by status
    $stmt = $conn->prepare("
        SELECT 
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
            COUNT(*) as total,
            SUM(CASE WHEN appointment_date = CURDATE() THEN 1 ELSE 0 END) as today,
            SUM(CASE WHEN appointment_date >= CURDATE() AND status = 'Approved' THEN 1 ELSE 0 END) as upcoming
        FROM Appointments
        WHERE faculty_id = ?
    ");
    $stmt->bind_param('i', $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $stats = [
            'pending' => (int)$row['pending'],
            'approved' => (int)$row['approved'],
            'rejected' => (int)$row['rejected'],
            'total' => (int)$row['total'],
            'today' => (int)$row['today'],
            'upcoming' => (int)$row['upcoming']
        ];
    }
    
    $stmt->close();
    return $stats;
}

/**
 * Get faculty's upcoming appointments
 */
function getFacultyUpcomingAppointments($faculty_id, $limit = 10) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT 
            a.appointment_id,
            a.appointment_date,
            a.topic,
            a.purpose,
            a.status,
            s.student_id,
            s.course,
            s.year_level,
            u.user_id,
            u.name as student_name,
            u.email as student_email,
            u.profile_picture,
            av.day_of_week,
            av.start_time,
            av.end_time
        FROM Appointments a
        INNER JOIN Student s ON a.student_id = s.student_id
        INNER JOIN Users u ON s.user_id = u.user_id
        INNER JOIN Availability av ON a.availability_id = av.availability_id
        WHERE a.faculty_id = ? 
            AND a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date ASC, av.start_time ASC
        LIMIT ?
    ");
    $stmt->bind_param('ii', $faculty_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    $stmt->close();
    return $appointments;
}

/**
 * Get faculty's pending appointments
 */
function getFacultyPendingAppointments($faculty_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT 
            a.appointment_id,
            a.appointment_date,
            a.topic,
            a.purpose,
            a.status,
            s.student_id,
            s.course,
            s.year_level,
            u.user_id,
            u.name as student_name,
            u.email as student_email,
            u.profile_picture,
            av.day_of_week,
            av.start_time,
            av.end_time
        FROM Appointments a
        INNER JOIN Student s ON a.student_id = s.student_id
        INNER JOIN Users u ON s.user_id = u.user_id
        INNER JOIN Availability av ON a.availability_id = av.availability_id
        WHERE a.faculty_id = ? 
            AND a.status = 'Pending'
            AND a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date ASC, av.start_time ASC
    ");
    $stmt->bind_param('i', $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    $stmt->close();
    return $appointments;
}

/**
 * Get faculty's today's appointments
 */
function getFacultyTodayAppointments($faculty_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT 
            a.appointment_id,
            a.appointment_date,
            a.topic,
            a.purpose,
            a.status,
            s.student_id,
            s.course,
            s.year_level,
            u.user_id,
            u.name as student_name,
            u.email as student_email,
            u.profile_picture,
            av.day_of_week,
            av.start_time,
            av.end_time
        FROM Appointments a
        INNER JOIN Student s ON a.student_id = s.student_id
        INNER JOIN Users u ON s.user_id = u.user_id
        INNER JOIN Availability av ON a.availability_id = av.availability_id
        WHERE a.faculty_id = ? 
            AND a.appointment_date = CURDATE()
            AND a.status = 'Approved'
        ORDER BY av.start_time ASC
    ");
    $stmt->bind_param('i', $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    $stmt->close();
    return $appointments;
}

/**
 * Get faculty availability schedule
 */
function getFacultyAvailability($faculty_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT availability_id, day_of_week, start_time, end_time
        FROM Availability
        WHERE faculty_id = ?
        ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')
    ");
    $stmt->bind_param('i', $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $availability = [];
    while ($row = $result->fetch_assoc()) {
        $availability[] = $row;
    }
    
    $stmt->close();
    return $availability;
}

/**
 * Helper function to get profile image path
 */
function getFacultyProfileImage($profilePicture) {
    if (empty($profilePicture)) {
        return '../uploads/profile_pics/default_image.png';
    }
    
    if (strpos($profilePicture, '/') === 0) {
        return '..' . $profilePicture;
    }
    
    return $profilePicture;
}
?>
