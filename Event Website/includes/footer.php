<?php
// Shared footer for all pages
?>
<footer class="mt-auto py-5 border-top" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%); color: #fff;">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="fw-bold mb-3">Events Hub</h5>
                <p class="text-white-50 small mb-0">Discover, register, and manage events in one place.</p>
            </div>
            <div class="col-md-4">
                <h5 class="fw-bold mb-3">Contact Details</h5>
                <div class="d-flex flex-column gap-2 small">
                    <div>
                        <strong class="text-white">Address:</strong>
                        <p class="text-white-50 mb-0">Vaarahi Enclave<br>6/13 Brodipet, Guntur</p>
                    </div>
                    <div>
                        <strong class="text-white">Phone:</strong>
                        <p class="text-white-50 mb-0">
                            <a href="tel:+919121214564" class="text-white-50 text-decoration-none">+91 9121214564</a>
                        </p>
                    </div>
                    <div>
                        <strong class="text-white">Email:</strong>
                        <p class="text-white-50 mb-0">
                            <a href="mailto:info@jathinsoftware.com" class="text-white-50 text-decoration-none">info@jathinsoftware.com</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <h5 class="fw-bold mb-3">Quick Links</h5>
                <div class="d-flex flex-column gap-2 small">
                    <?php
                    $user = current_user();
                    if (!$user):
                    ?>
                    <a href="<?= htmlspecialchars(base_url('index.php')) ?>" class="text-white-50 text-decoration-none">Home</a>
                    <?php endif; ?>
                    <a href="<?= htmlspecialchars(base_url('user/events.php')) ?>" class="text-white-50 text-decoration-none">Events</a>
                    <?php if (!$user): ?>
                    <a href="<?= htmlspecialchars(base_url('about.php')) ?>" class="text-white-50 text-decoration-none">About</a>
                    <?php endif; ?>
                    <a href="<?= htmlspecialchars(base_url('gallery.php')) ?>" class="text-white-50 text-decoration-none">Gallery</a>
                    <?php if (!function_exists('is_admin') || !is_admin()): ?>
                    <a href="<?= htmlspecialchars(base_url('contact.php')) ?>" class="text-white-50 text-decoration-none">Contact</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <hr class="my-4" style="border-color: rgba(255, 255, 255, 0.1);">
        <div class="text-center text-white-50 small">
            &copy; <?php echo date('Y'); ?> Events Hub. All rights reserved.
        </div>
    </div>
</footer>

