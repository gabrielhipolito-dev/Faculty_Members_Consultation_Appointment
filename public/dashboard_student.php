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
                        $professors = getStudentFeaturedProfessors($user['user_id'], 2);
                        if (!empty($professors)):
                    ?>
                        <div class="row">
                            <?php foreach ($professors as $prof): ?>
                                        <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-0" style="border-radius: 12px; overflow: hidden;">
                                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 80px;"></div>
                                        <div class="card-body text-center" style="margin-top: -40px; position: relative; z-index: 1;">
                                            <img src="<?php $imagePath = getStudentProfileImage($prof['profile_picture']); echo (strpos($imagePath, '/') === 0 ? '..' : '') . htmlspecialchars($imagePath); ?>" alt="Professor" class="rounded-circle mb-3" width="80" height="80" style="border: 4px solid white; object-fit: cover; background-color: #f0f0f0;">
                                            <h6 class="mb-1 fw-600"><?php echo htmlspecialchars($prof['name']); ?></h6>
                                            <small class="text-muted d-block mb-3"><?php echo htmlspecialchars($prof['specialization'] ?? 'Specialization'); ?></small>
                                            <p class="small text-muted mb-3"><?php echo htmlspecialchars($prof['department'] ?? 'Department'); ?></p>
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
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #667eea;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">📅 Upcoming Appointments</h5>
                        <a href="my_appointments.php" class="btn btn-sm btn-link" style="color: #667eea; text-decoration: none;">View All →</a>
                    </div>
                    
                    <?php
                        $appointments = getStudentUpcomingAppointments($user['user_id'], 5);
                        if (!empty($appointments)):
                    ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size: 0.95rem;">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th style="border-bottom: 2px solid #dee2e6;">Professor</th>
                                        <th style="border-bottom: 2px solid #dee2e6;">Topic</th>
                                        <th style="border-bottom: 2px solid #dee2e6;">Date & Time</th>
                                        <th style="border-bottom: 2px solid #dee2e6;">Status</th>
                                        <th style="border-bottom: 2px solid #dee2e6;">Purpose</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $apt): ?>
                                        <tr style="border-bottom: 1px solid #dee2e6;">
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?php $imagePath = getStudentProfileImage($apt['profile_picture']); echo (strpos($imagePath, '/') === 0 ? '..' : '') . htmlspecialchars($imagePath); ?>" alt="Professor" class="rounded-circle" width="36" height="36" style="object-fit: cover; background-color: #f0f0f0;">
                                                    <div>
                                                        <div style="font-weight: 600; color: #333;"><?php echo htmlspecialchars($apt['professor_name']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($apt['day_of_week']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle"><?php echo htmlspecialchars($apt['topic'] ?? 'N/A'); ?></td>
                                            <td class="align-middle">
                                                <div><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></div>
                                                <small class="text-muted"><?php echo date('h:i A', strtotime($apt['start_time'])) . ' - ' . date('h:i A', strtotime($apt['end_time'])); ?></small>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge" style="background-color: <?php echo strtolower($apt['status']) === 'approved' ? '#28a745' : '#ffc107'; ?>; color: <?php echo strtolower($apt['status']) === 'approved' ? 'white' : '#333'; ?>; padding: 6px 10px; font-size: 0.75rem;"><?php echo ucfirst($apt['status']); ?></span>
                                            </td>
                                            <td class="align-middle" style="color: #667eea; font-size: 0.9rem;"><?php echo htmlspecialchars(substr($apt['purpose'] ?? 'N/A', 0, 30)) . (strlen($apt['purpose'] ?? '') > 30 ? '...' : ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <p style="font-size: 2.5rem; margin-bottom: 1rem;">📭</p>
                            <p class="text-muted mb-3">No upcoming appointments yet</p>
                            <p class="small text-muted mb-3">Book an appointment with a professor to get started</p>
                            <a href="search_professors.php" class="btn btn-primary btn-sm" style="background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); border: none; border-radius: 6px; padding: 8px 20px;">Search Professors</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php include __DIR__ . '/../includes/profile_modal.php'; ?>
