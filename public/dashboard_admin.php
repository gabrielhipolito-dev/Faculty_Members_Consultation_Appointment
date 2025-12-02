<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first");
    exit();
}
?>
<?php
require_once __DIR__ . '/../config/db.php';
$user = null;
try {
        $stmt = $conn->prepare('SELECT user_id, name, username, email, role, birthdate, gender, contact_number, address FROM Users WHERE user_id = ? LIMIT 1');
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
                $user = $res->fetch_assoc();
        }
        $stmt->close();
} catch (Exception $e) {
        $user = null;
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <?php
                        // Determine avatar path: prefer uploaded profile_picture from Users, Student, or Faculty tables; otherwise default
                        $avatar = '../uploads/profile_pics/default.png';
                        if ($user) {
                            $storedPath = null;

                            // If Users table contains profile_picture (not typical here), use it
                            if (isset($user['profile_picture']) && !empty($user['profile_picture'])) {
                                $storedPath = $user['profile_picture'];
                            }

                            // If not found on Users, try role-specific tables
                            if (empty($storedPath)) {
                                $role = $user['role'] ?? '';
                                if (strtolower($role) === 'student') {
                                    $s = $conn->prepare('SELECT profile_picture FROM Student WHERE user_id = ? LIMIT 1');
                                    $s->bind_param('i', $_SESSION['user_id']);
                                    $s->execute();
                                    $sres = $s->get_result();
                                    if ($sres && $sres->num_rows === 1) {
                                        $srow = $sres->fetch_assoc();
                                        $storedPath = $srow['profile_picture'] ?? null;
                                    }
                                    $s->close();
                                } elseif (strtolower($role) === 'faculty') {
                                    $f = $conn->prepare('SELECT profile_picture FROM Faculty WHERE user_id = ? LIMIT 1');
                                    $f->bind_param('i', $_SESSION['user_id']);
                                    $f->execute();
                                    $fres = $f->get_result();
                                    if ($fres && $fres->num_rows === 1) {
                                        $frow = $fres->fetch_assoc();
                                        $storedPath = $frow['profile_picture'] ?? null;
                                    }
                                    $f->close();
                                }
                            }

                            // Normalize stored path and check file existence
                            if (!empty($storedPath)) {
                                // storedPath usually like 'uploads/profile_pics/filename.png'
                                $candidate = __DIR__ . '/../' . ltrim($storedPath, '/');
                                if (file_exists($candidate)) {
                                    $avatar = '../' . ltrim($storedPath, '/');
                                } else {
                                    // try if only filename stored in DB
                                    $candidate2 = __DIR__ . '/../uploads/profile_pics/' . basename($storedPath);
                                    if (file_exists($candidate2)) {
                                        $avatar = '../uploads/profile_pics/' . basename($storedPath);
                                    }
                                }
                            }
                        }
                    ?>

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

        <div class="col-md-8">
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
                                <table class="table table-borderless table-sm">
                                    <tr><th>Name</th><td><?php echo htmlspecialchars($user['name']); ?></td></tr>
                                    <tr><th>Username</th><td><?php echo htmlspecialchars($user['username']); ?></td></tr>
                                    <tr><th>Email</th><td><?php echo htmlspecialchars($user['email']); ?></td></tr>
                                </table>
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
