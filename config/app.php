<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Dhaka');

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

$langExtrasEn = [
    'meta_description' => 'Track income and expenses in BDT with a clear dashboard, categories, and exports.',
    'lang_bn' => 'Bengali',
    'lang_en' => 'English',
    'theme_light' => 'Light',
    'theme_dark' => 'Dark',
    'footer_tagline' => 'Simple personal finance for Bangladesh.',
    'footer_useful_title' => 'Useful',
    'footer_currency_note' => 'Amounts are shown in Bangladeshi Taka (৳).',
    'footer_mobile_note' => 'Works on phone and desktop; switch language or theme anytime.',
    'footer_privacy_note' => 'Your data stays in your session until you log out.',
    'footer_links_title' => 'Quick links',
    'footer_rights' => 'All rights reserved.',
    'footer_made_by' => 'Made by',
    'footer_author' => 'Mehraz',
    'footer_github' => 'Source on GitHub',
    'export_csv' => 'Export CSV',
    'export_csv_short' => 'CSV',
    'tip_of_day' => 'Tip of the day',
    'landing_kicker' => 'Income · Expense · Balance',
    'landing_hero_tagline' => 'Income, expense, and balance in one calm view.',
    'landing_guest_bdt_badge' => 'BDT-first',
    'landing_future_live' => 'Live',
    'landing_future_blip_1' => 'Filters',
    'landing_future_blip_2' => 'Categories',
    'landing_future_blip_3' => 'CSV',
    'landing_points_title' => 'What you can do here',
    'landing_point_dashboard' => 'Open the dashboard for totals, chart, and recent activity.',
    'landing_point_categories' => 'Organize spending with categories that match your life.',
    'landing_point_profile' => 'Update profile and preferences when you need a change.',
    'guest_features_title' => 'Full app',
    'guest_features_text' => 'Use the dashboard, categories, and profile after you sign in.',
    'guest_gate_hint' => 'Sign in to use the dashboard, categories, and profile.',
    'guest_nav_hint' => 'Use the menu to switch language, theme, or auth.',
    'try_dashboard' => 'Try dashboard',
    'try_categories' => 'Categories',
    'try_profile' => 'Profile',
    'login_continue_hint' => 'After login you will return to the page you came from.',
    'date_filter_label' => 'Date filter',
    'daily_tips' => [
        'Tag recurring bills so monthly reviews stay quick.',
        'Split large purchases across categories to see true cost drivers.',
        'Export CSV before month-end to keep a backup outside the app.',
    ],
    'guest_preview_title' => 'Preview mode',
    'guest_preview_body' => 'You are viewing the dashboard layout. Sign in or create an account to add transactions, export data, and manage categories.',
    'guest_login_cta' => 'Sign in to use features',
    'guest_signup_cta' => 'Create free account',
    'footer_timezone_note' => 'Dates use Asia/Dhaka.',
    'today_income' => "Today's income",
    'today_expense' => "Today's expense",
    'money_tip_label' => 'Money tip',
    'password_show' => 'Show',
    'password_hide' => 'Hide',
    'nav_bar_home' => 'Home',
    'nav_bar_dashboard' => 'Dash',
    'nav_bar_categories' => 'Cats',
    'nav_bar_profile' => 'You',
    'nav_bar_login' => 'Log in',
];

$langExtrasBn = [
    'meta_description' => 'আয়, খরচ, ক্যাটাগরি ও ড্যাশবোর্ড—একই জায়গায়, ৳ এ।',
    'lang_bn' => 'বাংলা',
    'lang_en' => 'ইংরেজি',
    'theme_light' => 'লাইট',
    'theme_dark' => 'ডার্ক',
    'footer_tagline' => 'ব্যক্তিগত আর্থিক হিসাব—সহজ ও পরিষ্কার।',
    'footer_useful_title' => 'দরকারি তথ্য',
    'footer_currency_note' => 'টাকার পরিমাণ বাংলাদেশি টাকা (৳) তে দেখানো হয়।',
    'footer_mobile_note' => 'মোবাইল ও ডেস্কটপ—ভাষা ও থিম যেকোনো সময় বদলান।',
    'footer_privacy_note' => 'লগআউট না করা পর্যন্ত ডাটা সেশনে থাকে।',
    'footer_links_title' => 'দ্রুত লিংক',
    'footer_rights' => 'সর্বস্বত্ব সংরক্ষিত।',
    'footer_made_by' => 'তৈরি করেছেন',
    'footer_author' => 'মেহরাজ',
    'footer_github' => 'গিটহাবে সোর্স কোড',
    'export_csv' => 'সিএসভি এক্সপোর্ট',
    'export_csv_short' => 'সিএসভি',
    'tip_of_day' => 'আজকের টিপ',
    'landing_kicker' => 'আয় · খরচ · ব্যালেন্স',
    'landing_hero_tagline' => 'আয়, খরচ ও ব্যালেন্স—এক নজরে, পরিষ্কার লেআউটে।',
    'landing_guest_bdt_badge' => '৳ প্রথম',
    'landing_future_live' => 'লাইভ',
    'landing_future_blip_1' => 'ফিল্টার',
    'landing_future_blip_2' => 'ক্যাটাগরি',
    'landing_future_blip_3' => 'সিএসভি',
    'landing_points_title' => 'এখানে কী করতে পারবেন',
    'landing_point_dashboard' => 'ড্যাশবোর্ডে মোট হিসাব, চার্ট ও সাম্প্রতিক লেনদেন দেখুন।',
    'landing_point_categories' => 'নিজের প্রয়োজন অনুযায়ী ক্যাটাগরি বানিয়ে খরচ সাজান।',
    'landing_point_profile' => 'প্রয়োজনে নাম, ইমেইল ও পাসওয়ার্ড আপডেট করুন।',
    'guest_features_title' => 'পূর্ণ অ্যাপ',
    'guest_features_text' => 'ড্যাশবোর্ড, ক্যাটাগরি ও প্রোফাইল—সব ব্যবহার করতে লগইন বা সাইন আপ করুন।',
    'guest_gate_hint' => 'সম্পূর্ণ ফিচার ব্যবহারে লগইন বা সাইন আপ করুন। ড্যাশবোর্ড প্রিভিউ সবাই দেখতে পারবেন।',
    'guest_nav_hint' => 'উপরের মেনু থেকে ভাষা, থিম ও অ্যাকাউন্ট বদলাতে পারবেন।',
    'try_dashboard' => 'ড্যাশবোর্ড',
    'try_categories' => 'ক্যাটাগরি',
    'try_profile' => 'প্রোফাইল',
    'login_continue_hint' => 'লগইনের পর আপনি যে পৃষ্ঠা থেকে এসেছিলেন সেখানে ফিরে যাবেন।',
    'date_filter_label' => 'তারিখ অনুযায়ী ফিল্টার',
    'daily_tips' => [
        'নিয়মিত বিল আলাদা ট্যাগ রাখলে মাস শেষে হিসাব দেখা সহজ হয়।',
        'বড় কেনাকাটা একাধিক ক্যাটাগরিতে ভাগ করে লিখলে খরচের চিত্র স্পষ্ট হয়।',
        'মাস শেষের আগে সিএসভি ফাইল এক্সপোর্ট করে নিরাপদে সংরক্ষণ করুন।',
    ],
    'guest_preview_title' => 'প্রিভিউ মোড',
    'guest_preview_body' => 'আপনি ড্যাশবোর্ডের চেহারা দেখছেন। লেনদেন যোগ, সিএসভি এক্সপোর্ট ও ক্যাটাগরি ব্যবস্থাপনার জন্য লগইন বা সাইন আপ করুন।',
    'guest_login_cta' => 'লগইন করে ব্যবহার করুন',
    'guest_signup_cta' => 'বিনামূল্যে অ্যাকাউন্ট খুলুন',
    'footer_timezone_note' => 'তারিখ এশিয়া/ঢাকা সময়ে।',
    'today_income' => 'আজকের আয়',
    'today_expense' => 'আজকের খরচ',
    'money_tip_label' => 'আর্থিক টিপ',
    'password_show' => 'দেখান',
    'password_hide' => 'লুকান',
    'nav_bar_home' => 'হোম',
    'nav_bar_dashboard' => 'ড্যাশবোর্ড',
    'nav_bar_categories' => 'ক্যাটাগরি',
    'nav_bar_profile' => 'প্রোফাইল',
    'nav_bar_login' => 'লগইন',
];

$lang = array_merge($lang, $currentLang === 'bn' ? $langExtrasBn : $langExtrasEn);

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

if (!function_exists('safe_internal_path')) {
    /**
     * Allow only same-site paths under /expense-tracker/ for ?next= redirects.
     */
    function safe_internal_path(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        $parts = parse_url($path);
        if ($parts === false) {
            return '';
        }

        $p = $parts['path'] ?? '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        if ($p === '' || !str_starts_with($p, '/expense-tracker/')) {
            return '';
        }

        if (str_contains($p, '..') || str_contains($p, "\0")) {
            return '';
        }

        return $p . $query . $fragment;
    }
}

if (!function_exists('format_bdt')) {
    function format_bdt(float $amount, int $decimals = 2): string
    {
        return '৳ ' . number_format($amount, $decimals);
    }
}

if (!function_exists('daily_money_tip')) {
    function daily_money_tip(array $lang): string
    {
        $tips = $lang['daily_tips'] ?? [];
        if (!is_array($tips) || $tips === []) {
            return '';
        }

        return $tips[array_rand($tips)];
    }
}
