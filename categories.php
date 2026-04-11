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
$successMessage = '';
$errors = [];

$editId = (int) ($_GET['edit'] ?? 0);
$editCategory = null;

if (isset($_SESSION['category_success'])) {
    $successMessage = $_SESSION['category_success'];
    unset($_SESSION['category_success']);
}

/*
|--------------------------------------------------------------------------
| Add / Update Category
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        $errors[] = $currentLang === 'bn' ? 'ক্যাটাগরির নাম দেওয়া আবশ্যক।' : 'Category name is required.';
    }

    if (mb_strlen($name) > 100) {
        $errors[] = $currentLang === 'bn' ? 'ক্যাটাগরির নাম খুব বড় হয়েছে।' : 'Category name is too long.';
    }

    if (empty($errors)) {
        if ($categoryId > 0) {
            $checkStmt = $conn->prepare("SELECT id FROM categories WHERE id = ? AND user_id = ?");
            $checkStmt->bind_param('ii', $categoryId, $userId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows === 0) {
                $errors[] = $currentLang === 'bn' ? 'ক্যাটাগরি পাওয়া যায়নি।' : 'Category not found.';
            }
            $checkStmt->close();
        }
    }

    if (empty($errors)) {
        $duplicateStmt = $conn->prepare("
            SELECT id 
            FROM categories 
            WHERE user_id = ? AND name = ? AND id != ?
        ");
        $duplicateStmt->bind_param('isi', $userId, $name, $categoryId);
        $duplicateStmt->execute();
        $duplicateResult = $duplicateStmt->get_result();

        if ($duplicateResult->num_rows > 0) {
            $errors[] = $currentLang === 'bn'
                ? 'এই নামের ক্যাটাগরি আগেই আছে।'
                : 'A category with this name already exists.';
        }
        $duplicateStmt->close();
    }

    if (empty($errors)) {
        if ($categoryId > 0) {
            $updateStmt = $conn->prepare("
                UPDATE categories
                SET name = ?
                WHERE id = ? AND user_id = ?
            ");
            $updateStmt->bind_param('sii', $name, $categoryId, $userId);

            if ($updateStmt->execute()) {
                $_SESSION['category_success'] = $currentLang === 'bn'
                    ? 'ক্যাটাগরি সফলভাবে আপডেট হয়েছে।'
                    : 'Category updated successfully.';
                $updateStmt->close();
                header('Location: /expense-tracker/categories.php');
                exit();
            } else {
                $errors[] = $currentLang === 'bn'
                    ? 'ক্যাটাগরি আপডেট করা যায়নি।'
                    : 'Failed to update category.';
            }

            $updateStmt->close();
        } else {
            $insertStmt = $conn->prepare("
                INSERT INTO categories (user_id, name)
                VALUES (?, ?)
            ");
            $insertStmt->bind_param('is', $userId, $name);

            if ($insertStmt->execute()) {
                $_SESSION['category_success'] = $currentLang === 'bn'
                    ? 'ক্যাটাগরি সফলভাবে যোগ হয়েছে।'
                    : 'Category added successfully.';
                $insertStmt->close();
                header('Location: /expense-tracker/categories.php');
                exit();
            } else {
                $errors[] = $currentLang === 'bn'
                    ? 'ক্যাটাগরি যোগ করা যায়নি।'
                    : 'Failed to add category.';
            }

            $insertStmt->close();
        }
    }
}

/*
|--------------------------------------------------------------------------
| Delete Category
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $deleteId = (int) ($_POST['delete_id'] ?? 0);

    if ($deleteId > 0) {
        $checkUsageStmt = $conn->prepare("
            SELECT COUNT(*) AS total
            FROM expenses
            WHERE category_id = ? AND user_id = ?
        ");
        $checkUsageStmt->bind_param('ii', $deleteId, $userId);
        $checkUsageStmt->execute();
        $usageResult = $checkUsageStmt->get_result()->fetch_assoc();
        $checkUsageStmt->close();

        if ((int) ($usageResult['total'] ?? 0) > 0) {
            $_SESSION['category_success'] = $currentLang === 'bn'
                ? 'এই ক্যাটাগরিতে লেনদেন আছে, তাই ডিলিট করা যায়নি।'
                : 'This category has transactions, so it cannot be deleted.';
        } else {
            $deleteStmt = $conn->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
            $deleteStmt->bind_param('ii', $deleteId, $userId);

            if ($deleteStmt->execute()) {
                $_SESSION['category_success'] = $currentLang === 'bn'
                    ? 'ক্যাটাগরি সফলভাবে ডিলিট হয়েছে।'
                    : 'Category deleted successfully.';
            } else {
                $_SESSION['category_success'] = $currentLang === 'bn'
                    ? 'ক্যাটাগরি ডিলিট করা যায়নি।'
                    : 'Failed to delete category.';
            }

            $deleteStmt->close();
        }
    }

    header('Location: /expense-tracker/categories.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Edit Category Load
|--------------------------------------------------------------------------
*/
if ($editId > 0) {
    $editStmt = $conn->prepare("
        SELECT id, name
        FROM categories
        WHERE id = ? AND user_id = ?
    ");
    $editStmt->bind_param('ii', $editId, $userId);
    $editStmt->execute();
    $editResult = $editStmt->get_result();
    $editCategory = $editResult->fetch_assoc();
    $editStmt->close();

    if (!$editCategory) {
        header('Location: /expense-tracker/categories.php');
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| Category List
|--------------------------------------------------------------------------
*/
$categories = [];
$listStmt = $conn->prepare("
    SELECT c.id, c.name, COUNT(e.id) AS total_transactions
    FROM categories c
    LEFT JOIN expenses e ON c.id = e.category_id AND e.user_id = c.user_id
    WHERE c.user_id = ?
    GROUP BY c.id, c.name
    ORDER BY c.name ASC
");
$listStmt->bind_param('i', $userId);
$listStmt->execute();
$listResult = $listStmt->get_result();

while ($row = $listResult->fetch_assoc()) {
    $categories[] = $row;
}
$listStmt->close();

$pageTitle = $currentLang === 'bn' ? 'ক্যাটাগরি ব্যবস্থাপনা' : 'Category Management';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container page-wrapper">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                        <div>
                            <h2 class="fw-bold mb-1">
                                <?php echo e($currentLang === 'bn' ? 'ক্যাটাগরি ব্যবস্থাপনা' : 'Category Management'); ?>
                            </h2>
                            <p class="soft-muted mb-0">
                                <?php echo e($currentLang === 'bn'
                                    ? 'বাংলাদেশ ভিত্তিক আয়-ব্যয়ের ক্যাটাগরি যোগ, এডিট ও ডিলিট করুন।'
                                    : 'Add, edit, and delete Bangladesh-based income and expense categories.'); ?>
                            </p>
                        </div>

                        <a href="/expense-tracker/dashboard.php" class="btn btn-outline-secondary">
                            <?php echo e($currentLang === 'bn' ? 'ড্যাশবোর্ডে ফিরুন' : 'Back to Dashboard'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="col-12">
                <div class="alert alert-success shadow-sm mb-0">
                    <?php echo e($successMessage); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="col-12">
                <div class="alert alert-danger shadow-sm mb-0">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-lg-5">
            <div class="card border-0 shadow-lg h-100">
                <div class="card-body p-4">
                    <h4 class="section-title">
                        <?php echo e($editCategory
                            ? ($currentLang === 'bn' ? 'ক্যাটাগরি এডিট করুন' : 'Edit Category')
                            : ($currentLang === 'bn' ? 'নতুন ক্যাটাগরি যোগ করুন' : 'Add New Category')); ?>
                    </h4>

                    <form method="POST" class="row g-3">
                        <input type="hidden" name="save_category" value="1">
                        <input type="hidden" name="category_id" value="<?php echo (int) ($editCategory['id'] ?? 0); ?>">

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <?php echo e($currentLang === 'bn' ? 'ক্যাটাগরির নাম' : 'Category Name'); ?>
                            </label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="<?php echo e($editCategory['name'] ?? ''); ?>"
                                placeholder="<?php echo e($currentLang === 'bn'
                                    ? 'যেমন: বাজার, ভাড়া, বিকাশ, বেতন'
                                    : 'Example: Grocery, Rent, bKash, Salary'); ?>"
                            >
                        </div>

                        <div class="col-12 d-flex flex-column flex-sm-row gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <?php echo e($editCategory ? $lang['update'] : $lang['save']); ?>
                            </button>

                            <?php if ($editCategory): ?>
                                <a href="/expense-tracker/categories.php" class="btn btn-outline-secondary px-4">
                                    <?php echo e($lang['cancel']); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="small soft-muted">
                        <?php echo e($currentLang === 'bn'
                            ? 'টিপস: আয় ও খরচের জন্য আলাদা ক্যাটাগরি রাখতে পারেন, যেমন Salary, Freelance Income, Groceries, Rent, Mobile Recharge, Internet Bill।'
                            : 'Tip: Keep separate categories for income and expense, such as Salary, Freelance Income, Groceries, Rent, Mobile Recharge, and Internet Bill.'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-lg table-card">
                <div class="card-body p-0">
                    <div class="p-4 border-bottom">
                        <h4 class="section-title mb-0">
                            <?php echo e($currentLang === 'bn' ? 'ক্যাটাগরির তালিকা' : 'Category List'); ?>
                        </h4>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th><?php echo e($currentLang === 'bn' ? 'নাম' : 'Name'); ?></th>
                                    <th><?php echo e($currentLang === 'bn' ? 'লেনদেন সংখ্যা' : 'Transactions'); ?></th>
                                    <th class="text-center"><?php echo e($lang['action']); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td class="fw-semibold"><?php echo e($category['name']); ?></td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo (int) $category['total_transactions']; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                                    <a href="/expense-tracker/categories.php?edit=<?php echo (int) $category['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <?php echo e($lang['edit']); ?>
                                                    </a>

                                                    <form method="POST" action="/expense-tracker/categories.php" onsubmit="return confirm('<?php echo e($lang['delete_confirm']); ?>');">
                                                        <input type="hidden" name="delete_category" value="1">
                                                        <input type="hidden" name="delete_id" value="<?php echo (int) $category['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <?php echo e($lang['delete']); ?>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5 soft-muted">
                                            <?php echo e($currentLang === 'bn'
                                                ? 'এখনও কোনো ক্যাটাগরি যোগ করা হয়নি।'
                                                : 'No categories added yet.'); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>