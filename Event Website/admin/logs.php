<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$action = isset($_GET['action']) ? trim((string) $_GET['action']) : '';
$adminId = isset($_GET['admin_id']) ? (int) $_GET['admin_id'] : 0;

$sql = "SELECT l.id, l.admin_id, l.action, l.target_type, l.target_id, l.meta, l.ip, l.created_at, u.name AS admin_name, u.email AS admin_email
        FROM admin_logs l
        JOIN users u ON u.id = l.admin_id
        WHERE 1=1";
$params = [];

if ($action !== '') {
    $sql .= " AND l.action = ?";
    $params[] = $action;
}
if ($adminId > 0) {
    $sql .= " AND l.admin_id = ?";
    $params[] = $adminId;
}
$sql .= " ORDER BY l.created_at DESC LIMIT 300";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$actions = $pdo->query("SELECT action, COUNT(*) c FROM admin_logs GROUP BY action ORDER BY action")->fetchAll(PDO::FETCH_ASSOC);
$admins = $pdo->query("SELECT id, name, email FROM users WHERE role='admin' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Logs - Events Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/style.css')) ?>">
</head>
<body class="d-flex flex-column min-vh-100">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="flex-grow-1 py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 fw-bold mb-1">Admin Logs</h1>
                <p class="text-muted small mb-0">Latest admin actions (showing up to 300).</p>
            </div>
            <a href="<?= htmlspecialchars(base_url('admin/dashboard.php')) ?>" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <form method="get" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small">Action</label>
                        <select class="form-select form-select-sm" name="action">
                            <option value="">All</option>
                            <?php foreach ($actions as $a): ?>
                                <option value="<?= htmlspecialchars($a['action']) ?>" <?= $action === $a['action'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($a['action']) ?> (<?= (int) $a['c'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Admin</label>
                        <select class="form-select form-select-sm" name="admin_id">
                            <option value="0">All</option>
                            <?php foreach ($admins as $ad): ?>
                                <option value="<?= (int) $ad['id'] ?>" <?= $adminId === (int) $ad['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ad['name']) ?> (<?= htmlspecialchars($ad['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary btn-sm" type="submit">Filter</button>
                        <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(base_url('admin/logs.php')) ?>">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr>
                        <th>When</th>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>IP</th>
                        <th>Meta</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td class="text-muted small"><?= date('M j, Y g:i A', strtotime($l['created_at'])) ?></td>
                            <td class="small">
                                <div class="fw-semibold"><?= htmlspecialchars($l['admin_name']) ?></div>
                                <div class="text-muted"><?= htmlspecialchars($l['admin_email']) ?></div>
                            </td>
                            <td><span class="badge bg-dark"><?= htmlspecialchars($l['action']) ?></span></td>
                            <td class="small text-muted">
                                <?= htmlspecialchars(($l['target_type'] ?: '—')) ?>
                                <?= $l['target_id'] ? ('#' . (int) $l['target_id']) : '' ?>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($l['ip'] ?: '—') ?></td>
                            <td class="small text-muted" style="max-width: 420px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= htmlspecialchars($l['meta'] ?: '—') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="6" class="text-muted text-center py-4">No logs found.</td></tr>
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

