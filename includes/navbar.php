<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$navNext = '';
$reqUri = $_SERVER['REQUEST_URI'] ?? '';
$safeNext = safe_internal_path($reqUri);
if ($safeNext !== '') {
    $navNext = 'next=' . rawurlencode($safeNext);
}
?>
<nav class="navbar navbar-expand-lg sticky-top shadow-sm app-navbar">
    <div class="container">
        <a class="navbar-brand navbar-brand--logo-only d-flex align-items-center" href="/expense-tracker/index.php" title="<?php echo e($lang['app_name']); ?>">
            <img src="/expense-tracker/assets/images/logo.png" alt="<?php echo e($lang['app_name']); ?>" class="navbar-logo" width="32" height="32" loading="lazy">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 mt-3 mt-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="/expense-tracker/index.php">
                        <?php echo e($lang['home']); ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="/expense-tracker/dashboard.php">
                        <?php echo e($lang['dashboard_title']); ?>
                    </a>
                </li>

                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'categories.php' ? 'active' : ''; ?>" href="/expense-tracker/categories.php">
                            <?php echo e($lang['manage_categories']); ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2">
                <div class="btn-group" role="group" aria-label="Language switcher">
                    <a href="<?php echo e(lang_url('bn')); ?>"
                       class="btn btn-sm <?php echo $currentLang === 'bn' ? 'btn-nav-active' : 'btn-nav-ghost'; ?>">
                        <?php echo e($lang['lang_bn']); ?>
                    </a>
                    <a href="<?php echo e(lang_url('en')); ?>"
                       class="btn btn-sm <?php echo $currentLang === 'en' ? 'btn-nav-active' : 'btn-nav-ghost'; ?>">
                        <?php echo e($lang['lang_en']); ?>
                    </a>
                </div>

                <div class="btn-group" role="group" aria-label="Theme switcher">
                    <a href="<?php echo e(theme_url('light')); ?>"
                       class="btn btn-sm <?php echo $currentTheme === 'light' ? 'btn-nav-active' : 'btn-nav-ghost'; ?>">
                        <?php echo e($lang['theme_light']); ?>
                    </a>
                    <a href="<?php echo e(theme_url('dark')); ?>"
                       class="btn btn-sm <?php echo $currentTheme === 'dark' ? 'btn-nav-active' : 'btn-nav-ghost'; ?>">
                        <?php echo e($lang['theme_dark']); ?>
                    </a>
                </div>

                <?php if (!is_logged_in()): ?>
                    <a href="/expense-tracker/login.php<?php echo $navNext !== '' ? '?' . e($navNext) : ''; ?>" class="btn btn-sm btn-nav-ghost">
                        <?php echo e($lang['login']); ?>
                    </a>
                    <a href="/expense-tracker/signup.php<?php echo $navNext !== '' ? '?' . e($navNext) : ''; ?>" class="btn btn-sm btn-nav-primary">
                        <?php echo e($lang['signup']); ?>
                    </a>
                <?php else: ?>
                    <span class="navbar-user small px-1">
                        <?php echo e($_SESSION['user_name'] ?? 'User'); ?>
                    </span>
                    <a href="/expense-tracker/profile.php" class="btn btn-sm btn-nav-solid">
                        <?php echo e($currentLang === 'bn' ? 'প্রোফাইল' : 'Profile'); ?>
                    </a>
                    <a href="/expense-tracker/logout.php" class="btn btn-sm btn-nav-danger">
                        <?php echo e($lang['logout']); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<main id="main-content" class="app-main flex-grow-1 w-100">
