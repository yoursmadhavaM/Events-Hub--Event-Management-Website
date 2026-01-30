<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Events Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/style.css')) ?>">
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars(base_url('favicon.ico')) ?>">
</head>
<body class="d-flex flex-column min-vh-100 page-bg-white">
<?php include __DIR__ . '/includes/navbar.php'; ?>

<main class="flex-grow-1">
    <section class="sd-hero sd-hero-page">
        <div class="sd-hero-bg" aria-hidden="true">
            <div class="sd-orb sd-orb-1"></div>
            <div class="sd-orb sd-orb-2"></div>
            <div class="sd-orb sd-orb-3"></div>
            <div class="sd-grid"></div>
        </div>
        <div class="container position-relative py-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="sd-page-kicker text-uppercase small mb-1" data-reveal>Events Hub</p>
                    <h1 class="sd-title h3 fw-bold mb-2" data-reveal>About Events Hub</h1>
                    <p class="sd-subtitle text-white-50 mb-0" data-reveal>
                        Events Hub gives organizers a clean way to publish and manage events while participants discover and register in one place.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 eh-page-content">
        <div class="container">
        <div class="row g-4 mt-2">
            <div class="col-md-4" data-reveal>
                <div class="card shadow-sm border-0 h-100 tilt-card" data-tilt>
                    <div class="card-body">
                        <h2 class="h6 fw-semibold">Admin-controlled approvals</h2>
                        <p class="text-muted small mb-0">New participant accounts are reviewed and approved by an admin.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-reveal>
                <div class="card shadow-sm border-0 h-100 tilt-card" data-tilt>
                    <div class="card-body">
                        <h2 class="h6 fw-semibold">Capacity limits</h2>
                        <p class="text-muted small mb-0">Admins set the maximum participants per event; registrations cannot exceed remaining seats.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-reveal>
                <div class="card shadow-sm border-0 h-100 tilt-card" data-tilt>
                    <div class="card-body">
                        <h2 class="h6 fw-semibold">Clean event presentation</h2>
                        <p class="text-muted small mb-0">Each event can have an image, shown in listings and event details.</p>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="<?= htmlspecialchars(base_url('assets/js/main.js')) ?>"></script>
</body>
</html>

