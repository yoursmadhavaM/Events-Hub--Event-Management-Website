<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$category = isset($_GET['category']) ? trim((string) $_GET['category']) : '';

$sql = "
    SELECT e.id, e.title, e.category, e.description, e.event_date, e.location, e.max_participants, e.image_path,
           (SELECT COUNT(*) FROM participants p
            JOIN registrations r ON r.id = p.registration_id
            WHERE r.event_id = e.id) AS registered
    FROM events e
    WHERE 1=1
";
$params = [];
if ($search !== '') {
    $sql .= " AND (e.title LIKE ? OR e.description LIKE ?)";
    $term = '%' . $search . '%';
    $params[] = $term;
    $params[] = $term;
}
if ($category !== '') {
    $sql .= " AND e.category LIKE ?";
    $params[] = '%' . $category . '%';
}
$sql .= " ORDER BY e.event_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - Events Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/style.css')) ?>">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 page-bg-white">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="flex-grow-1">
    <section class="sd-hero sd-hero-page">
        <div class="sd-hero-bg" aria-hidden="true">
            <div class="sd-orb sd-orb-1"></div>
            <div class="sd-orb sd-orb-2"></div>
            <div class="sd-orb sd-orb-3"></div>
            <div class="sd-grid"></div>
        </div>
        <div class="container position-relative py-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <p class="sd-page-kicker text-uppercase small mb-1 animate-bounce-in-left anim-delay-1" data-reveal>Schedule</p>
                    <h1 class="sd-title h4 fw-bold mb-1 animate-bounce-in-up anim-delay-2" data-reveal>Events</h1>
                    <p class="sd-subtitle text-white-50 small mb-0 animate-fade-in-up anim-delay-3" data-reveal>Browse fixtures and tournaments, then register single players or full teams.</p>
                </div>
                <form class="d-flex flex-wrap gap-2 align-items-center animate-bounce-in-right anim-delay-3" method="get" action="<?= htmlspecialchars(base_url('user/events.php')) ?>" data-reveal>
                    <input type="search" name="q" class="form-control form-control-sm" placeholder="Search by title" value="<?= htmlspecialchars($search) ?>">
                    <input type="text" name="category" class="form-control form-control-sm" placeholder="Filter by category" value="<?= htmlspecialchars($category) ?>" style="max-width: 160px;">
                    <button type="submit" class="btn btn-outline-light btn-sm">Search</button>
                </form>
            </div>
        </div>
    </section>

    <section class="py-4 eh-events-section">
    <div class="container">
        <div class="row g-3">
            <?php 
            $loop_index = 0;
            foreach ($events as $ev): 
                $loop_index++;
                $reg = (int) $ev['registered'];
                $max = (int) $ev['max_participants'];
                $full = $reg >= $max;
                $badge = 'eh-card-badge';
            ?>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $loop_index * 100 ?>" data-bounce="up">
                <div class="card h-100 eh-event-card tilt-card hover-bounce" data-tilt>
                    <?php if (!empty($ev['image_path'])): ?>
                        <img src="<?= htmlspecialchars(base_url($ev['image_path'])) ?>" class="card-img-top sport-img" alt="<?= htmlspecialchars($ev['title']) ?>" style="object-fit: cover; height: 180px;">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <span class="badge <?= $badge ?> mb-2"><?= htmlspecialchars($ev['category']) ?></span>
                        <h5 class="card-title fw-semibold"><?= htmlspecialchars($ev['title']) ?></h5>
                        <p class="card-text text-muted small mb-2"><?= htmlspecialchars(strlen($ev['description']) > 120 ? substr($ev['description'], 0, 120) . '…' : $ev['description']) ?></p>
                        <p class="small text-muted mb-1"><strong>Date:</strong> <?= date('M j, Y', strtotime($ev['event_date'])) ?> · <?= htmlspecialchars($ev['location']) ?></p>
                        <p class="small text-muted mb-3"><strong>Capacity:</strong> <?= $reg ?> / <?= $max ?> registered</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <a href="<?= htmlspecialchars(base_url('user/event_detail.php?id=' . $ev['id'])) ?>" class="btn btn-outline-secondary btn-sm">View Details</a>
                            <?php if ($full): ?>
                                <span class="btn btn-outline-secondary btn-sm disabled">Full</span>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars(base_url('user/event_detail.php?id=' . $ev['id'] . '#register')) ?>" class="btn btn-primary btn-sm">Register</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($events)): ?>
            <p class="eh-events-empty text-center py-5 mb-0">No events found. <?php if ($search || $category): ?>Try different search or category.<?php endif; ?></p>
        <?php endif; ?>
    </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bounce.js/0.8.1/bounce.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= htmlspecialchars(base_url('assets/js/main.js')) ?>"></script>
<script>
    AOS.init({
        duration: 600,
        easing: 'ease-in-out',
        once: true
    });
</script>
</body>
</html>
