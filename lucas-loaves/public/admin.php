<?php
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'admin_auth.php';

$activePage = '';
$pageTitle = 'Admin dashboard';
$pageDescription = 'Restricted Luca’s Loaves administration area.';
$loginError = null;
$dashboardError = null;
$isAdmin = admin_is_authenticated();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !$isAdmin) {
    $submittedToken = $_POST['csrf_token'] ?? null;
    $usernameInput = $_POST['username'] ?? null;
    $passwordInput = $_POST['password'] ?? null;
    $username = is_string($usernameInput) ? trim($usernameInput) : '';
    $password = is_string($passwordInput) ? $passwordInput : '';

    if (!csrf_is_valid(is_string($submittedToken) ? $submittedToken : null)) {
        $loginError = 'Your sign-in session expired. Refresh the page and try again.';
    } elseif (
        !preg_match('/\A[A-Za-z0-9._-]{3,80}\z/', $username)
        || $password === ''
        || strlen($password) > 72
    ) {
        $loginError = 'Enter your username and password.';
    } else {
        try {
            $statement = db()->prepare(
                'SELECT id, username, password_hash FROM admin_users WHERE username = :username LIMIT 1'
            );
            $statement->execute(['username' => $username]);
            $admin = $statement->fetch();

            if ($admin === false || !password_verify($password, (string) $admin['password_hash'])) {
                $loginError = 'The username or password was not recognised.';
            } else {
                $updateLogin = db()->prepare(
                    'UPDATE admin_users SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id'
                );
                $updateLogin->execute(['id' => (int) $admin['id']]);

                session_regenerate_id(true);
                unset($_SESSION['csrf_token']);
                csrf_token();
                $_SESSION['admin_user_id'] = (int) $admin['id'];
                $_SESSION['admin_username'] = (string) $admin['username'];
                $_SESSION['admin_last_activity'] = time();
                header('Location: admin.php', true, 303);
                exit;
            }
        } catch (PDOException $exception) {
            error_log('Luca\'s Loaves admin sign-in failed: ' . $exception->getMessage());
            $loginError = 'Admin sign-in is temporarily unavailable. Please try again later.';
        }
    }
}

$isAdmin = admin_is_authenticated();
$orders = [];
$applications = [];

if ($isAdmin) {
    try {
        $orders = db()->query(
            'SELECT id, order_number, customer_name, email, pickup_date, pickup_time, total, status, created_at
             FROM orders
             ORDER BY created_at DESC
             LIMIT 100'
        )->fetchAll();
        $applications = db()->query(
            'SELECT id, full_name, email, cover_letter, resume_original_name, status, submitted_at
             FROM applications
             ORDER BY submitted_at DESC
             LIMIT 100'
        )->fetchAll();
    } catch (PDOException $exception) {
        error_log('Luca\'s Loaves admin dashboard query failed: ' . $exception->getMessage());
        $dashboardError = 'The dashboard records could not be loaded. Please try again later.';
    }
}

$expired = isset($_GET['expired']) && $_GET['expired'] === '1';
$loginRequired = isset($_GET['required']) && $_GET['required'] === '1';
$loggedOut = isset($_GET['logged_out']) && $_GET['logged_out'] === '1';

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'header.php';
?>
<?php if (!$isAdmin): ?>
    <section class="page-heading">
        <div class="container">
            <p class="eyebrow">Staff access</p>
            <h1>Admin sign in</h1>
            <p>This area is for authorised Luca's Loaves staff only.</p>
        </div>
    </section>
    <section class="section">
        <div class="container admin-login-wrap">
            <div class="admin-login-card">
                <h2>Welcome back.</h2>
                <?php if ($expired): ?>
                    <p class="notice notice-error" role="status">Your session ended after 30 minutes of inactivity. Please sign in again.</p>
                <?php endif; ?>
                <?php if ($loginRequired): ?>
                    <p class="notice notice-error" role="status">Sign in to access the staff dashboard.</p>
                <?php endif; ?>
                <?php if ($loggedOut): ?>
                    <p class="notice notice-success" role="status">You have been securely logged out.</p>
                <?php endif; ?>
                <?php if ($loginError !== null): ?>
                    <p class="notice notice-error" role="alert"><?= e($loginError) ?></p>
                <?php endif; ?>
                <form method="post" action="admin.php">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="form-field">
                        <label for="username">Username</label>
                        <input id="username" name="username" type="text" autocomplete="username" maxlength="80" required>
                    </div>
                    <div class="form-field">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" maxlength="200" required>
                    </div>
                    <button class="button button-full" type="submit">Sign in securely</button>
                </form>
            </div>
        </div>
    </section>
<?php else: ?>
    <section class="page-heading admin-heading">
        <div class="container admin-heading-inner">
            <div>
                <p class="eyebrow">Bakery operations</p>
                <h1>Admin dashboard</h1>
                <p>Signed in as <?= e($_SESSION['admin_username'] ?? 'admin') ?>. For your security, you will be signed out after 30 minutes without activity.</p>
            </div>
            <form method="post" action="admin-logout.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button class="button button-outline" type="submit">Log out</button>
            </form>
        </div>
    </section>
    <section class="section admin-section">
        <div class="container">
            <?php if ($dashboardError !== null): ?>
                <p class="notice notice-error" role="alert"><?= e($dashboardError) ?></p>
            <?php else: ?>
                <div class="admin-section-heading">
                    <div>
                        <p class="eyebrow">Latest activity</p>
                        <h2>Customer orders</h2>
                    </div>
                    <span class="admin-count"><?= e(count($orders)) ?> shown · up to 100</span>
                </div>
                <?php if ($orders === []): ?>
                    <p class="empty-state">No customer orders have been placed yet.</p>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th scope="col">Order</th>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Pickup</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Status</th>
                                    <th scope="col"><span class="visually-hidden">Details</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td data-label="Order"><?= e($order['order_number']) ?></td>
                                        <td data-label="Customer"><?= e($order['customer_name']) ?><span class="table-subtext"><?= e($order['email']) ?></span></td>
                                        <td data-label="Pickup"><?= e($order['pickup_date']) ?><span class="table-subtext"><?= e(date('g:i a', strtotime((string) $order['pickup_time']))) ?></span></td>
                                        <td data-label="Total">$<?= e(number_format((float) $order['total'], 2)) ?></td>
                                        <td data-label="Status"><span class="status-pill"><?= e(ucfirst((string) $order['status'])) ?></span></td>
                                        <td data-label="Details"><a class="table-link" href="admin-order.php?id=<?= e($order['id']) ?>">View order</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="admin-section-heading admin-applications-heading">
                    <div>
                        <p class="eyebrow">People who reached out</p>
                        <h2>Job applications</h2>
                    </div>
                    <span class="admin-count"><?= e(count($applications)) ?> shown · up to 100</span>
                </div>
                <?php if ($applications === []): ?>
                    <p class="empty-state">No job applications have been received yet.</p>
                <?php else: ?>
                    <div class="application-list">
                        <?php foreach ($applications as $application): ?>
                            <article class="application-card">
                                <div class="application-card-heading">
                                    <div>
                                        <h3><?= e($application['full_name']) ?></h3>
                                        <a href="mailto:<?= e($application['email']) ?>"><?= e($application['email']) ?></a>
                                    </div>
                                    <div class="application-meta">
                                        <span class="status-pill"><?= e(ucfirst((string) $application['status'])) ?></span>
                                        <time datetime="<?= e($application['submitted_at']) ?>"><?= e(date('M j, Y', strtotime((string) $application['submitted_at']))) ?></time>
                                    </div>
                                </div>
                                <details class="cover-letter-details">
                                    <summary>Read cover letter</summary>
                                    <p><?= nl2br(e($application['cover_letter'])) ?></p>
                                </details>
                                <div class="application-downloads">
                                    <a class="table-link" href="admin-download.php?document=resume&amp;id=<?= e($application['id']) ?>">Download resume</a>
                                    <a class="table-link" href="admin-download.php?document=cover-letter&amp;id=<?= e($application['id']) ?>">Download cover letter</a>
                                    <span class="table-subtext"><?= e($application['resume_original_name']) ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
