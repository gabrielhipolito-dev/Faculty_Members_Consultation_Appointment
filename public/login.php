<?php
/**
 * login.php
 * Renders login form.
 * - If accessed directly, includes header/footer and full page layout.
 * - If included, only renders the login card.
 */

// Check if this page is accessed directly
$direct = realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME']);

// Include header and page layout if direct
if ($direct) {
    include __DIR__ . '/../includes/header.php';
    ?>
    <div class="container-fluid login-page">
        <div class="row w-100">
            <!-- Promo area -->
            <div class="col-lg-7 promo-area d-flex flex-column justify-content-center">
                <div class="px-5">
                    <div class="logo">Galileo</div>
                    <h1>We Are The Best<br/>In Business</h1>
                    <p class="lead">
                        Elevate your workspace efficiency with Galileo's sleek and intuitive login page. 
                        Designed with simplicity, security, and speed in mind.
                    </p>
                    <!-- registration moved to dashboard for admins -->
                </div>
            </div>
            <!-- Login card area -->
            <div class="col-lg-5 d-flex align-items-center justify-content-center">
<?php } ?>

    <!-- Login card -->
    <div class="glass-card w-100" style="max-width:440px;">
        <h5 class="text-center mb-3">WELCOME BACK EXCLUSIVE MEMBER</h5>
        <p class="text-center small mb-4">LOG IN TO CONTINUE</p>

        <!-- Display error message if exists -->
        <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Login form -->
        <form action="../actions/login_action.php" method="POST" novalidate>
            <!-- Identifier (username/email) -->
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-0">👤</span>
                    <input 
                        type="text" 
                        name="identifier" 
                        class="form-control" 
                        placeholder="Email or username" 
                        required 
                        autofocus
                    >
                </div>
            </div>

            <!-- Password -->
            <div class="mb-3 position-relative">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="Password" 
                    required
                >
                <div class="position-absolute" style="right:12px; top:10px;">
                    <small class="show-password" id="togglePassword">SHOW</small>
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-proceed w-100">Proceed to my Account</button>
        </form>
    </div>

<?php
// Close layout if direct request
if ($direct) {
    ?>
            </div> <!-- end login card column -->
        </div> <!-- end row -->
    </div> <!-- end container-fluid -->

    <!-- Password show/hide script -->
    <script>
        (function() {
            const toggle = document.getElementById('togglePassword');
            const pwd = document.getElementById('password');
            if (!toggle || !pwd) return;

            toggle.addEventListener('click', function() {
                if (pwd.type === 'password') {
                    pwd.type = 'text';
                    toggle.textContent = 'HIDE';
                } else {
                    pwd.type = 'password';
                    toggle.textContent = 'SHOW';
                }
            });
        })();
    </script>

    <?php
    include __DIR__ . '/../includes/footer.php';
}
