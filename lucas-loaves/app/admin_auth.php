<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

function admin_is_authenticated(): bool
{
    if (
        !isset($_SESSION['admin_user_id'], $_SESSION['admin_last_activity'])
        || !is_int($_SESSION['admin_user_id'])
        || !is_int($_SESSION['admin_last_activity'])
    ) {
        return false;
    }

    if (time() - $_SESSION['admin_last_activity'] >= 1800) {
        unset($_SESSION['admin_user_id'], $_SESSION['admin_username'], $_SESSION['admin_last_activity']);
        session_regenerate_id(true);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return false;
    }

    $_SESSION['admin_last_activity'] = time();
    return true;
}

function admin_require_auth(): void
{
    $hadAdminSession = isset($_SESSION['admin_user_id']);

    if (!admin_is_authenticated()) {
        $reason = $hadAdminSession ? 'expired=1' : 'required=1';
        header('Location: admin.php?' . $reason, true, 303);
        exit;
    }
}
