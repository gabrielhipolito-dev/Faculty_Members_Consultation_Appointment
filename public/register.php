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
							<input type="text" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($formData['contact_number']); ?>">
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
							<textarea name="address" class="form-control"><?php echo htmlspecialchars($formData['address']); ?></textarea>
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
								<select name="department" class="form-select">
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
								<select name="specialization" class="form-select">
									<option value="">-- Select a specialization --</option>
									<optgroup label="🖥 CCIT — BS Computer Science">
										<option value="Algorithms" <?php echo ($formData['specialization'] === 'Algorithms') ? 'selected' : ''; ?>>Algorithms</option>
										<option value="Data Structures" <?php echo ($formData['specialization'] === 'Data Structures') ? 'selected' : ''; ?>>Data Structures</option>
										<option value="Operating Systems" <?php echo ($formData['specialization'] === 'Operating Systems') ? 'selected' : ''; ?>>Operating Systems</option>
										<option value="Computer Networks" <?php echo ($formData['specialization'] === 'Computer Networks') ? 'selected' : ''; ?>>Computer Networks</option>
										<option value="Machine Learning" <?php echo ($formData['specialization'] === 'Machine Learning') ? 'selected' : ''; ?>>Machine Learning</option>
										<option value="Artificial Intelligence" <?php echo ($formData['specialization'] === 'Artificial Intelligence') ? 'selected' : ''; ?>>Artificial Intelligence</option>
										<option value="Software Engineering" <?php echo ($formData['specialization'] === 'Software Engineering') ? 'selected' : ''; ?>>Software Engineering</option>
										<option value="Database Systems" <?php echo ($formData['specialization'] === 'Database Systems') ? 'selected' : ''; ?>>Database Systems</option>
										<option value="Distributed Systems" <?php echo ($formData['specialization'] === 'Distributed Systems') ? 'selected' : ''; ?>>Distributed Systems</option>
										<option value="Web Development" <?php echo ($formData['specialization'] === 'Web Development') ? 'selected' : ''; ?>>Web Development</option>
										<option value="Mobile Development" <?php echo ($formData['specialization'] === 'Mobile Development') ? 'selected' : ''; ?>>Mobile Development</option>
										<option value="Computer Architecture" <?php echo ($formData['specialization'] === 'Computer Architecture') ? 'selected' : ''; ?>>Computer Architecture</option>
										<option value="Cybersecurity" <?php echo ($formData['specialization'] === 'Cybersecurity') ? 'selected' : ''; ?>>Cybersecurity</option>
										<option value="Cloud Computing" <?php echo ($formData['specialization'] === 'Cloud Computing') ? 'selected' : ''; ?>>Cloud Computing</option>
										<option value="Human-Computer Interaction" <?php echo ($formData['specialization'] === 'Human-Computer Interaction') ? 'selected' : ''; ?>>Human-Computer Interaction</option>
										<option value="Game Development" <?php echo ($formData['specialization'] === 'Game Development') ? 'selected' : ''; ?>>Game Development</option>
										<option value="Computer Graphics" <?php echo ($formData['specialization'] === 'Computer Graphics') ? 'selected' : ''; ?>>Computer Graphics</option>
									</optgroup>
									<optgroup label="🖥 CCIT — BS Information Technology">
										<option value="Networking" <?php echo ($formData['specialization'] === 'Networking') ? 'selected' : ''; ?>>Networking</option>
										<option value="System Administration" <?php echo ($formData['specialization'] === 'System Administration') ? 'selected' : ''; ?>>System Administration</option>
										<option value="Web Technologies" <?php echo ($formData['specialization'] === 'Web Technologies') ? 'selected' : ''; ?>>Web Technologies</option>
										<option value="Cloud Infrastructure" <?php echo ($formData['specialization'] === 'Cloud Infrastructure') ? 'selected' : ''; ?>>Cloud Infrastructure</option>
										<option value="IT Security" <?php echo ($formData['specialization'] === 'IT Security') ? 'selected' : ''; ?>>IT Security</option>
										<option value="Server Management" <?php echo ($formData['specialization'] === 'Server Management') ? 'selected' : ''; ?>>Server Management</option>
										<option value="Database Administration" <?php echo ($formData['specialization'] === 'Database Administration') ? 'selected' : ''; ?>>Database Administration</option>
										<option value="Systems Integration" <?php echo ($formData['specialization'] === 'Systems Integration') ? 'selected' : ''; ?>>Systems Integration</option>
										<option value="IT Project Management" <?php echo ($formData['specialization'] === 'IT Project Management') ? 'selected' : ''; ?>>IT Project Management</option>
										<option value="DevOps" <?php echo ($formData['specialization'] === 'DevOps') ? 'selected' : ''; ?>>DevOps</option>
										<option value="Multimedia Systems" <?php echo ($formData['specialization'] === 'Multimedia Systems') ? 'selected' : ''; ?>>Multimedia Systems</option>
									</optgroup>
									<optgroup label="🖥 CCIT — BS Information Systems">
										<option value="Business Analytics" <?php echo ($formData['specialization'] === 'Business Analytics') ? 'selected' : ''; ?>>Business Analytics</option>
										<option value="Systems Analysis & Design" <?php echo ($formData['specialization'] === 'Systems Analysis & Design') ? 'selected' : ''; ?>>Systems Analysis & Design</option>
										<option value="Enterprise Resource Planning (ERP)" <?php echo ($formData['specialization'] === 'Enterprise Resource Planning (ERP)') ? 'selected' : ''; ?>>Enterprise Resource Planning (ERP)</option>
										<option value="E-Commerce Systems" <?php echo ($formData['specialization'] === 'E-Commerce Systems') ? 'selected' : ''; ?>>E-Commerce Systems</option>
										<option value="IT Governance" <?php echo ($formData['specialization'] === 'IT Governance') ? 'selected' : ''; ?>>IT Governance</option>
										<option value="Data Management" <?php echo ($formData['specialization'] === 'Data Management') ? 'selected' : ''; ?>>Data Management</option>
										<option value="Process Modeling" <?php echo ($formData['specialization'] === 'Process Modeling') ? 'selected' : ''; ?>>Process Modeling</option>
									</optgroup>
									<optgroup label="🖥 CCIT — BSCS–BSBA (Dual)">
										<option value="Data Analytics" <?php echo ($formData['specialization'] === 'Data Analytics') ? 'selected' : ''; ?>>Data Analytics</option>
										<option value="Information Systems Management" <?php echo ($formData['specialization'] === 'Information Systems Management') ? 'selected' : ''; ?>>Information Systems Management</option>
									</optgroup>
									<optgroup label="⚙️ Chemical Engineering">
										<option value="Thermodynamics" <?php echo ($formData['specialization'] === 'Thermodynamics') ? 'selected' : ''; ?>>Thermodynamics</option>
										<option value="Chemical Reaction Engineering" <?php echo ($formData['specialization'] === 'Chemical Reaction Engineering') ? 'selected' : ''; ?>>Chemical Reaction Engineering</option>
										<option value="Process Control" <?php echo ($formData['specialization'] === 'Process Control') ? 'selected' : ''; ?>>Process Control</option>
										<option value="Transport Phenomena" <?php echo ($formData['specialization'] === 'Transport Phenomena') ? 'selected' : ''; ?>>Transport Phenomena</option>
										<option value="Biochemical Engineering" <?php echo ($formData['specialization'] === 'Biochemical Engineering') ? 'selected' : ''; ?>>Biochemical Engineering</option>
										<option value="Process Design" <?php echo ($formData['specialization'] === 'Process Design') ? 'selected' : ''; ?>>Process Design</option>
									</optgroup>
									<optgroup label="⚙️ Civil Engineering">
										<option value="Structural Engineering" <?php echo ($formData['specialization'] === 'Structural Engineering') ? 'selected' : ''; ?>>Structural Engineering</option>
										<option value="Hydraulics" <?php echo ($formData['specialization'] === 'Hydraulics') ? 'selected' : ''; ?>>Hydraulics</option>
										<option value="Transportation Engineering" <?php echo ($formData['specialization'] === 'Transportation Engineering') ? 'selected' : ''; ?>>Transportation Engineering</option>
										<option value="Geotechnical Engineering" <?php echo ($formData['specialization'] === 'Geotechnical Engineering') ? 'selected' : ''; ?>>Geotechnical Engineering</option>
										<option value="Construction Engineering" <?php echo ($formData['specialization'] === 'Construction Engineering') ? 'selected' : ''; ?>>Construction Engineering</option>
										<option value="Environmental Engineering" <?php echo ($formData['specialization'] === 'Environmental Engineering') ? 'selected' : ''; ?>>Environmental Engineering</option>
									</optgroup>
									<optgroup label="⚙️ Computer Engineering">
										<option value="Digital Systems" <?php echo ($formData['specialization'] === 'Digital Systems') ? 'selected' : ''; ?>>Digital Systems</option>
										<option value="Microprocessors" <?php echo ($formData['specialization'] === 'Microprocessors') ? 'selected' : ''; ?>>Microprocessors</option>
										<option value="Embedded Systems" <?php echo ($formData['specialization'] === 'Embedded Systems') ? 'selected' : ''; ?>>Embedded Systems</option>
										<option value="Computer Hardware Design" <?php echo ($formData['specialization'] === 'Computer Hardware Design') ? 'selected' : ''; ?>>Computer Hardware Design</option>
										<option value="Robotics" <?php echo ($formData['specialization'] === 'Robotics') ? 'selected' : ''; ?>>Robotics</option>
										<option value="Signal Processing" <?php echo ($formData['specialization'] === 'Signal Processing') ? 'selected' : ''; ?>>Signal Processing</option>
									</optgroup>
									<optgroup label="⚙️ Electronics Engineering">
										<option value="Analog Electronics" <?php echo ($formData['specialization'] === 'Analog Electronics') ? 'selected' : ''; ?>>Analog Electronics</option>
										<option value="Digital Electronics" <?php echo ($formData['specialization'] === 'Digital Electronics') ? 'selected' : ''; ?>>Digital Electronics</option>
										<option value="Communications Engineering" <?php echo ($formData['specialization'] === 'Communications Engineering') ? 'selected' : ''; ?>>Communications Engineering</option>
										<option value="VLSI Design" <?php echo ($formData['specialization'] === 'VLSI Design') ? 'selected' : ''; ?>>VLSI Design</option>
										<option value="RF Engineering" <?php echo ($formData['specialization'] === 'RF Engineering') ? 'selected' : ''; ?>>RF Engineering</option>
										<option value="Control Systems" <?php echo ($formData['specialization'] === 'Control Systems') ? 'selected' : ''; ?>>Control Systems</option>
									</optgroup>
									<optgroup label="⚙️ Electrical Engineering">
										<option value="Power Systems" <?php echo ($formData['specialization'] === 'Power Systems') ? 'selected' : ''; ?>>Power Systems</option>
										<option value="Electrical Machines" <?php echo ($formData['specialization'] === 'Electrical Machines') ? 'selected' : ''; ?>>Electrical Machines</option>
										<option value="Renewable Energy" <?php echo ($formData['specialization'] === 'Renewable Energy') ? 'selected' : ''; ?>>Renewable Energy</option>
										<option value="Instrumentation" <?php echo ($formData['specialization'] === 'Instrumentation') ? 'selected' : ''; ?>>Instrumentation</option>
										<option value="Electromagnetics" <?php echo ($formData['specialization'] === 'Electromagnetics') ? 'selected' : ''; ?>>Electromagnetics</option>
										<option value="Power Transmission" <?php echo ($formData['specialization'] === 'Power Transmission') ? 'selected' : ''; ?>>Power Transmission</option>
									</optgroup>
									<optgroup label="⚙️ Mechanical Engineering">
										<option value="Fluid Mechanics" <?php echo ($formData['specialization'] === 'Fluid Mechanics') ? 'selected' : ''; ?>>Fluid Mechanics</option>
										<option value="Machine Design" <?php echo ($formData['specialization'] === 'Machine Design') ? 'selected' : ''; ?>>Machine Design</option>
										<option value="Heat Transfer" <?php echo ($formData['specialization'] === 'Heat Transfer') ? 'selected' : ''; ?>>Heat Transfer</option>
										<option value="Manufacturing Engineering" <?php echo ($formData['specialization'] === 'Manufacturing Engineering') ? 'selected' : ''; ?>>Manufacturing Engineering</option>
										<option value="Automotive Engineering" <?php echo ($formData['specialization'] === 'Automotive Engineering') ? 'selected' : ''; ?>>Automotive Engineering</option>
									</optgroup>
									<optgroup label="⚙️ Mining Engineering">
										<option value="Mineral Processing" <?php echo ($formData['specialization'] === 'Mineral Processing') ? 'selected' : ''; ?>>Mineral Processing</option>
										<option value="Mine Safety" <?php echo ($formData['specialization'] === 'Mine Safety') ? 'selected' : ''; ?>>Mine Safety</option>
										<option value="Mine Design" <?php echo ($formData['specialization'] === 'Mine Design') ? 'selected' : ''; ?>>Mine Design</option>
										<option value="Rock Mechanics" <?php echo ($formData['specialization'] === 'Rock Mechanics') ? 'selected' : ''; ?>>Rock Mechanics</option>
										<option value="Mining Operations" <?php echo ($formData['specialization'] === 'Mining Operations') ? 'selected' : ''; ?>>Mining Operations</option>
									</optgroup>
									<optgroup label="⚙️ Industrial Engineering">
										<option value="Operations Research" <?php echo ($formData['specialization'] === 'Operations Research') ? 'selected' : ''; ?>>Operations Research</option>
										<option value="Supply Chain Management" <?php echo ($formData['specialization'] === 'Supply Chain Management') ? 'selected' : ''; ?>>Supply Chain Management</option>
										<option value="Systems Engineering" <?php echo ($formData['specialization'] === 'Systems Engineering') ? 'selected' : ''; ?>>Systems Engineering</option>
										<option value="Production Planning" <?php echo ($formData['specialization'] === 'Production Planning') ? 'selected' : ''; ?>>Production Planning</option>
										<option value="Quality Control" <?php echo ($formData['specialization'] === 'Quality Control') ? 'selected' : ''; ?>>Quality Control</option>
										<option value="Ergonomics" <?php echo ($formData['specialization'] === 'Ergonomics') ? 'selected' : ''; ?>>Ergonomics</option>
									</optgroup>
									<optgroup label="🏛 Architecture">
										<option value="Architectural Design" <?php echo ($formData['specialization'] === 'Architectural Design') ? 'selected' : ''; ?>>Architectural Design</option>
										<option value="Urban Planning" <?php echo ($formData['specialization'] === 'Urban Planning') ? 'selected' : ''; ?>>Urban Planning</option>
										<option value="Landscape Architecture" <?php echo ($formData['specialization'] === 'Landscape Architecture') ? 'selected' : ''; ?>>Landscape Architecture</option>
										<option value="Building Technology" <?php echo ($formData['specialization'] === 'Building Technology') ? 'selected' : ''; ?>>Building Technology</option>
										<option value="Environmental Architecture" <?php echo ($formData['specialization'] === 'Environmental Architecture') ? 'selected' : ''; ?>>Environmental Architecture</option>
										<option value="Drafting & CAD" <?php echo ($formData['specialization'] === 'Drafting & CAD') ? 'selected' : ''; ?>>Drafting & CAD</option>
									</optgroup>
									<optgroup label="🔬 Biology">
										<option value="Microbiology" <?php echo ($formData['specialization'] === 'Microbiology') ? 'selected' : ''; ?>>Microbiology</option>
										<option value="Genetics" <?php echo ($formData['specialization'] === 'Genetics') ? 'selected' : ''; ?>>Genetics</option>
										<option value="Ecology" <?php echo ($formData['specialization'] === 'Ecology') ? 'selected' : ''; ?>>Ecology</option>
										<option value="Molecular Biology" <?php echo ($formData['specialization'] === 'Molecular Biology') ? 'selected' : ''; ?>>Molecular Biology</option>
										<option value="Anatomy & Physiology" <?php echo ($formData['specialization'] === 'Anatomy & Physiology') ? 'selected' : ''; ?>>Anatomy & Physiology</option>
										<option value="Environmental Biology" <?php echo ($formData['specialization'] === 'Environmental Biology') ? 'selected' : ''; ?>>Environmental Biology</option>
									</optgroup>
									<optgroup label="🔬 Chemistry">
										<option value="Organic Chemistry" <?php echo ($formData['specialization'] === 'Organic Chemistry') ? 'selected' : ''; ?>>Organic Chemistry</option>
										<option value="Inorganic Chemistry" <?php echo ($formData['specialization'] === 'Inorganic Chemistry') ? 'selected' : ''; ?>>Inorganic Chemistry</option>
										<option value="Analytical Chemistry" <?php echo ($formData['specialization'] === 'Analytical Chemistry') ? 'selected' : ''; ?>>Analytical Chemistry</option>
										<option value="Physical Chemistry" <?php echo ($formData['specialization'] === 'Physical Chemistry') ? 'selected' : ''; ?>>Physical Chemistry</option>
										<option value="Biochemistry" <?php echo ($formData['specialization'] === 'Biochemistry') ? 'selected' : ''; ?>>Biochemistry</option>
										<option value="Environmental Chemistry" <?php echo ($formData['specialization'] === 'Environmental Chemistry') ? 'selected' : ''; ?>>Environmental Chemistry</option>
									</optgroup>
									<optgroup label="🔬 Physics">
										<option value="Mechanics" <?php echo ($formData['specialization'] === 'Mechanics') ? 'selected' : ''; ?>>Mechanics</option>
										<option value="Electromagnetism" <?php echo ($formData['specialization'] === 'Electromagnetism') ? 'selected' : ''; ?>>Electromagnetism</option>
										<option value="Quantum Physics" <?php echo ($formData['specialization'] === 'Quantum Physics') ? 'selected' : ''; ?>>Quantum Physics</option>
										<option value="Optics" <?php echo ($formData['specialization'] === 'Optics') ? 'selected' : ''; ?>>Optics</option>
										<option value="Modern Physics" <?php echo ($formData['specialization'] === 'Modern Physics') ? 'selected' : ''; ?>>Modern Physics</option>
									</optgroup>
									<optgroup label="🔬 Mathematics">
										<option value="Calculus" <?php echo ($formData['specialization'] === 'Calculus') ? 'selected' : ''; ?>>Calculus</option>
										<option value="Algebra" <?php echo ($formData['specialization'] === 'Algebra') ? 'selected' : ''; ?>>Algebra</option>
										<option value="Geometry" <?php echo ($formData['specialization'] === 'Geometry') ? 'selected' : ''; ?>>Geometry</option>
										<option value="Statistics" <?php echo ($formData['specialization'] === 'Statistics') ? 'selected' : ''; ?>>Statistics</option>
										<option value="Probability" <?php echo ($formData['specialization'] === 'Probability') ? 'selected' : ''; ?>>Probability</option>
										<option value="Differential Equations" <?php echo ($formData['specialization'] === 'Differential Equations') ? 'selected' : ''; ?>>Differential Equations</option>
										<option value="Number Theory" <?php echo ($formData['specialization'] === 'Number Theory') ? 'selected' : ''; ?>>Number Theory</option>
										<option value="Linear Algebra" <?php echo ($formData['specialization'] === 'Linear Algebra') ? 'selected' : ''; ?>>Linear Algebra</option>
									</optgroup>
									<optgroup label="🧑‍💼 Accountancy">
										<option value="Financial Accounting" <?php echo ($formData['specialization'] === 'Financial Accounting') ? 'selected' : ''; ?>>Financial Accounting</option>
										<option value="Auditing" <?php echo ($formData['specialization'] === 'Auditing') ? 'selected' : ''; ?>>Auditing</option>
										<option value="Taxation" <?php echo ($formData['specialization'] === 'Taxation') ? 'selected' : ''; ?>>Taxation</option>
										<option value="Accounting Information Systems" <?php echo ($formData['specialization'] === 'Accounting Information Systems') ? 'selected' : ''; ?>>Accounting Information Systems</option>
									</optgroup>
									<optgroup label="🧑‍💼 Business Administration">
										<option value="Marketing" <?php echo ($formData['specialization'] === 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
										<option value="Financial Management" <?php echo ($formData['specialization'] === 'Financial Management') ? 'selected' : ''; ?>>Financial Management</option>
										<option value="Economics" <?php echo ($formData['specialization'] === 'Economics') ? 'selected' : ''; ?>>Economics</option>
										<option value="Entrepreneurship" <?php echo ($formData['specialization'] === 'Entrepreneurship') ? 'selected' : ''; ?>>Entrepreneurship</option>
										<option value="Operations Management" <?php echo ($formData['specialization'] === 'Operations Management') ? 'selected' : ''; ?>>Operations Management</option>
									</optgroup>
									<optgroup label="🧑‍💼 Hospitality Management">
										<option value="Food & Beverage Services" <?php echo ($formData['specialization'] === 'Food & Beverage Services') ? 'selected' : ''; ?>>Food & Beverage Services</option>
										<option value="Front Office Operations" <?php echo ($formData['specialization'] === 'Front Office Operations') ? 'selected' : ''; ?>>Front Office Operations</option>
										<option value="Tourism & Events" <?php echo ($formData['specialization'] === 'Tourism & Events') ? 'selected' : ''; ?>>Tourism & Events</option>
										<option value="Culinary Arts" <?php echo ($formData['specialization'] === 'Culinary Arts') ? 'selected' : ''; ?>>Culinary Arts</option>
									</optgroup>
									<optgroup label="🧑‍💼 Tourism">
										<option value="Tourism Planning" <?php echo ($formData['specialization'] === 'Tourism Planning') ? 'selected' : ''; ?>>Tourism Planning</option>
										<option value="Travel Management" <?php echo ($formData['specialization'] === 'Travel Management') ? 'selected' : ''; ?>>Travel Management</option>
										<option value="Heritage & Culture" <?php echo ($formData['specialization'] === 'Heritage & Culture') ? 'selected' : ''; ?>>Heritage & Culture</option>
									</optgroup>
									<optgroup label="🧑‍🏫 Education">
										<option value="English" <?php echo ($formData['specialization'] === 'English') ? 'selected' : ''; ?>>English</option>
										<option value="Science" <?php echo ($formData['specialization'] === 'Science') ? 'selected' : ''; ?>>Science</option>
										<option value="Mathematics" <?php echo ($formData['specialization'] === 'Mathematics') ? 'selected' : ''; ?>>Mathematics</option>
										<option value="Social Studies" <?php echo ($formData['specialization'] === 'Social Studies') ? 'selected' : ''; ?>>Social Studies</option>
										<option value="Early Childhood Education" <?php echo ($formData['specialization'] === 'Early Childhood Education') ? 'selected' : ''; ?>>Early Childhood Education</option>
										<option value="Educational Technology" <?php echo ($formData['specialization'] === 'Educational Technology') ? 'selected' : ''; ?>>Educational Technology</option>
									</optgroup>
									<optgroup label="🧑‍🏫 Psychology">
										<option value="Clinical Psychology" <?php echo ($formData['specialization'] === 'Clinical Psychology') ? 'selected' : ''; ?>>Clinical Psychology</option>
										<option value="Developmental Psychology" <?php echo ($formData['specialization'] === 'Developmental Psychology') ? 'selected' : ''; ?>>Developmental Psychology</option>
										<option value="Industrial Psychology" <?php echo ($formData['specialization'] === 'Industrial Psychology') ? 'selected' : ''; ?>>Industrial Psychology</option>
										<option value="Counseling Psychology" <?php echo ($formData['specialization'] === 'Counseling Psychology') ? 'selected' : ''; ?>>Counseling Psychology</option>
									</optgroup>
									<optgroup label="🧑‍🏫 Communication">
										<option value="Journalism" <?php echo ($formData['specialization'] === 'Journalism') ? 'selected' : ''; ?>>Journalism</option>
										<option value="Broadcasting" <?php echo ($formData['specialization'] === 'Broadcasting') ? 'selected' : ''; ?>>Broadcasting</option>
										<option value="Advertising" <?php echo ($formData['specialization'] === 'Advertising') ? 'selected' : ''; ?>>Advertising</option>
										<option value="Public Relations" <?php echo ($formData['specialization'] === 'Public Relations') ? 'selected' : ''; ?>>Public Relations</option>
									</optgroup>
									<optgroup label="🧑‍🏫 Political Science">
										<option value="Governance" <?php echo ($formData['specialization'] === 'Governance') ? 'selected' : ''; ?>>Governance</option>
										<option value="Public Policy" <?php echo ($formData['specialization'] === 'Public Policy') ? 'selected' : ''; ?>>Public Policy</option>
										<option value="International Relations" <?php echo ($formData['specialization'] === 'International Relations') ? 'selected' : ''; ?>>International Relations</option>
									</optgroup>
									<optgroup label="🩺 Nursing">
										<option value="Medical-Surgical Nursing" <?php echo ($formData['specialization'] === 'Medical-Surgical Nursing') ? 'selected' : ''; ?>>Medical-Surgical Nursing</option>
										<option value="Community Health Nursing" <?php echo ($formData['specialization'] === 'Community Health Nursing') ? 'selected' : ''; ?>>Community Health Nursing</option>
										<option value="Psychiatric Nursing" <?php echo ($formData['specialization'] === 'Psychiatric Nursing') ? 'selected' : ''; ?>>Psychiatric Nursing</option>
										<option value="Maternal & Child Nursing" <?php echo ($formData['specialization'] === 'Maternal & Child Nursing') ? 'selected' : ''; ?>>Maternal & Child Nursing</option>
										<option value="Nursing Research" <?php echo ($formData['specialization'] === 'Nursing Research') ? 'selected' : ''; ?>>Nursing Research</option>
									</optgroup>
									<optgroup label="💊 Pharmacy">
										<option value="Pharmacology" <?php echo ($formData['specialization'] === 'Pharmacology') ? 'selected' : ''; ?>>Pharmacology</option>
										<option value="Pharmaceutics" <?php echo ($formData['specialization'] === 'Pharmaceutics') ? 'selected' : ''; ?>>Pharmaceutics</option>
										<option value="Clinical Pharmacy" <?php echo ($formData['specialization'] === 'Clinical Pharmacy') ? 'selected' : ''; ?>>Clinical Pharmacy</option>
										<option value="Drug Development" <?php echo ($formData['specialization'] === 'Drug Development') ? 'selected' : ''; ?>>Drug Development</option>
										<option value="Pharmaceutical Chemistry" <?php echo ($formData['specialization'] === 'Pharmaceutical Chemistry') ? 'selected' : ''; ?>>Pharmaceutical Chemistry</option>
									</optgroup>
									<optgroup label="⚖️ Law">
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

		// Contact number must be numbers only
		const contact = form.contact_number.value.trim();
		if (contact !== "" && !/^[0-9]+$/.test(contact)) {
			alert("Contact number must contain numbers only.");
			form.contact_number.focus();
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
