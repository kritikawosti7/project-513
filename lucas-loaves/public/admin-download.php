<?php
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'admin_auth.php';
admin_require_auth();

$applicationId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$documentInput = $_GET['document'] ?? null;
$document = is_string($documentInput) ? $documentInput : '';

if ($applicationId === false || !in_array($document, ['resume', 'cover-letter'], true)) {
    http_response_code(404);
    exit('Document not found.');
}

try {
    $statement = db()->prepare(
        'SELECT id, cover_letter, resume_path
         FROM applications
         WHERE id = :id
         LIMIT 1'
    );
    $statement->execute(['id' => $applicationId]);
    $application = $statement->fetch();
} catch (PDOException $exception) {
    error_log('Luca\'s Loaves admin download lookup failed: ' . $exception->getMessage());
    http_response_code(503);
    exit('Document is temporarily unavailable.');
}

if ($application === false) {
    http_response_code(404);
    exit('Document not found.');
}

header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, private');

if ($document === 'cover-letter') {
    $coverLetter = (string) $application['cover_letter'];
    header('Content-Type: text/plain; charset=UTF-8');
    header('Content-Disposition: attachment; filename="application-' . $applicationId . '-cover-letter.txt"');
    header('Content-Length: ' . strlen($coverLetter));
    echo $coverLetter;
    exit;
}

$storedFilename = (string) $application['resume_path'];
if (!preg_match('/\A[a-f0-9]{48}\.(pdf|docx)\z/', $storedFilename, $matches)) {
    http_response_code(404);
    exit('Document not found.');
}

$applicationDirectory = realpath(STORAGE_ROOT . DIRECTORY_SEPARATOR . 'applications');
$resumePath = realpath(STORAGE_ROOT . DIRECTORY_SEPARATOR . 'applications' . DIRECTORY_SEPARATOR . $storedFilename);

if (
    $applicationDirectory === false
    || $resumePath === false
    || strcasecmp(dirname($resumePath), $applicationDirectory) !== 0
    || !is_file($resumePath)
    || !is_readable($resumePath)
) {
    http_response_code(404);
    exit('Document not found.');
}

$extension = $matches[1];
$mimeType = $extension === 'pdf'
    ? 'application/pdf'
    : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
$downloadName = 'application-' . $applicationId . '-resume.' . $extension;

header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . (string) filesize($resumePath));
readfile($resumePath);
exit;
