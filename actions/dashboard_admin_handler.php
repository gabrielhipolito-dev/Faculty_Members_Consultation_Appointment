<?php
// actions/dashboard_admin_handler.php - Data provider for admin dashboard
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';

function adminResolveProfileImage($rawPath) {
    $default = '../uploads/profile_pics/default_image.png';
    if (!$rawPath || trim($rawPath) === '') return $default;
    if (strpos($rawPath, '/') === 0) return '..' . $rawPath;
    return $rawPath;
}

function getAdminStats($conn) {
    $stats = [
        'faculty' => 0,
        'students' => 0,
        'pending' => 0,
        'today' => 0,
        'upcoming' => 0,
        'totalAppointments' => 0,
    ];

    $queries = [
        'faculty' => "SELECT COUNT(*) as c FROM Users WHERE role = 'Faculty' AND status = 'Active'",
        'students' => "SELECT COUNT(*) as c FROM Users WHERE role = 'Student' AND status = 'Active'",
        'pending' => "SELECT COUNT(*) as c FROM Appointments WHERE status = 'Pending'",
        'today' => "SELECT COUNT(*) as c FROM Appointments WHERE status = 'Approved' AND appointment_date = CURDATE()",
        'upcoming' => "SELECT COUNT(*) as c FROM Appointments WHERE status = 'Approved' AND appointment_date >= CURDATE()",
        'totalAppointments' => "SELECT COUNT(*) as c FROM Appointments",
    ];

    foreach ($queries as $key => $sql) {
        $res = $conn->query($sql);
        if ($res && ($row = $res->fetch_assoc())) {
            $stats[$key] = (int)$row['c'];
        }
    }

    return $stats;
}

function getAdminPendingAppointments($conn, $limit = 10) {
    $data = [];
    $stmt = $conn->prepare("\n        SELECT \n            a.appointment_id,\n            a.appointment_date,\n            a.topic,\n            a.purpose,\n            a.status,\n            av.day_of_week,\n            av.start_time,\n            av.end_time,\n            su.name AS student_name,\n            su.profile_picture AS student_pic,\n            fu.name AS faculty_name,\n            fu.profile_picture AS faculty_pic\n        FROM Appointments a\n        INNER JOIN Availability av ON a.availability_id = av.availability_id\n        INNER JOIN Student s ON a.student_id = s.student_id\n        INNER JOIN Users su ON s.user_id = su.user_id\n        INNER JOIN Faculty f ON a.faculty_id = f.faculty_id\n        INNER JOIN Users fu ON f.user_id = fu.user_id\n        WHERE a.status = 'Pending'\n        ORDER BY a.appointment_date ASC, av.start_time ASC\n        LIMIT ?\n    ");
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
    return $data;
}

function getAdminUpcomingAppointments($conn, $limit = 10) {
    $data = [];
    $stmt = $conn->prepare("\n        SELECT \n            a.appointment_id,\n            a.appointment_date,\n            a.topic,\n            a.status,\n            av.day_of_week,\n            av.start_time,\n            av.end_time,\n            su.name AS student_name,\n            su.profile_picture AS student_pic,\n            fu.name AS faculty_name,\n            fu.profile_picture AS faculty_pic\n        FROM Appointments a\n        INNER JOIN Availability av ON a.availability_id = av.availability_id\n        INNER JOIN Student s ON a.student_id = s.student_id\n        INNER JOIN Users su ON s.user_id = su.user_id\n        INNER JOIN Faculty f ON a.faculty_id = f.faculty_id\n        INNER JOIN Users fu ON f.user_id = fu.user_id\n        WHERE a.status = 'Approved' AND a.appointment_date >= CURDATE()\n        ORDER BY a.appointment_date ASC, av.start_time ASC\n        LIMIT ?\n    ");
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
    return $data;
}

function getAdminTodayAppointments($conn, $limit = 10) {
    $data = [];
    $stmt = $conn->prepare("\n        SELECT \n            a.appointment_id,\n            a.appointment_date,\n            a.topic,\n            av.day_of_week,\n            av.start_time,\n            av.end_time,\n            su.name AS student_name,\n            su.profile_picture AS student_pic,\n            fu.name AS faculty_name,\n            fu.profile_picture AS faculty_pic\n        FROM Appointments a\n        INNER JOIN Availability av ON a.availability_id = av.availability_id\n        INNER JOIN Student s ON a.student_id = s.student_id\n        INNER JOIN Users su ON s.user_id = su.user_id\n        INNER JOIN Faculty f ON a.faculty_id = f.faculty_id\n        INNER JOIN Users fu ON f.user_id = fu.user_id\n        WHERE a.status = 'Approved' AND a.appointment_date = CURDATE()\n        ORDER BY av.start_time ASC\n        LIMIT ?\n    ");
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
    return $data;
}

function getAdminAllStudents($conn) {
    $data = [];
    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.name,
            u.email,
            u.contact_number,
            u.profile_picture,
            st.course,
            st.year_level,
            st.student_number,
            u.status
        FROM Users u
        INNER JOIN Student st ON u.user_id = st.user_id
        WHERE u.role = 'Student'
        ORDER BY u.name ASC
    ");
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
    return $data;
}

function getAdminAllFaculty($conn) {
    $data = [];
    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.name,
            u.email,
            u.contact_number,
            u.profile_picture,
            f.department,
            f.specialization,
            f.faculty_number,
            u.status
        FROM Users u
        INNER JOIN Faculty f ON u.user_id = f.user_id
        WHERE u.role = 'Faculty'
        ORDER BY u.name ASC
    ");
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
    return $data;
}
