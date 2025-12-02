<?php
// Student dashboard partial
?>
<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">Student Profile</h5>
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
        <h5 class="card-title">Student Actions</h5>
        <p class="small text-muted">Quick links for students</p>
        <a href="appointments.php" class="btn btn-outline-primary btn-sm me-2">My Appointments</a>
        <a href="book_appointment.php" class="btn btn-primary btn-sm">Book Appointment</a>
    </div>
</div>
<?php
// includes/dashboard_student.php
?>

<div class="card mb-3">
  <div class="card-body">
    <h5 class="card-title">Student Dashboard</h5>
    <p>Welcome, <?php echo htmlspecialchars($user['name'] ?? 'Student'); ?>. Here are your student-specific actions.</p>
    <div class="d-flex gap-2">
      <a href="appointments.php" class="btn btn-primary">Book Appointment</a>
      <a href="my_schedule.php" class="btn btn-outline-secondary">My Schedule</a>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h6>My Details</h6>
    <p class="small text-muted">Student number, course and year are managed in your profile.</p>
    <a href="profile.php" class="btn btn-sm btn-outline-primary">View Profile</a>
  </div>
</div>
