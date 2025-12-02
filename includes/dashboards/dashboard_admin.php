<?php
// Admin dashboard partial
?>
<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">Admin Profile</h5>
        <?php if ($user): ?>
            <table class="table table-borderless table-sm">
                <tr><th>Name</th><td><?php echo htmlspecialchars($user['name']); ?></td></tr>
                <tr><th>Username</th><td><?php echo htmlspecialchars($user['username']); ?></td></tr>
                <tr><th>Email</th><td><?php echo htmlspecialchars($user['email']); ?></td></tr>
                <tr><th>Role</th><td><?php echo htmlspecialchars($user['role']); ?></td></tr>
            </table>
        <?php else: ?>
            <p class="text-muted">No profile information available.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Administration</h5>
        <p class="small text-muted">Administrative actions</p>
        <a href="manage_users.php" class="btn btn-outline-primary btn-sm me-2">Manage Users</a>
        <a href="register.php" class="btn btn-primary btn-sm me-2">Create Account</a>
        <a href="reports.php" class="btn btn-outline-secondary btn-sm">View Reports</a>
    </div>
</div>
