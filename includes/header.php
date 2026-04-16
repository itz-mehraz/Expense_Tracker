<?php
if (!isset($pageTitle)) {
    $pageTitle = $lang['app_name'];
}
$metaDesc = $lang['meta_description'] ?? $lang['intro'];
$bodyClassExtras = $bodyClassExtras ?? '';
?>
<!DOCTYPE html>
<html lang="<?php echo e($currentLang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo e($metaDesc); ?>">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/expense-tracker/assets/css/style.css" rel="stylesheet">
</head>
<body class="app-body <?php echo e($currentTheme === 'dark' ? 'theme-dark' : 'theme-light'); ?><?php echo $bodyClassExtras !== '' ? ' ' . e($bodyClassExtras) : ''; ?>">
<div class="app-shell d-flex flex-column min-vh-100 w-100">
