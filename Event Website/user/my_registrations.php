<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (is_admin()) {
    flash('error', 'Admins do not register for events.');
    redirect('admin/dashboard.php');
}

$me = current_user();
$stmt = $pdo->prepare("
    SELECT r.id AS reg_id, r.created_at AS registered_at, e.id AS event_id, e.title, e.event_date, e.location, e.category,
           (SELECT COUNT(*) FROM participants p WHERE p.registration_id = r.id) AS participant_count
    FROM registrations r
    JOIN events e ON e.id = r.event_id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$me['id']]);
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flashError = flash_get('error');
$flashSuccess = flash_get('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registrations - Events Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/style.css')) ?>">
</head>
<body class="d-flex flex-column min-vh-100">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="flex-grow-1 py-4">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
            <div>
                <h1 class="h4 fw-bold mb-1">My Registrations</h1>
                <p class="text-muted small mb-0">Events you have registered for and your participants.</p>
            </div>
            <a href="<?= htmlspecialchars(base_url('user/events.php')) ?>" class="btn btn-outline-primary btn-sm">Browse More Events</a>
        </div>

        <?php if ($flashError): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0 fw-semibold">Registered Events</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Category</th>
                        <th>Participants</th>
                        <th>Registered At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($registrations as $r): ?>
                    <tr>
                        <td><a href="<?= htmlspecialchars(base_url('user/event_detail.php?id=' . $r['event_id'])) ?>" class="text-decoration-none"><?= htmlspecialchars($r['title']) ?></a></td>
                        <td><?= date('M j, Y', strtotime($r['event_date'])) ?></td>
                        <td><?= htmlspecialchars($r['location']) ?></td>
                        <td><?= htmlspecialchars($r['category']) ?></td>
                        <td><?= (int) $r['participant_count'] ?></td>
                        <td><?= date('M j, Y g:i A', strtotime($r['registered_at'])) ?></td>
                        <td class="text-end">
                            <a href="<?= htmlspecialchars(base_url('user/event_detail.php?id=' . $r['event_id'])) ?>" class="btn btn-sm btn-outline-secondary me-1">View</a>
                            <form method="post" action="<?= htmlspecialchars(base_url('user/unregister.php')) ?>" class="d-inline" onsubmit="return confirm('Unregister from this event?');">
                                <input type="hidden" name="event_id" value="<?= (int) $r['event_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Unregister</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($registrations)): ?>
                    <tr>
                        <td colspan="7" class="text-muted text-center py-5">No registrations yet. <a href="<?= htmlspecialchars(base_url('user/events.php')) ?>">Browse events</a> to register.</td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars(base_url('assets/js/main.js')) ?>"></script>
</body>
</html>
