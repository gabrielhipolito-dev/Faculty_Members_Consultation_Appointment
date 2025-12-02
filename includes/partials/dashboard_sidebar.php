<?php
// Sidebar partial: expects `$user` and `$conn` to be in scope
require_once __DIR__ . '/../function.php';

$avatar = get_avatar_path($conn, $user);
?>
<div class="col-md-4">
    <div class="card mb-3">
        <div class="card-body">
            <div class="text-center mb-2">
                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Profile" class="rounded-circle" style="width:80px;height:80px;object-fit:cover;cursor:pointer;" data-bs-toggle="modal" data-bs-target="#profileModal">
            </div>

            <h5 class="card-title">Welcome</h5>
            <p class="card-text">
                <?php if ($user): ?>
                    <strong><?php echo htmlspecialchars($user['name']); ?></strong><br>
                    <small class="text-muted"><?php echo htmlspecialchars($user['role']); ?></small>
                <?php else: ?>
                    <em>User information not available</em>
                <?php endif; ?>
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
