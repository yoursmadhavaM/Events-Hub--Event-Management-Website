<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    $dest = $_SESSION['redirect_after_login'] ?? null;
    unset($_SESSION['redirect_after_login']);
    $base = base_url('');
    $valid = $dest && ($base === '' ? (strpos($dest, '/') === 0) : (strpos($dest, $base) === 0 || strpos($dest, ltrim($base, '/')) === 0));
    if ($valid) {
        header('Location: ' . $dest, true, 302);
        exit;
    }
    redirect(is_admin() ? 'admin/dashboard.php' : 'user/events.php');
}

if (isset($_GET['redirect']) && is_string($_GET['redirect'])) {
    $r = trim($_GET['redirect']);
    if ($r !== '' && strpos($r, '//') === false) {
        $_SESSION['redirect_after_login'] = $r[0] === '/' ? $r : base_url($r);
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $loginAs = trim((string) ($_POST['login_as'] ?? 'participant')); // participant | admin

    if (!$email || !$password) {
        $error = 'Please enter email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, name, email, password_hash, role, status FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            // If the account was rejected/deleted by admin, show a clear message.
            $d = $pdo->prepare('SELECT reason, deleted_at FROM deleted_users WHERE email = ? ORDER BY deleted_at DESC LIMIT 1');
            $d->execute([$email]);
            $deleted = $d->fetch(PDO::FETCH_ASSOC);
            if ($deleted) {
                $error = 'Account was deleted and not approved. Please register again.';
            } else {
                $error = 'Invalid email or password.';
            }
        } elseif ($user['status'] === 'pending') {
            $error = 'Your account is awaiting admin approval.';
        } elseif ($user['status'] !== 'active') {
            $error = 'Your account has been suspended. Contact an administrator.';
        } elseif (!password_verify($password, $user['password_hash'])) {
            $error = 'Invalid email or password.';
        } elseif ($loginAs === 'admin' && $user['role'] !== 'admin') {
            $error = 'This account is not an admin account.';
        } elseif ($loginAs !== 'admin' && $user['role'] === 'admin') {
            $error = 'Please switch to Admin login to continue.';
        } else {
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'status' => $user['status'],
            ];
            $dest = $_SESSION['redirect_after_login'] ?? null;
            unset($_SESSION['redirect_after_login']);
            $base = base_url('');
            if ($dest && ($base === '' ? (strpos($dest, '/') === 0) : (strpos($dest, $base) === 0))) {
                header('Location: ' . $dest, true, 302);
                exit;
            }
            redirect(is_admin() ? 'admin/dashboard.php' : 'user/events.php');
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
    <title>Login - Events Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/style.css')) ?>">
</head>
<body class="d-flex flex-column min-vh-100">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="flex-grow-1 auth-page">
    <div class="container">
        <div class="auth-card">
                <h1 class="auth-title text-center">Welcome back</h1>
                <p class="auth-subtitle text-center">Log in to access Events Hub.</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger small"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success small"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= htmlspecialchars(base_url('auth/login.php')) ?>" novalidate>
                    <div class="mb-3">
                        <label class="form-label required">Login as</label>
                        <div class="btn-group w-100" role="group" aria-label="Login role">
                            <input type="radio" class="btn-check" name="login_as" id="login_as_participant" value="participant" autocomplete="off" <?= ($_POST['login_as'] ?? 'participant') !== 'admin' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="login_as_participant">Participant</label>

                            <input type="radio" class="btn-check" name="login_as" id="login_as_admin" value="admin" autocomplete="off" <?= ($_POST['login_as'] ?? '') === 'admin' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="login_as_admin">Admin</label>
                        </div>
                        <div class="form-text" id="loginRoleHelp">Select the account type you are logging into.</div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label required">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label required">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                            <label class="form-check-label small text-muted" for="remember">Remember me</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                    <p class="text-center small text-muted mb-0">
                        New here? <a href="<?= htmlspecialchars(base_url('auth/register.php')) ?>" class="text-decoration-none">Create an account</a>
                    </p>
                </form>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars(base_url('assets/js/main.js')) ?>"></script>
<script>
    (function () {
        const p = document.getElementById('login_as_participant');
        const a = document.getElementById('login_as_admin');
        const help = document.getElementById('loginRoleHelp');
        if (!p || !a || !help) return;
        function sync() {
            help.textContent = a.checked
                ? 'Admin login: access the dashboard and manage events, users, and gallery.'
                : 'Participant login: browse events and manage your registrations.';
        }
        p.addEventListener('change', sync);
        a.addEventListener('change', sync);
        sync();
    })();
</script>
</body>
</html>
