<?php
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$activePage = '';
$pageTitle = 'Site map';
$pageDescription = 'Browse all pages on the Luca’s Loaves website.';

$sitePages = [
    ['title' => 'Home', 'href' => 'index.php', 'description' => 'Meet the bakery and discover our daily breads.'],
    ['title' => 'Our story', 'href' => 'about.php', 'description' => 'Learn about our ingredients and slow sourdough process.'],
    ['title' => 'Order online', 'href' => 'order.php', 'description' => 'Choose a loaf and arrange a bakery pickup.'],
    ['title' => 'Careers', 'href' => 'careers.php', 'description' => 'Explore opportunities to join the bakery team.'],
    ['title' => 'Contact & visit', 'href' => 'contact.php', 'description' => 'Find our address, contact details, and opening hours.'],
    ['title' => 'Privacy policy', 'href' => 'privacy.php', 'description' => 'Read how information is handled on this website.'],
    ['title' => 'Admin sign in', 'href' => 'admin.php', 'description' => 'Restricted staff access to bakery orders and applications.'],
];

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'header.php';
?>
<section class="page-heading">
    <div class="container">
        <p class="eyebrow">A helpful place to start</p>
        <h1>Site map</h1>
        <p>Everything you need to explore Luca's Loaves, gathered in one place.</p>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="sitemap-grid">
            <?php foreach ($sitePages as $page): ?>
                <article class="sitemap-card">
                    <h2><a href="<?= e($page['href']) ?>"><?= e($page['title']) ?></a></h2>
                    <p><?= e($page['description']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
