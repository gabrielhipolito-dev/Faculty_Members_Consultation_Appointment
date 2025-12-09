<?php
// Profile modal expects $avatar and $user to be defined in the including file
// Get role-specific data if not already loaded
$role_data = null;
if (!empty($user) && !empty($conn)) {
    $role = strtolower($user['role'] ?? '');
    
    if ($role === 'student') {
        $stmt = $conn->prepare("SELECT course, year_level, student_number FROM Student WHERE user_id = ?");
        $stmt->bind_param("i", $user['user_id']);
        $stmt->execute();
        $role_data = $stmt->get_result()->fetch_assoc();
    } elseif ($role === 'faculty') {
        $stmt = $conn->prepare("SELECT department, specialization, faculty_number FROM Faculty WHERE user_id = ?");
        $stmt->bind_param("i", $user['user_id']);
        $stmt->execute();
        $role_data = $stmt->get_result()->fetch_assoc();
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
                        <img src="<?php echo htmlspecialchars($avatar ?? ($user['profile_picture'] ?? '/uploads/profile_pics/default_image.png')); ?>" alt="Profile" class="rounded-circle mb-3" style="width:160px;height:160px;object-fit:cover;">
                    </div>
                    <div class="col-md-8">
                        <?php if (!empty($user)): ?>
                            <table class="table table-borderless">
                                <tr><th>Name</th><td><?php echo htmlspecialchars($user['name']); ?></td></tr>
                                <tr><th>Username</th><td><?php echo htmlspecialchars($user['username']); ?></td></tr>
                                <tr><th>Email</th><td><?php echo htmlspecialchars($user['email']); ?></td></tr>
                                <tr><th>Role</th><td><?php echo htmlspecialchars($user['role']); ?></td></tr>
                                <tr><th>Contact</th><td><?php echo htmlspecialchars($user['contact_number'] ?? ''); ?></td></tr>
                                <tr><th>Birthdate</th><td><?php echo htmlspecialchars($user['birthdate'] ?? ''); ?></td></tr>
                                <tr><th>Gender</th><td><?php echo htmlspecialchars($user['gender'] ?? ''); ?></td></tr>
                                <tr><th>Address</th><td><?php echo htmlspecialchars($user['address'] ?? ''); ?></td></tr>
                                
                                <!-- Student-specific fields -->
                                <?php if (strtolower($user['role'] ?? '') === 'student' && !empty($role_data)): ?>
                                    <tr><th>Course</th><td><?php echo htmlspecialchars($role_data['course'] ?? ''); ?></td></tr>
                                    <tr><th>Year Level</th><td><?php echo htmlspecialchars($role_data['year_level'] ?? ''); ?></td></tr>
                                    <tr><th>Student Number</th><td><?php echo htmlspecialchars($role_data['student_number'] ?? ''); ?></td></tr>
                                <?php endif; ?>
                                
                                <!-- Faculty-specific fields -->
                                <?php if (strtolower($user['role'] ?? '') === 'faculty' && !empty($role_data)): ?>
                                    <tr><th>Department</th><td><?php echo htmlspecialchars($role_data['department'] ?? ''); ?></td></tr>
                                    <tr><th>Specialization</th><td><?php echo htmlspecialchars($role_data['specialization'] ?? ''); ?></td></tr>
                                    <tr><th>Faculty Number</th><td><?php echo htmlspecialchars($role_data['faculty_number'] ?? ''); ?></td></tr>
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
