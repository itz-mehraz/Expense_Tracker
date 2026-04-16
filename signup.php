<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';

$next = safe_internal_path($_POST['next'] ?? $_GET['next'] ?? '');

if (isset($_SESSION['user_id'])) {
    $dest = $next !== '' ? $next : '/expense-tracker/dashboard.php';
    header('Location: ' . $dest);
    exit();
}

$name = '';
$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '') {
        $errors[] = $lang['name_required'];
    }

    if ($email === '') {
        $errors[] = $lang['email_required'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $lang['valid_email'];
    }

    if ($password === '') {
        $errors[] = $lang['password_required'];
    } elseif (strlen($password) < 6) {
        $errors[] = $lang['password_min'];
    }

    if ($confirmPassword === '') {
        $errors[] = $lang['confirm_password_required'];
    } elseif ($password !== $confirmPassword) {
        $errors[] = $lang['password_mismatch'];
    }

    if (empty($errors)) {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param('s', $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = $lang['email_exists'];
        }

        $checkStmt->close();
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $insertStmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $insertStmt->bind_param('sss', $name, $email, $hashedPassword);

        if ($insertStmt->execute()) {
            $_SESSION['success_message'] = $lang['registration_success'];
            $loginUrl = '/expense-tracker/login.php' . ($next !== '' ? '?next=' . rawurlencode($next) : '');
            header('Location: ' . $loginUrl);
            exit();
        } else {
            $errors[] = $lang['registration_failed'];
        }

        $insertStmt->close();
    }
}

$pageTitle = $lang['signup_title'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container page-wrapper auth-page">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5 col-xl-4">
            <div class="card border-0 shadow-lg auth-card">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <span class="badge rounded-pill hero-badge mb-3">
                            <?php echo e($lang['signup']); ?>
                        </span>
                        <h2 class="fw-bold mb-2"><?php echo e($lang['signup_title']); ?></h2>
                        <p class="soft-muted mb-0">
                            <?php echo e($currentLang === 'bn'
                                ? 'একটি নতুন অ্যাকাউন্ট তৈরি করে আপনার খরচ ট্র্যাক করা শুরু করুন।'
                                : 'Create a new account and start tracking your expenses.'); ?>
                        </p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <?php if ($next !== ''): ?>
                            <input type="hidden" name="next" value="<?php echo e($next); ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                <?php echo e($lang['name']); ?>
                            </label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control"
                                value="<?php echo e($name); ?>"
                                placeholder="<?php echo e($currentLang === 'bn' ? 'আপনার নাম লিখুন' : 'Enter your name'); ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <?php echo e($lang['email']); ?>
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="<?php echo e($email); ?>"
                                placeholder="<?php echo e($currentLang === 'bn' ? 'আপনার ইমেইল লিখুন' : 'Enter your email'); ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <?php echo e($lang['password']); ?>
                            </label>
                            <div class="input-group password-input-wrap">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="<?php echo e($currentLang === 'bn' ? 'কমপক্ষে ৬ অক্ষরের পাসওয়ার্ড' : 'At least 6 characters'); ?>"
                                    autocomplete="new-password"
                                >
                                <button type="button" class="btn btn-outline-secondary btn-toggle-password" data-password-toggle="password" data-label-show="<?php echo e($lang['password_show']); ?>" data-label-hide="<?php echo e($lang['password_hide']); ?>"><?php echo e($lang['password_show']); ?></button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">
                                <?php echo e($lang['confirm_password']); ?>
                            </label>
                            <div class="input-group password-input-wrap">
                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    class="form-control"
                                    placeholder="<?php echo e($currentLang === 'bn' ? 'আবার পাসওয়ার্ড লিখুন' : 'Enter password again'); ?>"
                                    autocomplete="new-password"
                                >
                                <button type="button" class="btn btn-outline-secondary btn-toggle-password" data-password-toggle="confirm_password" data-label-show="<?php echo e($lang['password_show']); ?>" data-label-hide="<?php echo e($lang['password_hide']); ?>"><?php echo e($lang['password_show']); ?></button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-hero-cta w-100 py-2">
                            <?php echo e($lang['create_account']); ?>
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-2">
                            <?php echo e($lang['already_have_account']); ?>
                            <a href="/expense-tracker/login.php<?php echo $next !== '' ? '?next=' . e(rawurlencode($next)) : ''; ?>" class="fw-semibold">
                                <?php echo e($lang['login_here']); ?>
                            </a>
                        </p>

                        <a href="/expense-tracker/index.php" class="soft-muted">
                            <?php echo e($lang['back_home']); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>