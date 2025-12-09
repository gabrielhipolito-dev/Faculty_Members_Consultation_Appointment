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
							<select name="gender" class="form-select">
								<option value="">-- Select gender (optional) --</option>
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
								<input type="text" name="course" class="form-control" value="<?php echo htmlspecialchars($formData['course']); ?>">
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
								<input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($formData['department']); ?>">
							</div>
							<div class="mb-3">
								<label class="form-label">Specialization</label>
								<input type="text" name="specialization" class="form-control" value="<?php echo htmlspecialchars($formData['specialization']); ?>">
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
