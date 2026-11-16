<?php
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$activePage = 'contact';
$pageTitle = 'Visit or contact us';
$pageDescription = 'Find Luca’s Loaves, check our opening hours, or get in touch with the bakery.';

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'header.php';
?>
<section class="page-heading">
    <div class="container">
        <p class="eyebrow">We would love to see you</p>
        <h1>Come say hello.</h1>
        <p>Drop in for a fresh loaf, ask us about our bread-making classes, or get in touch before your visit.</p>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="contact-grid">
            <article class="contact-card">
                <p class="eyebrow">Visit</p>
                <h2>Find the bakery</h2>
                <p>Address: 123 Bakery Lane, Your City</p>
                <a class="map-link" href="https://maps.google.com/?q=123+Bakery+Lane" target="_blank" rel="noopener noreferrer">Open in Google Maps <span aria-hidden="true">↗</span></a>
            </article>
            <article class="contact-card">
                <p class="eyebrow">Get in touch</p>
                <h2>We are all ears</h2>
                <p>Phone: <a href="tel:+10000000000">(000) 000-0000</a></p>
                <p>Email: <a href="mailto:hello@lucasloaves.example">hello@lucasloaves.example</a></p>
            </article>
            <article class="contact-card">
                <p class="eyebrow">Opening hours</p>
                <h2>Every single day</h2>
                <p>Monday–Sunday</p>
                <p><strong>7:00 am – 4:00 pm</strong></p>
            </article>
        </div>
    </div>
</section>
<section class="section section-cream">
    <div class="container split-layout">
        <div class="split-copy">
            <p class="eyebrow">Bread-making classes</p>
            <h2>Learn to bake at home.</h2>
            <p>Our small-group classes are a chance to get hands-on with sourdough, learn how to work with a starter, and share a few good hours around the baker's bench.</p>
            <p>Class dates and availability vary. Contact us and we will let you know what is coming up.</p>
            <a class="button" href="mailto:hello@lucasloaves.example?subject=Bread-making%20classes">Ask about a class</a>
        </div>
        <div class="split-image" role="img" aria-label="Fresh bread prepared by hand in the bakery"></div>
    </div>
</section>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
