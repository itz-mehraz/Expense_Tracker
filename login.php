<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';

$next = safe_internal_path($_GET['next'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postNext = safe_internal_path($_POST['next'] ?? '');
    if ($postNext !== '') {
        $next = $postNext;
    }
}

if (isset($_SESSION['user_id'])) {
    $dest = $next !== '' ? $next : '/expense-tracker/dashboard.php';
    header('Location: ' . $dest);
    exit();
}

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '') {
        $errors[] = $lang['email_required'];
    }

    if ($password === '') {
        $errors[] = $lang['password_required'];
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                $dest = $next !== '' ? $next : '/expense-tracker/dashboard.php';
                header('Location: ' . $dest);
                exit();
            }
        }

        $errors[] = $lang['invalid_credentials'];
        $stmt->close();
    }
}

$pageTitle = $lang['login_title'];
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
                            <?php echo e($lang['login']); ?>
                        </span>
                        <h2 class="fw-bold mb-2"><?php echo e($lang['login_title']); ?></h2>
                        <p class="soft-muted mb-0">
                            <?php echo e($currentLang === 'bn'
                                ? 'আপনার অ্যাকাউন্টে প্রবেশ করে খরচ ব্যবস্থাপনা শুরু করুন।'
                                : 'Access your account and start managing your expenses.'); ?>
                        </p>
                        <?php if ($next !== ''): ?>
                            <p class="small soft-muted mt-2 mb-0"><?php echo e($lang['login_continue_hint']); ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <?php
                            echo e($_SESSION['success_message']);
                            unset($_SESSION['success_message']);
                            ?>
                        </div>
                    <?php endif; ?>

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

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <?php echo e($lang['password']); ?>
                            </label>
                            <div class="input-group password-input-wrap">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="<?php echo e($currentLang === 'bn' ? 'আপনার পাসওয়ার্ড লিখুন' : 'Enter your password'); ?>"
                                    autocomplete="current-password"
                                >
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary btn-toggle-password"
                                    data-password-toggle="password"
                                    data-label-show="<?php echo e($lang['password_show']); ?>"
                                    data-label-hide="<?php echo e($lang['password_hide']); ?>"
                                ><?php echo e($lang['password_show']); ?></button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-hero-cta w-100 py-2">
                            <?php echo e($lang['login']); ?>
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-2">
                            <?php echo e($lang['no_account']); ?>
                            <a href="/expense-tracker/signup.php<?php echo $next !== '' ? '?next=' . e(rawurlencode($next)) : ''; ?>" class="fw-semibold">
                                <?php echo e($lang['signup_here']); ?>
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