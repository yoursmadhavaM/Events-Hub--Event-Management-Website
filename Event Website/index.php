<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect logged-in users to their appropriate dashboard
$user = current_user();
if ($user) {
    if (is_admin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('user/events.php');
    }
}

// High-level stats for landing page counters
$statsUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$statsEvents = (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
$statsParticipants = (int) $pdo->query('SELECT COUNT(*) FROM participants')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Hub – Discover, register, and manage events in one place</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/style.css')) ?>">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars(base_url('favicon.ico')) ?>">
</head>
<body class="d-flex flex-column min-vh-100">
<?php include __DIR__ . '/includes/navbar.php'; ?>

<main class="flex-grow-1">
    <section class="sd-hero">
        <div class="sd-hero-bg" aria-hidden="true">
            <div class="sd-orb sd-orb-1"></div>
            <div class="sd-orb sd-orb-2"></div>
            <div class="sd-orb sd-orb-3"></div>
            <div class="sd-grid"></div>
        </div>

        <div class="container position-relative pt-4 pt-lg-5 pb-5 pb-lg-6">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div class="sd-kicker mb-2 animate-bounce-in-down anim-delay-1" data-reveal>
                        <span class="sd-pill">Events Hub</span>
                        <span class="sd-dot"></span>
                        <span class="text-white-50">Discover, register, and manage events in one place</span>
                    </div>
                    <h1 class="sd-title display-5 fw-bold mb-3 animate-bounce-in-up anim-delay-2" data-reveal>
                        Events Hub – Your gateway to events that matter
                    </h1>
                    <p class="sd-subtitle lead mb-4 animate-fade-in-up anim-delay-3" data-reveal>
                        Create and manage events, or browse and register—all in one clean, modern platform.
                    </p>
                    <div class="d-flex flex-wrap gap-2 animate-bounce-in-up anim-delay-4" data-reveal>
                        <a href="<?= htmlspecialchars(base_url('auth/register.php')) ?>" class="btn btn-primary btn-lg hover-bounce">Get Started</a>
                        <a href="<?= htmlspecialchars(base_url('auth/login.php')) ?>" class="btn btn-outline-light btn-lg hover-bounce">Login</a>
                        <a href="<?= htmlspecialchars(base_url('user/events.php')) ?>" class="btn btn-outline-light btn-lg hover-bounce">Browse Events</a>
                    </div>

                    <div class="row g-3 mt-4 sd-stat-row animate-bounce-in-up anim-delay-5" data-reveal>
                        <div class="col-sm-4" data-bounce="up">
                            <div class="sd-stat">
                                <div class="sd-stat-number" data-count-target="<?= $statsEvents ?>">0</div>
                                <div class="sd-stat-label">Active events</div>
                            </div>
                        </div>
                        <div class="col-sm-4" data-bounce="up">
                            <div class="sd-stat">
                                <div class="sd-stat-number" data-count-target="<?= $statsParticipants ?>">0</div>
                                <div class="sd-stat-label">Registered participants</div>
                            </div>
                        </div>
                        <div class="col-sm-4" data-bounce="up">
                            <div class="sd-stat">
                                <div class="sd-stat-number" data-count-target="<?= $statsUsers ?>">0</div>
                                <div class="sd-stat-label">Approved users</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 position-relative">
                    <div class="sd-3d-container sd-3d-standalone animate-zoom-in anim-delay-3" data-reveal>
                        <canvas id="sd-3d-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="sd-game-strip-section py-4" data-bounce="up">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h6 text-uppercase text-muted mb-0 animate-pulse-slow">Sports in motion</h2>
                <span class="small text-muted">Live glimpse of game energy</span>
            </div>
        </div>
        <div class="sd-game-strip sd-sports-motion" aria-hidden="true">
            <div class="sd-game-track">
                <?php
                $sportsImages = [
                    'https://img.freepik.com/premium-psd/man-running-transparent-background_1232542-25509.jpg',
                    'https://wallpapercave.com/wp/wp10106615.jpg',
                    'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEiOxUu3I6qz9vl-noHknBLo4XbBiFPlEe_Q2CFfzBKDwuG8rt5WZP09WEy2r_yIfQ_u09q2LpxqJtr_4mwsUj-Oa1rWwZArv1bV5DtrD9Cj8vlZyAxLiHOP6kVygzric6yDisXXp6TRXbuZDh09enUJyAmaMISbFdbSFQOTXoq7YMEs8pg7jJRtgxGB/w320-h320/4480793_2374948.jpg',
                    'https://mir-s3-cdn-cf.behance.net/projects/404/7129af57741691.Y3JvcCw4ODEsNjg5LDI4LDIx.png',
                    'https://thumbs.dreamstime.com/b/d-animated-cartoon-young-boy-wearing-blue-white-tennis-outfit-holding-yellow-tennis-racket-to-serve-323514422.jpg',
                    'https://img.freepik.com/premium-vector/two-men-plays-volleyball-through-net_634954-457.jpg?w=2000',
                    'https://img.freepik.com/premium-vector/happy-cute-little-boy-playing-soccer-football-game-action-cartoon-vector-illustration_135170-1399.jpg?w=1480',
                    'https://tse4.mm.bing.net/th/id/OIP.CiS8R04iGcW7P1EwLoNAtwHaI7?pid=Api&P=0&h=180',
                    'https://static.vecteezy.com/system/resources/previews/016/920/945/original/softball-sports-cartoon-colored-clipart-free-vector.jpg',
                    'https://static.vecteezy.com/system/resources/previews/011/754/759/original/cartoon-field-hockey-sport-male-player-action-vector.jpg',
                ];
                foreach ($sportsImages as $url) {
                    echo '<div class="sd-game-card sd-game-card-circle" style="background-image: url(\'' . htmlspecialchars($url) . '\');"></div>';
                }
                foreach ($sportsImages as $url) {
                    echo '<div class="sd-game-card sd-game-card-circle" style="background-image: url(\'' . htmlspecialchars($url) . '\');"></div>';
                }
                ?>
            </div>
        </div>
    </section>

    <section class="py-5 eh-features-section">
        <div class="container">
            <div class="text-center mb-4">
                <h2 class="h3 fw-bold mb-2 animate-fade-in-up">Built for serious sports operations</h2>
                <p class="text-muted mb-0 animate-fade-in anim-delay-1">A clean, modern system for organizers and participants.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="sd-feature tilt-card sport-card hover-bounce animate-bounce-in-up anim-delay-2" data-tilt>
                        <div class="sd-feature-icon animate-pulse-slow">01</div>
                        <h3 class="h5 fw-semibold mb-2">Event publishing</h3>
                        <p class="text-muted mb-0">Admins create events with dates, locations, categories, and images—kept consistent across the site.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="sd-feature tilt-card sport-card hover-bounce animate-bounce-in-up anim-delay-3" data-tilt>
                        <div class="sd-feature-icon animate-pulse-slow">02</div>
                        <h3 class="h5 fw-semibold mb-2">Team registrations</h3>
                        <p class="text-muted mb-0">Users can register and add multiple participants in a single flow—perfect for sports team entries.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="sd-feature tilt-card sport-card hover-bounce animate-bounce-in-up anim-delay-4" data-tilt>
                        <div class="sd-feature-icon animate-pulse-slow">03</div>
                        <h3 class="h5 fw-semibold mb-2">Visibility & control</h3>
                        <p class="text-muted mb-0">Account approvals and activity logs keep the platform organized and accountable.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bounce.js/0.8.1/bounce.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= htmlspecialchars(base_url('assets/js/main.js')) ?>"></script>
<script src="<?= htmlspecialchars(base_url('assets/js/three-scene.js')) ?>"></script>
<script>
    // Initialize AOS (Animate On Scroll)
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        offset: 100
    });
</script>
</body>
</html>
