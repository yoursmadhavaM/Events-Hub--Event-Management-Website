<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_log.php';
require_admin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string) ($_POST['title'] ?? ''));
    $category = trim((string) ($_POST['category'] ?? ''));
    $event_date = trim((string) ($_POST['event_date'] ?? ''));
    $location = trim((string) ($_POST['location'] ?? ''));
    $max_participants = (int) ($_POST['max_participants'] ?? 0);
    $max_team_size = (int) ($_POST['max_team_size'] ?? 0);
    $description = trim((string) ($_POST['description'] ?? ''));
    $imagePath = null;

    if (!$title || !$category || !$event_date || !$location || $max_participants < 1 || $max_team_size < 1 || !$description) {
        $error = 'Please fill all required fields.';
    } elseif ($max_team_size > $max_participants) {
        $error = 'Max team size cannot be greater than max participants.';
    } else {
        // Handle optional event image upload
        if (isset($_FILES['image']) && is_array($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if (($_FILES['image']['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                $error = 'Image upload failed. Please try again.';
            } else {
                $tmp = (string) ($_FILES['image']['tmp_name'] ?? '');
                $size = (int) ($_FILES['image']['size'] ?? 0);
                $orig = (string) ($_FILES['image']['name'] ?? '');
                $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if ($size <= 0 || $size > 5 * 1024 * 1024) {
                    $error = 'Image must be <= 5MB.';
                } elseif (!in_array($ext, $allowed, true)) {
                    $error = 'Image must be JPG, PNG, or WEBP.';
                } elseif (!is_uploaded_file($tmp)) {
                    $error = 'Invalid image upload.';
                } else {
                    $uploadsDir = realpath(__DIR__ . '/../uploads');
                    if (!$uploadsDir) {
                        $uploadsDir = __DIR__ . '/../uploads';
                        @mkdir($uploadsDir, 0775, true);
                    }
                    $eventsDir = __DIR__ . '/../uploads/events';
                    if (!is_dir($eventsDir)) {
                        @mkdir($eventsDir, 0775, true);
                    }
                    $fileName = 'event_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $destFs = $eventsDir . '/' . $fileName;
                    if (!@move_uploaded_file($tmp, $destFs)) {
                        $error = 'Failed to save uploaded image.';
                    } else {
                        $imagePath = 'uploads/events/' . $fileName;
                    }
                }
            }
        }

        if (!$error) {
            $stmt = $pdo->prepare('INSERT INTO events (title, category, event_date, location, max_participants, max_team_size, image_path, description) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$title, $category, $event_date, $location, $max_participants, $max_team_size, $imagePath, $description]);
            admin_log('event_create', 'event', (int) $pdo->lastInsertId(), ['title' => $title]);
        }
        if (!$error) {
        flash('success', 'Event created successfully.');
        redirect('admin/dashboard.php');
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - Events Hub Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/style.css')) ?>">
</head>
<body class="d-flex flex-column min-vh-100">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="flex-grow-1 py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 fw-bold mb-1">Create New Event</h1>
                <p class="text-muted small mb-0">Add an event. It will appear on the site for users to browse and register.</p>
            </div>
            <a href="<?= htmlspecialchars(base_url('admin/dashboard.php')) ?>" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="post" action="<?= htmlspecialchars(base_url('admin/create_event.php')) ?>" enctype="multipart/form-data" novalidate>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="title" class="form-label required">Event Title</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="e.g., Tech Innovation Summit 2026" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="category" class="form-label required">Category</label>
                            <input type="text" class="form-control" id="category" name="category" placeholder="e.g., Technology, Workshop, Sports" value="<?= htmlspecialchars($_POST['category'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="event_date" class="form-label required">Date &amp; Time</label>
                            <input type="datetime-local" class="form-control" id="event_date" name="event_date" value="<?= htmlspecialchars($_POST['event_date'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="max_participants" class="form-label required">Max Participants</label>
                            <input type="number" class="form-control" id="max_participants" name="max_participants" min="1" step="1" placeholder="e.g., 100" value="<?= htmlspecialchars($_POST['max_participants'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="max_team_size" class="form-label required">Max Team Size</label>
                            <input type="number" class="form-control" id="max_team_size" name="max_team_size" min="1" step="1" placeholder="e.g., 5" value="<?= htmlspecialchars($_POST['max_team_size'] ?? '') ?>" required>
                            <div class="form-text">Maximum participants allowed in a single registration.</div>
                        </div>
                        <div class="col-12">
                            <label for="location" class="form-label required">Location</label>
                            <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Online (Zoom) or Conference Hall A" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label required">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5" placeholder="Describe what the event is about." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label for="image" class="form-label">Event Image <span class="text-muted">(optional)</span></label>
                            <input class="form-control" type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,image/*">
                            <div class="form-text">Recommended: 1200×630. Max 5MB. JPG/PNG/WEBP.</div>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                            <button type="reset" class="btn btn-outline-secondary">Clear</button>
                            <button type="submit" class="btn btn-primary">Create Event</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars(base_url('assets/js/main.js')) ?>"></script>
</body>
</html>
