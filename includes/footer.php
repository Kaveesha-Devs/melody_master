</main>

<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-brand">
                <h3>Melody Masters</h3>
                <p>Your premier destination for musical instruments, accessories, and sheet music. Elevate your sound.
                </p>
            </div>

            <div class="footer-links">
                <h4>Shop</h4>
                <ul>
                    <li><a href="/shop.php?category=guitars">Guitars</a></li>
                    <li><a href="/shop.php?category=keyboards">Keyboards</a></li>
                    <li><a href="/shop.php?category=drums">Drums</a></li>
                    <li><a href="/shop.php?category=accessories">Accessories</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h4>Account</h4>
                <ul>
                    <?php if (is_logged_in()): ?>
                        <li><a href="/dashboard.php">My Profile</a></li>
                        <li><a href="/cart.php">Shopping Cart</a></li>
                    <?php else: ?>
                        <li><a href="/login.php">Sign In</a></li>
                        <li><a href="/register.php">Create Account</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-contact">
                <h4>Contact Us</h4>
                <p>Email: support@melodymasters.com</p>
                <p>Phone: +44 20 7946 0958</p>
                <p>123 Symphony Avenue<br>London, UK</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy;
                <?php echo date('Y'); ?> Melody Masters Instrument Shop. All rights reserved.
            </p>
        </div>
    </div>
</footer>
<script src="/js/script.js" defer></script>
</body>

</html>