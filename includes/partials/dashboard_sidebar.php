<?php
// Top navbar partial: expects `$user` and `$conn` to be in scope
require_once __DIR__ . '/../function.php';

// Ensure $user is defined
if (!isset($user)) {
    $user = null;
}

$avatar = get_avatar_path($conn, $user);
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom mb-4">
    <div class="container-fluid">
        <span class="navbar-brand">Dashboard</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                
                <li class="nav-item">
                    <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Profile" class="rounded-circle ms-3" style="width:40px;height:40px;object-fit:cover;cursor:pointer;" data-bs-toggle="modal" data-bs-target="#profileModal">
                </li>
                <?php if (!empty($user) && strtolower($user['role'] ?? '') === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Manage Accounts</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <?php if ($user): ?>
                            <?php echo htmlspecialchars($user['name']); ?>
                        <?php else: ?>
                            User
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">View Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Sign Out</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
