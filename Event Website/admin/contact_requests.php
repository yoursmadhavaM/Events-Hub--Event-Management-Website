<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$error = '';
$success = '';

// Ensure contact_requests table exists (in case migration was not run)
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contact_requests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('new', 'read', 'replied', 'archived') NOT NULL DEFAULT 'new',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB;
    ");
} catch (PDOException $e) {
    $error = 'Contact requests storage is not initialized. Please check database permissions.';
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
    $action = $_POST['action'];
    
    if ($requestId > 0) {
        try {
            if ($action === 'mark_read') {
                $stmt = $pdo->prepare('UPDATE contact_requests SET status = ? WHERE id = ?');
                $stmt->execute(['read', $requestId]);
                $success = 'Contact request marked as read.';
            } elseif ($action === 'mark_replied') {
                $stmt = $pdo->prepare('UPDATE contact_requests SET status = ? WHERE id = ?');
                $stmt->execute(['replied', $requestId]);
                $success = 'Contact request marked as replied.';
            } elseif ($action === 'archive') {
                $stmt = $pdo->prepare('UPDATE contact_requests SET status = ? WHERE id = ?');
                $stmt->execute(['archived', $requestId]);
                $success = 'Contact request archived.';
            } elseif ($action === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM contact_requests WHERE id = ?');
                $stmt->execute([$requestId]);
                $success = 'Contact request deleted.';
            }
        } catch (PDOException $e) {
            $error = 'Failed to update contact request.';
        }
    }
}

$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$whereClause = '';
$params = [];

if ($statusFilter !== 'all' && in_array($statusFilter, ['new', 'read', 'replied', 'archived'], true)) {
    $whereClause = 'WHERE status = ?';
    $params[] = $statusFilter;
}

$sql = "SELECT id, name, email, message, status, created_at FROM contact_requests $whereClause ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get counts for badges
$counts = [
    'all' => (int) $pdo->query('SELECT COUNT(*) FROM contact_requests')->fetchColumn(),
    'new' => (int) $pdo->query("SELECT COUNT(*) FROM contact_requests WHERE status = 'new'")->fetchColumn(),
    'read' => (int) $pdo->query("SELECT COUNT(*) FROM contact_requests WHERE status = 'read'")->fetchColumn(),
    'replied' => (int) $pdo->query("SELECT COUNT(*) FROM contact_requests WHERE status = 'replied'")->fetchColumn(),
    'archived' => (int) $pdo->query("SELECT COUNT(*) FROM contact_requests WHERE status = 'archived'")->fetchColumn(),
];

$flashError = flash_get('error');
$flashSuccess = flash_get('success');
$error = $error ?: $flashError;
$success = $success ?: $flashSuccess;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Requests - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/style.css')) ?>">
</head>
<body class="d-flex flex-column min-vh-100">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="flex-grow-1">
    <section class="dashboard-header py-4 mb-4">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h1 class="h3 fw-bold mb-1">Contact Requests</h1>
                    <p class="mb-0 small opacity-75">Manage and respond to contact form submissions.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= htmlspecialchars(base_url('admin/dashboard.php')) ?>" class="btn btn-outline-light btn-sm">← Back to Dashboard</a>
                </div>
            </div>
        </div>
    </section>

    <section class="pb-4">
        <div class="container">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- Status Filter Tabs -->
            <ul class="nav nav-pills mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $statusFilter === 'all' ? 'active' : '' ?>" href="?status=all">
                        All <span class="badge bg-secondary"><?= $counts['all'] ?></span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $statusFilter === 'new' ? 'active' : '' ?>" href="?status=new">
                        New <span class="badge bg-danger"><?= $counts['new'] ?></span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $statusFilter === 'read' ? 'active' : '' ?>" href="?status=read">
                        Read <span class="badge bg-info"><?= $counts['read'] ?></span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $statusFilter === 'replied' ? 'active' : '' ?>" href="?status=replied">
                        Replied <span class="badge bg-success"><?= $counts['replied'] ?></span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $statusFilter === 'archived' ? 'active' : '' ?>" href="?status=archived">
                        Archived <span class="badge bg-secondary"><?= $counts['archived'] ?></span>
                    </a>
                </li>
            </ul>

            <!-- Contact Requests List -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 fw-semibold">Contact Requests</h2>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($requests)): ?>
                        <div class="text-center py-5 text-muted">
                            <p class="mb-0">No contact requests found.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requests as $req): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($req['name']) ?></strong></td>
                                            <td>
                                                <a href="mailto:<?= htmlspecialchars($req['email']) ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($req['email']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <div style="max-width: 300px;">
                                                    <p class="mb-0 small text-muted" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                        <?= htmlspecialchars($req['message']) ?>
                                                    </p>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $req['status'];
                                                $statusClasses = [
                                                    'new' => 'bg-danger',
                                                    'read' => 'bg-info',
                                                    'replied' => 'bg-success',
                                                    'archived' => 'bg-secondary'
                                                ];
                                                $statusLabels = [
                                                    'new' => 'New',
                                                    'read' => 'Read',
                                                    'replied' => 'Replied',
                                                    'archived' => 'Archived'
                                                ];
                                                $badgeClass = $statusClasses[$status] ?? 'bg-secondary';
                                                $badgeLabel = $statusLabels[$status] ?? ucfirst($status);
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                                            </td>
                                            <td class="small text-muted">
                                                <?= date('M j, Y g:i A', strtotime($req['created_at'])) ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <?php if ($status === 'new'): ?>
                                                        <form method="post" class="d-inline">
                                                            <input type="hidden" name="request_id" value="<?= (int) $req['id'] ?>">
                                                            <input type="hidden" name="action" value="mark_read">
                                                            <button type="submit" class="btn btn-sm btn-outline-info">Mark Read</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <?php if ($status !== 'replied'): ?>
                                                        <form method="post" class="d-inline">
                                                            <input type="hidden" name="request_id" value="<?= (int) $req['id'] ?>">
                                                            <input type="hidden" name="action" value="mark_replied">
                                                            <button type="submit" class="btn btn-sm btn-outline-success">Mark Replied</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <?php if ($status !== 'archived'): ?>
                                                        <form method="post" class="d-inline">
                                                            <input type="hidden" name="request_id" value="<?= (int) $req['id'] ?>">
                                                            <input type="hidden" name="action" value="archive">
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Archive</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?= $req['id'] ?>">View</button>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this contact request?');">
                                                        <input type="hidden" name="request_id" value="<?= (int) $req['id'] ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- View Modal -->
                                        <div class="modal fade" id="viewModal<?= $req['id'] ?>" tabindex="-1" aria-labelledby="viewModalLabel<?= $req['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="viewModalLabel<?= $req['id'] ?>">Contact Request Details</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <strong>Name:</strong>
                                                            <p class="mb-0"><?= htmlspecialchars($req['name']) ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Email:</strong>
                                                            <p class="mb-0">
                                                                <a href="mailto:<?= htmlspecialchars($req['email']) ?>"><?= htmlspecialchars($req['email']) ?></a>
                                                            </p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Status:</strong>
                                                            <p class="mb-0">
                                                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                                                            </p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Date:</strong>
                                                            <p class="mb-0"><?= date('F j, Y \a\t g:i A', strtotime($req['created_at'])) ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Message:</strong>
                                                            <p class="mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($req['message']) ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="mailto:<?= htmlspecialchars($req['email']) ?>?subject=Re: Your Contact Request" class="btn btn-primary">Reply via Email</a>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars(base_url('assets/js/main.js')) ?>"></script>
</body>
</html>
