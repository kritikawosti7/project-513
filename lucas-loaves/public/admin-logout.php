<?php
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'admin_auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: admin.php', true, 303);
    exit;
}

admin_require_auth();
$submittedToken = $_POST['csrf_token'] ?? null;

if (!csrf_is_valid(is_string($submittedToken) ? $submittedToken : null)) {
    http_response_code(403);
    exit('Invalid request token. Return to the admin page and try again.');
}

unset($_SESSION['admin_user_id'], $_SESSION['admin_username'], $_SESSION['admin_last_activity']);
session_regenerate_id(true);
unset($_SESSION['csrf_token']);
csrf_token();
header('Location: admin.php?logged_out=1', true, 303);
exit;
