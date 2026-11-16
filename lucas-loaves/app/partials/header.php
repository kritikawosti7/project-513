<?php
$activePage = $activePage ?? '';
$pageTitle = $pageTitle ?? APP_NAME;
$pageDescription = $pageDescription ?? 'Naturally leavened artisan sourdough, baked with care.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#5A3A22">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="site.js" defer></script>
</head>
<body>
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php" aria-label="Luca's Loaves home">
            <span class="brand-mark" aria-hidden="true">L</span>
            <span class="brand-name">Luca's <span>Loaves</span></span>
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation">
            <span class="menu-toggle-label">Menu</span>
            <span class="menu-icon" aria-hidden="true"></span>
        </button>
        <nav class="primary-navigation" id="primary-navigation" aria-label="Main navigation">
            <a href="index.php"<?= $activePage === 'home' ? ' aria-current="page"' : '' ?>>Home</a>
            <a href="about.php"<?= $activePage === 'about' ? ' aria-current="page"' : '' ?>>Our story</a>
            <a href="order.php"<?= $activePage === 'order' ? ' aria-current="page"' : '' ?>>Order bread</a>
            <a href="careers.php"<?= $activePage === 'careers' ? ' aria-current="page"' : '' ?>>Careers</a>
            <a href="contact.php"<?= $activePage === 'contact' ? ' aria-current="page"' : '' ?>>Visit us</a>
        </nav>
    </div>
</header>
<main id="main-content">
