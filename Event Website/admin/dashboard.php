<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$statsUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$statsEvents = (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
$statsParticipants = (int) $pdo->query('SELECT COUNT(*) FROM participants')->fetchColumn();

$events = $pdo->query("
    SELECT e.id, e.title, e.category, e.event_date, e.location, e.max_participants,
           (SELECT COUNT(*) FROM participants p
            JOIN registrations r ON r.id = p.registration_id
            WHERE r.event_id = e.id) AS registered
    FROM events e
    ORDER BY e.event_date ASC
")->fetchAll(PDO::FETCH_ASSOC);

$users = $pdo->query('SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

$categories = [];
$catRows = $pdo->query('SELECT category, COUNT(*) AS c FROM events GROUP BY category')->fetchAll(PDO::FETCH_ASSOC);
$totalCat = array_sum(array_column($catRows, 'c'));
foreach ($catRows as $r) {
    $pct = $totalCat ? (int) round($r['c'] / $totalCat * 100) : 0;
    $categories[] = ['name' => $r['category'], 'count' => (int) $r['c'], 'pct' => $pct];
}

$flashError = flash_get('error');
$flashSuccess = flash_get('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Events Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/style.css')) ?>?v=2">
    <style>
        /* Force admin dashboard theme (overrides cache/global styles) */
        body.admin-dashboard-page { background: linear-gradient(180deg, #0f172a 0%, #1e293b 25%, #0f172a 100%) !important; }
        body.admin-dashboard-page main { background: transparent !important; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 admin-dashboard-page">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="flex-grow-1 admin-dashboard-main">
    <section class="dashboard-header py-5 mb-4">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h1 class="h3 fw-bold mb-2 text-white">Admin Dashboard</h1>
                    <p class="mb-0 small text-white-50">Manage events, users, and platform activity.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge rounded-pill">Role: Admin</span>
                    <span class="badge rounded-pill">Status: Active</span>
                </div>
            </div>
        </div>
    </section>

    <section class="pb-4">
        <div class="container">
            <?php if ($flashError): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
            <?php endif; ?>
            <?php if ($flashSuccess): ?>
                <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <h2 class="h6 fw-semibold mb-0 eh-admin-section-title">Overview</h2>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= htmlspecialchars(base_url('admin/logs.php')) ?>" class="btn btn-outline-secondary btn-sm">View Logs</a>
                    <a href="<?= htmlspecialchars(base_url('admin/create_event.php')) ?>" class="btn btn-primary btn-sm">+ Create Event</a>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-4 col-sm-6">
                    <div class="card stat-card shadow-sm border-0 anim-in anim-delay-1">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small mb-1" style="letter-spacing: 0.08em;">Total Users</p>
                            <h3 class="fw-bold mb-0"><?= $statsUsers ?></h3>
                            <small class="text-muted">Manage below</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card stat-card shadow-sm border-0 anim-in anim-delay-2">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small mb-1" style="letter-spacing: 0.08em;">Events</p>
                            <h3 class="fw-bold mb-0"><?= $statsEvents ?></h3>
                            <small class="text-muted">Manage and add dynamically</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card stat-card shadow-sm border-0 anim-in anim-delay-3">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small mb-1" style="letter-spacing: 0.08em;">Participants</p>
                            <h3 class="fw-bold mb-0"><?= $statsParticipants ?></h3>
                            <small class="text-muted">Across all events</small>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-pills mb-4" id="adminTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="events-tab" data-bs-toggle="pill" data-bs-target="#events" type="button" role="tab">Events</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="users-tab" data-bs-toggle="pill" data-bs-target="#users" type="button" role="tab">User Management</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="analytics-tab" data-bs-toggle="pill" data-bs-target="#analytics" type="button" role="tab">Analytics</button>
                </li>
            </ul>

            <div class="tab-content" id="adminTabsContent">
                <div class="tab-pane fade show active" id="events" role="tabpanel">
                    <div class="card shadow-sm border-0 mb-4 admin-all-events-card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h2 class="h6 mb-0 fw-semibold eh-admin-section-title">All Events</h2>
                            <a href="<?= htmlspecialchars(base_url('admin/create_event.php')) ?>" class="btn btn-primary btn-sm">+ Create Event</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Max</th>
                                    <th>Registered</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($events as $e): ?>
                                <tr>
                                    <td><?= htmlspecialchars($e['title']) ?></td>
                                    <td><?= htmlspecialchars($e['category']) ?></td>
                                    <td><?= date('M j, Y', strtotime($e['event_date'])) ?></td>
                                    <td><?= htmlspecialchars($e['location']) ?></td>
                                    <td><?= (int) $e['max_participants'] ?></td>
                                    <td><?= (int) $e['registered'] ?></td>
                                    <td class="text-end">
                                        <a href="<?= htmlspecialchars(base_url('user/event_detail.php?id=' . $e['id'])) ?>" class="btn btn-sm btn-outline-secondary me-1">View</a>
                                        <a href="<?= htmlspecialchars(base_url('admin/edit_event.php?id=' . $e['id'])) ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                        <form method="post" action="<?= htmlspecialchars(base_url('admin/delete_event.php')) ?>" class="d-inline" onsubmit="return confirm('Delete this event and all its registrations?');">
                                            <input type="hidden" name="id" value="<?= (int) $e['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($events)): ?>
                                <tr><td colspan="7" class="text-muted text-center py-4">No events yet. <a href="<?= htmlspecialchars(base_url('admin/create_event.php')) ?>">Create one</a>.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="users" role="tabpanel">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white">
                            <h2 class="h6 mb-0 fw-semibold eh-admin-section-title">User Management</h2>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $me = current_user();
                                foreach ($users as $u):
                                    $isMe = $me && (int) $me['id'] === (int) $u['id'];
                                    $isAdmin = $u['role'] === 'admin';
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['name']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><span class="badge <?= $isAdmin ? 'bg-dark' : 'bg-info text-dark' ?>"><?= $isAdmin ? 'Admin' : 'User' ?></span></td>
                                    <?php
                                        $status = (string) $u['status'];
                                        $statusClass = $status === 'active' ? 'approved' : ($status === 'pending' ? 'pending' : 'rejected');
                                        $statusLabel = $status === 'active' ? 'Active' : ($status === 'pending' ? 'Pending' : 'Suspended');
                                    ?>
                                    <td><span class="status-badge status-<?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                                    <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                                    <td class="text-end">
                                        <?php if ($isMe): ?>
                                            <span class="btn btn-sm btn-outline-secondary disabled">You</span>
                                        <?php elseif ($isAdmin): ?>
                                            <span class="btn btn-sm btn-outline-secondary disabled">—</span>
                                        <?php else: ?>
                                            <?php if ($u['status'] === 'pending'): ?>
                                                <form method="post" action="<?= htmlspecialchars(base_url('admin/user_action.php')) ?>" class="d-inline">
                                                    <input type="hidden" name="action" value="approve">
                                                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-success me-1">Approve</button>
                                                </form>
                                                <form method="post" action="<?= htmlspecialchars(base_url('admin/user_action.php')) ?>" class="d-inline" onsubmit="return confirm('Reject this account? It will be deleted and stored in records.');">
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger me-1">Reject</button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="post" action="<?= htmlspecialchars(base_url('admin/user_action.php')) ?>" class="d-inline">
                                                <input type="hidden" name="action" value="suspend">
                                                <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-warning me-1"><?= $u['status'] === 'suspended' ? 'Reactivate' : 'Suspend' ?></button>
                                            </form>
                                            <form method="post" action="<?= htmlspecialchars(base_url('admin/user_action.php')) ?>" class="d-inline" onsubmit="return confirm('Delete this user and their registrations?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($users)): ?>
                                <tr><td colspan="6" class="text-muted text-center py-4">No users.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="analytics" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h2 class="h6 mb-0 fw-semibold eh-admin-section-title">Analytics</h2>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 h-100">
                                        <h6 class="fw-semibold mb-1">Events</h6>
                                        <ul class="list-unstyled small mb-0 text-muted">
                                            <li>Total: <?= $statsEvents ?></li>
                                            <li>Participants: <?= $statsParticipants ?></li>
                                            <li>Avg per event: <?= $statsEvents ? round($statsParticipants / $statsEvents, 1) : 0 ?></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 h-100">
                                        <h6 class="fw-semibold mb-1">Top Categories</h6>
                                        <ul class="list-unstyled small mb-0 text-muted">
                                            <?php foreach ($categories as $c): ?>
                                                <li><?= htmlspecialchars($c['name']) ?> (<?= $c['pct'] ?>%)</li>
                                            <?php endforeach; ?>
                                            <?php if (empty($categories)): ?>
                                                <li class="text-muted">—</li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 h-100">
                                        <h6 class="fw-semibold mb-1">Users</h6>
                                        <ul class="list-unstyled small mb-0 text-muted">
                                            <li>Total: <?= $statsUsers ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars(base_url('assets/js/main.js')) ?>"></script>
</body>
</html>
