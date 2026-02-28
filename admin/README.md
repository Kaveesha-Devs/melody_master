# Melody Masters - Music Instrument Shop
## IT3113 Advanced Web Technologies Assignment

### Setup Instructions

1. **Requirements**: PHP 7.4+, MySQL 5.7+, Apache/Nginx with mod_rewrite

2. **Database Setup**:
   ```
   mysql -u root -p < database.sql
   ```

3. **Configuration**:
   Edit `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   define('DB_NAME', 'melody_masters');
   define('SITE_URL', 'http://localhost/melody-masters');
   ```

4. **File Permissions**:
   ```
   chmod 755 images/products/
   chmod 755 uploads/
   ```

### Demo Accounts
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@melodymaster.com | password |
| Staff | staff@melodymaster.com | password |
| Customer | john@example.com | password |

### Project Structure
```
melody-masters/
├── index.php               # Homepage
├── shop.php                # Product listing/search/filter
├── product.php             # Product detail + reviews
├── cart.php                # Shopping cart
├── cart-action.php         # AJAX cart actions
├── checkout.php            # Checkout form
├── order-confirmation.php  # Order success page
├── order-detail.php        # Customer order detail
├── account.php             # Customer account dashboard
├── login.php               # Login
├── register.php            # Registration
├── logout.php              # Logout
├── download.php            # Digital product download
├── admin/
│   ├── index.php           # Admin dashboard
│   ├── products.php        # Product CRUD
│   ├── orders.php          # Order management
│   ├── users.php           # User management
│   ├── categories.php      # Category management
│   └── reviews.php         # Review moderation
├── includes/
│   ├── config.php          # Site config + DB constants
│   ├── db.php              # Database connection + helpers
│   ├── functions.php       # Core business logic functions
│   ├── header.php          # Public header
│   ├── footer.php          # Public footer
│   └── product-card.php    # Product card partial
├── css/style.css           # Main stylesheet
├── js/main.js              # Main JavaScript
└── database.sql            # Database schema + sample data
```

### Features Implemented
✅ Guest browsing (no auth required)
✅ User registration and login
✅ Product categories with subcategories
✅ Product search, filter by category/price/brand, sort
✅ Product detail page with specifications, reviews
✅ Shopping cart (session for guests, DB for logged-in)
✅ Cart merging on login
✅ Shipping calculation (free over £100, digital = free)
✅ Checkout with shipping address
✅ Order placement and confirmation
✅ Digital product download system with download limits
✅ Customer account dashboard
✅ Order history and order detail view
✅ Password change
✅ Product reviews (only for verified purchasers)
✅ Admin dashboard with stats and low-stock alerts
✅ Admin product management (add/edit/delete + image upload)
✅ Admin order management (status update + tracking number)
✅ Admin category management
✅ Admin review moderation (approve/reject)
✅ User management with role control (admin only)
✅ Responsive design (Bootstrap 5)
✅ AJAX cart actions
