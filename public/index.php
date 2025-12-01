<?php
session_start();

// If user already logged in, send to dashboard
if (!empty($_SESSION['user_id'])) {
        header('Location: dashboard.php');
        exit;
}

include("../includes/header.php");
?>

<div class="container-fluid login-page">
    <div class="row w-100">
        <div class="col-lg-7 promo-area d-flex flex-column justify-content-center">
            <div class="px-5">
                <div class="logo">Galileo</div>
                <h1>We Are The Best<br/>In Business</h1>
                <p class="lead">Elevate your workspace efficiency with Galileo's sleek and intuitive login page. Designed with simplicity, security, and speed in mind.</p>
                <p><a class="btn btn-outline-light" href="register.php">Create an account</a></p>
            </div>
        </div>

        <div class="col-lg-5 d-flex align-items-center justify-content-center">
            <?php include __DIR__ . '/login.php'; ?>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>

