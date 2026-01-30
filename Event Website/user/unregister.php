<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('user/events.php');
}

$eventId = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
if ($eventId < 1) {
    flash('error', 'Invalid event.');
    redirect('user/events.php');
}

$me = current_user();
$stmt = $pdo->prepare('SELECT id FROM registrations WHERE event_id = ? AND user_id = ?');
$stmt->execute([$eventId, $me['id']]);
$reg = $stmt->fetch();
if (!$reg) {
    flash('error', 'You are not registered for this event.');
    redirect('user/event_detail.php?id=' . $eventId);
}

$del = $pdo->prepare('DELETE FROM registrations WHERE id = ?');
$del->execute([$reg['id']]);
flash('success', 'You have been unregistered from this event.');
redirect('user/event_detail.php?id=' . $eventId);
