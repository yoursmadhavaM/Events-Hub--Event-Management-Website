<?php
if (!defined('EVENTHUB_NAVBAR_LOADED')) {
    define('EVENTHUB_NAVBAR_LOADED', true);
    if (!function_exists('base_url')) {
        require_once __DIR__ . '/db.php';
        require_once __DIR__ . '/auth.php';
    }
}
// Ensure auth helpers exist so admin vs user nav is correct (e.g. hide Contact for admin)
if (!function_exists('current_user')) {
    require_once __DIR__ . '/auth.php';
}
$user = current_user();
$isAdmin = is_admin();
?>
<nav class="navbar navbar-expand-lg navbar-light eh-navbar shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary animate-pulse-slow" href="<?= htmlspecialchars($user ? ($isAdmin ? base_url('admin/dashboard.php') : base_url('user/events.php')) : base_url('index.php')) ?>">Events Hub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars(base_url('user/events.php')) ?>">Events</a>
                </li>
                <?php if (!$user): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars(base_url('about.php')) ?>">About</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars(base_url('gallery.php')) ?>">Gallery</a>
                </li>
                <?php if (!$isAdmin): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars(base_url('contact.php')) ?>">Contact</a>
                </li>
                <?php endif; ?>
                <?php if ($user): ?>
                    <?php if (!$isAdmin): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars(base_url('user/my_registrations.php')) ?>">My Registrations</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($isAdmin): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars(base_url('admin/dashboard.php')) ?>">Admin</a>
                    </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <div class="d-flex gap-2 align-items-center">
                <?php if ($user): ?>
                    <span class="small text-muted d-none d-md-inline"><?= htmlspecialchars($user['name']) ?></span>
                    <a href="<?= htmlspecialchars(base_url('auth/logout.php')) ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
                <?php else: ?>
                    <a href="<?= htmlspecialchars(base_url('auth/login.php')) ?>" class="btn btn-outline-primary btn-sm">Login</a>
                    <a href="<?= htmlspecialchars(base_url('auth/register.php')) ?>" class="btn btn-primary btn-sm">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
