<?php include("../includes/header.php"); ?>

<div class="container-fluid login-page">
    <div class="row w-100">
        <div class="col-lg-7 promo-area d-flex flex-column justify-content-center">
            <div class="px-5">
                <div class="logo">Galileo</div>
                <h1>We Are The Best<br/>In Business</h1>
                <p class="lead">Elevate your workspace efficiency with Galileo's sleek and intuitive login page. Designed with simplicity, security, and speed in mind.</p>
            </div>
        </div>

        <div class="col-lg-5 d-flex align-items-center justify-content-center">
            <div class="glass-card w-100" style="max-width:440px;">
                <h5 class="text-center mb-3">WELCOME BACK EXCLUSIVE MEMBER</h5>
                <p class="text-center small mb-4">LOG IN TO CONTINUE</p>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <?php include __DIR__ . '/../includes/login-form.php'; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle show/hide password
    (function(){
        const toggle = document.getElementById('togglePassword');
        const pwd = document.getElementById('password');
        if (!toggle || !pwd) return;
        toggle.addEventListener('click', function(){
            if (pwd.type === 'password') { pwd.type = 'text'; toggle.textContent = 'HIDE'; }
            else { pwd.type = 'password'; toggle.textContent = 'SHOW'; }
        });
    })();
</script>

<?php include("../includes/footer.php"); ?>

