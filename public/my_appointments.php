<?php
// public/my_appointments.php - Student appointments list
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../actions/load_user.php';

// Guard: must be student
if (($user['role'] ?? '') !== 'Student') {
    header('Location: index.php?error=Only students can view appointments');
    exit;
}

// Get student_id from user
$stmt = $conn->prepare('SELECT student_id FROM Student WHERE user_id = ?');
$stmt->bind_param('i', $user['user_id']);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    header('Location: dashboard_student.php?error=Student profile not found');
    exit;
}
$student = $res->fetch_assoc();
$student_id = $student['student_id'];
$stmt->close();

// Helper to resolve profile image
function resolveProfileImage($rawPath) {
    $default = '../uploads/profile_pics/default_image.png';
    if (empty($rawPath)) return $default;
    if (strpos($rawPath, '/') === 0) {
        return '..' . $rawPath;
    }
    return $rawPath;
}

// Fetch appointments for this student
$appointments = [];
$stmt = $conn->prepare('
    SELECT 
        a.appointment_id,
        a.appointment_date,
        a.topic,
        a.purpose,
        a.status,
        av.day_of_week,
        av.start_time,
        av.end_time,
        u.name AS faculty_name,
        u.profile_picture
    FROM Appointments a
    INNER JOIN Availability av ON a.availability_id = av.availability_id
    INNER JOIN Faculty f ON a.faculty_id = f.faculty_id
    INNER JOIN Users u ON f.user_id = u.user_id
    WHERE a.student_id = ?
    ORDER BY a.appointment_date DESC, av.start_time DESC
');
$stmt->bind_param('i', $student_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/partials/dashboard_sidebar.php';
?>

<div class="container-fluid py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div class="text-white">
                    <h2 class="fw-bold mb-1">📅 My Appointments</h2>
                    <p class="mb-0 opacity-75">See your approved and pending consultations</p>
                </div>
                <a href="dashboard_student.php" class="btn btn-light" style="border-radius: 8px;">← Back to Dashboard</a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 10px;">
                <strong>Success!</strong> <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 10px;">
                <strong>Error!</strong> <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-body p-4">
                        <?php if (empty($appointments)): ?>
                            <div class="text-center py-5">
                                <span style="font-size: 48px;">🗓️</span>
                                <p class="text-muted mt-3 mb-1">No appointments yet.</p>
                                <a href="search_professors.php" class="btn btn-primary" style="border-radius: 8px;">Book a Consultation</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr class="text-muted small">
                                            <th>Professor</th>
                                            <th>Topic</th>
                                            <th>Date & Time</th>
                                            <th>Status</th>
                                            <th>Purpose</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appointments as $apt): ?>
                                            <?php
                                                $badgeClass = 'secondary';
                                                if ($apt['status'] === 'Approved') $badgeClass = 'success';
                                                elseif ($apt['status'] === 'Pending') $badgeClass = 'warning';
                                                elseif ($apt['status'] === 'Rejected') $badgeClass = 'danger';
                                            ?>
                                            <tr>
                                                <td class="d-flex align-items-center gap-2">
                                                    <img src="<?php echo htmlspecialchars(resolveProfileImage($apt['profile_picture'])); ?>" alt="Professor" width="40" height="40" style="object-fit: cover; border-radius: 50%; background: #f0f0f0;">
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($apt['faculty_name']); ?></div>
                                                        <div class="text-muted small"><?php echo htmlspecialchars($apt['day_of_week']); ?></div>
                                                    </div>
                                                </td>
                                                <td class="fw-semibold"><?php echo htmlspecialchars($apt['topic']); ?></td>
                                                <td>
                                                    <div class="fw-bold"><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></div>
                                                    <div class="text-muted small"><?php echo date('h:i A', strtotime($apt['start_time'])) . ' - ' . date('h:i A', strtotime($apt['end_time'])); ?></div>
                                                </td>
                                                <td><span class="badge bg-<?php echo $badgeClass; ?>"><?php echo htmlspecialchars($apt['status']); ?></span></td>
                                                <td class="text-muted small" style="max-width: 240px;"><?php echo nl2br(htmlspecialchars($apt['purpose'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table > :not(caption) > * > * { padding: 0.95rem 0.75rem; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/profile_modal.php'; ?>
