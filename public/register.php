<?php
// Simple registration page. Submits to ../actions/register_action.php
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card">
				<div class="card-body">
					<h3 class="card-title">Create Account</h3>
					
					<?php if (isset($_GET['error'])): ?>
						<div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
					<?php endif; ?>
					
					<?php if (isset($_GET['success'])): ?>
						<div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
					<?php endif; ?>

					<form action="../actions/register_action.php" method="post" id="registerForm" enctype="multipart/form-data">
						
						<div class="mb-3">
							<label class="form-label">Full name</label>
							<input type="text" name="name" class="form-control" required>
						</div>

						<div class="mb-3">
							<label class="form-label">Username</label>
							<input type="text" name="username" class="form-control" required>
						</div>

						<div class="mb-3">
							<label class="form-label">Email</label>
							<input type="email" name="email" id="email" class="form-control" required>
							<div class="form-text text-danger" id="emailError" style="display:none">Email must be a @gmail.com address.</div>
						</div>

						<div class="mb-3">
							<label class="form-label">Password</label>
							<div class="input-group">
								<input type="password" name="password" id="password" class="form-control" required>
								<button type="button" class="btn btn-outline-secondary" id="togglePassword">Show</button>
							</div>
							<div id="pwStrength" class="form-text mt-1"></div>
						</div>

						<div class="mb-3">
							<label class="form-label">Contact number</label>
							<input type="text" name="contact_number" class="form-control">
						</div>

						<div class="mb-3">
							<label class="form-label">Birthday</label>
							<input type="date" name="birthdate" class="form-control">
						</div>

						<div class="mb-3">
							<label class="form-label">Gender</label>
							<select name="gender" class="form-select">
								<option value="">-- Select gender (optional) --</option>
								<option value="Male">Male</option>
								<option value="Female">Female</option>
								<option value="Other">Other</option>
							</select>
						</div>

						<div class="mb-3">
							<label class="form-label">Address</label>
							<textarea name="address" class="form-control"></textarea>
						</div>

						<div class="mb-3">
							<label class="form-label">Role</label>
							<div>
								<label class="me-3"><input type="radio" name="role" value="Student" checked> Student</label>
								<label class="me-3"><input type="radio" name="role" value="Faculty"> Faculty</label>
								<label><input type="radio" name="role" value="Admin"> Admin</label>
							</div>
						</div>

						<!-- Student fields -->
						<div id="studentFields">
							<div class="mb-3">
								<label class="form-label">Course</label>
								<input type="text" name="course" class="form-control">
							</div>
							<div class="mb-3">
								<label class="form-label">Year level</label>
								<select name="year_level" class="form-select">
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
								</select>
							</div>
							<div class="mb-3">
								<label class="form-label">Student number</label>
								<input type="text" name="student_number" class="form-control">
							</div>
						</div>

						<!-- Faculty fields -->
						<div id="facultyFields" style="display:none;">
							<div class="mb-3">
								<label class="form-label">Department</label>
								<input type="text" name="department" class="form-control">
							</div>
							<div class="mb-3">
								<label class="form-label">Specialization</label>
								<input type="text" name="specialization" class="form-control">
							</div>
							<div class="mb-3">
								<label class="form-label">Faculty number</label>
								<input type="text" name="faculty_number" class="form-control">
							</div>
						</div>

						<div class="mb-3">
							<label class="form-label">Profile picture</label>
							<input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="form-control">
							<div class="mt-2">
								<img id="preview" src="" alt="" style="max-width:120px;display:none;border-radius:6px;" />
							</div>
						</div>

						<button class="btn btn-primary" type="submit">Create account</button>
					</form>
				</div>
			</div>
		</div>
	</div>
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
