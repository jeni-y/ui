<?php if (!empty($_SESSION['auth_error'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_SESSION['auth_error']) ?>
    </div>
    <?php unset($_SESSION['auth_error']); ?>
<?php endif; ?>