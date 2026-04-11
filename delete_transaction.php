<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /expense-tracker/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /expense-tracker/dashboard.php');
    exit();
}

$userId = (int) $_SESSION['user_id'];
$transactionId = (int) ($_POST['id'] ?? 0);

if ($transactionId <= 0) {
    $_SESSION['dashboard_success'] = $currentLang === 'bn'
        ? 'সঠিক লেনদেন নির্বাচন করা হয়নি।'
        : 'Invalid transaction selected.';
    header('Location: /expense-tracker/dashboard.php');
    exit();
}

$checkStmt = $conn->prepare("SELECT id FROM expenses WHERE id = ? AND user_id = ?");
$checkStmt->bind_param('ii', $transactionId, $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    $checkStmt->close();
    $_SESSION['dashboard_success'] = $currentLang === 'bn'
        ? 'এই লেনদেনটি আপনার নয় বা পাওয়া যায়নি।'
        : 'Transaction not found or does not belong to you.';
    header('Location: /expense-tracker/dashboard.php');
    exit();
}
$checkStmt->close();

$deleteStmt = $conn->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
$deleteStmt->bind_param('ii', $transactionId, $userId);

if ($deleteStmt->execute()) {
    $_SESSION['dashboard_success'] = $currentLang === 'bn'
        ? 'লেনদেন সফলভাবে ডিলিট হয়েছে।'
        : 'Transaction deleted successfully.';
} else {
    $_SESSION['dashboard_success'] = $currentLang === 'bn'
        ? 'লেনদেন ডিলিট করা যায়নি।'
        : 'Failed to delete transaction.';
}

$deleteStmt->close();

header('Location: /expense-tracker/dashboard.php');
exit();