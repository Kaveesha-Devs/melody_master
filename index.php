<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Fetch recent products for the homepage
$recent_products = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC 
    LIMIT 4
")->fetchAll();

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section
    style="background: linear-gradient(rgba(15,15,17,0.7), rgba(15,15,17,0.9)), url('https://images.unsplash.com/photo-1511379938547-c1f69419868d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover; padding: 6rem 2rem; border-radius: var(--border-radius); text-align: center; margin-bottom: 4rem; border: 1px solid var(--border);">
    <h1 style="font-size: 3.5rem; margin-bottom: 1.5rem; text-shadow: 0 4px 10px rgba(0,0,0,0.5);">Discover Your True
        Sound</h1>
    <p style="font-size: 1.2rem; color: var(--text-secondary); max-width: 600px; margin: 0 auto 2rem;">Premium acoustic,
        electric, and digital instruments curated for musicians who demand nothing but the absolute best.</p>
    <a href="shop.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">Shop the Collection</a>
</section>

<!-- Categories Overview -->
<section style="margin-bottom: 4rem;">
    <h2 class="text-center" style="margin-bottom: 2rem;">Explore Categories</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">

        <a href="shop.php?category=guitars"
            style="display: block; position: relative; border-radius: var(--border-radius); overflow: hidden; aspect-ratio: 4/3; group;">
            <div
                style="position: absolute; inset: 0; background: url('https://images.unsplash.com/photo-1516924962500-2b4b3b99ea02?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80') center/cover; transition: transform 0.5s; class: 'cat-img';">
            </div>
            <div style="position: absolute; inset: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8));"></div>
            <h3 style="position: absolute; bottom: 1.5rem; left: 1.5rem; color: #fff; margin: 0;">Guitars</h3>
        </a>

        <a href="shop.php?category=keyboards"
            style="display: block; position: relative; border-radius: var(--border-radius); overflow: hidden; aspect-ratio: 4/3; group;">
            <div
                style="position: absolute; inset: 0; background: url('https://images.unsplash.com/photo-1552422535-c45813c61732?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80') center/cover; transition: transform 0.5s; class: 'cat-img';">
            </div>
            <div style="position: absolute; inset: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8));"></div>
            <h3 style="position: absolute; bottom: 1.5rem; left: 1.5rem; color: #fff; margin: 0;">Keyboards</h3>
        </a>

        <a href="shop.php?category=drums"
            style="display: block; position: relative; border-radius: var(--border-radius); overflow: hidden; aspect-ratio: 4/3; group;">
            <div
                style="position: absolute; inset: 0; background: url('https://images.unsplash.com/photo-1519892300165-cb5542fb47c7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80') center/cover; transition: transform 0.5s; class: 'cat-img';">
            </div>
            <div style="position: absolute; inset: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8));"></div>
            <h3 style="position: absolute; bottom: 1.5rem; left: 1.5rem; color: #fff; margin: 0;">Drums & Percussion
            </h3>
        </a>

        <a href="shop.php?category=digital"
            style="display: block; position: relative; border-radius: var(--border-radius); overflow: hidden; aspect-ratio: 4/3; group;">
            <div
                style="position: absolute; inset: 0; background: url('https://images.unsplash.com/photo-1507838153406-b53af14f8263?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80') center/cover; transition: transform 0.5s; class: 'cat-img';">
            </div>
            <div style="position: absolute; inset: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8));"></div>
            <h3 style="position: absolute; bottom: 1.5rem; left: 1.5rem; color: #fff; margin: 0;">Digital Sheet Music
            </h3>
        </a>

    </div>
</section>

<!-- Featured Products -->
<section>
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <h2 style="margin: 0;">New Arrivals</h2>
        <a href="shop.php" style="color: var(--primary-color);">View All &rarr;</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;">
        <?php foreach ($recent_products as $product): ?>
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--border-radius); overflow: hidden; transition: transform 0.3s; display: flex; flex-direction: column;"
                onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <a href="product.php?id=<?php echo $product['id']; ?>"
                    style="display: block; height: 250px; background: #222; position: relative;">
                    <?php if ($product['image_url']): ?>
                        <img src="<?php echo h($product['image_url']); ?>" alt="<?php echo h($product['name']); ?>"
                            style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div
                            style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-secondary); font-family: 'Playfair Display', serif; font-size: 1.5rem; font-style: italic;">
                            Melody Masters</div>
                    <?php endif; ?>

                    <?php if ($product['is_digital']): ?>
                        <span
                            style="position: absolute; top: 1rem; right: 1rem; background: rgba(0,0,0,0.7); color: var(--primary-color); padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold; border: 1px solid var(--primary-color);">Digital</span>
                    <?php endif; ?>
                </a>
                <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
                    <div
                        style="font-size: 0.85rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">
                        <?php echo h($product['category_name']); ?>
                    </div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; color: var(--text-primary);">
                        <a href="product.php?id=<?php echo $product['id']; ?>" style="color: inherit;">
                            <?php echo h($product['name']); ?>
                        </a>
                    </h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem; flex: 1;">
                        <?php echo h(substr($product['description'], 0, 80)) . '...'; ?>
                    </p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                        <div style="font-size: 1.25rem; font-weight: bold; color: var(--primary-color);">
                            <?php echo format_price($product['price']); ?>
                        </div>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary"
                            style="padding: 0.5rem 1rem; font-size: 0.9rem;">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<style>
    /* Add hover effect to category images */
    a.group:hover .cat-img {
        transform: scale(1.05);
    }
</style>

<?php require_once 'includes/footer.php'; ?>