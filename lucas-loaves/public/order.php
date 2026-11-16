<?php
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$activePage = 'order';
$pageTitle = 'Order bread online';
$pageDescription = 'Choose artisan sourdough and arrange a convenient bakery pickup from Luca’s Loaves.';
$errors = [];
$orderError = null;
$customerName = '';
$customerEmail = '';
$customerPhone = '';
$pickupDate = '';
$pickupTime = '';
$orderConfirmation = $_SESSION['order_confirmation'] ?? null;
unset($_SESSION['order_confirmation']);
$cartMessage = $_SESSION['cart_message'] ?? null;
unset($_SESSION['cart_message']);

$cart = [];
$savedCart = $_SESSION['cart'] ?? [];
if (is_array($savedCart)) {
    foreach ($savedCart as $productId => $quantity) {
        $validatedId = filter_var((string) $productId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (
            $validatedId !== false
            && is_int($quantity)
            && $quantity >= 1
            && $quantity <= 99
        ) {
            $cart[(int) $validatedId] = $quantity;
        }
    }
}
$_SESSION['cart'] = $cart;

$pickupTimes = [];
for ($minutes = 7 * 60; $minutes <= (15 * 60) + 30; $minutes += 30) {
    $pickupTimes[] = sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
}

$moneyFromCents = static function (int $cents): string {
    return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
};

$priceToCents = static function (string $price): int {
    $parts = explode('.', $price, 2);
    $whole = (int) $parts[0];
    $fraction = (int) str_pad(substr($parts[1] ?? '0', 0, 2), 2, '0');
    return ($whole * 100) + $fraction;
};

$redirectToOrder = static function (): never {
    header('Location: order.php', true, 303);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? null;
    if (!csrf_is_valid(is_string($submittedToken) ? $submittedToken : null)) {
        $errors['form'] = 'Your form session expired. Please refresh the page and submit again.';
    } else {
        $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';

        try {
            if ($action === 'add') {
                $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]);

                if ($productId === false || $quantity === false) {
                    $errors['form'] = 'Choose a valid product and quantity.';
                } else {
                    $statement = db()->prepare(
                        'SELECT id FROM products WHERE id = :id AND is_active = 1'
                    );
                    $statement->execute(['id' => $productId]);

                    if ($statement->fetchColumn() === false) {
                        $errors['form'] = 'That loaf is no longer available.';
                    } elseif (($cart[$productId] ?? 0) + $quantity > 99) {
                        $errors['form'] = 'The maximum quantity for each loaf is 99.';
                    } else {
                        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
                        $_SESSION['cart'] = $cart;
                        $_SESSION['cart_message'] = 'Added to your basket.';
                        $redirectToOrder();
                    }
                }
            } elseif ($action === 'update') {
                $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]);

                if ($productId === false || $quantity === false || !isset($cart[$productId])) {
                    $errors['form'] = 'Choose a valid basket item and quantity.';
                } else {
                    $cart[$productId] = $quantity;
                    $_SESSION['cart'] = $cart;
                    $_SESSION['cart_message'] = 'Your basket has been updated.';
                    $redirectToOrder();
                }
            } elseif ($action === 'remove') {
                $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                if ($productId === false || !isset($cart[$productId])) {
                    $errors['form'] = 'That basket item could not be found.';
                } else {
                    unset($cart[$productId]);
                    $_SESSION['cart'] = $cart;
                    $_SESSION['cart_message'] = 'The item has been removed.';
                    $redirectToOrder();
                }
            } elseif ($action === 'checkout') {
                $nameInput = $_POST['customer_name'] ?? null;
                $emailInput = $_POST['email'] ?? null;
                $phoneInput = $_POST['phone'] ?? null;
                $dateInput = $_POST['pickup_date'] ?? null;
                $timeInput = $_POST['pickup_time'] ?? null;
                $customerName = is_string($nameInput) ? trim($nameInput) : '';
                $customerEmail = is_string($emailInput) ? trim($emailInput) : '';
                $customerPhone = is_string($phoneInput) ? trim($phoneInput) : '';
                $pickupDate = is_string($dateInput) ? trim($dateInput) : '';
                $pickupTime = is_string($timeInput) ? trim($timeInput) : '';

                if ($customerName === '' || mb_strlen($customerName) > 150) {
                    $errors['customer_name'] = 'Enter your name (up to 150 characters).';
                }
                if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL) || strlen($customerEmail) > 254) {
                    $errors['email'] = 'Enter a valid email address.';
                }
                if (mb_strlen($customerPhone) > 40) {
                    $errors['phone'] = 'Phone number must be 40 characters or fewer.';
                }
                $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $pickupDate);
                $dateErrors = DateTimeImmutable::getLastErrors();
                if (
                    $parsedDate === false
                    || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
                    || $parsedDate->format('Y-m-d') !== $pickupDate
                    || $pickupDate < (new DateTimeImmutable('today'))->format('Y-m-d')
                ) {
                    $errors['pickup_date'] = 'Choose today or a later pickup date.';
                }
                if (!in_array($pickupTime, $pickupTimes, true)) {
                    $errors['pickup_time'] = 'Choose a pickup time from 7:00 am to 3:30 pm.';
                }
                if ($cart === []) {
                    $errors['form'] = 'Add at least one loaf to your basket before checking out.';
                }

                if ($errors === []) {
                    $connection = db();
                    $connection->beginTransaction();

                    $ids = array_keys($cart);
                    $placeholders = [];
                    $parameters = [];
                    foreach ($ids as $index => $id) {
                        $placeholder = ':product_' . $index;
                        $placeholders[] = $placeholder;
                        $parameters[$placeholder] = $id;
                    }

                    $productQuery = $connection->prepare(
                        'SELECT id, name, price
                         FROM products
                         WHERE is_active = 1 AND id IN (' . implode(', ', $placeholders) . ')
                         FOR UPDATE'
                    );
                    $productQuery->execute($parameters);
                    $orderProducts = $productQuery->fetchAll();

                    if (count($orderProducts) !== count($cart)) {
                        $connection->rollBack();
                        $errors['form'] = 'One or more basket items are no longer available. Review your basket and try again.';
                    } else {
                        $subtotalCents = 0;
                        $orderLines = [];
                        foreach ($orderProducts as $product) {
                            $productId = (int) $product['id'];
                            $quantity = $cart[$productId];
                            $unitPriceCents = $priceToCents((string) $product['price']);
                            $lineTotalCents = $unitPriceCents * $quantity;
                            $subtotalCents += $lineTotalCents;
                            $orderLines[] = [
                                'product_id' => $productId,
                                'name' => (string) $product['name'],
                                'unit_price' => $moneyFromCents($unitPriceCents),
                                'quantity' => $quantity,
                                'line_total' => $moneyFromCents($lineTotalCents),
                            ];
                        }

                        $orderNumber = 'LL-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(8)));
                        $orderStatement = $connection->prepare(
                            'INSERT INTO orders
                                (order_number, customer_name, email, phone, pickup_date, pickup_time, subtotal, total)
                             VALUES
                                (:order_number, :customer_name, :email, :phone, :pickup_date, :pickup_time, :subtotal, :total)'
                        );
                        $orderStatement->execute([
                            'order_number' => $orderNumber,
                            'customer_name' => $customerName,
                            'email' => $customerEmail,
                            'phone' => $customerPhone === '' ? null : $customerPhone,
                            'pickup_date' => $pickupDate,
                            'pickup_time' => $pickupTime . ':00',
                            'subtotal' => $moneyFromCents($subtotalCents),
                            'total' => $moneyFromCents($subtotalCents),
                        ]);
                        $orderId = (int) $connection->lastInsertId();

                        $itemStatement = $connection->prepare(
                            'INSERT INTO order_items
                                (order_id, product_id, product_name, unit_price, quantity, line_total)
                             VALUES
                                (:order_id, :product_id, :product_name, :unit_price, :quantity, :line_total)'
                        );
                        foreach ($orderLines as $line) {
                            $itemStatement->execute([
                                'order_id' => $orderId,
                                'product_id' => $line['product_id'],
                                'product_name' => $line['name'],
                                'unit_price' => $line['unit_price'],
                                'quantity' => $line['quantity'],
                                'line_total' => $line['line_total'],
                            ]);
                        }

                        $connection->commit();
                        $_SESSION['order_confirmation'] = [
                            'number' => $orderNumber,
                            'total' => $moneyFromCents($subtotalCents),
                        ];
                        $_SESSION['cart'] = [];
                        $redirectToOrder();
                    }
                }
            } else {
                $errors['form'] = 'Choose a valid basket action.';
            }
        } catch (PDOException $exception) {
            if (isset($connection) && $connection instanceof PDO && $connection->inTransaction()) {
                $connection->rollBack();
            }
            error_log('Luca\'s Loaves order operation failed: ' . $exception->getMessage());
            $orderError = 'We could not complete that request. Please try again in a moment.';
        }
    }
}

$products = [];
$cartItems = [];
$cartSubtotalCents = 0;
try {
    $products = db()->query(
        'SELECT id, name, slug, description, price FROM products WHERE is_active = 1 ORDER BY id'
    )->fetchAll();
    $productsById = [];
    foreach ($products as $product) {
        $productsById[(int) $product['id']] = $product;
    }

    foreach ($cart as $productId => $quantity) {
        if (!isset($productsById[$productId])) {
            unset($cart[$productId]);
            continue;
        }

        $product = $productsById[$productId];
        $unitPriceCents = $priceToCents((string) $product['price']);
        $lineTotalCents = $unitPriceCents * $quantity;
        $cartSubtotalCents += $lineTotalCents;
        $cartItems[] = [
            'id' => $productId,
            'name' => (string) $product['name'],
            'price' => $moneyFromCents($unitPriceCents),
            'quantity' => $quantity,
            'line_total' => $moneyFromCents($lineTotalCents),
        ];
    }
    $_SESSION['cart'] = $cart;
} catch (PDOException $exception) {
    error_log('Luca\'s Loaves order catalogue failed: ' . $exception->getMessage());
    $orderError = 'The bread catalogue is temporarily unavailable. Please try again later.';
}

$productImages = [
    'white-loaf' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=700&q=80',
    'rye-loaf' => 'https://images.unsplash.com/photo-1585478259715-876acc5be8eb?auto=format&fit=crop&w=700&q=80',
    'spelt-loaf' => 'https://images.unsplash.com/photo-1534620808146-d33bb39128b2?auto=format&fit=crop&w=700&q=80',
    'seeded-loaf' => 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?auto=format&fit=crop&w=700&q=80',
];

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'header.php';
?>
<section class="page-heading">
    <div class="container">
        <p class="eyebrow">Baked fresh, picked up by you</p>
        <h1>Order online</h1>
        <p>Choose your loaves, select a pickup time, and we will have your order ready at the bakery.</p>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php if (is_array($orderConfirmation) && isset($orderConfirmation['number'], $orderConfirmation['total'])): ?>
            <div class="notice notice-success order-confirmation" role="status">
                <h2>Your order is in the oven queue.</h2>
                <p>Order <strong><?= e($orderConfirmation['number']) ?></strong> is confirmed. Your total is <strong>$<?= e($orderConfirmation['total']) ?></strong>. Please keep your order number handy for pickup.</p>
            </div>
        <?php endif; ?>
        <?php if ($cartMessage !== null): ?>
            <p class="notice notice-success" role="status"><?= e($cartMessage) ?></p>
        <?php endif; ?>
        <?php if ($orderError !== null): ?>
            <p class="notice notice-error" role="alert"><?= e($orderError) ?></p>
        <?php endif; ?>
        <?php if (isset($errors['form'])): ?>
            <p class="notice notice-error" role="alert"><?= e($errors['form']) ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="section section-cream order-products">
    <div class="container">
        <div class="section-heading">
            <p class="eyebrow">A loaf for every table</p>
            <h2>Choose your bread</h2>
        </div>
        <div class="product-grid">
            <?php if ($products === []): ?>
                <p class="empty-state">No loaves are available right now. Please check back soon.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <?php $imageUrl = $productImages[$product['slug']] ?? $productImages['white-loaf']; ?>
                    <article class="product-card">
                        <img class="product-image" src="<?= e($imageUrl) ?>" alt="<?= e($product['name']) ?> artisan sourdough loaf" loading="lazy">
                        <div class="product-card-body">
                            <h3><?= e($product['name']) ?></h3>
                            <p class="product-description"><?= e($product['description']) ?></p>
                            <p class="product-price">$<?= e(number_format((float) $product['price'], 2)) ?></p>
                            <form class="add-to-cart-form" method="post" action="order.php">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
                                <label for="quantity_<?= e($product['id']) ?>">Quantity</label>
                                <input id="quantity_<?= e($product['id']) ?>" name="quantity" type="number" min="1" max="99" value="1" required>
                                <button class="button" type="submit">Add to basket</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container order-layout">
        <div class="cart-panel">
            <p class="eyebrow">Your selection</p>
            <h2>Your basket</h2>
            <?php if ($cartItems === []): ?>
                <p class="cart-empty">Your basket is empty. Choose a loaf above to get started.</p>
            <?php else: ?>
                <div class="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                        <article class="cart-item">
                            <div class="cart-item-details">
                                <h3><?= e($item['name']) ?></h3>
                                <p>$<?= e($item['price']) ?> each · Line total: <strong>$<?= e($item['line_total']) ?></strong></p>
                            </div>
                            <div class="cart-item-actions">
                                <form method="post" action="order.php" class="quantity-update-form">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?= e($item['id']) ?>">
                                    <label class="visually-hidden" for="cart_quantity_<?= e($item['id']) ?>">Quantity of <?= e($item['name']) ?></label>
                                    <input id="cart_quantity_<?= e($item['id']) ?>" name="quantity" type="number" min="1" max="99" value="<?= e($item['quantity']) ?>" required>
                                    <button class="button button-outline button-small" type="submit">Update</button>
                                </form>
                                <form method="post" action="order.php">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= e($item['id']) ?>">
                                    <button class="remove-link" type="submit">Remove</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="cart-total">
                    <span>Subtotal &amp; total</span>
                    <strong>$<?= e($moneyFromCents($cartSubtotalCents)) ?></strong>
                </div>
                <p class="cart-note">Pickup is free. No payment is collected on this website; pay at the bakery.</p>
            <?php endif; ?>
        </div>

        <div class="checkout-panel">
            <p class="eyebrow">One last step</p>
            <h2>Pickup details</h2>
            <?php if ($cartItems === []): ?>
                <p>Add a loaf to your basket to continue to checkout.</p>
            <?php else: ?>
                <?php if ($errors !== [] && !isset($errors['form'])): ?>
                    <div class="notice notice-error" role="alert">Please review the highlighted fields.</div>
                <?php endif; ?>
                <form method="post" action="order.php">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="checkout">
                    <div class="form-field">
                        <label for="customer_name">Customer name <span aria-hidden="true">*</span></label>
                        <input id="customer_name" name="customer_name" type="text" autocomplete="name" maxlength="150" required value="<?= e($customerName) ?>" aria-invalid="<?= isset($errors['customer_name']) ? 'true' : 'false' ?>" aria-describedby="<?= isset($errors['customer_name']) ? 'customer_name_error' : '' ?>">
                        <?php if (isset($errors['customer_name'])): ?><p class="field-error" id="customer_name_error"><?= e($errors['customer_name']) ?></p><?php endif; ?>
                    </div>
                    <div class="form-field">
                        <label for="checkout_email">Email address <span aria-hidden="true">*</span></label>
                        <input id="checkout_email" name="email" type="email" autocomplete="email" maxlength="254" required value="<?= e($customerEmail) ?>" aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>" aria-describedby="<?= isset($errors['email']) ? 'checkout_email_error' : '' ?>">
                        <?php if (isset($errors['email'])): ?><p class="field-error" id="checkout_email_error"><?= e($errors['email']) ?></p><?php endif; ?>
                    </div>
                    <div class="form-field">
                        <label for="phone">Phone number <span class="optional">(optional)</span></label>
                        <input id="phone" name="phone" type="tel" autocomplete="tel" maxlength="40" value="<?= e($customerPhone) ?>" aria-invalid="<?= isset($errors['phone']) ? 'true' : 'false' ?>" aria-describedby="<?= isset($errors['phone']) ? 'phone_error' : '' ?>">
                        <?php if (isset($errors['phone'])): ?><p class="field-error" id="phone_error"><?= e($errors['phone']) ?></p><?php endif; ?>
                    </div>
                    <div class="form-field">
                        <label for="pickup_date">Pickup date <span aria-hidden="true">*</span></label>
                        <input id="pickup_date" name="pickup_date" type="date" min="<?= e(date('Y-m-d')) ?>" required value="<?= e($pickupDate) ?>" aria-invalid="<?= isset($errors['pickup_date']) ? 'true' : 'false' ?>" aria-describedby="<?= isset($errors['pickup_date']) ? 'pickup_date_error' : '' ?>">
                        <?php if (isset($errors['pickup_date'])): ?><p class="field-error" id="pickup_date_error"><?= e($errors['pickup_date']) ?></p><?php endif; ?>
                    </div>
                    <div class="form-field">
                        <label for="pickup_time">Pickup time <span aria-hidden="true">*</span></label>
                        <select id="pickup_time" name="pickup_time" required aria-invalid="<?= isset($errors['pickup_time']) ? 'true' : 'false' ?>" aria-describedby="<?= isset($errors['pickup_time']) ? 'pickup_time_error' : '' ?>">
                            <option value="">Choose a time</option>
                            <?php foreach ($pickupTimes as $time): ?>
                                <?php $timeLabel = date('g:i a', strtotime($time)); ?>
                                <option value="<?= e($time) ?>"<?= $pickupTime === $time ? ' selected' : '' ?>><?= e($timeLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['pickup_time'])): ?><p class="field-error" id="pickup_time_error"><?= e($errors['pickup_time']) ?></p><?php endif; ?>
                    </div>
                    <button class="button button-full" type="submit">Place pickup order · $<?= e($moneyFromCents($cartSubtotalCents)) ?></button>
                    <p class="checkout-note">Your order total is recalculated from current bakery prices before it is saved.</p>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
