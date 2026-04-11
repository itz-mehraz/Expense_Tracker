<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm app-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/expense-tracker/index.php">
            <?php echo e($lang['app_name']); ?>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 mt-3 mt-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="/expense-tracker/index.php">
                        <?php echo e($lang['home']); ?>
                    </a>
                </li>

                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="/expense-tracker/dashboard.php">
                            <?php echo e($lang['dashboard_title']); ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2">
                <div class="btn-group" role="group" aria-label="Language switcher">
                    <a href="<?php echo e(lang_url('bn')); ?>"
                       class="btn btn-sm <?php echo $currentLang === 'bn' ? 'btn-warning text-dark' : 'btn-outline-light'; ?>">
                        বাংলা
                    </a>
                    <a href="<?php echo e(lang_url('en')); ?>"
                       class="btn btn-sm <?php echo $currentLang === 'en' ? 'btn-warning text-dark' : 'btn-outline-light'; ?>">
                        English
                    </a>
                </div>

                <div class="btn-group" role="group" aria-label="Theme switcher">
                    <a href="<?php echo e(theme_url('light')); ?>"
                       class="btn btn-sm <?php echo $currentTheme === 'light' ? 'btn-info text-dark' : 'btn-outline-light'; ?>">
                        <?php echo e($currentLang === 'bn' ? 'Light' : 'Light'); ?>
                    </a>
                    <a href="<?php echo e(theme_url('dark')); ?>"
                       class="btn btn-sm <?php echo $currentTheme === 'dark' ? 'btn-info text-dark' : 'btn-outline-light'; ?>">
                        <?php echo e($currentLang === 'bn' ? 'Dark' : 'Dark'); ?>
                    </a>
                </div>

                <?php if (!is_logged_in()): ?>
                    <a href="/expense-tracker/login.php" class="btn btn-sm btn-outline-light">
                        <?php echo e($lang['login']); ?>
                    </a>
                    <a href="/expense-tracker/signup.php" class="btn btn-sm btn-primary">
                        <?php echo e($lang['signup']); ?>
                    </a>
                <?php else: ?>
                    <span class="text-white-50 small px-1">
                        <?php echo e($_SESSION['user_name'] ?? 'User'); ?>
                    </span>
                    <a href="/expense-tracker/profile.php" class="btn btn-sm btn-navbar-solid">
                        <?php echo e($currentLang === 'bn' ? 'প্রোফাইল' : 'Profile'); ?>
                    </a>
                    <a href="/expense-tracker/logout.php" class="btn btn-sm btn-danger">
                        <?php echo e($lang['logout']); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>