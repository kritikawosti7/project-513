<?php
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'admin_auth.php';
admin_require_auth();

$orderId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($orderId === false) {
    http_response_code(404);
    exit('Order not found.');
}

try {
    $orderStatement = db()->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
    $orderStatement->execute(['id' => $orderId]);
    $order = $orderStatement->fetch();

    if ($order === false) {
        http_response_code(404);
        exit('Order not found.');
    }

    $itemsStatement = db()->prepare(
        'SELECT product_name, unit_price, quantity, line_total
         FROM order_items
         WHERE order_id = :order_id
         ORDER BY id'
    );
    $itemsStatement->execute(['order_id' => $orderId]);
    $items = $itemsStatement->fetchAll();
} catch (PDOException $exception) {
    error_log('Luca\'s Loaves admin order lookup failed: ' . $exception->getMessage());
    http_response_code(503);
    exit('Order details are temporarily unavailable.');
}

$activePage = '';
$pageTitle = 'Order details';
$pageDescription = 'Restricted order details for Luca’s Loaves staff.';
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'header.php';
?>
<section class="page-heading">
    <div class="container">
        <p class="eyebrow">Staff view</p>
        <h1>Order details</h1>
        <p><?= e($order['order_number']) ?> · placed <?= e(date('M j, Y g:i a', strtotime((string) $order['created_at']))) ?></p>
    </div>
</section>
<section class="section">
    <div class="container admin-order-layout">
        <div class="cart-panel">
            <p class="eyebrow">Items ordered</p>
            <h2><?= e($order['order_number']) ?></h2>
            <div class="admin-order-items">
                <?php foreach ($items as $item): ?>
                    <div class="admin-order-item">
                        <span><?= e($item['product_name']) ?> × <?= e($item['quantity']) ?></span>
                        <strong>$<?= e(number_format((float) $item['line_total'], 2)) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="cart-total">
                <span>Order total</span>
                <strong>$<?= e(number_format((float) $order['total'], 2)) ?></strong>
            </div>
        </div>
        <aside class="checkout-panel">
            <p class="eyebrow">Customer &amp; pickup</p>
            <h2><?= e($order['customer_name']) ?></h2>
            <p><strong>Email</strong><br><a href="mailto:<?= e($order['email']) ?>"><?= e($order['email']) ?></a></p>
            <?php if ($order['phone'] !== null && $order['phone'] !== ''): ?>
                <p><strong>Phone</strong><br><?= e($order['phone']) ?></p>
            <?php endif; ?>
            <p><strong>Pickup</strong><br><?= e($order['pickup_date']) ?> at <?= e(date('g:i a', strtotime((string) $order['pickup_time']))) ?></p>
            <p><strong>Status</strong><br><span class="status-pill"><?= e(ucfirst((string) $order['status'])) ?></span></p>
            <a class="button button-outline" href="admin.php">Back to dashboard</a>
        </aside>
    </div>
</section>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
