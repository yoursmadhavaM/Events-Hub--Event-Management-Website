<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    
    if (empty($name)) {
        $error = 'Name is required.';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Valid email is required.';
    } elseif (empty($message)) {
        $error = 'Message is required.';
    } else {
        try {
            // Ensure contact_requests table exists (in case migration was not run)
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

            $stmt = $pdo->prepare('INSERT INTO contact_requests (name, email, message) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $message]);
            $success = 'Thank you! Your message has been sent. We will get back to you soon.';
            // Clear form fields
            $_POST = [];
        } catch (PDOException $e) {
            $error = 'Failed to send message. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Events Hub</title>
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
                    <p class="sd-page-kicker text-uppercase small mb-1" data-reveal>Get in Touch</p>
                    <h1 class="sd-title h3 fw-bold mb-2" data-reveal>Contact Us</h1>
                    <p class="sd-subtitle text-white-50 mb-0" data-reveal>Have a question about an event or your account approval? Send us a message.</p>
                </div>
            </div>
        </div>
    </section>
    <div class="container py-5 eh-page-content">
        <div class="row g-4">
            <div class="col-lg-6">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="post" class="card shadow-sm border-0 tilt-card" data-tilt>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Name</label>
                            <input class="form-control" type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Email</label>
                            <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Message</label>
                            <textarea class="form-control" name="message" rows="5" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Send</button>
                    </div>
                </form>
            </div>
            <div class="col-lg-6">
                <div class="d-flex flex-column gap-3 h-100">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h2 class="h6 fw-semibold mb-2">Address</h2>
                            <p class="text-muted small mb-0">
                                Vaarahi Enclave<br>
                                6/13 Brodipet, Guntur
                            </p>
                            <hr class="my-3">
                            <h3 class="h6 fw-semibold mb-2">Reach Us</h3>
                            <p class="text-muted small mb-0">
                                <a class="text-decoration-none" href="tel:+919121214564">+91 9121214564</a>
                            </p>
                            <hr class="my-3">
                            <h3 class="h6 fw-semibold mb-2">E-Mail to Us</h3>
                            <p class="text-muted small mb-0">
                                Mail to:
                                <a class="text-decoration-none" href="mailto:info@jathinsoftware.com">info@jathinsoftware.com</a>
                            </p>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 flex-grow-1 overflow-hidden">
                        <div class="card-body p-0">
                            <div class="ratio ratio-16x9">
                                <iframe
                                    src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d9084.632831558798!2d80.432804!3d16.307232!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a4a75a39a508fab%3A0x879e6a7f054421a9!2sJathin%20Software%20Pvt%20Ltd!5e1!3m2!1sen!2sin!4v1769693412861!5m2!1sen!2sin"
                                    style="border:0;"
                                    allowfullscreen=""
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    title="Map"
                                ></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="<?= htmlspecialchars(base_url('assets/js/main.js')) ?>"></script>
</body>
</html>

