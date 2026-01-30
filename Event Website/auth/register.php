<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    redirect(is_admin() ? 'admin/dashboard.php' : 'user/events.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');
    $terms = !empty($_POST['terms']);

    if (!$name || !$email || !$password || !$terms) {
        $error = 'Please fill all required fields and accept the terms.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, status) VALUES (?,?,?,?,?)');
            // New accounts require admin approval.
            $ins->execute([$name, $email, $hash, 'user', 'pending']);
            flash('success', 'Account created. Awaiting admin approval before you can log in.');
            redirect('auth/login.php');
        }
    }
}

$error = $error ?: flash_get('error');
$success = flash_get('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Events Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/style.css')) ?>">
</head>
<body class="d-flex flex-column min-vh-100">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="flex-grow-1 auth-page">
    <div class="container">
        <div class="auth-card">
                <h1 class="auth-title text-center">Create your account</h1>
                <p class="auth-subtitle text-center">
                    Create an account to browse events and register yourself or multiple participants.
                </p>

                <?php if ($error): ?>
                    <div class="alert alert-danger small"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success small"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= htmlspecialchars(base_url('auth/register.php')) ?>" novalidate>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label required">Full name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label required">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label required">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Create a secure password (min 8)" required>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label required">Confirm password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
                        </div>
                        <div class="col-12">
                            <label for="role" class="form-label required">Register as</label>
                            <select id="role" name="role" class="form-select" required>
                                <option value="user" selected>Participant</option>
                            </select>
                            <div class="form-text">Create an account to browse events and register yourself or multiple participants.</div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="terms" value="1" id="terms" <?= !empty($_POST['terms']) ? 'checked' : '' ?> required>
                                <label class="form-check-label small text-muted" for="terms">I agree to the terms of use and privacy policy.</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">Create account</button>
                        </div>
                    </div>
                    <p class="text-center small text-muted mt-3 mb-0">
                        Already have an account? <a href="<?= htmlspecialchars(base_url('auth/login.php')) ?>" class="text-decoration-none">Login</a>
                    </p>
                </form>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars(base_url('assets/js/main.js')) ?>"></script>
</body>
</html>
