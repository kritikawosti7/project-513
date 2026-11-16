<?php
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$activePage = 'about';
$pageTitle = 'Our story';
$pageDescription = 'Learn about Luca’s Loaves and our slow, thoughtful approach to artisan sourdough.';

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'header.php';
?>
<section class="page-heading">
    <div class="container">
        <p class="eyebrow">The Luca's way</p>
        <h1>Our story</h1>
        <p>Good bread is not rushed. It is grown, shaped, and baked with care—one small batch at a time.</p>
    </div>
</section>
<section class="section">
    <div class="container split-layout">
        <div class="split-copy">
            <p class="eyebrow">A neighbourhood bakery</p>
            <h2>Made slowly. Shared generously.</h2>
            <p>Luca's Loaves began with a simple idea: bring people back to bread with character. We make naturally leavened sourdough in small batches, guided by the seasons, the feel of the dough, and the rhythm of a long fermentation.</p>
            <p>Our bread is made without commercial yeast. We build each loaf around a living sourdough starter, organic flour, and filtered water, then hand-knead and shape every dough. The process takes 14–17 hours from mixing to bake, giving each loaf time to develop its flavour and texture.</p>
            <p>We bake in a solar-powered oven and believe thoughtful ingredients and patient craft make a difference you can taste.</p>
        </div>
        <div class="split-image story-image" role="img" aria-label="Golden artisan loaves fresh from the oven"></div>
    </div>
</section>
<section class="section section-cream">
    <div class="container">
        <div class="section-heading">
            <p class="eyebrow">Our everyday promises</p>
            <h2>Nothing complicated. Nothing rushed.</h2>
        </div>
        <div class="values-grid">
            <article class="value-card">
                <span class="value-number">01</span>
                <h3>A living starter</h3>
                <p>Our sourdough rises naturally. We use no commercial yeast, letting time and our starter do the work.</p>
            </article>
            <article class="value-card">
                <span class="value-number">02</span>
                <h3>Hands in the dough</h3>
                <p>We hand-knead and shape our loaves, paying attention to every batch from first fold to final score.</p>
            </article>
            <article class="value-card">
                <span class="value-number">03</span>
                <h3>Time well spent</h3>
                <p>Each loaf is prepared over 14–17 hours so its flavour can develop at its own unhurried pace.</p>
            </article>
            <article class="value-card">
                <span class="value-number">04</span>
                <h3>Thoughtful ingredients</h3>
                <p>Organic flour and filtered water are the simple foundation of all our everyday breads.</p>
            </article>
            <article class="value-card">
                <span class="value-number">05</span>
                <h3>Sun-powered baking</h3>
                <p>Our solar-powered oven brings each loaf to a crisp, deeply golden finish.</p>
            </article>
            <article class="value-card">
                <span class="value-number">06</span>
                <h3>Made to be shared</h3>
                <p>We bake for the breakfast table, the long lunch, and all the good moments in between.</p>
            </article>
        </div>
    </div>
</section>
<section class="section">
    <div class="container section-heading">
        <p class="eyebrow">Find your next favourite</p>
        <h2>Fresh bread, made every day.</h2>
        <a class="button" href="order.php">Explore our loaves</a>
    </div>
</section>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
