<?php include __DIR__ . '/../actions/load_user.php'; ?>
<?php include __DIR__ . '/../actions/dashboard_admin_handler.php'; ?>

<?php
if (($user['role'] ?? '') !== 'Admin') {
    header('Location: index.php?error=Only admins can access this dashboard');
    exit;
}

$stats = getAdminStats($conn);
$pendingAppointments = getAdminPendingAppointments($conn, 10);
$upcomingAppointments = getAdminUpcomingAppointments($conn, 8);
$todayAppointments = getAdminTodayAppointments($conn, 6);
?>

<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/partials/dashboard_sidebar.php'; ?>

<div class="container-fluid py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <div class="text-white">
                    <h2 class="fw-bold mb-2">Admin Control Center</h2>
                    <p class="mb-0 opacity-75">Monitor students, faculty, and all consultations</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-body text-center">
                        <div class="rounded-circle mx-auto mb-2" style="width: 48px; height: 48px; background:#E3F2FD; display:flex; align-items:center; justify-content:center;">👥</div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['students']; ?></h4>
                        <small class="text-muted">Active Students</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-body text-center">
                        <div class="rounded-circle mx-auto mb-2" style="width: 48px; height: 48px; background:#E8F5E9; display:flex; align-items:center; justify-content:center;">🎓</div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['faculty']; ?></h4>
                        <small class="text-muted">Active Faculty</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-body text-center">
                        <div class="rounded-circle mx-auto mb-2" style="width: 48px; height: 48px; background:#FFF3E0; display:flex; align-items:center; justify-content:center;">⏳</div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['pending']; ?></h4>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-body text-center">
                        <div class="rounded-circle mx-auto mb-2" style="width: 48px; height: 48px; background:#E3F2FD; display:flex; align-items:center; justify-content:center;">📅</div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['today']; ?></h4>
                        <small class="text-muted">Today</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-body text-center">
                        <div class="rounded-circle mx-auto mb-2" style="width: 48px; height: 48px; background:#F3E5F5; display:flex; align-items:center; justify-content:center;">🚀</div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['upcoming']; ?></h4>
                        <small class="text-muted">Upcoming</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-body text-center">
                        <div class="rounded-circle mx-auto mb-2" style="width: 48px; height: 48px; background:#E0F7FA; display:flex; align-items:center; justify-content:center;">📊</div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['totalAppointments']; ?></h4>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Pending Requests -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">⏳ Pending Appointments</h5>
                            <span class="badge bg-warning text-dark"><?php echo count($pendingAppointments); ?> pending</span>
                        </div>

                        <?php if (empty($pendingAppointments)): ?>
                            <p class="text-muted text-center py-4 mb-0">No pending requests</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($pendingAppointments as $apt): ?>
                                    <div class="list-group-item px-0 py-3">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?php echo htmlspecialchars(adminResolveProfileImage($apt['student_pic'])); ?>" alt="Student" width="42" height="42" style="object-fit: cover; border-radius: 50%; background:#f0f0f0;">
                                                <div>
                                                    <div class="fw-bold">Student: <?php echo htmlspecialchars($apt['student_name']); ?></div>
                                                    <div class="text-muted small">Topic: <?php echo htmlspecialchars($apt['topic']); ?></div>
                                                </div>
                                            </div>
                                            <div class="text-end small">
                                                <div class="fw-bold text-primary"><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></div>
                                                <div class="text-muted"><?php echo htmlspecialchars($apt['day_of_week']); ?> · <?php echo date('h:i A', strtotime($apt['start_time'])); ?> - <?php echo date('h:i A', strtotime($apt['end_time'])); ?></div>
                                                <div class="text-muted">Faculty: <?php echo htmlspecialchars($apt['faculty_name']); ?></div>
                                            </div>
                                        </div>
                                        <div class="mt-2 text-muted small">Purpose: <?php echo htmlspecialchars($apt['purpose']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Upcoming / Today -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">📅 Today</h5>
                            <span class="badge bg-success"><?php echo count($todayAppointments); ?> scheduled</span>
                        </div>
                        <?php if (empty($todayAppointments)): ?>
                            <p class="text-muted text-center py-3 mb-0">No appointments today</p>
                        <?php else: ?>
                            <?php foreach ($todayAppointments as $apt): ?>
                                <div class="mb-3 p-3" style="background:#f8f9fa; border-radius: 10px;">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($apt['student_name']); ?></div>
                                            <div class="text-muted small">Faculty: <?php echo htmlspecialchars($apt['faculty_name']); ?></div>
                                        </div>
                                        <div class="text-primary fw-bold"><?php echo date('h:i A', strtotime($apt['start_time'])); ?> - <?php echo date('h:i A', strtotime($apt['end_time'])); ?></div>
                                    </div>
                                    <div class="text-muted small mt-1">Topic: <?php echo htmlspecialchars($apt['topic']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">🚀 Upcoming</h5>
                            <span class="badge bg-primary">Next <?php echo count($upcomingAppointments); ?></span>
                        </div>
                        <?php if (empty($upcomingAppointments)): ?>
                            <p class="text-muted text-center py-3 mb-0">No upcoming approvals</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($upcomingAppointments as $apt): ?>
                                    <div class="list-group-item px-0 py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($apt['student_name']); ?></div>
                                                <div class="text-muted small">Faculty: <?php echo htmlspecialchars($apt['faculty_name']); ?></div>
                                            </div>
                                            <div class="text-end small">
                                                <div class="fw-bold text-primary"><?php echo date('M d', strtotime($apt['appointment_date'])); ?></div>
                                                <div class="text-muted"><?php echo htmlspecialchars($apt['day_of_week']); ?> · <?php echo date('h:i A', strtotime($apt['start_time'])); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card { transition: all 0.2s ease; }
    .card:hover { transform: translateY(-2px); }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/profile_modal.php'; ?>
