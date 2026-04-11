<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/app.php';

if (isset($_SESSION['user_id'])) {
    header('Location: /expense-tracker/dashboard.php');
    exit();
}

$pageTitle = $lang['app_name'] . ' | ' . $lang['home_title'];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg overflow-hidden home-hero-card">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-7 text-center text-lg-start">
                            <span class="badge rounded-pill hero-badge mb-3">
                                <?php echo e($lang['app_name']); ?>
                            </span>

                            <h1 class="display-5 fw-bold mb-3">
                                <?php echo e($lang['app_name']); ?>
                            </h1>

                            <p class="lead text-muted mb-4">
                                <?php echo e($lang['intro']); ?>
                            </p>

                            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                                <a href="/expense-tracker/signup.php" class="btn btn-primary btn-lg px-4">
                                    <?php echo e($lang['signup']); ?>
                                </a>
                                <a href="/expense-tracker/login.php" class="btn btn-outline-primary btn-lg px-4">
                                    <?php echo e($lang['login']); ?>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="hero-side-box">
                                <div class="feature-card">
                                    <h5 class="mb-2"><?php echo e($lang['feature_track_title']); ?></h5>
                                    <p class="mb-0 text-muted"><?php echo e($lang['feature_track_text']); ?></p>
                                </div>

                                <div class="feature-card mt-3">
                                    <h5 class="mb-2"><?php echo e($lang['feature_category_title']); ?></h5>
                                    <p class="mb-0 text-muted"><?php echo e($lang['feature_category_text']); ?></p>
                                </div>

                                <div class="feature-card mt-3">
                                    <h5 class="mb-2"><?php echo e($lang['feature_summary_title']); ?></h5>
                                    <p class="mb-0 text-muted"><?php echo e($lang['feature_summary_text']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <p class="text-center text-muted mb-0 small">
                        <?php echo e($lang['project_footer']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>