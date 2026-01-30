<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_log.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/dashboard.php');
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id < 1) {
    flash('error', 'Invalid event.');
    redirect('admin/dashboard.php');
}

// If event has an uploaded image, delete it from disk as well.
$imgStmt = $pdo->prepare('SELECT image_path FROM events WHERE id = ?');
$imgStmt->execute([$id]);
$img = $imgStmt->fetchColumn();

$stmt = $pdo->prepare('DELETE FROM events WHERE id = ?');
$stmt->execute([$id]);
if ($stmt->rowCount()) {
    if ($img) {
        $img = (string) $img;
        $imgFs = realpath(__DIR__ . '/../' . ltrim($img, '/'));
        $allowedRoot = realpath(__DIR__ . '/../uploads/events');
        if ($imgFs && $allowedRoot && str_starts_with(strtolower(str_replace('\\', '/', $imgFs)), strtolower(str_replace('\\', '/', $allowedRoot)))) {
            @unlink($imgFs);
        }
    }
    flash('success', 'Event deleted.');
    admin_log('event_delete', 'event', (int) $id);
} else {
    flash('error', 'Event not found or already deleted.');
}
redirect('admin/dashboard.php');
