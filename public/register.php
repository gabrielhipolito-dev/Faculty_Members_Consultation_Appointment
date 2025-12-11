<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';

$currentUser = null;
$isAdmin = false;

if (isset($_SESSION['user_id'])) {
	$stmt = $conn->prepare("SELECT user_id, name, role FROM Users WHERE user_id = ? LIMIT 1");
	$stmt->bind_param('i', $_SESSION['user_id']);
	if ($stmt->execute()) {
		$currentUser = $stmt->get_result()->fetch_assoc();
		$isAdmin = $currentUser && $currentUser['role'] === 'Admin';
	}
	$stmt->close();
}

$editUserId = isset($_GET['edit_user_id']) ? (int) $_GET['edit_user_id'] : null;

$formData = [
	'user_id' => null,
	'name' => '',
	'username' => '',
	'email' => '',
	'password' => '',
	'role' => 'Student',
	'birthdate' => '',
	'gender' => '',
	'contact_number' => '',
	'address' => '',
	'course' => '',
	'year_level' => '1',
	'student_number' => '',
	'department' => '',
	'specialization' => '',
	'faculty_number' => '',
	'profile_picture' => '',
];

if ($isAdmin && $editUserId) {
	$stmt = $conn->prepare(
		"SELECT u.*, s.course, s.year_level, s.student_number, f.department, f.specialization, f.faculty_number
		 FROM Users u
		 LEFT JOIN Student s ON s.user_id = u.user_id
		 LEFT JOIN Faculty f ON f.user_id = u.user_id
		 WHERE u.user_id = ? LIMIT 1"
	);
	$stmt->bind_param('i', $editUserId);
	if ($stmt->execute()) {
		$res = $stmt->get_result();
		if ($res && $res->num_rows === 1) {
			$row = $res->fetch_assoc();
			$formData = array_merge($formData, [
				'user_id' => $row['user_id'],
				'name' => $row['name'] ?? '',
				'username' => $row['username'] ?? '',
				'email' => $row['email'] ?? '',
				'role' => $row['role'] ?? 'Student',
				'birthdate' => $row['birthdate'] ?? '',
				'gender' => $row['gender'] ?? '',
				'contact_number' => $row['contact_number'] ?? '',
				'address' => $row['address'] ?? '',
				'course' => $row['course'] ?? '',
				'year_level' => $row['year_level'] ?? '1',
				'student_number' => $row['student_number'] ?? '',
				'department' => $row['department'] ?? '',
				'specialization' => $row['specialization'] ?? '',
				'faculty_number' => $row['faculty_number'] ?? '',
				'profile_picture' => $row['profile_picture'] ?? '',
			]);
		}
	}
	$stmt->close();
}

$mode = ($isAdmin && $formData['user_id']) ? 'update' : 'create';

$userList = [];
if ($isAdmin) {
	$res = $conn->query("SELECT user_id, name, email, role, status FROM Users ORDER BY user_id DESC");
	if ($res) {
		while ($row = $res->fetch_assoc()) {
			$userList[] = $row;
		}
	}
}

// Simple registration / account management page. Submits to ../actions/register_action.php
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h3 class="card-title mb-0" id="manage"><?php echo ($mode === 'update') ? 'Update Account' : 'Create Account'; ?></h3>
						<?php if ($isAdmin): ?>
							<a class="btn btn-outline-secondary btn-sm" href="dashboard_admin.php">Back to Admin Dashboard</a>
						<?php endif; ?>
					</div>

					<?php if ($mode === 'update'): ?>
						<div class="alert alert-info d-flex justify-content-between align-items-center">
							<div class="me-2">Editing account #<?php echo htmlspecialchars($formData['user_id']); ?>. Leave the password blank to keep the current one.</div>
							<a class="btn btn-sm btn-outline-secondary" href="register.php">Cancel edit</a>
						</div>
					<?php endif; ?>
					
					<?php if (isset($_GET['error'])): ?>
						<div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
					<?php endif; ?>
					
					<?php if (isset($_GET['success'])): ?>
						<div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
					<?php endif; ?>

					<form action="../actions/register_action.php" method="post" id="registerForm" enctype="multipart/form-data">

						<input type="hidden" name="action" id="formAction" value="<?php echo htmlspecialchars($mode); ?>">
						<?php if ($mode === 'update'): ?>
							<input type="hidden" name="user_id" value="<?php echo htmlspecialchars($formData['user_id']); ?>">
							<input type="hidden" name="current_profile_picture" value="<?php echo htmlspecialchars($formData['profile_picture']); ?>">
						<?php endif; ?>
						
						<div class="mb-3">
							<label class="form-label">Full name</label>
							<input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($formData['name']); ?>" required>
						</div>

						<div class="mb-3">
							<label class="form-label">Username</label>
							<input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($formData['username']); ?>" required>
						</div>

						<div class="mb-3">
							<label class="form-label">Email</label>
							<input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
							<div class="form-text text-danger" id="emailError" style="display:none">Email must be a @gmail.com address.</div>
						</div>

						<div class="mb-3">
							<label class="form-label">Password</label>
							<div class="input-group">
								<input type="password" name="password" id="password" class="form-control" <?php echo ($mode === 'create') ? 'required' : ''; ?>>
								<button type="button" class="btn btn-outline-secondary" id="togglePassword">Show</button>
							</div>
							<?php if ($mode === 'update'): ?>
								<div class="form-text">Leave blank to keep the existing password.</div>
							<?php endif; ?>
							<div id="pwStrength" class="form-text mt-1"></div>
						</div>

						<div class="mb-3">
							<label class="form-label">Contact number</label>
							<div class="input-group">
								<span class="input-group-text">+63</span>
								<input type="text" name="contact_number" id="contact_number" class="form-control" placeholder="9XXXXXXXXX" value="<?php echo htmlspecialchars($formData['contact_number']); ?>" maxlength="10">
								<span class="input-group-text" id="phoneValidation" style="color: #999;">⚪</span>
							</div>
							<div class="form-text small" id="phoneHint">Format: 10 digits (9XXXXXXXXX)</div>
							<div class="form-text text-danger" id="phoneError" style="display:none">Contact number must be exactly 10 digits.</div>
						</div>

						<div class="mb-3">
							<label class="form-label">Birthday</label>
							<input type="date" name="birthdate" class="form-control" value="<?php echo htmlspecialchars($formData['birthdate']); ?>">
						</div>

						<div class="mb-3">
							<label class="form-label">Gender</label>
							<select name="gender" class="form-select" required>
								<option value="">-- Select gender --</option>
								<option value="Male" <?php echo ($formData['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
								<option value="Female" <?php echo ($formData['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
								<option value="Other" <?php echo ($formData['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
							</select>
						</div>

						<div class="mb-3">
							<label class="form-label">Address</label>
							<textarea name="address" id="address" class="form-control" maxlength="300" rows="3" placeholder="e.g., Street Number/Name, Barangay, City, Province, Postal Code"><?php echo htmlspecialchars($formData['address']); ?></textarea>
							<div class="d-flex justify-content-between align-items-center mt-2">
								<small class="form-text text-muted">Philippine address format recommended</small>
								<small class="form-text"><span id="charCount">0</span>/300</small>
							</div>
							<div class="form-text text-danger" id="addressError" style="display:none">Address must be 20-300 characters and include street, barangay, city, and province.</div>
						</div>

						<div class="mb-3">
							<label class="form-label">Role</label>
							<div>
								<label class="me-3"><input type="radio" name="role" value="Student" <?php echo ($formData['role'] === 'Student') ? 'checked' : ''; ?>> Student</label>
								<label class="me-3"><input type="radio" name="role" value="Faculty" <?php echo ($formData['role'] === 'Faculty') ? 'checked' : ''; ?>> Faculty</label>
								<label><input type="radio" name="role" value="Admin" <?php echo ($formData['role'] === 'Admin') ? 'checked' : ''; ?>> Admin</label>
							</div>
						</div>

						<!-- Student fields -->
						<div id="studentFields">
							<div class="mb-3">
								<label class="form-label">Course</label>
								<select name="course" class="form-select">
									<option value="">-- Select a course --</option>
									<optgroup label="College of Computing and Information Technology (CCIT)">
										<option value="BS Computer Science" <?php echo ($formData['course'] === 'BS Computer Science') ? 'selected' : ''; ?>>BS Computer Science</option>
										<option value="BS Information Technology" <?php echo ($formData['course'] === 'BS Information Technology') ? 'selected' : ''; ?>>BS Information Technology</option>
										<option value="BS Information Systems" <?php echo ($formData['course'] === 'BS Information Systems') ? 'selected' : ''; ?>>BS Information Systems</option>
										<option value="Dual-degree: BS Computer Science + BS Information Engineering" <?php echo ($formData['course'] === 'Dual-degree: BS Computer Science + BS Information Engineering') ? 'selected' : ''; ?>>Dual-degree: BS Computer Science + BS Information Engineering</option>
									</optgroup>
									<optgroup label="College of Engineering">
										<option value="BS Chemical Engineering" <?php echo ($formData['course'] === 'BS Chemical Engineering') ? 'selected' : ''; ?>>BS Chemical Engineering</option>
										<option value="BS Chemical Process Technology" <?php echo ($formData['course'] === 'BS Chemical Process Technology') ? 'selected' : ''; ?>>BS Chemical Process Technology</option>
										<option value="BS Civil Engineering" <?php echo ($formData['course'] === 'BS Civil Engineering') ? 'selected' : ''; ?>>BS Civil Engineering</option>
										<option value="BS Computer Engineering" <?php echo ($formData['course'] === 'BS Computer Engineering') ? 'selected' : ''; ?>>BS Computer Engineering</option>
										<option value="BS Electrical Engineering" <?php echo ($formData['course'] === 'BS Electrical Engineering') ? 'selected' : ''; ?>>BS Electrical Engineering</option>
										<option value="BS Electronics/Electronics & Communications Engineering" <?php echo ($formData['course'] === 'BS Electronics/Electronics & Communications Engineering') ? 'selected' : ''; ?>>BS Electronics/Electronics & Communications Engineering</option>
										<option value="BS Geology" <?php echo ($formData['course'] === 'BS Geology') ? 'selected' : ''; ?>>BS Geology</option>
										<option value="BS Industrial Engineering" <?php echo ($formData['course'] === 'BS Industrial Engineering') ? 'selected' : ''; ?>>BS Industrial Engineering</option>
										<option value="BS Mechanical Engineering" <?php echo ($formData['course'] === 'BS Mechanical Engineering') ? 'selected' : ''; ?>>BS Mechanical Engineering (including Mechatronics major/track)</option>
										<option value="BS Mining Engineering" <?php echo ($formData['course'] === 'BS Mining Engineering') ? 'selected' : ''; ?>>BS Mining Engineering</option>
										<option value="BS Petroleum Engineering" <?php echo ($formData['course'] === 'BS Petroleum Engineering') ? 'selected' : ''; ?>>BS Petroleum Engineering</option>
									</optgroup>
									<optgroup label="College of Science">
										<option value="BS Biology" <?php echo ($formData['course'] === 'BS Biology') ? 'selected' : ''; ?>>BS Biology</option>
										<option value="BS Chemistry" <?php echo ($formData['course'] === 'BS Chemistry') ? 'selected' : ''; ?>>BS Chemistry</option>
										<option value="BS Psychology" <?php echo ($formData['course'] === 'BS Psychology') ? 'selected' : ''; ?>>BS Psychology</option>
									</optgroup>
									<optgroup label="College of Business Administration">
										<option value="BS Accountancy" <?php echo ($formData['course'] === 'BS Accountancy') ? 'selected' : ''; ?>>BS Accountancy</option>
										<option value="BS Business Administration - Financial Management" <?php echo ($formData['course'] === 'BS Business Administration - Financial Management') ? 'selected' : ''; ?>>BS Business Administration - Financial Management</option>
										<option value="BS Business Administration - Marketing Management" <?php echo ($formData['course'] === 'BS Business Administration - Marketing Management') ? 'selected' : ''; ?>>BS Business Administration - Marketing Management</option>
										<option value="BS Business Administration - Operations Management" <?php echo ($formData['course'] === 'BS Business Administration - Operations Management') ? 'selected' : ''; ?>>BS Business Administration - Operations Management</option>
										<option value="BS Customs Administration" <?php echo ($formData['course'] === 'BS Customs Administration') ? 'selected' : ''; ?>>BS Customs Administration</option>
										<option value="BS Hospitality Management" <?php echo ($formData['course'] === 'BS Hospitality Management') ? 'selected' : ''; ?>>BS Hospitality Management</option>
									</optgroup>
									<optgroup label="College of Education and Liberal Arts">
										<option value="Bachelor of Elementary Education" <?php echo ($formData['course'] === 'Bachelor of Elementary Education') ? 'selected' : ''; ?>>Bachelor of Elementary Education</option>
										<option value="Bachelor of Secondary Education" <?php echo ($formData['course'] === 'Bachelor of Secondary Education') ? 'selected' : ''; ?>>Bachelor of Secondary Education</option>
										<option value="Bachelor of Physical Education / Exercise & Sports" <?php echo ($formData['course'] === 'Bachelor of Physical Education / Exercise & Sports') ? 'selected' : ''; ?>>Bachelor of Physical Education / Exercise & Sports</option>
										<option value="BA in Communication" <?php echo ($formData['course'] === 'BA in Communication') ? 'selected' : ''; ?>>BA in Communication</option>
										<option value="BA in Political Science" <?php echo ($formData['course'] === 'BA in Political Science') ? 'selected' : ''; ?>>BA in Political Science</option>
										<option value="BA in Philosophy" <?php echo ($formData['course'] === 'BA in Philosophy') ? 'selected' : ''; ?>>BA in Philosophy</option>
									</optgroup>
									<optgroup label="College of Architecture">
										<option value="BS Architecture" <?php echo ($formData['course'] === 'BS Architecture') ? 'selected' : ''; ?>>BS Architecture</option>
									</optgroup>
									<optgroup label="College of Nursing">
										<option value="BS Nursing" <?php echo ($formData['course'] === 'BS Nursing') ? 'selected' : ''; ?>>BS Nursing</option>
									</optgroup>
									<optgroup label="College of Pharmacy">
										<option value="BS Pharmacy" <?php echo ($formData['course'] === 'BS Pharmacy') ? 'selected' : ''; ?>>BS Pharmacy</option>
									</optgroup>
									<optgroup label="College of Law">
										<option value="Bachelor of Laws (LLB)" <?php echo ($formData['course'] === 'Bachelor of Laws (LLB)') ? 'selected' : ''; ?>>Bachelor of Laws (LLB)</option>
									</optgroup>
								</select>
							</div>
							<div class="mb-3">
								<label class="form-label">Year level</label>
								<select name="year_level" class="form-select">
									<?php for ($i = 1; $i <= 5; $i++): ?>
										<option value="<?php echo $i; ?>" <?php echo ($formData['year_level'] == (string)$i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
									<?php endfor; ?>
								</select>
							</div>
							<div class="mb-3">
								<label class="form-label">Student number</label>
								<input type="text" name="student_number" class="form-control" value="<?php echo htmlspecialchars($formData['student_number']); ?>">
							</div>
						</div>

						<!-- Faculty fields -->
						<div id="facultyFields" style="display:none;">
							<div class="mb-3">
								<label class="form-label">Department</label>
								<select name="department" id="department" class="form-select">
									<option value="">-- Select a department --</option>
									<optgroup label="College of Computing and Information Technology (CCIT)">
										<option value="College of Computing and Information Technology (CCIT)" <?php echo ($formData['department'] === 'College of Computing and Information Technology (CCIT)') ? 'selected' : ''; ?>>College of Computing and Information Technology (CCIT)</option>
									</optgroup>
									<optgroup label="College of Engineering">
										<option value="College of Engineering" <?php echo ($formData['department'] === 'College of Engineering') ? 'selected' : ''; ?>>College of Engineering</option>
									</optgroup>
									<optgroup label="College of Science">
										<option value="College of Science" <?php echo ($formData['department'] === 'College of Science') ? 'selected' : ''; ?>>College of Science</option>
									</optgroup>
									<optgroup label="College of Business Administration">
										<option value="College of Business Administration" <?php echo ($formData['department'] === 'College of Business Administration') ? 'selected' : ''; ?>>College of Business Administration</option>
									</optgroup>
									<optgroup label="College of Education and Liberal Arts">
										<option value="College of Education and Liberal Arts" <?php echo ($formData['department'] === 'College of Education and Liberal Arts') ? 'selected' : ''; ?>>College of Education and Liberal Arts</option>
									</optgroup>
									<optgroup label="College of Architecture">
										<option value="College of Architecture" <?php echo ($formData['department'] === 'College of Architecture') ? 'selected' : ''; ?>>College of Architecture</option>
									</optgroup>
									<optgroup label="College of Nursing">
										<option value="College of Nursing" <?php echo ($formData['department'] === 'College of Nursing') ? 'selected' : ''; ?>>College of Nursing</option>
									</optgroup>
									<optgroup label="College of Pharmacy">
										<option value="College of Pharmacy" <?php echo ($formData['department'] === 'College of Pharmacy') ? 'selected' : ''; ?>>College of Pharmacy</option>
									</optgroup>
									<optgroup label="College of Law">
										<option value="College of Law" <?php echo ($formData['department'] === 'College of Law') ? 'selected' : ''; ?>>College of Law</option>
									</optgroup>
								</select>
							</div>
							<div class="mb-3">
								<label class="form-label">Specialization</label>
								<select name="specialization" id="specialization" class="form-select">
									<option value="">-- Select a specialization --</option>
									<optgroup label="🖥️ College of Computing and Information Technology (CCIT)" data-department="College of Computing and Information Technology (CCIT)">
										<option value="Programming" <?php echo ($formData['specialization'] === 'Programming') ? 'selected' : ''; ?>>Programming</option>
										<option value="Data Structures" <?php echo ($formData['specialization'] === 'Data Structures') ? 'selected' : ''; ?>>Data Structures</option>
										<option value="Algorithms" <?php echo ($formData['specialization'] === 'Algorithms') ? 'selected' : ''; ?>>Algorithms</option>
										<option value="Operating Systems" <?php echo ($formData['specialization'] === 'Operating Systems') ? 'selected' : ''; ?>>Operating Systems</option>
										<option value="Computer Networks" <?php echo ($formData['specialization'] === 'Computer Networks') ? 'selected' : ''; ?>>Computer Networks</option>
										<option value="Software Engineering" <?php echo ($formData['specialization'] === 'Software Engineering') ? 'selected' : ''; ?>>Software Engineering</option>
										<option value="Database Systems" <?php echo ($formData['specialization'] === 'Database Systems') ? 'selected' : ''; ?>>Database Systems</option>
										<option value="Web Development" <?php echo ($formData['specialization'] === 'Web Development') ? 'selected' : ''; ?>>Web Development</option>
										<option value="Mobile App Development" <?php echo ($formData['specialization'] === 'Mobile App Development') ? 'selected' : ''; ?>>Mobile App Development</option>
										<option value="Cybersecurity" <?php echo ($formData['specialization'] === 'Cybersecurity') ? 'selected' : ''; ?>>Cybersecurity</option>
										<option value="Machine Learning" <?php echo ($formData['specialization'] === 'Machine Learning') ? 'selected' : ''; ?>>Machine Learning</option>
										<option value="Artificial Intelligence" <?php echo ($formData['specialization'] === 'Artificial Intelligence') ? 'selected' : ''; ?>>Artificial Intelligence</option>
										<option value="Cloud Computing" <?php echo ($formData['specialization'] === 'Cloud Computing') ? 'selected' : ''; ?>>Cloud Computing</option>
										<option value="Computer Architecture" <?php echo ($formData['specialization'] === 'Computer Architecture') ? 'selected' : ''; ?>>Computer Architecture</option>
										<option value="Human-Computer Interaction" <?php echo ($formData['specialization'] === 'Human-Computer Interaction') ? 'selected' : ''; ?>>Human-Computer Interaction</option>
										<option value="Data Analytics" <?php echo ($formData['specialization'] === 'Data Analytics') ? 'selected' : ''; ?>>Data Analytics</option>
										<option value="Systems Analysis & Design" <?php echo ($formData['specialization'] === 'Systems Analysis & Design') ? 'selected' : ''; ?>>Systems Analysis & Design</option>
										<option value="IT Infrastructure" <?php echo ($formData['specialization'] === 'IT Infrastructure') ? 'selected' : ''; ?>>IT Infrastructure</option>
										<option value="DevOps" <?php echo ($formData['specialization'] === 'DevOps') ? 'selected' : ''; ?>>DevOps</option>
										<option value="Multimedia Systems" <?php echo ($formData['specialization'] === 'Multimedia Systems') ? 'selected' : ''; ?>>Multimedia Systems</option>
									</optgroup>
									<optgroup label="⚙️ College of Engineering" data-department="College of Engineering">
										<option value="Calculus" <?php echo ($formData['specialization'] === 'Calculus') ? 'selected' : ''; ?>>Calculus</option>
										<option value="Engineering Mathematics" <?php echo ($formData['specialization'] === 'Engineering Mathematics') ? 'selected' : ''; ?>>Engineering Mathematics</option>
										<option value="Thermodynamics" <?php echo ($formData['specialization'] === 'Thermodynamics') ? 'selected' : ''; ?>>Thermodynamics</option>
										<option value="Fluid Mechanics" <?php echo ($formData['specialization'] === 'Fluid Mechanics') ? 'selected' : ''; ?>>Fluid Mechanics</option>
										<option value="Strength of Materials" <?php echo ($formData['specialization'] === 'Strength of Materials') ? 'selected' : ''; ?>>Strength of Materials</option>
										<option value="Structural Analysis" <?php echo ($formData['specialization'] === 'Structural Analysis') ? 'selected' : ''; ?>>Structural Analysis</option>
										<option value="Circuit Analysis" <?php echo ($formData['specialization'] === 'Circuit Analysis') ? 'selected' : ''; ?>>Circuit Analysis</option>
										<option value="Electronics" <?php echo ($formData['specialization'] === 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
										<option value="Digital Systems" <?php echo ($formData['specialization'] === 'Digital Systems') ? 'selected' : ''; ?>>Digital Systems</option>
										<option value="Microprocessors" <?php echo ($formData['specialization'] === 'Microprocessors') ? 'selected' : ''; ?>>Microprocessors</option>
										<option value="Embedded Systems" <?php echo ($formData['specialization'] === 'Embedded Systems') ? 'selected' : ''; ?>>Embedded Systems</option>
										<option value="Signal Processing" <?php echo ($formData['specialization'] === 'Signal Processing') ? 'selected' : ''; ?>>Signal Processing</option>
										<option value="Power Systems" <?php echo ($formData['specialization'] === 'Power Systems') ? 'selected' : ''; ?>>Power Systems</option>
										<option value="Electrical Machines" <?php echo ($formData['specialization'] === 'Electrical Machines') ? 'selected' : ''; ?>>Electrical Machines</option>
										<option value="Control Systems" <?php echo ($formData['specialization'] === 'Control Systems') ? 'selected' : ''; ?>>Control Systems</option>
										<option value="Manufacturing Processes" <?php echo ($formData['specialization'] === 'Manufacturing Processes') ? 'selected' : ''; ?>>Manufacturing Processes</option>
										<option value="Robotics" <?php echo ($formData['specialization'] === 'Robotics') ? 'selected' : ''; ?>>Robotics</option>
										<option value="Automotive Engineering" <?php echo ($formData['specialization'] === 'Automotive Engineering') ? 'selected' : ''; ?>>Automotive Engineering</option>
										<option value="Mine Design & Safety" <?php echo ($formData['specialization'] === 'Mine Design & Safety') ? 'selected' : ''; ?>>Mine Design & Safety</option>
										<option value="Industrial Systems & Operations Research" <?php echo ($formData['specialization'] === 'Industrial Systems & Operations Research') ? 'selected' : ''; ?>>Industrial Systems & Operations Research</option>
									</optgroup>
									<optgroup label="🏛️ College of Architecture" data-department="College of Architecture">
										<option value="Architectural Design" <?php echo ($formData['specialization'] === 'Architectural Design') ? 'selected' : ''; ?>>Architectural Design</option>
										<option value="Urban Planning" <?php echo ($formData['specialization'] === 'Urban Planning') ? 'selected' : ''; ?>>Urban Planning</option>
										<option value="Landscape Architecture" <?php echo ($formData['specialization'] === 'Landscape Architecture') ? 'selected' : ''; ?>>Landscape Architecture</option>
										<option value="Building Technology" <?php echo ($formData['specialization'] === 'Building Technology') ? 'selected' : ''; ?>>Building Technology</option>
										<option value="Environmental Architecture" <?php echo ($formData['specialization'] === 'Environmental Architecture') ? 'selected' : ''; ?>>Environmental Architecture</option>
										<option value="Drafting & CAD" <?php echo ($formData['specialization'] === 'Drafting & CAD') ? 'selected' : ''; ?>>Drafting & CAD</option>
									</optgroup>
									<optgroup label="🔬 College of Science" data-department="College of Science">
										<option value="Calculus" <?php echo ($formData['specialization'] === 'Calculus') ? 'selected' : ''; ?>>Calculus</option>
										<option value="Algebra" <?php echo ($formData['specialization'] === 'Algebra') ? 'selected' : ''; ?>>Algebra</option>
										<option value="Statistics" <?php echo ($formData['specialization'] === 'Statistics') ? 'selected' : ''; ?>>Statistics</option>
										<option value="Probability" <?php echo ($formData['specialization'] === 'Probability') ? 'selected' : ''; ?>>Probability</option>
										<option value="Differential Equations" <?php echo ($formData['specialization'] === 'Differential Equations') ? 'selected' : ''; ?>>Differential Equations</option>
										<option value="General Biology" <?php echo ($formData['specialization'] === 'General Biology') ? 'selected' : ''; ?>>General Biology</option>
										<option value="Microbiology" <?php echo ($formData['specialization'] === 'Microbiology') ? 'selected' : ''; ?>>Microbiology</option>
										<option value="Genetics" <?php echo ($formData['specialization'] === 'Genetics') ? 'selected' : ''; ?>>Genetics</option>
										<option value="Ecology" <?php echo ($formData['specialization'] === 'Ecology') ? 'selected' : ''; ?>>Ecology</option>
										<option value="Anatomy & Physiology" <?php echo ($formData['specialization'] === 'Anatomy & Physiology') ? 'selected' : ''; ?>>Anatomy & Physiology</option>
										<option value="General Chemistry" <?php echo ($formData['specialization'] === 'General Chemistry') ? 'selected' : ''; ?>>General Chemistry</option>
										<option value="Organic Chemistry" <?php echo ($formData['specialization'] === 'Organic Chemistry') ? 'selected' : ''; ?>>Organic Chemistry</option>
										<option value="Physical Chemistry" <?php echo ($formData['specialization'] === 'Physical Chemistry') ? 'selected' : ''; ?>>Physical Chemistry</option>
										<option value="Biochemistry" <?php echo ($formData['specialization'] === 'Biochemistry') ? 'selected' : ''; ?>>Biochemistry</option>
										<option value="Physics" <?php echo ($formData['specialization'] === 'Physics') ? 'selected' : ''; ?>>Physics</option>
									</optgroup>
									<optgroup label="🧑‍💼 College of Business Administration" data-department="College of Business Administration">
										<option value="Accounting" <?php echo ($formData['specialization'] === 'Accounting') ? 'selected' : ''; ?>>Accounting</option>
										<option value="Auditing" <?php echo ($formData['specialization'] === 'Auditing') ? 'selected' : ''; ?>>Auditing</option>
										<option value="Taxation" <?php echo ($formData['specialization'] === 'Taxation') ? 'selected' : ''; ?>>Taxation</option>
										<option value="Financial Management" <?php echo ($formData['specialization'] === 'Financial Management') ? 'selected' : ''; ?>>Financial Management</option>
										<option value="Marketing" <?php echo ($formData['specialization'] === 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
										<option value="Entrepreneurship" <?php echo ($formData['specialization'] === 'Entrepreneurship') ? 'selected' : ''; ?>>Entrepreneurship</option>
										<option value="Business Analytics" <?php echo ($formData['specialization'] === 'Business Analytics') ? 'selected' : ''; ?>>Business Analytics</option>
										<option value="Economics" <?php echo ($formData['specialization'] === 'Economics') ? 'selected' : ''; ?>>Economics</option>
										<option value="Operations Management" <?php echo ($formData['specialization'] === 'Operations Management') ? 'selected' : ''; ?>>Operations Management</option>
										<option value="Tourism Management" <?php echo ($formData['specialization'] === 'Tourism Management') ? 'selected' : ''; ?>>Tourism Management</option>
										<option value="Hospitality Management" <?php echo ($formData['specialization'] === 'Hospitality Management') ? 'selected' : ''; ?>>Hospitality Management</option>
									</optgroup>
									<optgroup label="🧑‍🏫 College of Education & Liberal Arts" data-department="College of Education and Liberal Arts">
										<option value="English" <?php echo ($formData['specialization'] === 'English') ? 'selected' : ''; ?>>English</option>
										<option value="Mathematics" <?php echo ($formData['specialization'] === 'Mathematics') ? 'selected' : ''; ?>>Mathematics</option>
										<option value="Science" <?php echo ($formData['specialization'] === 'Science') ? 'selected' : ''; ?>>Science</option>
										<option value="Social Studies" <?php echo ($formData['specialization'] === 'Social Studies') ? 'selected' : ''; ?>>Social Studies</option>
										<option value="Early Childhood Education" <?php echo ($formData['specialization'] === 'Early Childhood Education') ? 'selected' : ''; ?>>Early Childhood Education</option>
										<option value="Educational Technology" <?php echo ($formData['specialization'] === 'Educational Technology') ? 'selected' : ''; ?>>Educational Technology</option>
										<option value="Clinical Psychology" <?php echo ($formData['specialization'] === 'Clinical Psychology') ? 'selected' : ''; ?>>Clinical Psychology</option>
										<option value="Industrial Psychology" <?php echo ($formData['specialization'] === 'Industrial Psychology') ? 'selected' : ''; ?>>Industrial Psychology</option>
										<option value="Developmental Psychology" <?php echo ($formData['specialization'] === 'Developmental Psychology') ? 'selected' : ''; ?>>Developmental Psychology</option>
										<option value="Journalism" <?php echo ($formData['specialization'] === 'Journalism') ? 'selected' : ''; ?>>Journalism</option>
										<option value="Broadcasting" <?php echo ($formData['specialization'] === 'Broadcasting') ? 'selected' : ''; ?>>Broadcasting</option>
										<option value="Public Relations" <?php echo ($formData['specialization'] === 'Public Relations') ? 'selected' : ''; ?>>Public Relations</option>
										<option value="Governance & Public Policy" <?php echo ($formData['specialization'] === 'Governance & Public Policy') ? 'selected' : ''; ?>>Governance & Public Policy</option>
									</optgroup>
									<optgroup label="🩺 College of Nursing" data-department="College of Nursing">
										<option value="Medical-Surgical Nursing" <?php echo ($formData['specialization'] === 'Medical-Surgical Nursing') ? 'selected' : ''; ?>>Medical-Surgical Nursing</option>
										<option value="Community Health Nursing" <?php echo ($formData['specialization'] === 'Community Health Nursing') ? 'selected' : ''; ?>>Community Health Nursing</option>
										<option value="Psychiatric Nursing" <?php echo ($formData['specialization'] === 'Psychiatric Nursing') ? 'selected' : ''; ?>>Psychiatric Nursing</option>
										<option value="Maternal & Child Nursing" <?php echo ($formData['specialization'] === 'Maternal & Child Nursing') ? 'selected' : ''; ?>>Maternal & Child Nursing</option>
										<option value="Nursing Research" <?php echo ($formData['specialization'] === 'Nursing Research') ? 'selected' : ''; ?>>Nursing Research</option>
									</optgroup>
									<optgroup label="💊 College of Pharmacy" data-department="College of Pharmacy">
										<option value="Pharmacology" <?php echo ($formData['specialization'] === 'Pharmacology') ? 'selected' : ''; ?>>Pharmacology</option>
										<option value="Pharmaceutics" <?php echo ($formData['specialization'] === 'Pharmaceutics') ? 'selected' : ''; ?>>Pharmaceutics</option>
										<option value="Clinical Pharmacy" <?php echo ($formData['specialization'] === 'Clinical Pharmacy') ? 'selected' : ''; ?>>Clinical Pharmacy</option>
										<option value="Pharmaceutical Chemistry" <?php echo ($formData['specialization'] === 'Pharmaceutical Chemistry') ? 'selected' : ''; ?>>Pharmaceutical Chemistry</option>
										<option value="Drug Development" <?php echo ($formData['specialization'] === 'Drug Development') ? 'selected' : ''; ?>>Drug Development</option>
									</optgroup>
									<optgroup label="⚖️ College of Law" data-department="College of Law">
										<option value="Civil Law" <?php echo ($formData['specialization'] === 'Civil Law') ? 'selected' : ''; ?>>Civil Law</option>
										<option value="Criminal Law" <?php echo ($formData['specialization'] === 'Criminal Law') ? 'selected' : ''; ?>>Criminal Law</option>
										<option value="Constitutional Law" <?php echo ($formData['specialization'] === 'Constitutional Law') ? 'selected' : ''; ?>>Constitutional Law</option>
										<option value="Corporate Law" <?php echo ($formData['specialization'] === 'Corporate Law') ? 'selected' : ''; ?>>Corporate Law</option>
										<option value="Labor Law" <?php echo ($formData['specialization'] === 'Labor Law') ? 'selected' : ''; ?>>Labor Law</option>
										<option value="Taxation Law" <?php echo ($formData['specialization'] === 'Taxation Law') ? 'selected' : ''; ?>>Taxation Law</option>
									</optgroup>
								</select>
							</div>
							<div class="mb-3">
								<label class="form-label">Faculty number</label>
								<input type="text" name="faculty_number" class="form-control" value="<?php echo htmlspecialchars($formData['faculty_number']); ?>">
							</div>
						</div>

						<div class="mb-3">
							<label class="form-label">Profile picture</label>
							<input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="form-control">
							<div class="mt-2">
								<img id="preview" src="" alt="" style="max-width:120px;display:none;border-radius:6px;" />
							</div>
						</div>

						<button class="btn btn-primary" type="submit"><?php echo ($mode === 'update') ? 'Update account' : 'Create account'; ?></button>
						<?php if ($mode === 'update'): ?>
							<a href="register.php" class="btn btn-link">Back to create mode</a>
						<?php endif; ?>
					</form>
				</div>
			</div>
		</div>
	</div>
	<?php if ($isAdmin): ?>
	<div class="row justify-content-center mt-4">
		<div class="col-md-10">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Manage existing accounts</h5>
					<p class="small text-muted">Click Edit to load a user into the form above, or Delete to remove the account.</p>
					<?php if (empty($userList)): ?>
						<p class="text-muted mb-0">No accounts found.</p>
					<?php else: ?>
						<div class="table-responsive">
							<table class="table table-striped align-middle mb-0">
								<thead>
									<tr>
										<th>ID</th>
										<th>Name</th>
										<th>Email</th>
										<th>Role</th>
										<th>Status</th>
										<th class="text-end">Actions</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($userList as $u): ?>
										<tr>
											<td><?php echo htmlspecialchars($u['user_id']); ?></td>
											<td><?php echo htmlspecialchars($u['name']); ?></td>
											<td><?php echo htmlspecialchars($u['email']); ?></td>
											<td><?php echo htmlspecialchars($u['role']); ?></td>
											<td><?php echo htmlspecialchars($u['status']); ?></td>
											<td class="text-end">
												<form action="register.php" method="get" class="d-inline">
													<input type="hidden" name="edit_user_id" value="<?php echo htmlspecialchars($u['user_id']); ?>">
													<button class="btn btn-sm btn-outline-primary">Edit</button>
												</form>
												<form action="../actions/register_action.php" method="post" class="d-inline" onsubmit="return confirm('Delete this account?');">
													<input type="hidden" name="action" value="delete">
													<input type="hidden" name="user_id" value="<?php echo htmlspecialchars($u['user_id']); ?>">
													<button class="btn btn-sm btn-outline-danger">Delete</button>
												</form>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>

<script>
(function() {

	const form = document.getElementById('registerForm');
	const studentFields = document.getElementById('studentFields');
	const facultyFields = document.getElementById('facultyFields');
	const fileInput = document.getElementById('profile_picture');
	const preview = document.getElementById('preview');
	const emailEl = document.getElementById('email');
	const emailError = document.getElementById('emailError');
	const passwordEl = document.getElementById('password');
	const togglePassword = document.getElementById('togglePassword');
	const pwStrength = document.getElementById('pwStrength');
	const contactEl = document.getElementById('contact_number');
	const phoneValidation = document.getElementById('phoneValidation');
	const phoneError = document.getElementById('phoneError');
	const phoneHint = document.getElementById('phoneHint');
	const addressEl = document.getElementById('address');
	const charCount = document.getElementById('charCount');
	const addressError = document.getElementById('addressError');

	// List of valid Philippine cities/municipalities and provinces
	const philippineCities = [
		'imus', 'dasmariñas', 'bacoor', 'kawit', 'rosario', 'magallanes', 'maragondon',
		'tagaytay', 'silang', 'indang', 'general trivia', 'general mariano alvarez',
		'cavite city', 'manila', 'quezon city', 'cebu', 'davao', 'manila', 'caloocan',
		'las piñas', 'makati', 'mandaluyong', 'marikina', 'pasay', 'pasig', 'pateros',
		'san juan', 'taguig', 'paranaque', 'muntinlupa', 'iloilo', 'bacolod', 'cebuh'
	];

	const philippineProvinces = [
		'cavite', 'laguna', 'batangas', 'quezon', 'rizal', 'bulacan', 'nueva ecija',
		'pampanga', 'tarlac', 'zambales', 'pangasinan', 'ilocos norte', 'ilocos sur',
		'la union', 'benguet', 'ifugao', 'apayao', 'kalinga', 'mountain province',
		'cagayan', 'isabela', 'nueva vizcaya', 'quirino', 'aurora', 'sorsogon',
		'albay', 'camarines norte', 'camarines sur', 'misamis oriental', 'misamis occidental',
		'negros occidental', 'negros oriental', 'iloilo', 'capiz', 'aklan', 'antique',
		'masbate', 'siquijor', 'bohol', 'cebu', 'dinagat islands', 'surigao del norte',
		'surigao del sur', 'agusan del norte', 'agusan del sur', 'davao oriental',
		'davao occidental', 'davao del norte', 'davao del sur', 'south cotabato',
		'cotabato', 'sarangani', 'sultan kudarat', 'maguindanao', 'lanao del norte',
		'lanao del sur', 'basilan', 'sulu', 'tawi-tawi', 'palawan', 'romblon',
		'mindoro occidental', 'mindoro oriental', 'batanes', 'catanduanes'
	];

	// Address validation function
	function validateAddress(address) {
		const trimmed = address.trim().toLowerCase();
		
		// Check minimum length
		if (trimmed.length < 20) {
			return false;
		}

		// Check if address contains comma-separated components (street, barangay, city, province)
		const parts = trimmed.split(',').map(p => p.trim());
		if (parts.length < 3) {
			return false;
		}

		// Check if at least one part contains a Philippine city or province name
		const hasPhilippineLocation = parts.some(part => {
			return philippineCities.some(city => part.includes(city)) ||
				   philippineProvinces.some(province => part.includes(province)) ||
				   part.includes('philippines') || part.includes('ph');
		});

		return hasPhilippineLocation;
	}

	// Address character counter and validation
	addressEl.addEventListener('input', function() {
		const length = this.value.length;
		charCount.textContent = length;
		
		// Update character count color
		if (length > 0 && length < 20) {
			charCount.parentElement.style.color = '#dc3545';
		} else if (length >= 20 && length <= 300) {
			const isValid = validateAddress(this.value);
			charCount.parentElement.style.color = isValid ? '#28a745' : '#ffc107';
		} else {
			charCount.parentElement.style.color = '#999';
		}
	});

	// Address validation on blur
	addressEl.addEventListener('blur', function() {
		const value = this.value.trim();
		if (value !== '' && value.length >= 20) {
			if (!validateAddress(value)) {
				addressError.style.display = 'block';
			} else {
				addressError.style.display = 'none';
			}
		}
	});

	// Initialize character count on page load
	if (addressEl.value.length > 0) {
		charCount.textContent = addressEl.value.length;
	}

	// Phone number formatting and validation
	contactEl.addEventListener('input', function() {
		// Allow only digits
		let value = this.value.replace(/\D/g, '');
		
		// Limit to 10 digits
		if (value.length > 10) {
			value = value.substring(0, 10);
		}
		
		this.value = value;
		
		// Update validation indicator
		if (value.length === 0) {
			phoneValidation.textContent = '⚪';
			phoneValidation.style.color = '#999';
			phoneError.style.display = 'none';
			phoneHint.style.display = '';
		} else if (value.length === 10) {
			phoneValidation.textContent = '✓';
			phoneValidation.style.color = '#28a745';
			phoneError.style.display = 'none';
			phoneHint.style.display = 'none';
		} else {
			phoneValidation.textContent = '✕';
			phoneValidation.style.color = '#dc3545';
			phoneError.style.display = 'block';
			phoneHint.style.display = 'none';
		}
	});

	// Validate phone on blur
	contactEl.addEventListener('blur', function() {
		const value = this.value.trim();
		if (value !== '' && value.length !== 10) {
			phoneError.style.display = 'block';
		}
	});

	// Show/hide student/faculty fields
	function update() {
		const role = form.role.value;
		studentFields.style.display = (role === 'Student') ? '' : 'none';
		facultyFields.style.display = (role === 'Faculty') ? '' : 'none';
	}
	form.addEventListener('change', update);

	// Email live validation
	emailEl.addEventListener('input', function() {
		const v = emailEl.value.trim();
		if (v === '') { emailError.style.display = 'none'; return; }
		emailError.style.display = /@gmail\.com$/i.test(v) ? 'none' : 'block';
	});

	// Password strength
	function calcStrength(pw) {
		let score = 0;
		if (pw.length >= 8) score++;
		if (/[A-Z]/.test(pw)) score++;
		if (/[0-9]/.test(pw)) score++;
		if (/[^A-Za-z0-9]/.test(pw)) score++;
		if (score <= 1) return { label: 'Weak', color: 'red' };
		if (score === 2) return { label: 'Fair', color: '#e68a00' };
		if (score === 3) return { label: 'Good', color: 'green' };
		return { label: 'Strong', color: 'darkgreen' };
	}

	passwordEl.addEventListener('input', function () {
		const s = calcStrength(passwordEl.value || '');
		pwStrength.textContent = s.label;
		pwStrength.style.color = s.color;
	});

	// Show/hide password
	togglePassword.addEventListener('click', function () {
		if (passwordEl.type === 'password') {
			passwordEl.type = 'text';
			togglePassword.textContent = 'Hide';
		} else {
			passwordEl.type = 'password';
			togglePassword.textContent = 'Show';
		}
	});

	// Image preview
	fileInput.addEventListener('change', function(){
		const f = fileInput.files && fileInput.files[0];
		if (!f) { preview.style.display = 'none'; preview.src = ''; return; }
		preview.src = URL.createObjectURL(f);
		preview.style.display = '';
	});

	const existingImage = <?php echo json_encode($formData['profile_picture']); ?>;
	if (existingImage) {
		preview.src = existingImage;
		preview.style.display = '';
	}

	// FINAL VALIDATION ON SUBMIT
	form.addEventListener('submit', function (e) {

		// Email must end with @gmail.com
		const emailValue = emailEl.value.trim();
		if (!/@gmail\.com$/i.test(emailValue)) {
			emailError.style.display = 'block';
			emailEl.focus();
			e.preventDefault();
			return false;
		}

		// Contact number must be exactly 10 digits or empty
		const contact = contactEl.value.trim();
		if (contact !== "" && contact.length !== 10) {
			phoneError.style.display = 'block';
			contactEl.focus();
			e.preventDefault();
			return false;
		}

		// Role specific validation
		const role = form.role.value;

		// Student validation
		if (role === "Student") {

			if (form.course.value.trim() === "") {
				alert("Course is required for students.");
				form.course.focus();
				e.preventDefault();
				return false;
			}

			if (form.student_number.value.trim() === "") {
				alert("Student number is required.");
				form.student_number.focus();
				e.preventDefault();
				return false;
			}
		}

		// Faculty validation
		if (role === "Faculty") {

			if (form.department.value.trim() === "") {
				alert("Department is required for faculty.");
				form.department.focus();
				e.preventDefault();
				return false;
			}

			if (form.faculty_number.value.trim() === "") {
				alert("Faculty number is required.");
				form.faculty_number.focus();
				e.preventDefault();
				return false;
			}
		}

	});

	update(); // initialize UI
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
