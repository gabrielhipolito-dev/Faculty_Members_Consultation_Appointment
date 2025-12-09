<?php
session_start();

// Redirect logged-in users based on role
if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'Admin':
            header('Location: dashboard_admin.php'); exit;
        case 'Faculty':
            header('Location: dashboard_faculty.php'); exit;
        case 'Student':
            header('Location: dashboard_student.php'); exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid login-page">
    <div class="row w-100">
        <!-- Promo area -->
        <div class="col-lg-7 promo-area d-flex flex-column justify-content-center">
            <div class="px-5">
                <div class="logo">FACULTY MEMBERS CONSULTATION APPOINTMENT</div>
                <h1>We Are The Best<br/>In Business</h1>
                <p class="lead">
                    Elevate your workspace efficiency with Galileo's sleek and intuitive login page. 
                    Designed with simplicity, security, and speed in mind.
                </p>
            </div>
        </div>

        <!-- Login card -->
        <div class="col-lg-5 d-flex align-items-center justify-content-center">
            <?php include __DIR__ . '/login.php'; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
