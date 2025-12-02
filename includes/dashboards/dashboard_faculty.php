<?php
// Faculty dashboard partial
?>
<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">Faculty Profile</h5>
        <?php if ($user): ?>
            <table class="table table-borderless table-sm">
                <tr><th>Name</th><td><?php echo htmlspecialchars($user['name']); ?></td></tr>
                <tr><th>Username</th><td><?php echo htmlspecialchars($user['username']); ?></td></tr>
                <tr><th>Email</th><td><?php echo htmlspecialchars($user['email']); ?></td></tr>
                <tr><th>Contact</th><td><?php echo htmlspecialchars($user['contact_number'] ?? ''); ?></td></tr>
            </table>
        <?php else: ?>
            <p class="text-muted">No profile information available.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Faculty Actions</h5>
        <p class="small text-muted">Quick links for faculty</p>
        <a href="availability.php" class="btn btn-outline-secondary btn-sm me-2">Set Availability</a>
        <a href="appointments.php" class="btn btn-outline-primary btn-sm">My Appointments</a>
    </div>
</div>
<?php
// includes/dashboard_faculty.php
?>

<div class="card mb-3">
  <div class="card-body">
    <h5 class="card-title">Faculty Dashboard</h5>
    <p>Welcome, <?php echo htmlspecialchars($user['name'] ?? 'Faculty'); ?>. Here are faculty actions.</p>
    <div class="d-flex gap-2">
      <a href="availability.php" class="btn btn-primary">Manage Availability</a>
      <a href="appointments.php" class="btn btn-outline-secondary">View Bookings</a>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h6>Schedule</h6>
    <p class="small text-muted">Create or edit your available consultation times.</p>
    <a href="create_schedule.php" class="btn btn-sm btn-outline-primary">Create Schedule</a>
  </div>
</div>
