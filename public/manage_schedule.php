<?php
// public/manage_schedule.php - Faculty schedule management
require_once __DIR__ . '/../actions/load_user.php';
require_once __DIR__ . '/../config/db.php';

// Check if logged in and is faculty
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first");
    exit;
}

// Verify faculty role - $user already loaded by load_user.php
if ($user['role'] !== 'Faculty') {
    header("Location: index.php?error=Only faculty can manage schedules");
    exit;
}

// Get faculty_id
$stmt = $conn->prepare("SELECT faculty_id FROM Faculty WHERE user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$facultyRes = $stmt->get_result();
if (!$facultyRes || $facultyRes->num_rows === 0) {
    header("Location: dashboard_faculty.php?error=Faculty profile not found");
    exit;
}
$faculty = $facultyRes->fetch_assoc();
$faculty_id = $faculty['faculty_id'];
$stmt->close();

// Get current availability
$availStmt = $conn->prepare("
    SELECT availability_id, day_of_week, start_time, end_time
    FROM Availability
    WHERE faculty_id = ?
    ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')
");
$availStmt->bind_param('i', $faculty_id);
$availStmt->execute();
$availRes = $availStmt->get_result();

$availability = [];
$availabilityByDay = [];
while ($row = $availRes->fetch_assoc()) {
    $availability[] = $row;
    $availabilityByDay[$row['day_of_week']] = $row;
}
$availStmt->close();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/partials/dashboard_sidebar.php';
?>

<div class="container-fluid py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <a href="dashboard_faculty.php" class="btn btn-light mb-3" style="border-radius: 8px;">
                    ← Back to Dashboard
                </a>
                <div class="text-white">
                    <h2 class="fw-bold mb-2">🕒 Manage Consultation Schedule</h2>
                    <p class="mb-0 opacity-75">Set your available days and times for student consultations</p>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 10px;">
                <strong>Success!</strong> <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 10px;">
                <strong>Error!</strong> <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Current Schedule -->
            <div class="col-lg-5 mb-4">
                <div class="card border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">📅 Current Schedule</h5>
                            <?php if (!empty($availability)): ?>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteAllSchedules()">
                                    <i class="bi bi-trash"></i> Delete All
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($availability)): ?>
                            <div class="text-center py-5">
                                <span style="font-size: 48px;">📭</span>
                                <p class="text-muted mt-3 mb-0">No schedule set yet</p>
                                <p class="small text-muted">Add your availability using the form →</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($availability as $slot): ?>
                                    <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 fw-bold text-primary"><?php echo htmlspecialchars($slot['day_of_week']); ?></h6>
                                            <p class="mb-0 small text-muted">
                                                <?php echo date('h:i A', strtotime($slot['start_time'])); ?> - 
                                                <?php echo date('h:i A', strtotime($slot['end_time'])); ?>
                                            </p>
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteSchedule(<?php echo $slot['availability_id']; ?>)">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Schedule Form -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">➕ Add Availability Slot</h5>

                        <form action="../actions/create_schedule.php" method="POST">
                            <input type="hidden" name="faculty_id" value="<?php echo $faculty_id; ?>">

                            <!-- Day of Week -->
                            <div class="mb-4">
                                <label for="day_of_week" class="form-label fw-bold">Day of Week *</label>
                                <select class="form-select form-select-lg" id="day_of_week" name="day_of_week" required style="border-radius: 8px;">
                                    <option value="">Select a day...</option>
                                    <option value="Monday" <?php echo isset($availabilityByDay['Monday']) ? 'disabled' : ''; ?>>
                                        Monday <?php echo isset($availabilityByDay['Monday']) ? '(Already set)' : ''; ?>
                                    </option>
                                    <option value="Tuesday" <?php echo isset($availabilityByDay['Tuesday']) ? 'disabled' : ''; ?>>
                                        Tuesday <?php echo isset($availabilityByDay['Tuesday']) ? '(Already set)' : ''; ?>
                                    </option>
                                    <option value="Wednesday" <?php echo isset($availabilityByDay['Wednesday']) ? 'disabled' : ''; ?>>
                                        Wednesday <?php echo isset($availabilityByDay['Wednesday']) ? '(Already set)' : ''; ?>
                                    </option>
                                    <option value="Thursday" <?php echo isset($availabilityByDay['Thursday']) ? 'disabled' : ''; ?>>
                                        Thursday <?php echo isset($availabilityByDay['Thursday']) ? '(Already set)' : ''; ?>
                                    </option>
                                    <option value="Friday" <?php echo isset($availabilityByDay['Friday']) ? 'disabled' : ''; ?>>
                                        Friday <?php echo isset($availabilityByDay['Friday']) ? '(Already set)' : ''; ?>
                                    </option>
                                </select>
                                <small class="text-muted">Note: Each day can only have one time slot. Delete existing slot to update.</small>
                            </div>

                            <!-- Start Time -->
                            <div class="mb-4">
                                <label for="start_time" class="form-label fw-bold">Start Time *</label>
                                <input type="time" class="form-control form-control-lg" id="start_time" name="start_time" required style="border-radius: 8px;">
                            </div>

                            <!-- End Time -->
                            <div class="mb-4">
                                <label for="end_time" class="form-label fw-bold">End Time *</label>
                                <input type="time" class="form-control form-control-lg" id="end_time" name="end_time" required style="border-radius: 8px;">
                                <small class="text-muted">End time must be after start time</small>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-lg" style="background-color: #FF6B35; color: white; border-radius: 8px; font-weight: 600;">
                                    <i class="bi bi-plus-circle"></i> Add Time Slot
                                </button>
                            </div>
                        </form>

                        <!-- Quick Add Presets -->
                        <div class="mt-4 pt-4 border-top">
                            <h6 class="fw-bold mb-3">⚡ Quick Add Presets</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <button class="btn btn-outline-secondary btn-sm w-100" onclick="setTime('09:00', '12:00')" style="border-radius: 6px;">
                                        Morning (9:00 AM - 12:00 PM)
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button class="btn btn-outline-secondary btn-sm w-100" onclick="setTime('13:00', '17:00')" style="border-radius: 6px;">
                                        Afternoon (1:00 PM - 5:00 PM)
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button class="btn btn-outline-secondary btn-sm w-100" onclick="setTime('09:00', '17:00')" style="border-radius: 6px;">
                                        Full Day (9:00 AM - 5:00 PM)
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button class="btn btn-outline-secondary btn-sm w-100" onclick="setTime('14:00', '16:00')" style="border-radius: 6px;">
                                        Short (2:00 PM - 4:00 PM)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips Card -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 15px; background-color: #FFF9E6;">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">💡 Tips for Setting Your Schedule</h6>
                        <ul class="mb-0 small">
                            <li>Set realistic time slots that you can consistently maintain</li>
                            <li>Consider buffer time between appointments for preparation</li>
                            <li>Each day can only have one time slot - choose your most available hours</li>
                            <li>Students can only book appointments during your set availability</li>
                            <li>You can delete and re-add slots if you need to change times</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .list-group-item {
        border: none;
        border-bottom: 1px solid #f0f0f0;
    }

    .list-group-item:last-child {
        border-bottom: none;
    }

    .card {
        transition: all 0.3s ease;
    }
</style>

<script>
    function setTime(start, end) {
        document.getElementById('start_time').value = start;
        document.getElementById('end_time').value = end;
    }

    function deleteSchedule(availabilityId) {
        if (!confirm('Are you sure you want to delete this time slot? Students will no longer be able to book appointments for this day.')) {
            return;
        }

        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../actions/delete_schedule.php';

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'availability_id';
        idInput.value = availabilityId;

        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }

    function deleteAllSchedules() {
        if (!confirm('Delete ALL availability slots? This is blocked if you have pending or approved appointments.')) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../actions/delete_all_schedule.php';

        document.body.appendChild(form);
        form.submit();
    }

    // Validate end time is after start time
    document.querySelector('form').addEventListener('submit', function(e) {
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;

        if (startTime && endTime && endTime <= startTime) {
            e.preventDefault();
            alert('End time must be after start time');
            document.getElementById('end_time').focus();
        }
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/profile_modal.php'; ?>
