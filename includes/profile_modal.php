<?php
// Profile modal expects $user to be defined in the including file
// Get role-specific data if not already loaded
$role_data = null;
if (!empty($user) && isset($conn)) {
    $role = strtolower($user['role'] ?? '');
    
    try {
        if ($role === 'student') {
            $stmt = $conn->prepare("SELECT user_id, course, year_level, student_number FROM Student WHERE user_id = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("i", $user['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $role_data = $result->fetch_assoc();
                }
                $stmt->close();
            }
        } elseif ($role === 'faculty') {
            $stmt = $conn->prepare("SELECT user_id, department, specialization, faculty_number FROM Faculty WHERE user_id = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("i", $user['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $role_data = $result->fetch_assoc();
                }
                $stmt->close();
            }
        }
    } catch (Exception $e) {
        error_log('Error fetching role data: ' . $e->getMessage());
        $role_data = null;
    }
}
?>
<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileModalLabel">Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? '/uploads/profile_pics/default_image.png'); ?>" alt="Profile" class="rounded-circle mb-3" style="width:160px;height:160px;object-fit:cover;">
                    </div>
                    <div class="col-md-8">
                        <?php if (!empty($user)): ?>
                            <table class="table table-borderless">
                                <tr><th style="width: 40%;">Name</th><td><?php echo htmlspecialchars($user['name'] ?? ''); ?></td></tr>
                                <tr><th>Username</th><td><?php echo htmlspecialchars($user['username'] ?? ''); ?></td></tr>
                                <tr><th>Email</th><td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td></tr>
                                <tr><th>Role</th><td><span class="badge bg-primary"><?php echo htmlspecialchars(ucfirst($user['role'] ?? '')); ?></span></td></tr>
                                <tr><th>Contact</th><td><?php echo htmlspecialchars($user['contact_number'] ?? 'N/A'); ?></td></tr>
                                <tr><th>Birthdate</th><td><?php echo htmlspecialchars($user['birthdate'] ?? 'N/A'); ?></td></tr>
                                <tr><th>Gender</th><td><?php echo htmlspecialchars($user['gender'] ?? 'N/A'); ?></td></tr>
                                <tr><th>Address</th><td><?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?></td></tr>
                                
                                <!-- Student-specific fields -->
                                <?php if (strtolower($user['role'] ?? '') === 'student'): ?>
                                    <tr><th>Course</th><td><?php echo htmlspecialchars($role_data['course'] ?? 'N/A'); ?></td></tr>
                                    <tr><th>Year Level</th><td><?php echo htmlspecialchars($role_data['year_level'] ?? 'N/A'); ?></td></tr>
                                    <tr><th>Student Number</th><td><?php echo htmlspecialchars($role_data['student_number'] ?? 'N/A'); ?></td></tr>
                                <?php endif; ?>
                                
                                <!-- Faculty-specific fields -->
                                <?php if (strtolower($user['role'] ?? '') === 'faculty'): ?>
                                    <tr><th>Department</th><td><?php echo htmlspecialchars($role_data['department'] ?? 'N/A'); ?></td></tr>
                                    <tr><th>Specialization</th><td><?php echo htmlspecialchars($role_data['specialization'] ?? 'N/A'); ?></td></tr>
                                    <tr><th>Faculty Number</th><td><?php echo htmlspecialchars($role_data['faculty_number'] ?? 'N/A'); ?></td></tr>
                                <?php endif; ?>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">No profile information available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
