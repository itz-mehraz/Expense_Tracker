<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!function_exists('bindDynamicParamsAjax')) {
    function bindDynamicParamsAjax(mysqli_stmt $stmt, string $types, array &$params): void
    {
        $bind = [];
        $bind[] = $types;

        foreach ($params as $key => &$value) {
            $bind[] = &$value;
        }

        call_user_func_array([$stmt, 'bind_param'], $bind);
    }
}

$userId = (int) $_SESSION['user_id'];

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

$extraSql = '';
$extraTypes = '';
$extraParams = [];

if ($search !== '') {
    $extraSql .= " AND (expenses.title LIKE ? OR expenses.note LIKE ?)";
    $searchLike = '%' . $search . '%';
    $extraTypes .= 'ss';
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

/*
|--------------------------------------------------------------------------
| Chart summary
|--------------------------------------------------------------------------
*/
$filteredIncome = 0;
$filteredExpense = 0;
$filteredCount = 0;

$chartSql = "
    SELECT
        COALESCE(SUM(CASE WHEN expenses.transaction_type = 'income' THEN expenses.amount ELSE 0 END), 0) AS filtered_income,
        COALESCE(SUM(CASE WHEN expenses.transaction_type = 'expense' THEN expenses.amount ELSE 0 END), 0) AS filtered_expense,
        COUNT(*) AS filtered_count
    FROM expenses
    WHERE expenses.user_id = ?
    $extraSql
";

$chartStmt = $conn->prepare($chartSql);
$chartParams = array_merge([$userId], $extraParams);
$chartTypes = 'i' . $extraTypes;
bindDynamicParamsAjax($chartStmt, $chartTypes, $chartParams);
$chartStmt->execute();
$chartRow = $chartStmt->get_result()->fetch_assoc();
$filteredIncome = (float) ($chartRow['filtered_income'] ?? 0);
$filteredExpense = (float) ($chartRow['filtered_expense'] ?? 0);
$filteredCount = (int) ($chartRow['filtered_count'] ?? 0);
$chartStmt->close();

/*
|--------------------------------------------------------------------------
| Transaction list
|--------------------------------------------------------------------------
*/
$listSql = "
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
    $extraSql
    ORDER BY expenses.expense_date DESC, expenses.id DESC
    LIMIT 15
";

$listStmt = $conn->prepare($listSql);
$listParams = array_merge([$userId], $extraParams);
$listTypes = 'i' . $extraTypes;
bindDynamicParamsAjax($listStmt, $listTypes, $listParams);
$listStmt->execute();
$listResult = $listStmt->get_result();

$transactionRows = [];
while ($row = $listResult->fetch_assoc()) {
    $transactionRows[] = $row;
}
$listStmt->close();

/*
|--------------------------------------------------------------------------
| Render chart html
|--------------------------------------------------------------------------
*/
ob_start();
?>
<div class="card border-0 shadow-lg analytics-card">
    <div class="card-body p-4">
        <div class="analytics-top">
            <div>
                <h4 class="section-title mb-1">
                    <?php echo e($currentLang === 'bn' ? 'ফিল্টার করা চার্ট' : 'Filtered Chart'); ?>
                </h4>
                <p class="soft-muted mb-0">
                    <?php echo e($currentLang === 'bn'
                        ? 'নির্বাচিত ফিল্টার অনুযায়ী চার্ট আপডেট হয়েছে।'
                        : 'Chart updated according to the selected filters.'); ?>
                </p>

                <div class="analytics-meta">
                    <span class="analytics-badge">
                        <?php echo e($currentLang === 'bn' ? 'আয়' : 'Income'); ?>:
                        ৳ <?php echo number_format($filteredIncome, 0); ?>
                    </span>
                    <span class="analytics-badge">
                        <?php echo e($currentLang === 'bn' ? 'খরচ' : 'Expense'); ?>:
                        ৳ <?php echo number_format($filteredExpense, 0); ?>
                    </span>
                    <span class="analytics-badge">
                        <?php echo e($currentLang === 'bn' ? 'ফলাফল' : 'Results'); ?>:
                        <?php echo $filteredCount; ?>
                    </span>
                </div>
            </div>

            <button
                class="btn btn-outline-primary chart-toggle-btn"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#analyticsChartCollapseAjax"
                aria-expanded="false"
                aria-controls="analyticsChartCollapseAjax"
                id="analyticsToggleBtnAjax"
            >
                <?php echo e($currentLang === 'bn' ? 'চার্ট দেখুন' : 'Show Chart'); ?>
            </button>
        </div>

        <div class="collapse mt-3" id="analyticsChartCollapseAjax">
            <div class="analytics-chart-layout">
                <?php if (($filteredIncome + $filteredExpense) > 0): ?>
                    <div class="analytics-chart-wrap">
                        <canvas id="financePieChartAjax"></canvas>
                    </div>
                <?php else: ?>
                    <div class="analytics-empty">
                        <?php echo e($currentLang === 'bn'
                            ? 'এই ফিল্টারের জন্য কোনো চার্ট ডাটা পাওয়া যায়নি।'
                            : 'No chart data found for this filter.'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$chartHtml = ob_get_clean();

/*
|--------------------------------------------------------------------------
| Render transaction html
|--------------------------------------------------------------------------
*/
ob_start();
?>
<div class="card border-0 shadow-lg table-card">
    <div class="card-body p-0">
        <div class="p-4 border-bottom">
            <h4 class="section-title mb-0"><?php echo e($lang['recent_expenses']); ?></h4>
        </div>

        <?php if (!empty($transactionRows)): ?>
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
                                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                                            <a href="/expense-tracker/edit_transaction.php?id=<?php echo (int) $item['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <?php echo e($lang['edit']); ?>
                                            </a>

                                            <form method="POST" action="/expense-tracker/delete_transaction.php" onsubmit="return confirm('<?php echo e($lang['delete_confirm']); ?>');">
                                                <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <?php echo e($lang['delete']); ?>
                                                </button>
                                            </form>
                                        </div>
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
                            <a href="/expense-tracker/edit_transaction.php?id=<?php echo (int) $item['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <?php echo e($lang['edit']); ?>
                            </a>

                            <form method="POST" action="/expense-tracker/delete_transaction.php" onsubmit="return confirm('<?php echo e($lang['delete_confirm']); ?>');">
                                <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <?php echo e($lang['delete']); ?>
                                </button>
                            </form>
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
<?php
$transactionsHtml = ob_get_clean();

header('Content-Type: application/json');
echo json_encode([
    'chartHtml' => $chartHtml,
    'transactionsHtml' => $transactionsHtml,
    'filteredIncome' => $filteredIncome,
    'filteredExpense' => $filteredExpense
]);