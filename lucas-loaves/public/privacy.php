<?php
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$activePage = '';
$pageTitle = 'Privacy policy';
$pageDescription = 'How Luca’s Loaves collects, uses, stores, and protects customer and applicant information.';

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'header.php';
?>
<section class="page-heading">
    <div class="container">
        <p class="eyebrow">Your information matters</p>
        <h1>Privacy policy</h1>
        <p>This policy explains what information Luca's Loaves collects through this website and how we use it.</p>
    </div>
</section>
<section class="section">
    <article class="container policy-content">
        <p><strong>Last updated: <?= date('F Y') ?></strong></p>
        <p>Luca's Loaves uses information submitted through its order and careers forms only to provide the requested bakery service or consider an employment application. We do not sell personal information.</p>

        <h2>Information we collect</h2>
        <p>When you place a pickup order, we collect your name, email address, chosen pickup date and time, and the items in your order. A phone number is optional. When you apply for a role, we collect your name, email address, cover letter, and resume file.</p>
        <p>The website also uses a session cookie to support features such as the shopping cart and form security. It is not used for advertising.</p>

        <h2>How we use information</h2>
        <ul>
            <li>To prepare and manage bakery pickup orders and send order-related communications.</li>
            <li>To review job applications and contact applicants about recruitment.</li>
            <li>To protect forms and the website from unauthorised or fraudulent use.</li>
        </ul>

        <h2>Storage and protection</h2>
        <p>Order and application details are stored in the bakery's database. Resume uploads are stored in a private server folder outside the public website directory and are available only to authorised administrators. We use access controls and reasonable technical safeguards to protect this information.</p>

        <h2>Retention</h2>
        <p>We retain order and application information only for as long as it is needed for the purposes described above, record keeping, or applicable legal requirements. Information that is no longer needed should be securely deleted.</p>

        <h2>Sharing and external services</h2>
        <p>We do not sell your information. Information may be processed by the hosting provider operating the website, or disclosed where required by law. Links to external services such as Google Maps are governed by those services' own privacy policies.</p>

        <h2>Your choices and contact</h2>
        <p>You may contact us to request access to, correction of, or deletion of information you have provided, subject to applicable record-keeping requirements. Email <a href="mailto:hello@lucasloaves.example">hello@lucasloaves.example</a> with privacy questions.</p>

        <h2>Policy updates</h2>
        <p>We may update this policy when our website or practices change. The latest version will be published on this page.</p>
    </article>
</section>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
