<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id < 1) {
    flash('error', 'Invalid event.');
    redirect('user/events.php');
}

$stmt = $pdo->prepare('SELECT * FROM events WHERE id = ?');
$stmt->execute([$id]);
$event = $stmt->fetch();
if (!$event) {
    flash('error', 'Event not found.');
    redirect('user/events.php');
}

$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM participants p
    JOIN registrations r ON r.id = p.registration_id
    WHERE r.event_id = ?
");
$countStmt->execute([$id]);
$registered = (int) $countStmt->fetchColumn();
$max = (int) $event['max_participants'];
$full = $registered >= $max;
$remaining = max(0, $max - $registered);
$maxTeam = max(1, (int) ($event['max_team_size'] ?? 1));
$teamLimit = min($remaining, $maxTeam);

$myRegistration = null;
$myParticipants = [];
$me = current_user();
if ($me) {
    $stmt = $pdo->prepare('SELECT id FROM registrations WHERE event_id = ? AND user_id = ?');
    $stmt->execute([$id, $me['id']]);
    $myRegistration = $stmt->fetch();
    if ($myRegistration) {
        $stmt = $pdo->prepare('SELECT name, email, phone FROM participants WHERE registration_id = ? ORDER BY id');
        $stmt->execute([$myRegistration['id']]);
        $myParticipants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$flashError = flash_get('error');
$flashSuccess = flash_get('success');
$eventDateFormatted = date('M j, Y · g:i A', strtotime($event['event_date']));
$badge = 'eh-card-badge';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['title']) ?> - Events Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/style.css')) ?>">
</head>
<body class="d-flex flex-column min-vh-100">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="flex-grow-1 py-4">
    <div class="container">
        <a href="<?= htmlspecialchars(base_url('user/events.php')) ?>" class="btn btn-outline-secondary btn-sm mb-3">&larr; Back to Events</a>

        <?php if ($flashError): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card eh-detail-card mb-3">
                    <?php if (!empty($event['image_path'])): ?>
                        <img src="<?= htmlspecialchars(base_url($event['image_path'])) ?>" class="card-img-top" alt="<?= htmlspecialchars($event['title']) ?>" style="object-fit: contain; max-height: 360px; background: rgba(15, 23, 42, 0.04);">
                    <?php endif; ?>
                    <div class="card-body">
                        <span class="badge <?= $badge ?> mb-2"><?= htmlspecialchars($event['category']) ?></span>
                        <h1 class="h4 fw-bold mb-1"><?= htmlspecialchars($event['title']) ?></h1>
                        <p class="small text-muted mb-3">Managed by <strong>Admin</strong></p>
                        <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                        <h2 class="h6 fw-semibold mb-2">Event Details</h2>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li><strong>Date &amp; Time:</strong> <?= $eventDateFormatted ?></li>
                            <li><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></li>
                            <li><strong>Category:</strong> <?= htmlspecialchars($event['category']) ?></li>
                            <li><strong>Maximum Participants:</strong> <?= $max ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4" id="register">
                <div class="card eh-detail-card mb-3">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-2">Registration Summary</h2>
                        <p class="small text-muted mb-2"><strong>Registered:</strong> <?= $registered ?> / <?= $max ?></p>
                        <?php if ($myRegistration): ?>
                            <p class="small text-success mb-2">You are registered for this event.</p>
                            <h3 class="h6 fw-semibold mb-2">Your participants</h3>
                            <ul class="list-unstyled small text-muted mb-3">
                                <?php foreach ($myParticipants as $p): ?>
                                    <li><?= htmlspecialchars($p['name']) ?> &lt;<?= htmlspecialchars($p['email']) ?>&gt;<?= $p['phone'] ? ' · ' . htmlspecialchars($p['phone']) : '' ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <form method="post" action="<?= htmlspecialchars(base_url('user/unregister.php')) ?>" class="mb-2" onsubmit="return confirm('Unregister from this event?');">
                                <input type="hidden" name="event_id" value="<?= $id ?>">
                                <button type="submit" class="btn btn-outline-danger w-100">Unregister</button>
                            </form>
                        <?php elseif ($full): ?>
                            <p class="small text-muted mb-0">This event is full.</p>
                        <?php elseif ($me): ?>
                            <p class="small text-muted mb-3">Seats available. Add one or more participants and fill their details.</p>
                            <button class="btn btn-primary w-100 mb-2" type="button" data-bs-toggle="modal" data-bs-target="#registerModal">Register for this Event</button>
                            <p class="small text-muted mt-2 mb-0">You can add up to <?= (int) $teamLimit ?> participant(s) per registration.</p>
                        <?php else: ?>
                            <p class="small text-muted mb-3">Seats available. Log in to register.</p>
                            <a href="<?= htmlspecialchars(base_url('auth/login.php?redirect=user/event_detail.php?id=' . $id)) ?>" class="btn btn-primary w-100 mb-2">Log in to Register</a>
                            <p class="small text-muted mt-2 mb-0">Add multiple participants; each fills their own name, email, and optional details.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php if ($me && !$myRegistration && !$full): ?>
<!-- Registration Modal: multiple participants -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="registerModalLabel">Register for <?= htmlspecialchars($event['title']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">Add one or more participants. Each person fills their own details below.</p>
                <form id="registrationForm" method="post" action="<?= htmlspecialchars(base_url('user/register_event.php')) ?>">
                    <input type="hidden" name="event_id" value="<?= $id ?>">
                    <div id="participantsContainer">
                        <div class="participant-block card border mb-3" data-participant-index="0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary">Participant 1</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-participant d-none" aria-label="Remove participant">Remove</button>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small required">Full name</label>
                                        <input type="text" class="form-control form-control-sm" name="participants[0][name]" placeholder="Full name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small required">Email</label>
                                        <input type="email" class="form-control form-control-sm" name="participants[0][email]" placeholder="email@example.com" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small">Phone <span class="text-muted">(optional)</span></label>
                                        <input type="tel" class="form-control form-control-sm" name="participants[0][phone]" placeholder="Phone number">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addParticipant">+ Add another participant</button>
                            <div class="small text-muted" id="seatHint">You can add: <?= (int) $teamLimit ?></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="registrationForm" class="btn btn-primary">Submit Registration</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars(base_url('assets/js/main.js')) ?>"></script>
<?php if ($me && !$myRegistration && !$full): ?>
<script>
(function () {
    const container = document.getElementById('participantsContainer');
    const addBtn = document.getElementById('addParticipant');
    const seatHint = document.getElementById('seatHint');
    if (!container || !addBtn) return;
    const maxAdd = <?= (int) $teamLimit ?>;
    let count = 1;

    function updateRemoveButtons() {
        const blocks = container.querySelectorAll('.participant-block');
        blocks.forEach(function (block) {
            const btn = block.querySelector('.remove-participant');
            if (btn) btn.classList.toggle('d-none', blocks.length <= 1);
        });
    }

    function syncSeatUI() {
        const blocks = container.querySelectorAll('.participant-block');
        const current = blocks.length;
        const left = Math.max(0, maxAdd - current);
        if (seatHint) seatHint.textContent = 'Remaining seats: ' + left;
        addBtn.disabled = current >= maxAdd;
        addBtn.classList.toggle('disabled', current >= maxAdd);
    }

    addBtn.addEventListener('click', function () {
        const blocks = container.querySelectorAll('.participant-block');
        if (blocks.length >= maxAdd) return;
        const block = document.createElement('div');
        block.className = 'participant-block card border mb-3';
        block.dataset.participantIndex = count;
        block.innerHTML = '<div class="card-body">' +
            '<div class="d-flex justify-content-between align-items-center mb-2">' +
            '<span class="badge bg-primary">Participant ' + (count + 1) + '</span>' +
            '<button type="button" class="btn btn-sm btn-outline-danger remove-participant" aria-label="Remove participant">Remove</button>' +
            '</div><div class="row g-2">' +
            '<div class="col-md-6"><label class="form-label small required">Full name</label>' +
            '<input type="text" class="form-control form-control-sm" name="participants[' + count + '][name]" placeholder="Full name" required></div>' +
            '<div class="col-md-6"><label class="form-label small required">Email</label>' +
            '<input type="email" class="form-control form-control-sm" name="participants[' + count + '][email]" placeholder="email@example.com" required></div>' +
            '<div class="col-12"><label class="form-label small">Phone <span class="text-muted">(optional)</span></label>' +
            '<input type="tel" class="form-control form-control-sm" name="participants[' + count + '][phone]" placeholder="Phone number"></div></div></div>';
        container.appendChild(block);
        count++;
        updateRemoveButtons();
        syncSeatUI();
    });

    container.addEventListener('click', function (e) {
        const rm = e.target.closest('.remove-participant');
        if (rm && !rm.classList.contains('d-none')) {
            rm.closest('.participant-block').remove();
            updateRemoveButtons();
            syncSeatUI();
        }
    });

    updateRemoveButtons();
    syncSeatUI();
    if (window.location.hash === '#register') {
        var modalEl = document.getElementById('registerModal');
        if (modalEl) {
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }
})();
</script>
<?php endif; ?>
</body>
</html>
