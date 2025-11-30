<?php
// Reusable login form include. Place this in pages under `public/` with:
// <?php include __DIR__ . '/login-form.php'; ?>
?>

<form action="../actions/login_action.php" method="POST" novalidate>
    <div class="mb-3">
        <div class="input-group">
            <span class="input-group-text bg-white border-0">👤</span>
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
    </div>

    <div class="mb-3 position-relative">
        <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
        <div class="position-absolute" style="right:12px;top:10px;">
            <small class="show-password" id="togglePassword">SHOW</small>
        </div>
    </div>

    <button type="submit" class="btn btn-proceed w-100">Proceed to my Account</button>
</form>

<script>
    // Toggle show/hide password (kept here so any page including the form has the behaviour)
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
