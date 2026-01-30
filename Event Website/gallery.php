<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/admin_log.php';

$error = '';
$success = '';

// Admin-only upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();

    $caption = trim((string) ($_POST['caption'] ?? ''));
    $uploaded = 0;

    if (!isset($_FILES['images']) || !is_array($_FILES['images']) || !isset($_FILES['images']['name'])) {
        $error = 'Please choose one or more images to upload.';
    } else {
        $names = (array) $_FILES['images']['name'];
        $tmps  = (array) $_FILES['images']['tmp_name'];
        $sizes = (array) $_FILES['images']['size'];
        $errs  = (array) $_FILES['images']['error'];
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        $galleryDir = __DIR__ . '/uploads/gallery';
        if (!is_dir($galleryDir)) {
            @mkdir($galleryDir, 0775, true);
        }

        $stmt = $pdo->prepare('INSERT INTO gallery_images (image_path, caption) VALUES (?, ?)');

        for ($i = 0; $i < count($names); $i++) {
            if (($errs[$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if (($errs[$i] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                $error = 'One of the images failed to upload. Please try again.';
                break;
            }
            $tmp = (string) ($tmps[$i] ?? '');
            $size = (int) ($sizes[$i] ?? 0);
            $orig = (string) ($names[$i] ?? '');
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            if ($size <= 0 || $size > 5 * 1024 * 1024) {
                $error = 'Each image must be <= 5MB.';
                break;
            }
            if (!in_array($ext, $allowed, true)) {
                $error = 'Images must be JPG, PNG, or WEBP.';
                break;
            }
            if (!is_uploaded_file($tmp)) {
                $error = 'Invalid image upload.';
                break;
            }

            $fileName = 'gallery_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $destFs = $galleryDir . '/' . $fileName;
            if (!@move_uploaded_file($tmp, $destFs)) {
                $error = 'Failed to save uploaded images.';
                break;
            }
            $imagePath = 'uploads/gallery/' . $fileName;
            $stmt->execute([$imagePath, $caption !== '' ? $caption : null]);
            $uploaded++;
        }
    }

    if (!$error && $uploaded > 0) {
        admin_log('gallery_upload', 'gallery', null, ['count' => $uploaded, 'caption' => $caption !== '' ? $caption : null]);
        flash('success', 'Uploaded ' . $uploaded . ' image(s) to gallery.');
        redirect('gallery.php');
    } elseif (!$error) {
        $error = 'Please choose one or more images to upload.';
    }
}

$error = $error ?: flash_get('error');
$success = flash_get('success');

$images = $pdo->query('SELECT id, image_path, caption, created_at FROM gallery_images ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Events Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/style.css')) ?>">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
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
                    <p class="sd-page-kicker text-uppercase small mb-1" data-reveal>Highlights</p>
                    <h1 class="sd-title h3 fw-bold mb-2" data-reveal>Gallery</h1>
                    <p class="sd-subtitle text-white-50 mb-0" data-reveal>Game moments, celebrations, and behind-the-scenes energy.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 eh-page-content">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
            <?php if (is_admin()): ?>
                <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#uploadPanel" aria-expanded="false">+ Add Image</button>
            <?php endif; ?>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (is_admin()): ?>
            <div class="collapse mb-4" id="uploadPanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3">Upload gallery image</h2>
                        <form method="post" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Image</label>
                                    <input class="form-control" type="file" name="images[]" accept=".jpg,.jpeg,.png,.webp,image/*" multiple required>
                                    <div class="form-text">Upload multiple images. Each max 5MB. JPG/PNG/WEBP.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Caption <span class="text-muted">(optional)</span></label>
                                    <input class="form-control" type="text" name="caption" placeholder="Short caption">
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-success" type="submit">Upload</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-3">
            <?php 
            $delay = 0;
            foreach ($images as $img): 
                $delay += 50;
            ?>
                <div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                    <div class="card shadow-sm border-0 h-100 gallery-item">
                        <img src="<?= htmlspecialchars(base_url($img['image_path'])) ?>" alt="<?= htmlspecialchars($img['caption'] ?: 'Gallery image') ?>" class="sport-img">
                        <?php if (!empty($img['caption'])): ?>
                            <div class="gallery-caption"><?= htmlspecialchars($img['caption']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($images)): ?>
            <p class="text-muted text-center py-5 mb-0">No gallery images yet.</p>
        <?php endif; ?>
    </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
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

