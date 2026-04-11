<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Language
|--------------------------------------------------------------------------
*/
$supportedLanguages = ['bn', 'en'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $supportedLanguages, true)) {
    $_SESSION['lang'] = $_GET['lang'];
}

if (!isset($_SESSION['lang']) || !in_array($_SESSION['lang'], $supportedLanguages, true)) {
    $_SESSION['lang'] = 'bn';
}

$currentLang = $_SESSION['lang'];

$langFile = __DIR__ . '/../lang/' . $currentLang . '.php';
if (!file_exists($langFile)) {
    $langFile = __DIR__ . '/../lang/bn.php';
}
$lang = require $langFile;

/*
|--------------------------------------------------------------------------
| Theme
|--------------------------------------------------------------------------
*/
$supportedThemes = ['light', 'dark'];

if (isset($_GET['theme']) && in_array($_GET['theme'], $supportedThemes, true)) {
    $_SESSION['theme'] = $_GET['theme'];
}

if (!isset($_SESSION['theme']) || !in_array($_SESSION['theme'], $supportedThemes, true)) {
    $_SESSION['theme'] = 'light';
}

$currentTheme = $_SESSION['theme'];

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/
if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('lang_url')) {
    function lang_url(string $language): string
    {
        $currentUrl = $_SERVER['PHP_SELF'] ?? '/expense-tracker/index.php';
        $query = $_GET;
        $query['lang'] = $language;

        return $currentUrl . '?' . http_build_query($query);
    }
}

if (!function_exists('theme_url')) {
    function theme_url(string $theme): string
    {
        $currentUrl = $_SERVER['PHP_SELF'] ?? '/expense-tracker/index.php';
        $query = $_GET;
        $query['theme'] = $theme;

        return $currentUrl . '?' . http_build_query($query);
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return isset($_SESSION['user_id']);
    }
}