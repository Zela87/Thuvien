<div class="topbar">
<div style="color: #5a5c69; font-weight: 600;">
        <i class="fas fa-user-circle"></i> <?php echo ($_SESSION['ho_ten'] ?? 'Khách'); ?>
    </div>

<?php if (($_SESSION['role'] ?? 'guest') != 'guest'): ?>
        <a href="../actions/auth-index.php?logout=true" class="btn-logout">Thoát</a>
    <?php endif; ?>
</div>