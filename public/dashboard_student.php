<?php include __DIR__ . '/../actions/load_user.php'; ?>
<?php include __DIR__ . '/../actions/dashboard_student_handler.php'; ?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<?php include __DIR__ . '/../includes/partials/dashboard_sidebar.php'; ?>

<div class="container-fluid py-5">
    <div class="row">
        <!-- Left Sidebar - Quick Actions -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body">
                    <h5 class="card-title mb-4">Quick Actions</h5>
                    <div class="d-flex flex-column gap-3">
                        <a href="search_professors.php" class="btn btn-primary btn-lg" style="background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); border: none; border-radius: 8px; font-weight: 600; padding: 12px 24px;">
                            🔍 Search Professors
                        </a>
                        <a href="my_appointments.php" class="btn btn-outline-primary btn-lg" style="border-radius: 8px; font-weight: 600; padding: 12px 24px;">
                            📅 My Appointments
                        </a>
                        <a href="logout.php" class="btn btn-outline-danger btn-lg" style="border-radius: 8px; font-weight: 600; padding: 12px 24px;">
                            🚪 Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content Area -->
        <div class="col-lg-9">
            <!-- Featured Professors Section -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body">
                    <h5 class="card-title mb-4">🎓 Featured Professors</h5>
                    <p class="text-muted small mb-4">Book your first consultation with a professor</p>
                    
                    <?php
                        $professors = getStudentFeaturedProfessors($user['user_id'], 6);
                        if (!empty($professors)):
                    ?>
                        <div class="row">
                            <?php foreach ($professors as $prof): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-0" style="border-radius: 12px; overflow: hidden;">
                                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 80px;"></div>
                                        <div class="card-body text-center" style="margin-top: -40px; position: relative; z-index: 1;">
                                            <img src="<?php echo htmlspecialchars(getStudentProfileImage($prof['profile_picture'])); ?>" alt="Professor" class="rounded-circle mb-3" width="80" height="80" style="border: 4px solid white; object-fit: cover; background-color: #f0f0f0;">
                                            <h6 class="mb-1 fw-600"><?php echo htmlspecialchars($prof['name']); ?></h6>
                                            <small class="text-muted d-block mb-3">Specialization</small>
                                            <p class="small text-muted mb-3">Available Mon-Fri</p>
                                            <a href="search_professors.php?id=<?php echo $prof['user_id']; ?>" class="btn btn-sm btn-primary" style="border-radius: 6px;">Book Now</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4 mb-0">No professors available</p>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4">
                        <a href="search_professors.php" class="btn btn-outline-primary" style="border-radius: 8px; padding: 10px 30px;">View All Professors →</a>
                    </div>
                </div>
            </div>

            <!-- Upcoming Appointments Section -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body">
                    <h5 class="card-title mb-4">📅 Upcoming Appointments</h5>
                    
                    <?php
                        $appointments = getStudentUpcomingAppointments($user['user_id'], 5);
                        if (!empty($appointments)):
                    ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($appointments as $apt): ?>
                                <div class="list-group-item px-0 py-3 border-bottom">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <img src="<?php echo htmlspecialchars(getStudentProfileImage($apt['profile_picture'])); ?>" alt="Professor" class="rounded-circle" width="40" height="40" style="object-fit: cover; background-color: #f0f0f0;">
                                        </div>
                                        <div class="col">
                                            <p class="mb-1 fw-600"><?php echo htmlspecialchars($apt['professor_name']); ?></p>
                                            <small class="text-muted"><?php echo date('M d, Y @ h:i A', strtotime($apt['appointment_date'] . ' ' . $apt['appointment_time'])); ?></small>
                                        </div>
                                        <div class="col-auto">
                                            <span class="badge" style="background-color: <?php echo $apt['status'] === 'confirmed' ? '#28a745' : '#ffc107'; ?>; color: <?php echo $apt['status'] === 'confirmed' ? 'white' : '#000'; ?>;"><?php echo ucfirst($apt['status']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4 mb-0">No upcoming appointments</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php include __DIR__ . '/../includes/profile_modal.php'; ?>
