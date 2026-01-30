<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/db.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('EVENTHUB_CONFIG')) {
    define('EVENTHUB_CONFIG', require __DIR__ . '/../config/database.php');
}
$cfg = EVENTHUB_CONFIG;

// Determine app base path for URLs.
// - If config sets base_path to 'auto' (or omits it), we derive it from DOCUMENT_ROOT + project root folder.
// - If config sets base_path to a string (e.g. '/Event%20Website'), we use it as-is.
$BASE_PATH = '';
if (is_array($cfg) && array_key_exists('base_path', $cfg) && $cfg['base_path'] !== null && $cfg['base_path'] !== 'auto') {
    $BASE_PATH = (string) $cfg['base_path'];
} else {
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath((string) $_SERVER['DOCUMENT_ROOT']) : false;
    $appRoot = realpath(__DIR__ . '/..');

    // Compute URL path to app root by mapping filesystem path to document root.
    if ($docRoot && $appRoot) {
        $docRootNorm = rtrim(str_replace('\\', '/', $docRoot), '/');
        $appRootNorm = rtrim(str_replace('\\', '/', $appRoot), '/');

        // Case-insensitive compare for Windows paths.
        $docLower = strtolower($docRootNorm);
        $appLower = strtolower($appRootNorm);
        if ($docLower !== '' && str_starts_with($appLower, $docLower)) {
            $rel = substr($appRootNorm, strlen($docRootNorm));
            $rel = '/' . ltrim($rel, '/');

            // Encode each path segment so spaces become %20, etc.
            $parts = array_values(array_filter(explode('/', $rel), static fn ($p) => $p !== ''));
            $parts = array_map('rawurlencode', $parts);
            $BASE_PATH = $parts ? ('/' . implode('/', $parts)) : '';
        }
    }
}

function base_url(string $path = ''): string {
    global $BASE_PATH;
    return rtrim($BASE_PATH, '/') . '/' . ltrim($path, '/');
}

function redirect(string $path, int $code = 302): void {
    header('Location: ' . base_url($path), true, $code);
    exit;
}

function flash(string $key, $message): void {
    $_SESSION['flash'][$key] = $message;
}

function flash_get(string $key) {
    $m = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $m;
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return !empty($_SESSION['user']);
}

function is_admin(): bool {
    $u = current_user();
    return $u && ($u['role'] ?? '') === 'admin';
}

function require_login(): void {
    global $BASE_PATH;
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        flash('error', 'Please log in to continue.');
        redirect('auth/login.php');
    }
}

function require_admin(): void {
    require_login();
    if (!is_admin()) {
        flash('error', 'Access denied. Admin only.');
        redirect('user/events.php');
    }
}

function user_can_register(): bool {
    $u = current_user();
    return $u && ($u['status'] ?? '') === 'active';
}
