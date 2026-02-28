<footer class="footer bg-dark text-light mt-5 py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-music text-primary fs-3 me-2"></i>
                    <div>
                        <h5 class="mb-0 text-white">Melody Masters</h5>
                        <small class="text-muted">Instrument Shop</small>
                    </div>
                </div>
                <p class="text-muted small">Your one-stop destination for all musical instruments, accessories and sheet music. Serving musicians from beginners to professionals.</p>
                <div class="social-links">
                    <a href="#" class="btn btn-outline-light btn-sm me-1"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm me-1"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm me-1"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            <div class="col-md-3">
                <h6 class="text-white mb-3">Shop By Category</h6>
                <ul class="list-unstyled">
                    <?php foreach(getCategories() as $cat): ?>
                    <li><a href="<?= SITE_URL ?>/shop.php?category=<?= $cat['slug'] ?>" class="text-muted text-decoration-none hover-light">
                        <i class="fas fa-chevron-right text-primary me-1 small"></i><?= sanitize($cat['name']) ?>
                    </a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 class="text-white mb-3">Customer Service</h6>
                <ul class="list-unstyled">
                    <li><a href="<?= SITE_URL ?>/account.php" class="text-muted text-decoration-none"><i class="fas fa-chevron-right text-primary me-1 small"></i>My Account</a></li>
                    <li><a href="<?= SITE_URL ?>/cart.php" class="text-muted text-decoration-none"><i class="fas fa-chevron-right text-primary me-1 small"></i>Shopping Cart</a></li>
                    <li><a href="<?= SITE_URL ?>/account.php?tab=orders" class="text-muted text-decoration-none"><i class="fas fa-chevron-right text-primary me-1 small"></i>Order Tracking</a></li>
                    <li><a href="#" class="text-muted text-decoration-none"><i class="fas fa-chevron-right text-primary me-1 small"></i>Returns & Refunds</a></li>
                    <li><a href="#" class="text-muted text-decoration-none"><i class="fas fa-chevron-right text-primary me-1 small"></i>FAQ</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 class="text-white mb-3">Contact Us</h6>
                <ul class="list-unstyled text-muted small">
                    <li class="mb-2"><i class="fas fa-map-marker-alt text-primary me-2"></i>123 Music Street, London, UK</li>
                    <li class="mb-2"><i class="fas fa-phone text-primary me-2"></i>+44 20 7946 0958</li>
                    <li class="mb-2"><i class="fas fa-envelope text-primary me-2"></i>info@melodymaster.com</li>
                    <li class="mb-2"><i class="fas fa-clock text-primary me-2"></i>Mon-Sat: 9AM - 7PM</li>
                </ul>
                <div class="mt-3">
                    <img src="https://via.placeholder.com/200x30/ffffff/333333?text=Secure+Payment" class="img-fluid rounded" alt="Payment methods">
                </div>
            </div>
        </div>
        <hr class="border-secondary mt-4">
        <div class="row align-items-center">
            <div class="col-md-6 text-muted small">&copy; <?= date('Y') ?> Melody Masters Instrument Shop. All rights reserved.</div>
            <div class="col-md-6 text-end text-muted small">
                <a href="#" class="text-muted text-decoration-none me-3">Privacy Policy</a>
                <a href="#" class="text-muted text-decoration-none me-3">Terms of Service</a>
                <a href="#" class="text-muted text-decoration-none">Sitemap</a>
            </div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body>
</html>
