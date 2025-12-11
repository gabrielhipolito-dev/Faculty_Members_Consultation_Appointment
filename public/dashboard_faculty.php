<?php include __DIR__ . '/../actions/load_user.php'; ?>
<?php include __DIR__ . '/../actions/dashboard_faculty_handler.php'; ?>

<?php
// Get faculty_id from user_id
$stmt = $conn->prepare("SELECT faculty_id FROM Faculty WHERE user_id = ?");
$stmt->bind_param('i', $user['user_id']);
$stmt->execute();
$facultyRes = $stmt->get_result();
if (!$facultyRes || $facultyRes->num_rows === 0) {
    die("Faculty profile not found");
}
$faculty = $facultyRes->fetch_assoc();
$faculty_id = $faculty['faculty_id'];
$stmt->close();

// Get dashboard data
$stats = getFacultyAppointmentStats($faculty_id);
$upcomingAppointments = getFacultyUpcomingAppointments($faculty_id, 5);
$pendingAppointments = getFacultyPendingAppointments($faculty_id);
$todayAppointments = getFacultyTodayAppointments($faculty_id);
$availability = getFacultyAvailability($faculty_id);
?>

<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/partials/dashboard_sidebar.php'; ?>

<div class="container-fluid py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="container">
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="text-white">
                    <h2 class="fw-bold mb-2">Welcome back, <?php echo htmlspecialchars($user['name']); ?>! 👋</h2>
                    <p class="mb-0 opacity-75">Here's what's happening with your consultations today</p>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-body text-center">
                        <div class="rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; background-color: #FFF3E0; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 24px;">⏳</span>
                        </div>
                        <h3 class="fw-bold mb-1"><?php echo $stats['pending']; ?></h3>
                        <p class="text-muted small mb-0">Pending Requests</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-body text-center">
                        <div class="rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; background-color: #E8F5E9; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 24px;">✅</span>
                        </div>
                        <h3 class="fw-bold mb-1"><?php echo $stats['approved']; ?></h3>
                        <p class="text-muted small mb-0">Approved</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-body text-center">
                        <div class="rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; background-color: #E3F2FD; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 24px;">📅</span>
                        </div>
                        <h3 class="fw-bold mb-1"><?php echo $stats['today']; ?></h3>
                        <p class="text-muted small mb-0">Today's Appointments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-body text-center">
                        <div class="rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; background-color: #F3E5F5; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 24px;">📊</span>
                        </div>
                        <h3 class="fw-bold mb-1"><?php echo $stats['total']; ?></h3>
                        <p class="text-muted small mb-0">Total Appointments</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Pending Requests -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">⏳ Pending Appointment Requests</h5>
                            <span class="badge bg-warning text-dark"><?php echo count($pendingAppointments); ?> Pending</span>
                        </div>

                        <?php if (empty($pendingAppointments)): ?>
                            <div class="text-center py-5">
                                <span style="font-size: 48px;">✨</span>
                                <p class="text-muted mt-3 mb-0">No pending requests. You're all caught up!</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($pendingAppointments as $apt): ?>
                                    <div class="list-group-item px-0 py-3">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <img src="<?php echo htmlspecialchars(getFacultyProfileImage($apt['profile_picture'])); ?>" alt="Student" class="rounded-circle" width="50" height="50" style="object-fit: cover; border: 2px solid #f0f0f0;">
                                            </div>
                                            <div class="col">
                                                <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($apt['student_name']); ?></h6>
                                                <p class="mb-1 small text-muted"><?php echo htmlspecialchars($apt['course']); ?> - Year <?php echo $apt['year_level']; ?></p>
                                                <p class="mb-0 small">
                                                    <strong>Topic:</strong> <?php echo htmlspecialchars($apt['topic']); ?>
                                                </p>
                                            </div>
                                            <div class="col-auto text-end">
                                                <p class="mb-1 small fw-bold text-primary">
                                                    <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?>
                                                </p>
                                                <p class="mb-2 small text-muted">
                                                    <?php echo date('h:i A', strtotime($apt['start_time'])); ?> - <?php echo date('h:i A', strtotime($apt['end_time'])); ?>
                                                </p>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-success" onclick="handleAppointment(<?php echo $apt['appointment_id']; ?>, 'Approved')">
                                                        <i class="bi bi-check-lg"></i> Approve
                                                    </button>
                                                    <button class="btn btn-danger" onclick="handleAppointment(<?php echo $apt['appointment_id']; ?>, 'Rejected')">
                                                        <i class="bi bi-x-lg"></i> Reject
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted"><strong>Purpose:</strong> <?php echo htmlspecialchars($apt['purpose']); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Upcoming Appointments -->
                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">📅 Upcoming Appointments</h5>

                        <?php if (empty($upcomingAppointments)): ?>
                            <div class="text-center py-5">
                                <span style="font-size: 48px;">📭</span>
                                <p class="text-muted mt-3 mb-0">No upcoming appointments scheduled</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($upcomingAppointments as $apt): ?>
                                    <div class="list-group-item px-0 py-3">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <img src="<?php echo htmlspecialchars(getFacultyProfileImage($apt['profile_picture'])); ?>" alt="Student" class="rounded-circle" width="45" height="45" style="object-fit: cover;">
                                            </div>
                                            <div class="col">
                                                <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($apt['student_name']); ?></h6>
                                                <p class="mb-0 small text-muted"><?php echo htmlspecialchars($apt['topic']); ?></p>
                                            </div>
                                            <div class="col-auto text-end">
                                                <p class="mb-1 small fw-bold"><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></p>
                                                <p class="mb-1 small text-muted"><?php echo date('h:i A', strtotime($apt['start_time'])); ?></p>
                                                <span class="badge bg-<?php echo $apt['status'] === 'Approved' ? 'success' : 'warning'; ?>">
                                                    <?php echo $apt['status']; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Today's Schedule -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">📌 Today's Schedule</h5>

                        <?php if (empty($todayAppointments)): ?>
                            <div class="text-center py-4">
                                <span style="font-size: 36px;">☀️</span>
                                <p class="text-muted mt-2 mb-0 small">No appointments today</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($todayAppointments as $apt): ?>
                                <div class="mb-3 p-3" style="background-color: #f8f9fa; border-radius: 10px; border-left: 4px solid #667eea;">
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="<?php echo htmlspecialchars(getFacultyProfileImage($apt['profile_picture'])); ?>" alt="Student" class="rounded-circle me-2" width="35" height="35" style="object-fit: cover;">
                                        <div>
                                            <p class="mb-0 fw-bold small"><?php echo htmlspecialchars($apt['student_name']); ?></p>
                                        </div>
                                    </div>
                                    <p class="mb-1 small text-muted"><?php echo htmlspecialchars($apt['topic']); ?></p>
                                    <p class="mb-0 small text-primary fw-bold">
                                        <i class="bi bi-clock"></i> <?php echo date('h:i A', strtotime($apt['start_time'])); ?> - <?php echo date('h:i A', strtotime($apt['end_time'])); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Availability Schedule -->
                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">🕒 My Availability</h5>

                        <?php if (empty($availability)): ?>
                            <p class="text-muted small text-center py-3">No schedule set</p>
                        <?php else: ?>
                            <?php foreach ($availability as $slot): ?>
                                <div class="mb-2 p-2" style="background-color: #f8f9fa; border-radius: 8px;">
                                    <p class="mb-0 small">
                                        <strong class="text-primary"><?php echo htmlspecialchars($slot['day_of_week']); ?></strong><br>
                                        <span class="text-muted"><?php echo date('h:i A', strtotime($slot['start_time'])); ?> - <?php echo date('h:i A', strtotime($slot['end_time'])); ?></span>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <a href="manage_schedule.php" class="btn btn-outline-primary btn-sm w-100 mt-3" style="border-radius: 8px;">
                            Manage Schedule
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    .list-group-item {
        border: none;
        border-bottom: 1px solid #f0f0f0;
    }

    .list-group-item:last-child {
        border-bottom: none;
    }

    .btn-group-sm > .btn {
        padding: 4px 12px;
        font-size: 0.85rem;
        border-radius: 6px;
    }
</style>

<script>
    function handleAppointment(appointmentId, action) {
        if (!confirm(`Are you sure you want to ${action.toLowerCase()} this appointment?`)) {
            return;
        }

        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../actions/update_appointment.php';

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'appointment_id';
        idInput.value = appointmentId;

        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = action;

        form.appendChild(idInput);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/profile_modal.php'; ?>
