<?php
require_once __DIR__ . '/../includes/auth.php';
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], $p['httponly']);
}
session_destroy();
flash('success', 'You have been logged out.');
redirect('auth/login.php');
