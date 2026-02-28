// Main JS for Melody Masters

// Cart AJAX Add
function addToCart(productId, qty = 1) {
    fetch(`${siteUrl}/cart-action.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add&product_id=${productId}&quantity=${qty}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            updateCartBadge(data.cart_count);
            showToast('Item added to cart!', 'success');
        } else {
            showToast(data.message || 'Error adding to cart', 'error');
        }
    });
}

function updateCartBadge(count) {
    document.querySelectorAll('.cart-badge').forEach(el => {
        el.textContent = count;
        el.style.display = count > 0 ? 'inline' : 'none';
    });
    // Update all cart links
    const cartBtns = document.querySelectorAll('a[href*="cart.php"]');
    cartBtns.forEach(btn => {
        const badge = btn.querySelector('.badge');
        if (badge) { badge.textContent = count; badge.style.display = count > 0 ? '' : 'none'; }
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-info');
    toast.className = `toast-notification ${bgClass} text-white`;
    toast.style.cssText = 'position:fixed;top:80px;right:20px;padding:12px 20px;border-radius:8px;z-index:9999;box-shadow:0 4px 15px rgba(0,0,0,0.2);min-width:200px;animation:slideIn 0.3s ease;';
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}`;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); }, 3000);
}

// Scroll to top
window.addEventListener('scroll', () => {
    const btn = document.getElementById('scrollTop');
    if (btn) btn.classList.toggle('show', window.scrollY > 300);
});
document.getElementById('scrollTop')?.addEventListener('click', () => window.scrollTo({top: 0, behavior: 'smooth'}));

// Cart quantity
document.querySelectorAll('.qty-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.closest('.qty-wrapper')?.querySelector('.cart-qty-input');
        if (!input) return;
        let val = parseInt(input.value) || 1;
        if (this.dataset.action === 'inc') val++;
        else if (this.dataset.action === 'dec') val = Math.max(1, val - 1);
        input.value = val;
    });
});

// Auto-hide alerts
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
        if (bsAlert) bsAlert.close();
    });
}, 5000);
