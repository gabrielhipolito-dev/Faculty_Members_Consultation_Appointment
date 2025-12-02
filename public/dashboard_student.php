<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=Please login first');
    exit();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../actions/dashboard_action.php';
$user = fetch_dashboard_user($conn, $_SESSION['user_id']);
include __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <img src="<?php echo htmlspecialchars($user['avatar'] ?? '../uploads/profile_pics/default.png'); ?>" alt="Profile" class="rounded-circle mb-2" style="width:80px;height:80px;object-fit:cover;cursor:pointer;" data-bs-toggle="modal" data-bs-target="#profileModal">
                    <h5 class="card-title">Welcome</h5>
                    <p class="card-text">
                        <strong><?php echo htmlspecialchars($user['name'] ?? ''); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($user['role'] ?? ''); ?></small>
                    </p>
                    <a href="logout.php" class="btn btn-sm btn-outline-secondary">Sign out</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6>Create</h6>
                    <p class="small text-muted">Create accounts or open the registration page.</p>
                    <a href="register.php" class="btn btn-primary">Create an account</a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <?php include __DIR__ . '/../includes/dashboards/dashboard_student.php'; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php include __DIR__ . '/../includes/profile_modal.php'; ?>
