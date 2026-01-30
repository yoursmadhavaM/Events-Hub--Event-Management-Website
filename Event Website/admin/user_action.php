<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_log.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/dashboard.php');
}

$action = trim((string) ($_POST['action'] ?? ''));
$userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
$me = current_user();
if ($userId < 1 || $me['id'] === $userId) {
    flash('error', 'Invalid user or you cannot modify yourself.');
    redirect('admin/dashboard.php');
}

$stmt = $pdo->prepare('SELECT id, role, status FROM users WHERE id = ?');
$stmt->execute([$userId]);
$target = $stmt->fetch();
if (!$target) {
    flash('error', 'User not found.');
    redirect('admin/dashboard.php');
}

if ($target['role'] === 'admin') {
    flash('error', 'You cannot modify another admin.');
    redirect('admin/dashboard.php');
}

if ($action === 'suspend') {
    $up = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
    $up->execute([$target['status'] === 'suspended' ? 'active' : 'suspended', $userId]);
    flash('success', $target['status'] === 'suspended' ? 'User reactivated.' : 'User suspended.');
    admin_log($target['status'] === 'suspended' ? 'user_reactivate' : 'user_suspend', 'user', (int) $userId);
} elseif ($action === 'approve') {
    if ($target['status'] !== 'pending') {
        flash('error', 'Only pending users can be approved.');
        redirect('admin/dashboard.php');
    }
    $up = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
    $up->execute(['active', $userId]);
    flash('success', 'User approved.');
    admin_log('user_approve', 'user', (int) $userId);
} elseif ($action === 'reject') {
    if ($target['status'] !== 'pending') {
        flash('error', 'Only pending users can be rejected.');
        redirect('admin/dashboard.php');
    }
    // Store deleted/rejected info
    $uStmt = $pdo->prepare('SELECT id, name, email, role, status FROM users WHERE id = ?');
    $uStmt->execute([$userId]);
    $u = $uStmt->fetch();
    if (!$u) {
        flash('error', 'User not found.');
        redirect('admin/dashboard.php');
    }
    $ins = $pdo->prepare('INSERT INTO deleted_users (original_user_id, name, email, role, status_at_deletion, deleted_by_admin_id, reason) VALUES (?,?,?,?,?,?,?)');
    $ins->execute([(int) $u['id'], $u['name'], $u['email'], $u['role'], $u['status'], (int) $me['id'], 'not approved']);
    $del = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $del->execute([$userId]);
    flash('success', 'User rejected and deleted.');
    admin_log('user_reject', 'user', (int) $userId, ['email' => $u['email']]);
} elseif ($action === 'delete') {
    $uStmt = $pdo->prepare('SELECT id, name, email, role, status FROM users WHERE id = ?');
    $uStmt->execute([$userId]);
    $u = $uStmt->fetch();
    if ($u) {
        $ins = $pdo->prepare('INSERT INTO deleted_users (original_user_id, name, email, role, status_at_deletion, deleted_by_admin_id, reason) VALUES (?,?,?,?,?,?,?)');
        $ins->execute([(int) $u['id'], $u['name'], $u['email'], $u['role'], $u['status'], (int) $me['id'], 'deleted by admin']);
    }
    $del = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $del->execute([$userId]);
    flash('success', 'User deleted.');
    admin_log('user_delete', 'user', (int) $userId, isset($u) && $u ? ['email' => $u['email']] : null);
} else {
    flash('error', 'Invalid action.');
}
redirect('admin/dashboard.php');
