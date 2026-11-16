<?php
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$activePage = 'careers';
$pageTitle = 'Careers at Luca’s Loaves';
$pageDescription = 'Apply to work with Luca’s Loaves. We welcome people who care about good bread and thoughtful service.';
$errors = [];
$fullName = '';
$email = '';
$coverLetter = '';
$successReference = $_SESSION['application_success'] ?? null;
unset($_SESSION['application_success']);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? null;
    $nameInput = $_POST['full_name'] ?? null;
    $emailInput = $_POST['email'] ?? null;
    $coverLetterInput = $_POST['cover_letter'] ?? null;
    $fullName = is_string($nameInput) ? trim($nameInput) : '';
    $email = is_string($emailInput) ? trim($emailInput) : '';
    $coverLetter = is_string($coverLetterInput) ? trim($coverLetterInput) : '';

    if (!csrf_is_valid(is_string($submittedToken) ? $submittedToken : null)) {
        $errors['form'] = 'Your form session expired. Please refresh the page and submit again.';
    }

    if ($fullName === '' || mb_strlen($fullName) > 150) {
        $errors['full_name'] = 'Enter your full name (up to 150 characters).';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        $errors['email'] = 'Enter a valid email address.';
    }

    if ($coverLetter === '' || mb_strlen($coverLetter) > 5000) {
        $errors['cover_letter'] = 'Write a cover letter (up to 5,000 characters).';
    }

    $resume = $_FILES['resume'] ?? null;
    $resumeExtension = '';
    $resumeMimeType = '';
    $resumeOriginalName = '';
    $resumeSize = 0;
    $resumeTemporaryPath = '';

    if (
        !is_array($resume)
        || !isset($resume['error'], $resume['tmp_name'], $resume['name'], $resume['size'])
        || !is_int($resume['error'])
        || !is_string($resume['tmp_name'])
        || !is_string($resume['name'])
        || !is_int($resume['size'])
    ) {
        $errors['resume'] = 'Choose a PDF or DOCX resume to upload.';
    } elseif ($resume['error'] !== UPLOAD_ERR_OK) {
        $uploadMessages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the server upload limit.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the 2 MB limit.',
            UPLOAD_ERR_PARTIAL => 'The resume upload was incomplete. Please try again.',
            UPLOAD_ERR_NO_FILE => 'Choose a PDF or DOCX resume to upload.',
            UPLOAD_ERR_NO_TMP_DIR => 'The server is not ready to receive uploads. Please contact the bakery.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not save the upload. Please try again later.',
            UPLOAD_ERR_EXTENSION => 'The server stopped the upload. Please contact the bakery.',
        ];
        $errors['resume'] = $uploadMessages[$resume['error']] ?? 'The resume could not be uploaded.';
    } else {
        $resumeExtension = strtolower(pathinfo((string) $resume['name'], PATHINFO_EXTENSION));
        $resumeSize = (int) $resume['size'];
        $resumeOriginalName = basename((string) $resume['name']);
        $resumeTemporaryPath = (string) $resume['tmp_name'];

        if (!in_array($resumeExtension, ['pdf', 'docx'], true)) {
            $errors['resume'] = 'Only PDF and DOCX resumes are accepted.';
        } elseif ($resumeSize <= 0 || $resumeSize > 2 * 1024 * 1024) {
            $errors['resume'] = 'Your resume must be no larger than 2 MB.';
        } elseif (!is_uploaded_file($resumeTemporaryPath)) {
            $errors['resume'] = 'The uploaded file could not be verified. Please try again.';
        } else {
            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            $resumeMimeType = (string) $fileInfo->file($resumeTemporaryPath);
            $fileHandle = fopen($resumeTemporaryPath, 'rb');
            $fileSignature = $fileHandle === false ? '' : (string) fread($fileHandle, 8);
            if ($fileHandle !== false) {
                fclose($fileHandle);
            }

            if ($resumeExtension === 'pdf') {
                if ($resumeMimeType !== 'application/pdf' || !str_starts_with($fileSignature, '%PDF-')) {
                    $errors['resume'] = 'The file does not appear to be a valid PDF.';
                }
            } else {
                $zipMimeTypes = [
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/zip',
                    'application/x-zip',
                    'application/x-zip-compressed',
                ];

                if (
                    !in_array($resumeMimeType, $zipMimeTypes, true)
                    || substr($fileSignature, 0, 4) !== "PK\x03\x04"
                    || !class_exists(ZipArchive::class)
                ) {
                    $errors['resume'] = 'The file does not appear to be a valid DOCX document.';
                } else {
                    $archive = new ZipArchive();
                    $opened = $archive->open($resumeTemporaryPath);
                    $isDocx = $opened === true
                        && $archive->locateName('[Content_Types].xml') !== false
                        && $archive->locateName('word/document.xml') !== false;
                    if ($opened === true) {
                        $archive->close();
                    }

                    if (!$isDocx) {
                        $errors['resume'] = 'The file does not appear to be a valid DOCX document.';
                    }
                }
            }
        }
    }

    if ($errors === []) {
        $applicationDirectory = STORAGE_ROOT . DIRECTORY_SEPARATOR . 'applications';
        if (!is_dir($applicationDirectory) || !is_writable($applicationDirectory)) {
            $errors['form'] = 'The private application storage is not available. Please contact the bakery.';
        } else {
            $storedFilename = bin2hex(random_bytes(24)) . '.' . $resumeExtension;
            $storedPath = $applicationDirectory . DIRECTORY_SEPARATOR . $storedFilename;

            if (!move_uploaded_file($resumeTemporaryPath, $storedPath)) {
                $errors['form'] = 'Your resume could not be saved. Please try again later.';
            } else {
                try {
                    $statement = db()->prepare(
                        'INSERT INTO applications
                            (full_name, email, cover_letter, resume_path, resume_original_name, resume_mime_type, resume_size)
                         VALUES
                            (:full_name, :email, :cover_letter, :resume_path, :resume_original_name, :resume_mime_type, :resume_size)'
                    );
                    $statement->execute([
                        'full_name' => $fullName,
                        'email' => $email,
                        'cover_letter' => $coverLetter,
                        'resume_path' => $storedFilename,
                        'resume_original_name' => $resumeOriginalName,
                        'resume_mime_type' => $resumeMimeType,
                        'resume_size' => $resumeSize,
                    ]);

                    $_SESSION['application_success'] = (int) db()->lastInsertId();
                    header('Location: careers.php', true, 303);
                    exit;
                } catch (PDOException $exception) {
                    if (is_file($storedPath) && !unlink($storedPath)) {
                        error_log('Luca\'s Loaves could not remove an orphaned resume upload: ' . $storedPath);
                    }
                    error_log('Luca\'s Loaves application save failed: ' . $exception->getMessage());
                    $errors['form'] = 'We could not save your application just now. Please try again later.';
                }
            }
        }
    }
}

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'header.php';
?>
<section class="page-heading">
    <div class="container">
        <p class="eyebrow">Come make good things with us</p>
        <h1>Careers</h1>
        <p>We are always glad to hear from thoughtful, curious people who care about good food and good work.</p>
    </div>
</section>
<section class="section">
    <div class="container split-layout">
        <div class="split-copy">
            <p class="eyebrow">An open door</p>
            <h2>There is room at our table.</h2>
            <p>Our bakery grows one loaf and one good conversation at a time. We are continually interested in meeting bakers, eager learners, and warm, reliable people who enjoy making a neighbourhood feel welcome.</p>
            <p>Tell us a little about yourself and the kind of work you would love to do. We will keep your application private and get in touch if a suitable opportunity comes up.</p>
            <p>Applications are reviewed by the Luca's Loaves team. Please send a PDF or DOCX resume of no more than 2 MB.</p>
        </div>
        <div class="split-image" role="img" aria-label="Baker preparing fresh dough in a bakery"></div>
    </div>
</section>
<section class="section section-cream">
    <div class="container form-layout">
        <div class="form-intro">
            <p class="eyebrow">Start a conversation</p>
            <h2>Apply to join our team.</h2>
            <p>Fields marked <span aria-hidden="true">*</span> are required. Your resume is stored privately outside the public website.</p>
        </div>
        <div class="form-panel">
            <?php if (is_int($successReference) && $successReference > 0): ?>
                <div class="notice notice-success" role="status">
                    <h2>Thank you for applying.</h2>
                    <p>Your application has been received. Your reference number is <strong>#<?= e($successReference) ?></strong>. We will be in touch if there is a suitable opportunity.</p>
                </div>
            <?php else: ?>
                <?php if (isset($errors['form'])): ?>
                    <div class="notice notice-error" role="alert"><?= e($errors['form']) ?></div>
                <?php endif; ?>
                <form method="post" action="careers.php" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="form-field">
                        <label for="full_name">Full name <span aria-hidden="true">*</span></label>
                        <input
                            id="full_name"
                            name="full_name"
                            type="text"
                            autocomplete="name"
                            maxlength="150"
                            required
                            value="<?= e($fullName) ?>"
                            aria-invalid="<?= isset($errors['full_name']) ? 'true' : 'false' ?>"
                            aria-describedby="<?= isset($errors['full_name']) ? 'full_name_error' : '' ?>"
                        >
                        <?php if (isset($errors['full_name'])): ?><p class="field-error" id="full_name_error"><?= e($errors['full_name']) ?></p><?php endif; ?>
                    </div>
                    <div class="form-field">
                        <label for="email">Email address <span aria-hidden="true">*</span></label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            maxlength="254"
                            required
                            value="<?= e($email) ?>"
                            aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>"
                            aria-describedby="<?= isset($errors['email']) ? 'email_error' : '' ?>"
                        >
                        <?php if (isset($errors['email'])): ?><p class="field-error" id="email_error"><?= e($errors['email']) ?></p><?php endif; ?>
                    </div>
                    <div class="form-field">
                        <label for="cover_letter">Cover letter <span aria-hidden="true">*</span></label>
                        <textarea
                            id="cover_letter"
                            name="cover_letter"
                            rows="7"
                            maxlength="5000"
                            required
                            aria-invalid="<?= isset($errors['cover_letter']) ? 'true' : 'false' ?>"
                            aria-describedby="<?= isset($errors['cover_letter']) ? 'cover_letter_error' : 'cover_letter_hint' ?>"
                        ><?= e($coverLetter) ?></textarea>
                        <?php if (isset($errors['cover_letter'])): ?>
                            <p class="field-error" id="cover_letter_error"><?= e($errors['cover_letter']) ?></p>
                        <?php else: ?>
                            <p class="field-hint" id="cover_letter_hint">Up to 5,000 characters.</p>
                        <?php endif; ?>
                    </div>
                    <div class="form-field">
                        <label for="resume">Resume (PDF or DOCX) <span aria-hidden="true">*</span></label>
                        <input
                            id="resume"
                            name="resume"
                            type="file"
                            accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            required
                            aria-invalid="<?= isset($errors['resume']) ? 'true' : 'false' ?>"
                            aria-describedby="<?= isset($errors['resume']) ? 'resume_error' : 'resume_hint' ?>"
                        >
                        <?php if (isset($errors['resume'])): ?>
                            <p class="field-error" id="resume_error"><?= e($errors['resume']) ?></p>
                        <?php else: ?>
                            <p class="field-hint" id="resume_hint">Maximum file size: 2 MB.</p>
                        <?php endif; ?>
                    </div>
                    <button class="button" type="submit">Send application</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
