<?php
/**
 * Lightweight admin logging helper.
 */

function admin_log(string $action, ?string $targetType = null, ?int $targetId = null, ?array $meta = null): void {
    if (!isset($GLOBALS['pdo'])) {
        return;
    }
    if (!function_exists('current_user')) {
        return;
    }
    $me = current_user();
    if (!$me || ($me['role'] ?? '') !== 'admin') {
        return;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $metaStr = null;
    if ($meta !== null) {
        $metaStr = json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    try {
        $stmt = $GLOBALS['pdo']->prepare('INSERT INTO admin_logs (admin_id, action, target_type, target_id, meta, ip) VALUES (?,?,?,?,?,?)');
        $stmt->execute([(int) $me['id'], $action, $targetType, $targetId, $metaStr, $ip]);
    } catch (Throwable $e) {
        // ignore logging failures
    }
}

