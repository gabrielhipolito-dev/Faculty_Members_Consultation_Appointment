<?php
// Profile modal expects $avatar and $user to be defined in the including file
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
                        <img src="<?php echo htmlspecialchars($avatar ?? ($user['avatar'] ?? '../uploads/profile_pics/default.png')); ?>" alt="Profile" class="rounded-circle mb-3" style="width:160px;height:160px;object-fit:cover;">
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
