<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /expense-tracker/login.php');
    exit();
}

$userId = (int) $_SESSION['user_id'];
$transactionId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($transactionId <= 0) {
    header('Location: /expense-tracker/dashboard.php');
    exit();
}

$categories = [];
$catStmt = $conn->prepare("SELECT id, name FROM categories WHERE user_id = ? ORDER BY name ASC");
$catStmt->bind_param('i', $userId);
$catStmt->execute();
$catResult = $catStmt->get_result();
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row;
}
$catStmt->close();

$stmt = $conn->prepare("
    SELECT id, category_id, transaction_type, payment_method, title, amount, expense_date, note
    FROM expenses
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param('ii', $transactionId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$transaction = $result->fetch_assoc();
$stmt->close();

if (!$transaction) {
    header('Location: /expense-tracker/dashboard.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $transactionType = $_POST['transaction_type'] ?? 'expense';
    $paymentMethod = $_POST['payment_method'] ?? 'cash';
    $expenseDate = $_POST['expense_date'] ?? '';
    $note = trim($_POST['note'] ?? '');

    if ($title === '') {
        $errors[] = $currentLang === 'bn' ? 'শিরোনাম দেওয়া আবশ্যক।' : 'Title is required.';
    }

    if ($amount === '' || !is_numeric($amount) || (float) $amount <= 0) {
        $errors[] = $currentLang === 'bn' ? 'সঠিক পরিমাণ লিখুন।' : 'Enter a valid amount.';
    }

    if ($categoryId <= 0) {
        $errors[] = $currentLang === 'bn' ? 'ক্যাটাগরি নির্বাচন করুন।' : 'Please select a category.';
    }

    if (!in_array($transactionType, ['expense', 'income'], true)) {
        $errors[] = $currentLang === 'bn' ? 'সঠিক লেনদেনের ধরন নির্বাচন করুন।' : 'Select a valid transaction type.';
    }

    if (!in_array($paymentMethod, ['bkash', 'nagad', 'cash', 'bank', 'card', 'other'], true)) {
        $errors[] = $currentLang === 'bn' ? 'সঠিক পেমেন্ট মেথড নির্বাচন করুন।' : 'Select a valid payment method.';
    }

    if ($expenseDate === '') {
        $errors[] = $currentLang === 'bn' ? 'তারিখ নির্বাচন করুন।' : 'Please select a date.';
    }

    if (empty($errors)) {
        $checkCategory = $conn->prepare("SELECT id FROM categories WHERE id = ? AND user_id = ?");
        $checkCategory->bind_param('ii', $categoryId, $userId);
        $checkCategory->execute();
        $checkCategoryResult = $checkCategory->get_result();

        if ($checkCategoryResult->num_rows === 0) {
            $errors[] = $currentLang === 'bn' ? 'এই ক্যাটাগরি আপনার নয়।' : 'This category does not belong to you.';
        }
        $checkCategory->close();
    }

    if (empty($errors)) {
        $amountValue = (float) $amount;

        $updateStmt = $conn->prepare("
            UPDATE expenses
            SET category_id = ?, transaction_type = ?, payment_method = ?, title = ?, amount = ?, expense_date = ?, note = ?
            WHERE id = ? AND user_id = ?
        ");

        $updateStmt->bind_param(
            'isssdssii',
            $categoryId,
            $transactionType,
            $paymentMethod,
            $title,
            $amountValue,
            $expenseDate,
            $note,
            $transactionId,
            $userId
        );

        if ($updateStmt->execute()) {
            $_SESSION['dashboard_success'] = $currentLang === 'bn'
                ? 'লেনদেন সফলভাবে আপডেট হয়েছে।'
                : 'Transaction updated successfully.';
            $updateStmt->close();
            header('Location: /expense-tracker/dashboard.php');
            exit();
        } else {
            $errors[] = $currentLang === 'bn'
                ? 'লেনদেন আপডেট করা যায়নি।'
                : 'Failed to update transaction.';
        }

        $updateStmt->close();
    }

    $transaction['title'] = $title;
    $transaction['amount'] = $amount;
    $transaction['category_id'] = $categoryId;
    $transaction['transaction_type'] = $transactionType;
    $transaction['payment_method'] = $paymentMethod;
    $transaction['expense_date'] = $expenseDate;
    $transaction['note'] = $note;
}

$pageTitle = $currentLang === 'bn' ? 'লেনদেন এডিট করুন' : 'Edit Transaction';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container page-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold mb-1">
                                <?php echo e($currentLang === 'bn' ? 'লেনদেন এডিট করুন' : 'Edit Transaction'); ?>
                            </h2>
                            <p class="soft-muted mb-0">
                                <?php echo e($currentLang === 'bn'
                                    ? 'আপনার আয় বা খরচের তথ্য আপডেট করুন।'
                                    : 'Update your income or expense information.'); ?>
                            </p>
                        </div>
                        <a href="/expense-tracker/dashboard.php" class="btn btn-outline-secondary">
                            <?php echo e($lang['cancel']); ?>
                        </a>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="row g-3">
                        <input type="hidden" name="id" value="<?php echo (int) $transactionId; ?>">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?php echo e($lang['title']); ?></label>
                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                value="<?php echo e($transaction['title']); ?>"
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
                                value="<?php echo e((string) $transaction['amount']); ?>"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold"><?php echo e($lang['transaction_type']); ?></label>
                            <select name="transaction_type" class="form-select form-control">
                                <option value="expense" <?php echo $transaction['transaction_type'] === 'expense' ? 'selected' : ''; ?>>
                                    <?php echo e($lang['expense']); ?>
                                </option>
                                <option value="income" <?php echo $transaction['transaction_type'] === 'income' ? 'selected' : ''; ?>>
                                    <?php echo e($lang['income']); ?>
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold"><?php echo e($lang['category']); ?></label>
                            <select name="category_id" class="form-select form-control">
                                <option value="0"><?php echo e($lang['select_category']); ?></option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo (int) $category['id']; ?>" <?php echo (int) $transaction['category_id'] === (int) $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo e($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold"><?php echo e($lang['payment_method']); ?></label>
                            <select name="payment_method" class="form-select form-control">
                                <?php
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
                                    <option value="<?php echo e($key); ?>" <?php echo $transaction['payment_method'] === $key ? 'selected' : ''; ?>>
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
                                value="<?php echo e($transaction['expense_date']); ?>"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?php echo e($lang['note']); ?></label>
                            <input
                                type="text"
                                name="note"
                                class="form-control"
                                value="<?php echo e($transaction['note'] ?? ''); ?>"
                            >
                        </div>

                        <div class="col-12 d-flex flex-column flex-sm-row gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <?php echo e($lang['update']); ?>
                            </button>
                            <a href="/expense-tracker/dashboard.php" class="btn btn-outline-secondary px-4">
                                <?php echo e($lang['cancel']); ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>