<?php
// Include database logic for professors
include __DIR__ . '/../actions/load_user.php';
include __DIR__ . '/../actions/dashboard_student_handler.php';
include __DIR__ . '/../actions/get_professors.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/partials/dashboard_sidebar.php';
?>

<div class="container-fluid py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <!-- Header Section -->
    <div class="container mb-5">
        <div class="row align-items-center">
            <div class="col-md-8">
                <a href="dashboard_student.php" class="btn btn-light mb-3" style="border-radius: 8px;">
                    ← Back to Dashboard
                </a>
                <h1 class="text-white mb-2" style="font-size: 2.5rem; font-weight: 700;">Search Doctor, Make an Appointment</h1>
                <p class="text-white-50">Browse our team of consultants, check their specialization & availability</p>
            </div>
        </div>
    </div>

    <!-- Search & Filter Section -->
    <div class="container mb-5">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title mb-3 fw-bold">Search by Name or Specialization</h6>
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search professor name or specialization..." style="border-radius: 8px 0 0 8px;">
                            <button class="btn" style="background-color: #FF6B35; color: white; border-radius: 0 8px 8px 0; border: none;">Search</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row g-2">
                    <!-- Filter by Department -->
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-3 fw-bold">Department</h6>
                                <select id="departmentFilter" class="form-select form-select-sm" style="border-radius: 6px;">
                                    <option value="">All</option>
                                    <option value="College of Computing and Information Technology (CCIT)">CCIT</option>
                                    <option value="College of Engineering">Engineering</option>
                                    <option value="College of Science">Science</option>
                                    <option value="College of Business Administration">Business</option>
                                    <option value="College of Education and Liberal Arts">Education</option>
                                    <option value="College of Architecture">Architecture</option>
                                    <option value="College of Nursing">Nursing</option>
                                    <option value="College of Pharmacy">Pharmacy</option>
                                    <option value="College of Law">Law</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- Filter by Availability -->
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-3 fw-bold">Available Days</h6>
                                <select id="dayFilter" class="form-select form-select-sm" style="border-radius: 6px;">
                                    <option value="">All Days</option>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Professor Cards Grid -->
    <div class="container">
        <div class="row g-4" id="professorGrid">
            <?php if (empty($professors)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <p>No professors available at the moment.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($professors as $prof): ?>
                    <div class="col-md-6 col-lg-4 professor-card" data-department="<?php echo htmlspecialchars($prof['department']); ?>" data-name="<?php echo strtolower(htmlspecialchars($prof['name'])); ?>" data-specialization="<?php echo strtolower(htmlspecialchars($prof['specialization'])); ?>">
                        <div class="card shadow-lg border-0 overflow-hidden h-100" style="border-radius: 12px; transition: all 0.3s ease;">
                            <!-- Professor Photo -->
                            <div style="height: 250px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); overflow: hidden; display: flex; align-items: center; justify-content: center; position: relative;">
                                <?php
                                // Handle profile picture path
                                $profilePicture = $prof['profile_picture'];
                                if (empty($profilePicture)) {
                                    $profilePath = '../uploads/profile_pics/default_image.png';
                                } else {
                                    // If path starts with /, convert to relative path
                                    if (strpos($profilePicture, '/') === 0) {
                                        $profilePath = '..' . $profilePicture;
                                    } else {
                                        $profilePath = $profilePicture;
                                    }
                                }
                                
                                // Check if file exists, use default if not
                                $fullPath = __DIR__ . '/' . $profilePath;
                                if (!file_exists($fullPath)) {
                                    $profilePath = '../uploads/profile_pics/default_image.png';
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($profilePath); ?>" alt="<?php echo htmlspecialchars($prof['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <div class="position-absolute top-0 end-0 p-2">
                                    <span class="badge bg-success">Available</span>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Name and Department -->
                                <h5 class="card-title fw-bold mb-1"><?php echo htmlspecialchars($prof['name']); ?></h5>
                                <p class="text-muted small mb-3"><?php echo htmlspecialchars($prof['department'] ?? 'Department'); ?></p>

                                <!-- Specialization -->
                                <p class="small mb-3">
                                    <span class="badge bg-light text-dark">
                                        <?php echo htmlspecialchars($prof['specialization'] ?? 'Specialist'); ?>
                                    </span>
                                </p>

                                <!-- Availability Info -->
                                <div class="mb-3" style="background-color: #f8f9fa; padding: 10px; border-radius: 8px;">
                                    <small class="fw-bold d-block mb-2">Schedule:</small>
                                    <div style="max-height: 80px; overflow-y: auto;">
                                        <?php if (empty($prof['availability'])): ?>
                                            <small class="text-muted">No schedule available</small>
                                        <?php else: ?>
                                            <?php foreach ($prof['availability'] as $slot): ?>
                                                <div class="small mb-1 availability-item" data-day="<?php echo htmlspecialchars($slot['day_of_week']); ?>">
                                                    <strong class="text-primary"><?php echo htmlspecialchars($slot['day_of_week']); ?></strong>
                                                    <span class="text-muted ms-2"><?php echo htmlspecialchars($slot['start_time'] . ' - ' . $slot['end_time']); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-white border-top">
                                <a href="book_appointment.php?faculty_id=<?php echo htmlspecialchars($prof['faculty_id']); ?>" class="btn w-100" style="background-color: #FF6B35; color: white; border-radius: 8px; font-weight: 600;">
                                    Book Appointment
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .professor-card:hover .card {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2) !important;
    }

    .form-select-sm {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }

    #professorGrid .col-md-6 {
        animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .badge {
        font-weight: 600;
        padding: 0.5rem 0.75rem;
    }
</style>

<script>
    // Search and filter functionality
    const searchInput = document.getElementById('searchInput');
    const departmentFilter = document.getElementById('departmentFilter');
    const dayFilter = document.getElementById('dayFilter');
    const professorCards = document.querySelectorAll('.professor-card');

    function filterProfessors() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedDept = departmentFilter.value;
        const selectedDay = dayFilter.value;

        professorCards.forEach(card => {
            const name = card.dataset.name;
            const specialization = card.dataset.specialization;
            const department = card.dataset.department;
            const hasDay = selectedDay ? 
                Array.from(card.querySelectorAll('.availability-item')).some(item => 
                    item.dataset.day === selectedDay
                ) : true;

            const matchesSearch = name.includes(searchTerm) || specialization.includes(searchTerm);
            const matchesDept = !selectedDept || department === selectedDept;
            const matchesDay = hasDay;

            if (matchesSearch && matchesDept && matchesDay) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('keyup', filterProfessors);
    departmentFilter.addEventListener('change', filterProfessors);
    dayFilter.addEventListener('change', filterProfessors);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/profile_modal.php'; ?>
