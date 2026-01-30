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

$stmt = $pdo->prepare('SELECT id, title, max_participants, max_team_size FROM events WHERE id = ?');
$stmt->execute([$eventId]);
$event = $stmt->fetch();
if (!$event) {
    flash('error', 'Event not found.');
    redirect('user/events.php');
}

$me = current_user();
$stmt = $pdo->prepare('SELECT id FROM registrations WHERE event_id = ? AND user_id = ?');
$stmt->execute([$eventId, $me['id']]);
if ($stmt->fetch()) {
    flash('error', 'You have already registered for this event.');
    redirect('user/event_detail.php?id=' . $eventId);
}

$participants = [];
if (isset($_POST['participants']) && is_array($_POST['participants'])) {
    foreach ($_POST['participants'] as $p) {
        $name = trim((string) ($p['name'] ?? ''));
        $email = trim((string) ($p['email'] ?? ''));
        $phone = trim((string) ($p['phone'] ?? ''));
        if ($name !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $participants[] = ['name' => $name, 'email' => $email, 'phone' => $phone];
        }
    }
}

if (empty($participants)) {
    flash('error', 'Add at least one participant with name and email.');
    redirect('user/event_detail.php?id=' . $eventId);
}

$maxTeam = max(1, (int) ($event['max_team_size'] ?? 1));
if (count($participants) > $maxTeam) {
    flash('error', 'Team size limit exceeded. Max allowed per registration is ' . $maxTeam . '.');
    redirect('user/event_detail.php?id=' . $eventId);
}

$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM participants p
    JOIN registrations r ON r.id = p.registration_id
    WHERE r.event_id = ?
");
$countStmt->execute([$eventId]);
$current = (int) $countStmt->fetchColumn();
$max = (int) $event['max_participants'];
if ($current + count($participants) > $max) {
    flash('error', 'Not enough seats. Only ' . ($max - $current) . ' left.');
    redirect('user/event_detail.php?id=' . $eventId);
}

try {
    $pdo->beginTransaction();
    $insReg = $pdo->prepare('INSERT INTO registrations (event_id, user_id) VALUES (?,?)');
    $insReg->execute([$eventId, $me['id']]);
    $regId = (int) $pdo->lastInsertId();
    $insPart = $pdo->prepare('INSERT INTO participants (registration_id, name, email, phone) VALUES (?,?,?,?)');
    foreach ($participants as $p) {
        $insPart->execute([$regId, $p['name'], $p['email'], $p['phone'] ?: null]);
    }
    $pdo->commit();
    flash('success', 'Registration successful. You registered ' . count($participants) . ' participant(s).');
} catch (Exception $e) {
    $pdo->rollBack();
    flash('error', 'Registration failed. Please try again.');
}
redirect('user/event_detail.php?id=' . $eventId);
