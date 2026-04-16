<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';

$guestView = !is_logged_in();
$userId = $guestView ? 0 : (int) $_SESSION['user_id'];
$userName = $guestView
    ? ($currentLang === 'bn' ? 'অতিথি' : 'Guest')
    : ($_SESSION['user_name'] ?? 'User');

$successMessage = '';
$errorMessage = [];

$defaultType = $_GET['type'] ?? 'expense';
if (!in_array($defaultType, ['expense', 'income'], true)) {
    $defaultType = 'expense';
}
$search = trim((string) ($_GET['search'] ?? ''));
$categoryFilter = (int) ($_GET['category'] ?? 0);
$typeFilter = $_GET['filter_type'] ?? '';

$datePreset = trim((string) ($_GET['date_preset'] ?? ''));
$dateFrom = trim((string) ($_GET['date_from'] ?? ''));
$dateTo = trim((string) ($_GET['date_to'] ?? ''));

$allowedPresets = ['today', 'yesterday', 'last7', 'this_month', 'last_month', 'custom'];

if (!in_array($datePreset, $allowedPresets, true)) {
    $datePreset = '';
}

$today = date('Y-m-d');

if ($datePreset === 'today') {
    $dateFrom = $today;
    $dateTo = $today;
} elseif ($datePreset === 'yesterday') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $dateFrom = $yesterday;
    $dateTo = $yesterday;
} elseif ($datePreset === 'last7') {
    $dateFrom = date('Y-m-d', strtotime('-6 days'));
    $dateTo = $today;
} elseif ($datePreset === 'this_month') {
    $dateFrom = date('Y-m-01');
    $dateTo = date('Y-m-t');
} elseif ($datePreset === 'last_month') {
    $dateFrom = date('Y-m-01', strtotime('first day of last month'));
    $dateTo = date('Y-m-t', strtotime('last day of last month'));
} elseif ($datePreset === 'custom') {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
        $dateFrom = '';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
        $dateTo = '';
    }
} else {
    $dateFrom = '';
    $dateTo = '';
}
/*
|--------------------------------------------------------------------------
| Add Transaction
|--------------------------------------------------------------------------
*/
if (!$guestView && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_transaction'])) {
    $title = trim($_POST['title'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $transactionType = $_POST['transaction_type'] ?? 'expense';
    $paymentMethod = $_POST['payment_method'] ?? 'cash';
    $expenseDate = $_POST['expense_date'] ?? '';
    $note = trim($_POST['note'] ?? '');

    if ($title === '') {
        $errorMessage[] = $currentLang === 'bn' ? 'শিরোনাম দেওয়া আবশ্যক।' : 'Title is required.';
    }

    if ($amount === '' || !is_numeric($amount) || (float) $amount <= 0) {
        $errorMessage[] = $currentLang === 'bn' ? 'সঠিক পরিমাণ লিখুন।' : 'Enter a valid amount.';
    }

    if ($categoryId <= 0) {
        $errorMessage[] = $currentLang === 'bn' ? 'ক্যাটাগরি নির্বাচন করুন।' : 'Please select a category.';
    }

    if (!in_array($transactionType, ['expense', 'income'], true)) {
        $errorMessage[] = $currentLang === 'bn' ? 'সঠিক লেনদেনের ধরন নির্বাচন করুন।' : 'Select a valid transaction type.';
    }

    if (!in_array($paymentMethod, ['bkash', 'nagad', 'cash', 'bank', 'card', 'other'], true)) {
        $errorMessage[] = $currentLang === 'bn' ? 'সঠিক পেমেন্ট মেথড নির্বাচন করুন।' : 'Select a valid payment method.';
    }

    if ($expenseDate === '') {
        $errorMessage[] = $currentLang === 'bn' ? 'তারিখ নির্বাচন করুন।' : 'Please select a date.';
    }

    if (empty($errorMessage)) {
        $checkCategory = $conn->prepare("SELECT id FROM categories WHERE id = ? AND user_id = ?");
        $checkCategory->bind_param('ii', $categoryId, $userId);
        $checkCategory->execute();
        $checkCategoryResult = $checkCategory->get_result();

        if ($checkCategoryResult->num_rows === 0) {
            $errorMessage[] = $currentLang === 'bn' ? 'এই ক্যাটাগরি আপনার নয়।' : 'This category does not belong to you.';
        }
        $checkCategory->close();
    }

    if (empty($errorMessage)) {
        $insertStmt = $conn->prepare("
            INSERT INTO expenses (user_id, category_id, transaction_type, payment_method, title, amount, expense_date, note)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $amountValue = (float) $amount;
        $insertStmt->bind_param(
            'iisssdss',
            $userId,
            $categoryId,
            $transactionType,
            $paymentMethod,
            $title,
            $amountValue,
            $expenseDate,
            $note
        );

        if ($insertStmt->execute()) {
            $_SESSION['dashboard_success'] = $currentLang === 'bn'
                ? 'লেনদেন সফলভাবে যোগ হয়েছে।'
                : 'Transaction added successfully.';

            header('Location: /expense-tracker/dashboard.php');
            exit();
        } else {
            $errorMessage[] = $currentLang === 'bn'
                ? 'লেনদেন যোগ করা যায়নি। আবার চেষ্টা করুন।'
                : 'Failed to add transaction. Please try again.';
        }

        $insertStmt->close();
    }
}

if (isset($_SESSION['dashboard_success'])) {
    $successMessage = $_SESSION['dashboard_success'];
    unset($_SESSION['dashboard_success']);
}

/*
|--------------------------------------------------------------------------
| Summary Data
|--------------------------------------------------------------------------
*/
$totalExpense = 0;
$totalIncome = 0;
$thisMonthExpense = 0;
$thisMonthIncome = 0;
$totalCategories = 0;
$totalEntries = 0;

$totalExpense = 0;
$totalIncome = 0;
$thisMonthExpense = 0;
$thisMonthIncome = 0;
$totalCategories = 0;
$totalEntries = 0;
$todayExpense = 0;
$todayIncome = 0;

if (!$guestView) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) AS total FROM expenses WHERE user_id = ? AND transaction_type = 'expense'");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $totalExpense = (float) ($row['total'] ?? 0);
    $stmt->close();

    $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) AS total FROM expenses WHERE user_id = ? AND transaction_type = 'income'");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $totalIncome = (float) ($row['total'] ?? 0);
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(amount), 0) AS total
        FROM expenses
        WHERE user_id = ?
          AND transaction_type = 'expense'
          AND MONTH(expense_date) = MONTH(CURDATE())
          AND YEAR(expense_date) = YEAR(CURDATE())
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $thisMonthExpense = (float) ($row['total'] ?? 0);
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(amount), 0) AS total
        FROM expenses
        WHERE user_id = ?
          AND transaction_type = 'income'
          AND MONTH(expense_date) = MONTH(CURDATE())
          AND YEAR(expense_date) = YEAR(CURDATE())
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $thisMonthIncome = (float) ($row['total'] ?? 0);
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM categories WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $totalCategories = (int) ($row['total'] ?? 0);
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM expenses WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $totalEntries = (int) ($row['total'] ?? 0);
    $stmt->close();

    $todayStr = date('Y-m-d');
    $stmt = $conn->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END), 0) AS te,
            COALESCE(SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END), 0) AS ti
        FROM expenses
        WHERE user_id = ? AND expense_date = ?
    ");
    $stmt->bind_param('is', $userId, $todayStr);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $todayExpense = (float) ($row['te'] ?? 0);
    $todayIncome = (float) ($row['ti'] ?? 0);
    $stmt->close();
}

$netBalance = $totalIncome - $totalExpense;



/*
|--------------------------------------------------------------------------
| Category List
|--------------------------------------------------------------------------
*/
$categories = [];
if (!$guestView) {
    $stmt = $conn->prepare("SELECT id, name FROM categories WHERE user_id = ? ORDER BY name ASC");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    $stmt->close();
}

$transactionRows = [];
$filteredIncome = 0;
$filteredExpense = 0;

if (!$guestView) {
    $sql = "
        SELECT
            expenses.id,
            expenses.title,
            expenses.amount,
            expenses.expense_date,
            expenses.note,
            expenses.transaction_type,
            expenses.payment_method,
            categories.name AS category_name
        FROM expenses
        LEFT JOIN categories ON expenses.category_id = categories.id
        WHERE expenses.user_id = ?
    ";

    $params = [$userId];
    $types = 'i';

    if ($search !== '') {
        $sql .= " AND (expenses.title LIKE ? OR expenses.note LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }

    if ($categoryFilter > 0) {
        $sql .= " AND expenses.category_id = ?";
        $params[] = $categoryFilter;
        $types .= 'i';
    }

    if (in_array($typeFilter, ['expense', 'income'], true)) {
        $sql .= " AND expenses.transaction_type = ?";
        $params[] = $typeFilter;
        $types .= 's';
    }

    if ($dateFrom !== '') {
        $sql .= " AND expenses.expense_date >= ?";
        $params[] = $dateFrom;
        $types .= 's';
    }

    if ($dateTo !== '') {
        $sql .= " AND expenses.expense_date <= ?";
        $params[] = $dateTo;
        $types .= 's';
    }

    $sql .= " ORDER BY expenses.expense_date DESC, expenses.id DESC LIMIT 15";

    $listStmt = $conn->prepare($sql);
    $listStmt->bind_param($types, ...$params);
    $listStmt->execute();
    $listResult = $listStmt->get_result();
    while ($row = $listResult->fetch_assoc()) {
        $transactionRows[] = $row;
    }
    $listStmt->close();

    $extraSql = '';
    $extraTypes = '';
    $extraParams = [];

    if ($search !== '') {
        $extraSql .= " AND (expenses.title LIKE ? OR expenses.note LIKE ?)";
        $extraTypes .= 'ss';
        $searchLike = '%' . $search . '%';
        $extraParams[] = $searchLike;
        $extraParams[] = $searchLike;
    }

    if ($categoryFilter > 0) {
        $extraSql .= " AND expenses.category_id = ?";
        $extraTypes .= 'i';
        $extraParams[] = $categoryFilter;
    }

    if (in_array($typeFilter, ['expense', 'income'], true)) {
        $extraSql .= " AND expenses.transaction_type = ?";
        $extraTypes .= 's';
        $extraParams[] = $typeFilter;
    }

    if ($dateFrom !== '') {
        $extraSql .= " AND expenses.expense_date >= ?";
        $extraTypes .= 's';
        $extraParams[] = $dateFrom;
    }

    if ($dateTo !== '') {
        $extraSql .= " AND expenses.expense_date <= ?";
        $extraTypes .= 's';
        $extraParams[] = $dateTo;
    }

    $chartSql = "
        SELECT
            COALESCE(SUM(CASE WHEN expenses.transaction_type = 'income' THEN expenses.amount ELSE 0 END), 0) AS filtered_income,
            COALESCE(SUM(CASE WHEN expenses.transaction_type = 'expense' THEN expenses.amount ELSE 0 END), 0) AS filtered_expense
        FROM expenses
        WHERE expenses.user_id = ?
        $extraSql
    ";

    $chartStmt = $conn->prepare($chartSql);
    $chartParams = array_merge([$userId], $extraParams);
    $chartTypes = 'i' . $extraTypes;
    $bind = [];
    $bind[] = $chartTypes;
    foreach ($chartParams as $k => &$v) {
        $bind[] = &$v;
    }
    unset($v);
    call_user_func_array([$chartStmt, 'bind_param'], $bind);
    $chartStmt->execute();
    $chartRow = $chartStmt->get_result()->fetch_assoc();
    $filteredIncome = (float) ($chartRow['filtered_income'] ?? 0);
    $filteredExpense = (float) ($chartRow['filtered_expense'] ?? 0);
    $chartStmt->close();
}

$moneyTip = daily_money_tip($lang);
$exportQuery = $_GET;
unset($exportQuery['lang'], $exportQuery['theme']);
$exportCsvHref = '/expense-tracker/export_csv.php?' . http_build_query($exportQuery);

$pageTitle = $lang['dashboard_title'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container page-wrapper">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg dashboard-hero-card">
                <div class="card-body p-4">
                    <div class="dashboard-hero-compact">
                        <div class="dashboard-hero-main">
                            <h2 class="dashboard-hero-title">
                                <?php echo e($lang['welcome']); ?>, <?php echo e($userName); ?>
                            </h2>

                            <p class="dashboard-hero-subtitle">
                                <?php echo e($guestView
                                    ? ($currentLang === 'bn'
                                        ? 'লেআউট দেখুন—ডাটা যোগ ও সম্পাদনা করতে লগইন করুন।'
                                        : 'Explore the layout; sign in to add and edit your data.')
                                    : ($currentLang === 'bn'
                                        ? 'আপনার আয়, খরচ এবং ব্যালেন্স এক নজরে দেখুন।'
                                        : 'See your income, expense, and balance at a glance.')); ?>
                            </p>

                            <div class="dashboard-stats-inline">
                                <div class="dashboard-stat-chip">
                                    <span class="chip-label">
                                        <?php echo e($currentLang === 'bn' ? 'মোট আয়' : 'Income'); ?>
                                    </span>
                                    <span class="chip-value">৳ <?php echo number_format($totalIncome, 0); ?></span>
                                </div>

                                <div class="dashboard-stat-chip">
                                    <span class="chip-label">
                                        <?php echo e($currentLang === 'bn' ? 'মোট খরচ' : 'Expense'); ?>
                                    </span>
                                    <span class="chip-value">৳ <?php echo number_format($totalExpense, 0); ?></span>
                                </div>

                                <div class="dashboard-stat-chip">
                                    <span class="chip-label">
                                        <?php echo e($currentLang === 'bn' ? 'ব্যালেন্স' : 'Balance'); ?>
                                    </span>
                                    <span class="chip-value">৳ <?php echo number_format($netBalance, 0); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-hero-btns">
                            <?php if ($guestView): ?>
                                <?php
                                $dashNext = 'next=' . rawurlencode('/expense-tracker/dashboard.php');
                                ?>
                                <a href="/expense-tracker/login.php?<?php echo e($dashNext); ?>" class="btn btn-hero-cta">
                                    <?php echo e($lang['guest_login_cta']); ?>
                                </a>
                                <a href="/expense-tracker/signup.php?<?php echo e($dashNext); ?>" class="btn btn-hero-secondary">
                                    <?php echo e($lang['guest_signup_cta']); ?>
                                </a>
                            <?php else: ?>
                                <a href="/expense-tracker/dashboard.php?type=expense#add-form" class="btn hero-btn-white">
                                    <?php echo e($lang['add_expense']); ?>
                                </a>
                                <a href="/expense-tracker/dashboard.php?type=income#add-form" class="btn btn-hero-income">
                                    <?php echo e($lang['add_income']); ?>
                                </a>
                                <a href="/expense-tracker/categories.php" class="btn hero-btn-glass">
                                    <?php echo e($lang['manage_categories']); ?>
                                </a>
                                <a href="/expense-tracker/profile.php" class="btn hero-btn-glass">
                                    <?php echo e($currentLang === 'bn' ? 'প্রোফাইল' : 'Profile'); ?>
                                </a>
                                <a href="/expense-tracker/logout.php" class="btn btn-hero-logout">
                                    <?php echo e($lang['logout']); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($guestView): ?>
            <div class="col-12">
                <div class="alert alert-guest-preview border-0 shadow-sm mb-0 d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
                    <div class="flex-grow-1">
                        <strong class="d-block mb-1"><?php echo e($lang['guest_preview_title']); ?></strong>
                        <span class="small mb-0"><?php echo e($lang['guest_preview_body']); ?></span>
                    </div>
                    <?php $dashNext = 'next=' . rawurlencode('/expense-tracker/dashboard.php'); ?>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="/expense-tracker/login.php?<?php echo e($dashNext); ?>" class="btn btn-sm btn-hero-cta"><?php echo e($lang['login']); ?></a>
                        <a href="/expense-tracker/signup.php?<?php echo e($dashNext); ?>" class="btn btn-sm btn-hero-secondary"><?php echo e($lang['signup']); ?></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($successMessage !== ''): ?>
            <div class="col-12">
                <div class="alert alert-success shadow-sm mb-0">
                    <?php echo e($successMessage); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="col-12">
                <div class="alert alert-danger shadow-sm mb-0">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errorMessage as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$guestView): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="dashboard-stat-card dashboard-stat-card--today p-4 h-100">
                <div class="dashboard-stat-title"><?php echo e($lang['today_income']); ?></div>
                <h3 class="dashboard-stat-value stat-income"><?php echo e(format_bdt($todayIncome)); ?></h3>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="dashboard-stat-card dashboard-stat-card--today p-4 h-100">
                <div class="dashboard-stat-title"><?php echo e($lang['today_expense']); ?></div>
                <h3 class="dashboard-stat-value stat-expense"><?php echo e(format_bdt($todayExpense)); ?></h3>
            </div>
        </div>
        <?php if ($moneyTip !== ''): ?>
        <div class="col-12 col-md-4 col-lg-6">
            <div class="dashboard-tip-card p-4 h-100">
                <div class="dashboard-stat-title"><?php echo e($lang['money_tip_label']); ?></div>
                <p class="mb-0 small"><?php echo e($moneyTip); ?></p>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <div class="col-6 col-lg-3">
            <div class="dashboard-stat-card p-4 h-100">
                <div class="dashboard-stat-title">
                    <?php echo e($currentLang === 'bn' ? 'মোট আয়' : 'Total Income'); ?>
                </div>
                <h3 class="dashboard-stat-value stat-income"><?php echo e(format_bdt($totalIncome)); ?></h3>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="dashboard-stat-card p-4 h-100">
                <div class="dashboard-stat-title"><?php echo e($lang['total_expenses']); ?></div>
                <h3 class="dashboard-stat-value stat-expense"><?php echo e(format_bdt($totalExpense)); ?></h3>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="dashboard-stat-card p-4 h-100">
                <div class="dashboard-stat-title">
                    <?php echo e($currentLang === 'bn' ? 'নেট ব্যালেন্স' : 'Net Balance'); ?>
                </div>
                <h3 class="dashboard-stat-value <?php echo $netBalance >= 0 ? 'stat-income' : 'stat-expense'; ?>">
                    <?php echo e(format_bdt($netBalance)); ?>
                </h3>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="dashboard-stat-card p-4 h-100">
                <div class="dashboard-stat-title">
                    <?php echo e($currentLang === 'bn' ? 'এই মাসের আয়' : 'This Month Income'); ?>
                </div>
                <h3 class="dashboard-stat-value">৳ <?php echo number_format($thisMonthIncome, 2); ?></h3>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="dashboard-stat-card p-4 h-100">
                <div class="dashboard-stat-title"><?php echo e($lang['this_month']); ?></div>
                <h3 class="dashboard-stat-value">৳ <?php echo number_format($thisMonthExpense, 2); ?></h3>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="dashboard-stat-card p-4 h-100">
                <div class="dashboard-stat-title"><?php echo e($lang['total_categories']); ?></div>
                <h3 class="dashboard-stat-value"><?php echo $totalCategories; ?></h3>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="dashboard-stat-card p-4 h-100">
                <div class="dashboard-stat-title"><?php echo e($lang['total_entries']); ?></div>
                <h3 class="dashboard-stat-value"><?php echo $totalEntries; ?></h3>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="dashboard-stat-card p-4 h-100">
                <div class="dashboard-stat-title">
                    <?php echo e($currentLang === 'bn' ? 'এই মাসের নেট' : 'This Month Net'); ?>
                </div>
                <h3 class="dashboard-stat-value <?php echo ($thisMonthIncome - $thisMonthExpense) >= 0 ? 'text-success' : 'text-danger'; ?>">
                    ৳ <?php echo number_format($thisMonthIncome - $thisMonthExpense, 2); ?>
                </h3>
            </div>
        </div>

        <?php if (!$guestView): ?>
        <div class="col-12" id="add-form">
            <div class="card border-0 shadow-lg card-elevated">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-2 mb-3">
                        <h4 class="section-title mb-0"><?php echo e($lang['add_transaction']); ?></h4>
                        <span class="badge <?php echo $defaultType === 'income' ? 'bg-success' : 'bg-primary'; ?>">
                            <?php echo e($defaultType === 'income' ? $lang['income'] : $lang['expense']); ?>
                        </span>
                    </div>

                    <form method="POST" class="row g-3">
                        <input type="hidden" name="add_transaction" value="1">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?php echo e($lang['title']); ?></label>
                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                placeholder="<?php echo e($currentLang === 'bn' ? 'যেমন: বাজার, বেতন, বিকাশ ক্যাশ ইন' : 'Example: Grocery, Salary, bKash Cash In'); ?>"
                                value="<?php echo e($_POST['title'] ?? ''); ?>"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?php echo e($lang['amount']); ?></label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="amount"
                                class="form-control"
                                placeholder="0.00"
                                value="<?php echo e($_POST['amount'] ?? ''); ?>"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold"><?php echo e($lang['transaction_type']); ?></label>
                            <select name="transaction_type" class="form-select form-control">
                                <option value="expense" <?php echo (($_POST['transaction_type'] ?? $defaultType) === 'expense') ? 'selected' : ''; ?>>
    <?php echo e($lang['expense']); ?>
</option>

<option value="income" <?php echo (($_POST['transaction_type'] ?? $defaultType) === 'income') ? 'selected' : ''; ?>>
    <?php echo e($lang['income']); ?>
</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold"><?php echo e($lang['category']); ?></label>
                            <select name="category_id" class="form-select form-control">
                                <option value="0"><?php echo e($lang['select_category']); ?></option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo (int) $category['id']; ?>" <?php echo ((int) ($_POST['category_id'] ?? 0) === (int) $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo e($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold"><?php echo e($lang['payment_method']); ?></label>
                            <select name="payment_method" class="form-select form-control">
                                <?php
                                $selectedMethod = $_POST['payment_method'] ?? 'cash';
                                $methods = [
                                    'bkash' => $lang['bkash'],
                                    'nagad' => $lang['nagad'],
                                    'cash' => $lang['cash'],
                                    'bank' => $lang['bank'],
                                    'card' => $lang['card'],
                                    'other' => $lang['other'],
                                ];
                                foreach ($methods as $key => $label):
                                ?>
                                    <option value="<?php echo e($key); ?>" <?php echo $selectedMethod === $key ? 'selected' : ''; ?>>
                                        <?php echo e($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?php echo e($lang['date']); ?></label>
                            <input
                                type="date"
                                name="expense_date"
                                class="form-control"
                                value="<?php echo e($_POST['expense_date'] ?? date('Y-m-d')); ?>"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?php echo e($lang['note']); ?></label>
                            <input
                                type="text"
                                name="note"
                                class="form-control"
                                placeholder="<?php echo e($currentLang === 'bn' ? 'ঐচ্ছিক নোট' : 'Optional note'); ?>"
                                value="<?php echo e($_POST['note'] ?? ''); ?>"
                            >
                        </div>

                        <div class="col-12 d-flex flex-column flex-sm-row gap-2">
                            <button type="submit" class="btn btn-hero-cta px-4">
                                <?php echo e($lang['save']); ?>
                            </button>
                            <a href="/expense-tracker/dashboard.php" class="btn btn-outline-secondary px-4">
                                <?php echo e($lang['reset']); ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- Analytics chart -->
        <div class="col-12" id="chartSectionWrap">
            <div class="card border-0 shadow-lg analytics-card">
                <div class="card-body p-4">
                    <div class="analytics-top">
                        <div>
                            <h4 class="section-title mb-1">
                                <?php echo e($currentLang === 'bn' ? 'আয়-ব্যয়ের চার্ট' : 'Income vs Expense Chart'); ?>
                            </h4>
                            <p class="soft-muted mb-0">
                                <?php echo e($currentLang === 'bn' ? 'চার্ট দেখতে চাইলে ওপেন করুন, না চাইলে মিনিমাইজ রাখুন।' : 'Open the chart when needed, or keep it minimized.'); ?>
                            </p>
                            <div class="analytics-meta">
                                <span class="analytics-badge">
                                    <?php echo e($currentLang === 'bn' ? 'আয় (ফিল্টার)' : 'Income (filtered)'); ?>:
                                    <?php echo e(format_bdt($filteredIncome, 0)); ?>
                                </span>
                                <span class="analytics-badge">
                                    <?php echo e($currentLang === 'bn' ? 'খরচ (ফিল্টার)' : 'Expense (filtered)'); ?>:
                                    <?php echo e(format_bdt($filteredExpense, 0)); ?>
                                </span>
                                <span class="analytics-badge">
                                    <?php echo e($currentLang === 'bn' ? 'ব্যালেন্স (ফিল্টার)' : 'Balance (filtered)'); ?>:
                                    <?php echo e(format_bdt($filteredIncome - $filteredExpense, 0)); ?>
                                </span>
                            </div>
                        </div>
                        <button
                            class="btn btn-outline-primary chart-toggle-btn"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#analyticsChartCollapse"
                            aria-expanded="false"
                            aria-controls="analyticsChartCollapse"
                            id="analyticsToggleBtn"
                        >
                            <?php echo e($currentLang === 'bn' ? 'চার্ট দেখুন' : 'Show Chart'); ?>
                        </button>
                    </div>
                    <div class="collapse mt-3" id="analyticsChartCollapse">
                        <div class="analytics-chart-layout">
                            <?php if (($filteredIncome + $filteredExpense) > 0): ?>
                                <div class="analytics-chart-wrap">
                                    <canvas id="financePieChart"></canvas>
                                </div>
                            <?php else: ?>
                                <div class="analytics-empty">
                                    <?php echo e($currentLang === 'bn' ? 'এখনও পর্যাপ্ত ডাটা নেই, তাই চার্ট দেখানো যাচ্ছে না।' : 'Not enough data yet to display the chart.'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search / filter -->
        <div class="col-12">
            <div class="card border-0 shadow-lg search-filter-card">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-2 mb-3">
                        <div>
                            <h4 class="section-title mb-1"><?php echo e($lang['search_expenses']); ?></h4>
                            <p class="soft-muted mb-0">
                                <?php echo e($currentLang === 'bn' ? 'কীওয়ার্ড, ক্যাটাগরি, ধরন এবং তারিখ দিয়ে ফিল্টার করুন।' : 'Filter by keyword, category, type, and date.'); ?>
                            </p>
                        </div>
                    </div>
                    <form method="GET" class="search-filter-grid search-filter-grid-wide" id="dashboardFilterForm">
                        <div class="search-field search-field-keyword">
                            <label class="form-label fw-semibold mb-2">
                                <?php echo e($currentLang === 'bn' ? 'কীওয়ার্ড' : 'Keyword'); ?>
                            </label>
                            <input type="text" name="search" class="form-control" value="<?php echo e($search); ?>" placeholder="<?php echo e($currentLang === 'bn' ? 'টাইটেল বা নোট দিয়ে সার্চ করুন' : 'Search by title or note'); ?>">
                        </div>
                        <div class="search-field">
                            <label class="form-label fw-semibold mb-2"><?php echo e($lang['category']); ?></label>
                            <select name="category" class="form-select form-control">
                                <option value="0"><?php echo e($lang['all_categories']); ?></option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo (int) $category['id']; ?>" <?php echo $categoryFilter === (int) $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo e($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="search-field">
                            <label class="form-label fw-semibold mb-2"><?php echo e($lang['transaction_type']); ?></label>
                            <select name="filter_type" class="form-select form-control">
                                <option value=""><?php echo e($currentLang === 'bn' ? 'সব ধরন' : 'All Types'); ?></option>
                                <option value="expense" <?php echo $typeFilter === 'expense' ? 'selected' : ''; ?>><?php echo e($lang['expense']); ?></option>
                                <option value="income" <?php echo $typeFilter === 'income' ? 'selected' : ''; ?>><?php echo e($lang['income']); ?></option>
                            </select>
                        </div>
                        <div class="search-field">
                            <label class="form-label fw-semibold mb-2"><?php echo e($lang['date_filter_label']); ?></label>
                            <select name="date_preset" class="form-select form-control" id="datePresetSelect">
                                <option value=""><?php echo e($currentLang === 'bn' ? 'সব সময়' : 'All Time'); ?></option>
                                <option value="today" <?php echo $datePreset === 'today' ? 'selected' : ''; ?>><?php echo e($currentLang === 'bn' ? 'আজ' : 'Today'); ?></option>
                                <option value="yesterday" <?php echo $datePreset === 'yesterday' ? 'selected' : ''; ?>><?php echo e($currentLang === 'bn' ? 'গতকাল' : 'Yesterday'); ?></option>
                                <option value="last7" <?php echo $datePreset === 'last7' ? 'selected' : ''; ?>><?php echo e($currentLang === 'bn' ? 'শেষ ৭ দিন' : 'Last 7 Days'); ?></option>
                                <option value="this_month" <?php echo $datePreset === 'this_month' ? 'selected' : ''; ?>><?php echo e($currentLang === 'bn' ? 'এই মাস' : 'This Month'); ?></option>
                                <option value="last_month" <?php echo $datePreset === 'last_month' ? 'selected' : ''; ?>><?php echo e($currentLang === 'bn' ? 'গত মাস' : 'Last Month'); ?></option>
                                <option value="custom" <?php echo $datePreset === 'custom' ? 'selected' : ''; ?>><?php echo e($currentLang === 'bn' ? 'কাস্টম' : 'Custom'); ?></option>
                            </select>
                        </div>
                        <div class="search-field custom-date-field" style="<?php echo $datePreset === 'custom' ? '' : 'display:none;'; ?>">
                            <label class="form-label fw-semibold mb-2"><?php echo e($currentLang === 'bn' ? 'শুরুর তারিখ' : 'From Date'); ?></label>
                            <input type="date" name="date_from" class="form-control" value="<?php echo e($dateFrom); ?>">
                        </div>
                        <div class="search-field custom-date-field" style="<?php echo $datePreset === 'custom' ? '' : 'display:none;'; ?>">
                            <label class="form-label fw-semibold mb-2"><?php echo e($currentLang === 'bn' ? 'শেষ তারিখ' : 'To Date'); ?></label>
                            <input type="date" name="date_to" class="form-control" value="<?php echo e($dateTo); ?>">
                        </div>
                        <div class="search-actions">
                            <button type="submit" class="btn btn-search-submit"><?php echo e($lang['search']); ?></button>
                            <a href="/expense-tracker/dashboard.php" class="btn btn-outline-secondary"><?php echo e($lang['reset']); ?></a>
                            <?php if (!$guestView): ?>
                                <a href="<?php echo e($exportCsvHref); ?>" class="btn btn-export-csv"><?php echo e($lang['export_csv']); ?></a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
      <div class="col-12" id="transactionSectionWrap">
    <div class="card border-0 shadow-lg table-card">
        <div class="card-body p-0">
            <div class="p-4 border-bottom">
                <h4 class="section-title mb-0"><?php echo e($lang['recent_expenses']); ?></h4>
            </div>

            <?php if (count($transactionRows) > 0): ?>
                <div class="desktop-transaction-table table-transactions">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th><?php echo e($lang['title']); ?></th>
                                    <th><?php echo e($lang['transaction_type']); ?></th>
                                    <th><?php echo e($lang['category']); ?></th>
                                    <th><?php echo e($lang['payment_method']); ?></th>
                                    <th><?php echo e($lang['amount']); ?></th>
                                    <th><?php echo e($lang['date']); ?></th>
                                    <th><?php echo e($lang['note']); ?></th>
                                    <th class="text-center"><?php echo e($lang['action']); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactionRows as $item): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo e($item['title']); ?></td>
                                        <td>
                                            <?php if ($item['transaction_type'] === 'income'): ?>
                                                <span class="badge bg-success"><?php echo e($lang['income']); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><?php echo e($lang['expense']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($item['category_name'] ?? $lang['no_category']); ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo e($lang[$item['payment_method']] ?? ucfirst($item['payment_method'])); ?>
                                            </span>
                                        </td>
                                        <td class="<?php echo $item['transaction_type'] === 'income' ? 'text-success' : 'text-danger'; ?> fw-semibold">
                                            ৳ <?php echo number_format((float) $item['amount'], 2); ?>
                                        </td>
                                        <td><?php echo e($item['expense_date']); ?></td>
                                        <td><?php echo e($item['note'] ?? ''); ?></td>
                                        <td class="text-center">
                                            <?php if (!$guestView): ?>
                                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                                    <a href="/expense-tracker/edit_transaction.php?id=<?php echo (int) $item['id']; ?>" class="btn btn-sm btn-action-edit">
                                                        <?php echo e($lang['edit']); ?>
                                                    </a>
                                                    <form method="POST" action="/expense-tracker/delete_transaction.php" onsubmit="return confirm('<?php echo e($lang['delete_confirm']); ?>');">
                                                        <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-action-delete">
                                                            <?php echo e($lang['delete']); ?>
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <span class="small soft-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mobile-transaction-list p-3">
                    <?php foreach ($transactionRows as $item): ?>
                        <div class="transaction-card p-3">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <div class="tc-label"><?php echo e($lang['title']); ?></div>
                                    <div class="tc-value"><?php echo e($item['title']); ?></div>
                                </div>

                                <div class="text-end">
                                    <?php if ($item['transaction_type'] === 'income'): ?>
                                        <span class="badge bg-success"><?php echo e($lang['income']); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><?php echo e($lang['expense']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <div class="tc-label"><?php echo e($lang['category']); ?></div>
                                    <div class="tc-value"><?php echo e($item['category_name'] ?? $lang['no_category']); ?></div>
                                </div>

                                <div class="col-6">
                                    <div class="tc-label"><?php echo e($lang['payment_method']); ?></div>
                                    <div class="tc-value"><?php echo e($lang[$item['payment_method']] ?? ucfirst($item['payment_method'])); ?></div>
                                </div>

                                <div class="col-6">
                                    <div class="tc-label"><?php echo e($lang['amount']); ?></div>
                                    <div class="tc-value <?php echo $item['transaction_type'] === 'income' ? 'tc-amount-income' : 'tc-amount-expense'; ?>">
                                        ৳ <?php echo number_format((float) $item['amount'], 2); ?>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="tc-label"><?php echo e($lang['date']); ?></div>
                                    <div class="tc-value"><?php echo e($item['expense_date']); ?></div>
                                </div>

                                <div class="col-12">
                                    <div class="tc-label"><?php echo e($lang['note']); ?></div>
                                    <div class="tc-value"><?php echo e($item['note'] ?? ''); ?></div>
                                </div>
                            </div>

                            <div class="tc-actions">
                                <?php if (!$guestView): ?>
                                    <a href="/expense-tracker/edit_transaction.php?id=<?php echo (int) $item['id']; ?>" class="btn btn-sm btn-action-edit">
                                        <?php echo e($lang['edit']); ?>
                                    </a>
                                    <form method="POST" action="/expense-tracker/delete_transaction.php" onsubmit="return confirm('<?php echo e($lang['delete_confirm']); ?>');">
                                        <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-action-delete">
                                            <?php echo e($lang['delete']); ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 soft-muted">
                    <?php echo e($lang['no_expenses_yet']); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function initDatePresetToggle() {
        const datePresetSelect = document.getElementById('datePresetSelect');
        const customDateFields = document.querySelectorAll('.custom-date-field');

        function toggleCustomDateFields() {
            if (!datePresetSelect) return;

            const showCustom = datePresetSelect.value === 'custom';

            customDateFields.forEach(function (field) {
                field.style.display = showCustom ? '' : 'none';
            });
        }

        if (datePresetSelect) {
            datePresetSelect.addEventListener('change', toggleCustomDateFields);
            toggleCustomDateFields();
        }
    }

    function initChartToggle(collapseId, buttonId) {
        const collapseEl = document.getElementById(collapseId);
        const toggleBtn = document.getElementById(buttonId);

        if (collapseEl && toggleBtn) {
            collapseEl.addEventListener('shown.bs.collapse', function () {
                toggleBtn.textContent = <?php echo json_encode($currentLang === 'bn' ? 'চার্ট মিনিমাইজ করুন' : 'Minimize Chart'); ?>;
            });

            collapseEl.addEventListener('hidden.bs.collapse', function () {
                toggleBtn.textContent = <?php echo json_encode($currentLang === 'bn' ? 'চার্ট দেখুন' : 'Show Chart'); ?>;
            });
        }
    }

    function renderDoughnutChart(canvasId, incomeValue, expenseValue) {
        const chartCanvas = document.getElementById(canvasId);

        if (!chartCanvas) return;

        const existing = typeof Chart !== 'undefined' ? Chart.getChart(chartCanvas) : null;
        if (existing) {
            existing.destroy();
        }

        const isDark = document.body.classList.contains('theme-dark');
        const textColor = isDark ? '#e5e7eb' : '#334155';

        new Chart(chartCanvas, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php echo json_encode($currentLang === 'bn' ? 'আয়' : 'Income'); ?>,
                    <?php echo json_encode($currentLang === 'bn' ? 'খরচ' : 'Expense'); ?>
                ],
                datasets: [{
                    data: [incomeValue, expenseValue],
                    backgroundColor: <?php echo json_encode(['#0f766e', '#c2410c']); ?>,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                aspectRatio: 1.15,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            boxWidth: 12,
                            padding: 14
                        }
                    }
                }
            }
        });
    }

    // initial page chart
    renderDoughnutChart(
        'financePieChart',
        <?php echo isset($filteredIncome) ? (float) $filteredIncome : 0; ?>,
        <?php echo isset($filteredExpense) ? (float) $filteredExpense : 0; ?>
    );

    initDatePresetToggle();
    initChartToggle('analyticsChartCollapse', 'analyticsToggleBtn');

    <?php if (!$guestView): ?>
    const filterForm = document.getElementById('dashboardFilterForm');
    const chartWrap = document.getElementById('chartSectionWrap');
    const transactionWrap = document.getElementById('transactionSectionWrap');

    if (filterForm && chartWrap && transactionWrap) {
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);

            fetch('/expense-tracker/dashboard_filter.php?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.chartHtml) {
                    chartWrap.innerHTML = data.chartHtml;
                }

                if (data.transactionsHtml) {
                    transactionWrap.innerHTML = data.transactionsHtml;
                }

                renderDoughnutChart(
                    'financePieChartAjax',
                    data.filteredIncome || 0,
                    data.filteredExpense || 0
                );

                initChartToggle('analyticsChartCollapseAjax', 'analyticsToggleBtnAjax');
            })
            .catch(error => {
                console.error('Filter request failed:', error);
            });
        });
    }
    <?php endif; ?>
});
</script>
<?php
require_once __DIR__ . '/includes/footer.php';
?>