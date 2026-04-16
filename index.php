<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/app.php';

$bodyClassExtras = 'is-home';

$pageTitle = $lang['app_name'] . ' | ' . $lang['home_title'];

$homeGuest = !is_logged_in();
$userName = $_SESSION['user_name'] ?? '';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container page-wrapper home-page home-page--minimal py-4 py-md-5 flex-grow-1">
    <?php if ($homeGuest): ?>
        <div class="home-landing home-landing--minimal home-landing--visual">
            <div class="home-hero-orbs" aria-hidden="true"></div>
            <div class="row justify-content-center position-relative">
                <div class="col-12 col-lg-10 col-xl-8">
                    <div class="guest-hero-panel guest-hero-panel--minimal h-100 text-center text-lg-start">
                        <div class="guest-hero-top justify-content-center justify-content-lg-start">
                            <span class="guest-pill"><?php echo e($lang['landing_guest_bdt_badge']); ?></span>
                            <span class="guest-pill guest-pill-muted"><?php echo e($lang['landing_kicker']); ?></span>
                        </div>
                        <h1 class="guest-hero-title guest-hero-title--minimal mt-3 mb-2 mb-lg-3">
                            <?php echo e($lang['landing_hero_tagline']); ?>
                        </h1>
                        <p class="guest-hero-lead guest-hero-lead--minimal mx-auto mx-lg-0 mb-4">
                            <?php echo e($lang['intro']); ?>
                        </p>
                        <div class="home-cta-row d-flex flex-column flex-sm-row flex-wrap justify-content-center justify-content-lg-start gap-2 mb-3">
                            <a href="/expense-tracker/signup.php" class="btn btn-guest-primary btn-lg px-4 w-100 w-sm-auto">
                                <?php echo e($lang['signup']); ?>
                            </a>
                            <a href="/expense-tracker/login.php" class="btn btn-guest-outline btn-lg px-4 w-100 w-sm-auto">
                                <?php echo e($lang['login']); ?>
                            </a>
                        </div>
                        <div class="home-hero-meta d-flex flex-column flex-sm-row flex-wrap align-items-center justify-content-center justify-content-lg-start gap-2 gap-sm-3 small">
                            <a href="/expense-tracker/dashboard.php" class="home-dashboard-link">
                                <i class="bi bi-layout-text-sidebar-reverse me-1" aria-hidden="true"></i>
                                <?php echo e($lang['home_dashboard_preview']); ?>
                            </a>
                            <span class="home-meta-divider d-none d-sm-inline" aria-hidden="true">·</span>
                            <span class="guest-gate guest-gate--inline mb-0">
                                <i class="bi bi-lock-fill me-1" aria-hidden="true"></i><?php echo e($lang['guest_gate_hint']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mt-4 mt-md-5">
                <div class="col-12 col-xl-10">
                    <p class="home-visual-heading text-center text-uppercase small fw-semibold mb-3"><?php echo e($lang['home_visual_heading']); ?></p>
                    <div class="home-visual-cards d-flex flex-wrap justify-content-center gap-3">
                        <div class="home-visual-card">
                            <div class="home-visual-icon"><i class="bi bi-pie-chart-fill" aria-hidden="true"></i></div>
                            <span><?php echo e($lang['home_visual_chart']); ?></span>
                        </div>
                        <div class="home-visual-card">
                            <div class="home-visual-icon"><i class="bi bi-currency-exchange" aria-hidden="true"></i></div>
                            <span><?php echo e($lang['home_visual_bdt']); ?></span>
                        </div>
                        <div class="home-visual-card">
                            <div class="home-visual-icon"><i class="bi bi-translate" aria-hidden="true"></i></div>
                            <span><?php echo e($lang['home_visual_lang']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="home-logged-wrap">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-lg overflow-hidden home-logged-card">
                        <div class="card-body p-4 p-md-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-7">
                                    <span class="badge rounded-pill hero-badge mb-3"><?php echo e($lang['landing_kicker']); ?></span>
                                    <h1 class="display-6 fw-bold mb-3">
                                        <?php echo e($lang['welcome']); ?>, <?php echo e($userName); ?>
                                    </h1>
                                    <p class="lead soft-muted mb-4"><?php echo e($lang['intro']); ?></p>
                                    <div class="d-flex flex-column flex-sm-row gap-3">
                                        <a href="/expense-tracker/dashboard.php" class="btn btn-primary btn-lg px-4"><?php echo e($lang['dashboard_title']); ?></a>
                                        <a href="/expense-tracker/categories.php" class="btn btn-outline-primary btn-lg px-4"><?php echo e($lang['manage_categories']); ?></a>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <h2 class="h5 fw-bold mb-3"><?php echo e($lang['landing_points_title']); ?></h2>
                                    <ul class="list-unstyled home-logged-list mb-0">
                                        <li class="d-flex gap-2 mb-3">
                                            <i class="bi bi-check-circle-fill text-primary flex-shrink-0 mt-1"></i>
                                            <span><?php echo e($lang['landing_point_dashboard']); ?></span>
                                        </li>
                                        <li class="d-flex gap-2 mb-3">
                                            <i class="bi bi-check-circle-fill text-primary flex-shrink-0 mt-1"></i>
                                            <span><?php echo e($lang['landing_point_categories']); ?></span>
                                        </li>
                                        <li class="d-flex gap-2">
                                            <i class="bi bi-check-circle-fill text-primary flex-shrink-0 mt-1"></i>
                                            <span><?php echo e($lang['landing_point_profile']); ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
