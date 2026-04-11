<?php
if (!isset($pageTitle)) {
    $pageTitle = $lang['app_name'];
}
?>
<!DOCTYPE html>
<html lang="<?php echo e($currentLang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/expense-tracker/assets/style.css" rel="stylesheet">
</head>
<body class="<?php echo e($currentTheme === 'dark' ? 'theme-dark' : 'theme-light'); ?>">