<?php
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$activePage = 'home';
$pageTitle = 'Artisan sourdough, made slowly';
$pageDescription = 'Discover naturally leavened artisan sourdough, thoughtfully made at Luca’s Loaves.';
$products = [];
$catalogueError = null;

try {
    $products = db()->query(
        'SELECT name, slug, description, price FROM products WHERE is_active = 1 ORDER BY id LIMIT 4'
    )->fetchAll();
} catch (PDOException $exception) {
    error_log('Luca\'s Loaves product catalogue failed: ' . $exception->getMessage());
    $catalogueError = 'Our bread list is temporarily unavailable. Please try again soon.';
}

$productImages = [
    'white-loaf' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=700&q=80',
    'rye-loaf' => 'https://images.unsplash.com/photo-1585478259715-876acc5be8eb?auto=format&fit=crop&w=700&q=80',
    'spelt-loaf' => 'https://images.unsplash.com/photo-1534620808146-d33bb39128b2?auto=format&fit=crop&w=700&q=80',
    'seeded-loaf' => 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?auto=format&fit=crop&w=700&q=80',
];

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'header.php';
?>
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <p class="eyebrow">Small batch · Naturally leavened</p>
            <h1>Good bread<br>takes time.</h1>
            <p class="hero-copy">Slow-fermented sourdough, made by hand and baked fresh every morning. Simple ingredients, a little patience, and a lot of care.</p>
            <div class="button-row">
                <a class="button" href="order.php">Order your loaf</a>
                <a class="button button-light" href="about.php">Meet the bakery</a>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container split-layout">
        <div class="split-copy">
            <p class="eyebrow">From our hands to your table</p>
            <h2>A slower, more thoughtful kind of bread.</h2>
            <p>At Luca's, we believe everyday bread can be extraordinary. We work with organic flour, filtered water, and our own living starter to make loaves with a crisp crust, an open crumb, and real depth of flavour.</p>
            <p>Every dough is hand-kneaded, given time to ferment, and baked in our solar-powered oven. No commercial yeast. No shortcuts. Just honest bread made with care.</p>
            <a class="text-link" href="about.php">The story behind our loaves <span aria-hidden="true">→</span></a>
        </div>
        <div class="split-image" role="img" aria-label="Freshly baked artisan sourdough loaves"></div>
    </div>
</section>

<section class="section section-cream">
    <div class="container">
        <div class="section-heading">
            <p class="eyebrow">Baked fresh, every day</p>
            <h2>Meet the daily loaf</h2>
            <p>Four simple favourites, made with the same patience and attention in every batch.</p>
        </div>
        <div class="product-grid">
            <?php if ($catalogueError !== null): ?>
                <p class="empty-state" role="status"><?= e($catalogueError) ?></p>
            <?php elseif ($products === []): ?>
                <p class="empty-state">Our bread list is being prepared. Please check back soon.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <?php $imageUrl = $productImages[$product['slug']] ?? $productImages['white-loaf']; ?>
                    <article class="product-card">
                        <img
                            class="product-image"
                            src="<?= e($imageUrl) ?>"
                            alt="<?= e($product['name']) ?> artisan sourdough loaf"
                            loading="lazy"
                        >
                        <div class="product-card-body">
                            <h3><?= e($product['name']) ?></h3>
                            <p class="product-price">$<?= e(number_format((float) $product['price'], 2)) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <p class="product-note">Prices shown per loaf. All breads are baked fresh in small batches.</p>
    </div>
</section>

<section class="section">
    <div class="container split-layout">
        <div class="split-image story-image" role="img" aria-label="Artisan bread cooling after baking"></div>
        <div class="split-copy">
            <p class="eyebrow">Learn the craft</p>
            <h2>There is a little baker in all of us.</h2>
            <p>Join us for a relaxed, hands-on bread-making class. Learn to care for a starter, shape your dough, and take home the confidence to bake a beautiful loaf in your own kitchen.</p>
            <a class="text-link" href="contact.php">Ask us about upcoming classes <span aria-hidden="true">→</span></a>
        </div>
    </div>
</section>

<section class="class-band">
    <div class="container class-content">
        <div>
            <p class="eyebrow">Your neighbourhood bakery</p>
            <h2>Come by for the smell of something good.</h2>
            <p>We bake every morning and keep the doors open all day. Stop in, say hello, and find your new favourite loaf.</p>
        </div>
        <a class="button button-light" href="contact.php">Plan your visit</a>
    </div>
</section>
<div class="hours-strip">
    <strong>Open seven days a week</strong>
    <span>7:00 am – 4:00 pm</span>
</div>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
