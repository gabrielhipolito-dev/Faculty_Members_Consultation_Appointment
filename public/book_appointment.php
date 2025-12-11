<?php
// Include necessary files
session_start();
require_once __DIR__ . '/../config/db.php';

// Check if logged in and is a student
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first");
    exit;
}

// Verify student role
$stmt = $conn->prepare("SELECT u.user_id, u.role FROM Users u WHERE u.user_id = ? LIMIT 1");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    header("Location: login.php");
    exit;
}
$currentUser = $res->fetch_assoc();
$stmt->close();

if ($currentUser['role'] !== 'Student') {
    header("Location: index.php?error=Only students can book appointments");
    exit;
}

// Get student_id from user_id
$stmt = $conn->prepare("SELECT student_id FROM Student WHERE user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$studentRes = $stmt->get_result();
if (!$studentRes || $studentRes->num_rows === 0) {
    header("Location: dashboard_student.php?error=Student profile not found");
    exit;
}
$student = $studentRes->fetch_assoc();
$student_id = $student['student_id'];
$stmt->close();

// Get faculty_id from URL
if (!isset($_GET['faculty_id']) || empty($_GET['faculty_id'])) {
    header("Location: search_professors.php?error=Faculty not specified");
    exit;
}

$faculty_id = intval($_GET['faculty_id']);

// Fetch faculty details
$stmt = $conn->prepare("
    SELECT 
        f.faculty_id, 
        f.department, 
        f.specialization,
        u.user_id,
        u.name, 
        u.email, 
        u.profile_picture
    FROM Faculty f
    INNER JOIN Users u ON u.user_id = f.user_id
    WHERE f.faculty_id = ? AND u.status = 'Active'
    LIMIT 1
");
$stmt->bind_param('i', $faculty_id);
$stmt->execute();
$facultyRes = $stmt->get_result();

if (!$facultyRes || $facultyRes->num_rows === 0) {
    header("Location: search_professors.php?error=Faculty not found");
    exit;
}

$faculty = $facultyRes->fetch_assoc();
$stmt->close();

// Get faculty availability
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
while ($row = $availRes->fetch_assoc()) {
    $availability[] = $row;
}
$availStmt->close();

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 40px 0;">
    <div class="container">
        <!-- Back Button -->
        <a href="search_professors.php" class="btn btn-light mb-4" style="border-radius: 8px;">
            ← Back to Search
        </a>

        <div class="row">
            <!-- Faculty Information Card -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-lg border-0" style="border-radius: 15px; overflow: hidden;">
                    <!-- Faculty Photo -->
                    <div style="height: 300px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                        <?php
                        $profilePicture = $faculty['profile_picture'];
                        if (empty($profilePicture)) {
                            $profilePath = '../uploads/profile_pics/default_image.png';
                        } else {
                            if (strpos($profilePicture, '/') === 0) {
                                $profilePath = '..' . $profilePicture;
                            } else {
                                $profilePath = $profilePicture;
                            }
                        }
                        
                        $fullPath = __DIR__ . '/' . $profilePath;
                        if (!file_exists($fullPath)) {
                            $profilePath = '../uploads/profile_pics/default_image.png';
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($profilePath); ?>" alt="<?php echo htmlspecialchars($faculty['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>

                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-2"><?php echo htmlspecialchars($faculty['name']); ?></h4>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($faculty['department']); ?></p>
                        
                        <div class="mb-3">
                            <span class="badge" style="background-color: #667eea; padding: 8px 15px; font-size: 0.9rem;">
                                <?php echo htmlspecialchars($faculty['specialization']); ?>
                            </span>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <h6 class="fw-bold mb-3">Available Schedule:</h6>
                            <?php if (empty($availability)): ?>
                                <p class="text-muted small">No schedule available</p>
                            <?php else: ?>
                                <div style="max-height: 200px; overflow-y: auto;">
                                    <?php foreach ($availability as $slot): ?>
                                        <div class="mb-2 p-2" style="background-color: #f8f9fa; border-radius: 6px;">
                                            <strong class="text-primary d-block"><?php echo htmlspecialchars($slot['day_of_week']); ?></strong>
                                            <small class="text-muted"><?php echo htmlspecialchars($slot['start_time'] . ' - ' . $slot['end_time']); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="col-lg-8">
                <div class="card shadow-lg border-0" style="border-radius: 15px;">
                    <div class="card-body p-5">
                        <h3 class="fw-bold mb-4">Book an Appointment</h3>

                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>Success!</strong> Your appointment has been booked successfully.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Error!</strong> <?php echo htmlspecialchars($_GET['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form id="bookingForm" action="../actions/book_appointment.php" method="POST">
                            <input type="hidden" name="faculty_id" value="<?php echo $faculty_id; ?>">
                            <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">

                            <!-- Select Time Slot -->
                            <div class="mb-4">
                                <label for="availability_id" class="form-label fw-bold">Select Time Slot *</label>
                                <select class="form-select form-select-lg" id="availability_id" name="availability_id" required style="border-radius: 8px;">
                                    <option value="">Choose a time slot...</option>
                                    <?php foreach ($availability as $slot): ?>
                                        <option value="<?php echo $slot['availability_id']; ?>">
                                            <?php echo htmlspecialchars($slot['day_of_week'] . ' - ' . $slot['start_time'] . ' to ' . $slot['end_time']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Appointment Date -->
                            <div class="mb-4">
                                <label for="appointment_date" class="form-label fw-bold">Appointment Date *</label>
                                <input type="date" class="form-control form-control-lg" id="appointment_date" name="appointment_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" style="border-radius: 8px;">
                                <small class="text-muted">Select a date that matches your chosen time slot's day.</small>
                            </div>

                            <!-- Topic -->
                            <div class="mb-4">
                                <label for="topic" class="form-label fw-bold">Topic/Subject *</label>
                                <input type="text" class="form-control form-control-lg" id="topic" name="topic" placeholder="e.g., Thesis Consultation, Course Guidance" required maxlength="255" style="border-radius: 8px;">
                            </div>

                            <!-- Purpose -->
                            <div class="mb-4">
                                <label for="purpose" class="form-label fw-bold">Purpose/Details *</label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="5" placeholder="Please provide details about your appointment..." required style="border-radius: 8px;"></textarea>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-lg flex-fill" style="background-color: #FF6B35; color: white; border-radius: 8px; font-weight: 600;">
                                    <i class="bi bi-calendar-check"></i> Book Appointment
                                </button>
                                <a href="search_professors.php" class="btn btn-outline-secondary btn-lg" style="border-radius: 8px; min-width: 120px;">
                                    Cancel
                                </a>
                            </div>
                        </form>
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

    .card {
        transition: all 0.3s ease;
    }

    .alert {
        border-radius: 10px;
    }
</style>

<script>
    // Validate that the selected date matches the day of week of the selected time slot
    const availabilitySelect = document.getElementById('availability_id');
    const dateInput = document.getElementById('appointment_date');
    const form = document.getElementById('bookingForm');

    // Store day mapping
    const dayMapping = {
        <?php foreach ($availability as $slot): ?>
            <?php echo $slot['availability_id']; ?>: '<?php echo $slot['day_of_week']; ?>',
        <?php endforeach; ?>
    };

    const dayOfWeekMap = {
        'Sunday': 0,
        'Monday': 1,
        'Tuesday': 2,
        'Wednesday': 3,
        'Thursday': 4,
        'Friday': 5,
        'Saturday': 6
    };

    form.addEventListener('submit', function(e) {
        const availId = availabilitySelect.value;
        const selectedDate = new Date(dateInput.value);
        
        if (!availId || !dateInput.value) {
            return;
        }

        const expectedDay = dayMapping[availId];
        const selectedDayIndex = selectedDate.getDay();
        const expectedDayIndex = dayOfWeekMap[expectedDay];

        if (selectedDayIndex !== expectedDayIndex) {
            e.preventDefault();
            alert(`Please select a ${expectedDay} for this time slot. You selected a ${Object.keys(dayOfWeekMap).find(key => dayOfWeekMap[key] === selectedDayIndex)}.`);
            dateInput.focus();
        }
    });

    // Provide feedback when selecting time slot
    availabilitySelect.addEventListener('change', function() {
        const availId = this.value;
        if (availId && dayMapping[availId]) {
            const day = dayMapping[availId];
            dateInput.setAttribute('placeholder', `Select a ${day}`);
        }
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
