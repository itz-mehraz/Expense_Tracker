<?php
$footerYear = date('Y');
$githubUrl = 'https://github.com/itz-mehraz/Expense_Tracker';
$reqUriFooter = $_SERVER['REQUEST_URI'] ?? '';
$safeFooterNext = safe_internal_path($reqUriFooter);
$dashNext = $safeFooterNext !== ''
    ? 'next=' . rawurlencode($safeFooterNext)
    : 'next=' . rawurlencode('/expense-tracker/index.php');

$isHomePage = basename($_SERVER['PHP_SELF'] ?? '') === 'index.php';
?>
</main>
<?php if (!$isHomePage): ?>
<nav class="mobile-footer-bar d-md-none" aria-label="<?php echo e($currentLang === 'bn' ? 'দ্রুত নেভিগেশন' : 'Quick navigation'); ?>">
    <a href="/expense-tracker/index.php" class="mfb-item <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'is-active' : ''; ?>">
        <i class="bi bi-house-door-fill mfb-icon" aria-hidden="true"></i>
        <span><?php echo e($lang['nav_bar_home']); ?></span>
    </a>
    <a href="/expense-tracker/dashboard.php" class="mfb-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'is-active' : ''; ?>">
        <i class="bi bi-speedometer2 mfb-icon" aria-hidden="true"></i>
        <span><?php echo e($lang['nav_bar_dashboard']); ?></span>
    </a>
    <?php if (is_logged_in()): ?>
        <a href="/expense-tracker/categories.php" class="mfb-item <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'is-active' : ''; ?>">
            <i class="bi bi-grid-1x2 mfb-icon" aria-hidden="true"></i>
            <span><?php echo e($lang['nav_bar_categories']); ?></span>
        </a>
        <a href="/expense-tracker/profile.php" class="mfb-item <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'is-active' : ''; ?>">
            <i class="bi bi-person-fill mfb-icon" aria-hidden="true"></i>
            <span><?php echo e($lang['nav_bar_profile']); ?></span>
        </a>
    <?php else: ?>
        <a href="/expense-tracker/login.php?<?php echo e($dashNext); ?>" class="mfb-item">
            <i class="bi bi-box-arrow-in-right mfb-icon" aria-hidden="true"></i>
            <span><?php echo e($lang['nav_bar_login']); ?></span>
        </a>
        <a href="/expense-tracker/signup.php?<?php echo e($dashNext); ?>" class="mfb-item mfb-item-accent">
            <i class="bi bi-person-plus mfb-icon" aria-hidden="true"></i>
            <span><?php echo e($lang['signup']); ?></span>
        </a>
    <?php endif; ?>
</nav>
<footer class="site-footer site-footer--compact mt-auto d-none d-md-block">
    <div class="container py-4 py-lg-4">
        <div class="row g-4 align-items-start justify-content-between">
            <div class="col-md-7 col-lg-8">
                <div class="footer-brand d-flex align-items-center gap-2 mb-2">
                    <img src="/expense-tracker/assets/images/logo.png" alt="<?php echo e($lang['app_name']); ?>" class="footer-logo" width="32" height="32" loading="lazy" title="<?php echo e($lang['app_name']); ?>">
                </div>
                <p class="footer-tagline mb-2 mb-md-3"><?php echo e($lang['footer_tagline']); ?></p>
                <p class="small soft-muted mb-0 footer-credit-line">
                    <?php echo e($lang['footer_made_by']); ?>
                    <span class="fw-semibold"><?php echo e($lang['footer_author']); ?></span>
                    <span class="footer-dot" aria-hidden="true"> · </span>
                    <a href="<?php echo e($githubUrl); ?>" class="footer-inline-link" target="_blank" rel="noopener noreferrer"><?php echo e($lang['footer_github']); ?></a>
                </p>
            </div>
            <div class="col-md-5 col-lg-3">
                <h6 class="footer-heading"><?php echo e($lang['footer_links_title']); ?></h6>
                <ul class="list-unstyled footer-links mb-0">
                    <li><a href="/expense-tracker/index.php"><?php echo e($lang['home']); ?></a></li>
                    <?php if (is_logged_in()): ?>
                        <li><a href="/expense-tracker/dashboard.php"><?php echo e($lang['dashboard_title']); ?></a></li>
                        <li><a href="/expense-tracker/categories.php"><?php echo e($lang['manage_categories']); ?></a></li>
                        <li><a href="/expense-tracker/profile.php"><?php echo e($currentLang === 'bn' ? 'প্রোফাইল' : 'Profile'); ?></a></li>
                    <?php else: ?>
                        <li><a href="/expense-tracker/login.php"><?php echo e($lang['login']); ?></a></li>
                        <li><a href="/expense-tracker/signup.php"><?php echo e($lang['signup']); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <hr class="footer-rule my-3 my-lg-4">
        <div class="footer-bottom small soft-muted text-center text-md-start">
            © <?php echo e($footerYear); ?> <?php echo e($lang['app_name']); ?>. <?php echo e($lang['footer_rights']); ?>
        </div>
    </div>
</footer>
<?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/expense-tracker/assets/js/app.js" defer></script>
</body>
</html>
