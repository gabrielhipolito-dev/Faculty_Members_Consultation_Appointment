<?php include __DIR__ . '/../actions/load_user.php'; ?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<?php include __DIR__ . '/../includes/partials/dashboard_sidebar.php'; ?>

<div class="container-fluid" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 40px 0;">
    <div class="container">
        <!-- Welcome Header -->
        <div class="row mb-5">
            <div class="col-md-8">
                <h1 class="text-white mb-2" style="font-size: 2.5rem; font-weight: 700;">
                    Welcome, <?php echo htmlspecialchars($user['name'] ?? 'Student'); ?>!
                </h1>
                <p class="text-white-50 mb-0">Manage your consultations and book appointments with professors</p>
            </div>
        </div>

        <!-- Stats/Quick Info Cards -->
        <div class="row g-3 mb-5">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body text-center py-4">
                        <div style="font-size: 2rem; color: #667eea; margin-bottom: 10px;">📅</div>
                        <h6 class="card-title mb-1 fw-bold">Upcoming</h6>
                        <p class="mb-0" style="font-size: 1.5rem; font-weight: 700; color: #333;">0</p>
                        <small class="text-muted">Appointments</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body text-center py-4">
                        <div style="font-size: 2rem; color: #FF6B35; margin-bottom: 10px;">✅</div>
                        <h6 class="card-title mb-1 fw-bold">Completed</h6>
                        <p class="mb-0" style="font-size: 1.5rem; font-weight: 700; color: #333;">0</p>
                        <small class="text-muted">Consultations</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body text-center py-4">
                        <div style="font-size: 2rem; color: #764ba2; margin-bottom: 10px;">👨‍🏫</div>
                        <h6 class="card-title mb-1 fw-bold">Professors</h6>
                        <p class="mb-0" style="font-size: 1.5rem; font-weight: 700; color: #333;">15</p>
                        <small class="text-muted">Available</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body text-center py-4">
                        <div style="font-size: 2rem; color: #4CAF50; margin-bottom: 10px;">⭐</div>
                        <h6 class="card-title mb-1 fw-bold">Rating</h6>
                        <p class="mb-0" style="font-size: 1.5rem; font-weight: 700; color: #333;">4.8</p>
                        <small class="text-muted">Average</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row g-4">
            <!-- Profile & Quick Actions -->
            <div class="col-md-4">
                <!-- Profile Card -->
                <div class="card border-0 shadow-lg rounded-3 overflow-hidden mb-4">
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100px;"></div>
                    <div class="card-body text-center" style="margin-top: -50px;">
                        <?php
                        $profilePath = $user['profile_picture'] ?? '/uploads/profile_pics/default_image.png';
                        $fullPath = __DIR__ . '/..' . ltrim($profilePath, '/');
                        if (!file_exists($fullPath)) {
                            $profilePath = '/uploads/profile_pics/default_image.png';
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($profilePath); ?>" alt="Profile" style="width: 100px; height: 100px; border-radius: 50%; border: 5px solid white; object-fit: cover; margin-bottom: 15px;">
                        
                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($user['name'] ?? 'Student'); ?></h5>
                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($user['email'] ?? 'student@university.edu'); ?></p>
                        
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill mb-3" data-bs-toggle="modal" data-bs-target="#profileModal">
                            View Full Profile
                        </button>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card border-0 shadow-lg rounded-3">
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-3">Quick Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="search_professors.php" class="btn btn-lg rounded-3" style="background-color: #FF6B35; color: white; font-weight: 600; padding: 12px;">
                                🔍 Search Professors
                            </a>
                            <a href="my_appointments.php" class="btn btn-outline-primary btn-lg rounded-3" style="font-weight: 600; padding: 12px;">
                                📅 My Appointments
                            </a>
                            <a href="logout.php" class="btn btn-outline-danger btn-lg rounded-3" style="font-weight: 600; padding: 12px;">
                                🚪 Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Content -->
            <div class="col-md-8">
                <!-- Upcoming Appointments Section -->
                <div class="card border-0 shadow-lg rounded-3 mb-4">
                    <div class="card-header bg-white border-0 rounded-top-3 pt-4 pb-3">
                        <h5 class="fw-bold mb-0">📅 Upcoming Appointments</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-5 text-muted">
                            <p style="font-size: 3rem; margin-bottom: 10px;">📭</p>
                            <p class="mb-2">No upcoming appointments</p>
                            <p class="small">Book your first consultation with a professor</p>
                            <a href="search_professors.php" class="btn btn-sm btn-primary rounded-pill mt-3">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recently Viewed / Featured Professors -->
                <div class="card border-0 shadow-lg rounded-3">
                    <div class="card-header bg-white border-0 rounded-top-3 pt-4 pb-3">
                        <h5 class="fw-bold mb-0">👨‍🏫 Featured Professors</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Professor Card 1 -->
                            <div class="col-md-6">
                                <div class="p-3 rounded-3" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                                    <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); margin-bottom: 10px;"></div>
                                    <h6 class="fw-bold mb-1">Dr. Sample Professor</h6>
                                    <p class="small text-muted mb-2">Specialization</p>
                                    <small class="badge bg-light text-dark">Available Mon-Fri</small>
                                </div>
                            </div>
                            <!-- Professor Card 2 -->
                            <div class="col-md-6">
                                <div class="p-3 rounded-3" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                                    <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); margin-bottom: 10px;"></div>
                                    <h6 class="fw-bold mb-1">Prof. Another Name</h6>
                                    <p class="small text-muted mb-2">Specialization</p>
                                    <small class="badge bg-light text-dark">Available Tue-Thu</small>
                                </div>
                            </div>
                        </div>
                        <a href="search_professors.php" class="btn btn-outline-primary btn-sm w-100 rounded-3 mt-3">
                            View All Professors →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        font-family: 'Inter', sans-serif;
    }

    .rounded-3 {
        border-radius: 12px !important;
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
    }

    .btn {
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php include __DIR__ . '/../includes/profile_modal.php'; ?>
