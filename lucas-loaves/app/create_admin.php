<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

$readInput = static function (string $prompt): string {
    fwrite(STDOUT, $prompt);
    $value = fgets(STDIN);
    return $value === false ? '' : trim($value);
};

$username = $readInput('Admin username (letters, numbers, dot, dash, underscore): ');
$password = $readInput('Admin password (at least 12 characters): ');
$passwordConfirmation = $readInput('Confirm admin password: ');

if (!preg_match('/\A[A-Za-z0-9._-]{3,80}\z/', $username)) {
    fwrite(STDERR, "Username must be 3–80 characters and use only letters, numbers, dot, dash, or underscore.\n");
    exit(1);
}

if (strlen($password) < 12 || strlen($password) > 72) {
    fwrite(STDERR, "Password must be between 12 and 72 bytes for bcrypt.\n");
    exit(1);
}

if (!hash_equals($password, $passwordConfirmation)) {
    fwrite(STDERR, "The passwords did not match.\n");
    exit(1);
}

try {
    $statement = db()->prepare(
        'INSERT INTO admin_users (username, password_hash) VALUES (:username, :password_hash)'
    );
    $statement->execute([
        'username' => $username,
        'password_hash' => password_hash($password, PASSWORD_BCRYPT),
    ]);
    fwrite(STDOUT, "Admin account created. Sign in at public/admin.php.\n");
} catch (PDOException $exception) {
    error_log('Luca\'s Loaves admin setup failed: ' . $exception->getMessage());
    fwrite(STDERR, "Could not create the admin account. Check the database connection and ensure the username is unused.\n");
    exit(1);
}
