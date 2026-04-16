<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';

if (!is_logged_in()) {
    header('Location: /expense-tracker/login.php?next=' . rawurlencode('/expense-tracker/dashboard.php'));
    exit();
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

$sql = "
    SELECT
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
    $st = '%' . $search . '%';
    $params[] = $st;
    $params[] = $st;
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

$sql .= ' ORDER BY expenses.expense_date DESC, expenses.id DESC LIMIT 10000';

$stmt = $conn->prepare($sql);
$bind = [];
$bind[] = $types;
foreach ($params as $k => &$v) {
    $bind[] = &$v;
}
unset($v);
call_user_func_array([$stmt, 'bind_param'], $bind);
$stmt->execute();
$result = $stmt->get_result();

$headers = [
    'title' => $lang['title'],
    'type' => $lang['transaction_type'],
    'category' => $lang['category'],
    'payment' => $lang['payment_method'],
    'amount' => $lang['amount'],
    'date' => $lang['date'],
    'note' => $lang['note'],
];

$filename = 'expenses_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($out, array_values($headers));

while ($row = $result->fetch_assoc()) {
    $pm = $row['payment_method'];
    $payLabel = $lang[$pm] ?? $pm;
    fputcsv($out, [
        $row['title'],
        $row['transaction_type'] === 'income' ? $lang['income'] : $lang['expense'],
        $row['category_name'] ?? '',
        $payLabel,
        number_format((float) $row['amount'], 2, '.', ''),
        $row['expense_date'],
        $row['note'] ?? '',
    ]);
}

fclose($out);
$stmt->close();
