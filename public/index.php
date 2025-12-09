<?php
session_start();

// Redirect logged-in users based on role
if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {

    switch ($_SESSION['role']) {
        case 'Admin':
            header('Location: dashboard_admin.php');
            exit;

        case 'Faculty':
            header('Location: dashboard_faculty.php');
            exit;

        case 'Student':
            header('Location: dashboard_student.php');
            exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid login-page">
    <div class="row w-100">
        <div class="col-lg-7 promo-area d-flex flex-column justify-content-center">
        </div>

        <div class="col-lg-5 d-flex align-items-center justify-content-center">
            <?php include __DIR__ . '/login.php'; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
