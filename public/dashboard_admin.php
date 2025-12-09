<?php include __DIR__ . '/../actions/load_user.php'; ?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<?php include __DIR__ . '/../includes/partials/dashboard_sidebar.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <?php
                // Include role-specific dashboard partials from includes/dashboards/
                $role = strtolower($user['role'] ?? '');
                $partial = __DIR__ . '/../includes/dashboards/dashboard_' . $role . '.php';
                if (file_exists($partial)) {
                    include $partial;
                } else {
                    // fallback: show basic profile + quick actions
                    ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Profile</h5>
                            <?php if ($user): ?>
                                <p class="mb-2"><strong><?php echo htmlspecialchars($user['name']); ?></strong></p>
                                <p class="small text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#profileModal">View profile</button>
                            <?php else: ?>
                                <p class="text-muted">No profile information available.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Quick actions</h5>
                            <p class="small text-muted">Use these action links to navigate.</p>
                            <a href="appointments.php" class="btn btn-outline-primary btn-sm me-2">My Appointments</a>
                            <a href="availability.php" class="btn btn-outline-secondary btn-sm">Availability</a>
                        </div>
                    </div>
                    <?php
                }
            ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php include __DIR__ . '/../includes/profile_modal.php'; ?>
