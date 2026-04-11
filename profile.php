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

$stmt = $conn->prepare("SELECT id, name, email, mobile_number FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header('Location: /expense-tracker/login.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Update Profile Info
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobileNumber = trim($_POST['mobile_number'] ?? '');

    if ($name === '') {
        $errors[] = $currentLang === 'bn' ? 'নাম দেওয়া আবশ্যক।' : 'Name is required.';
    }

    if ($email === '') {
        $errors[] = $currentLang === 'bn' ? 'ইমেইল দেওয়া আবশ্যক।' : 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $currentLang === 'bn' ? 'সঠিক ইমেইল দিন।' : 'Please enter a valid email.';
    }

    if ($mobileNumber !== '' && !preg_match('/^[0-9+\-\s]{6,20}$/', $mobileNumber)) {
        $errors[] = $currentLang === 'bn' ? 'সঠিক মোবাইল নম্বর দিন।' : 'Please enter a valid mobile number.';
    }

    if (empty($errors)) {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkStmt->bind_param('si', $email, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $errors[] = $currentLang === 'bn'
                ? 'এই ইমেইল অন্য একটি অ্যাকাউন্টে ব্যবহৃত হচ্ছে।'
                : 'This email is already used by another account.';
        }
        $checkStmt->close();
    }

    if (empty($errors)) {
        $updateStmt = $conn->prepare("
            UPDATE users
            SET name = ?, email = ?, mobile_number = ?
            WHERE id = ?
        ");
        $updateStmt->bind_param('sssi', $name, $email, $mobileNumber, $userId);

        if ($updateStmt->execute()) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            $successMessage = $currentLang === 'bn'
                ? 'প্রোফাইল সফলভাবে আপডেট হয়েছে।'
                : 'Profile updated successfully.';

            $user['name'] = $name;
            $user['email'] = $email;
            $user['mobile_number'] = $mobileNumber;
        } else {
            $errors[] = $currentLang === 'bn'
                ? 'প্রোফাইল আপডেট করা যায়নি।'
                : 'Failed to update profile.';
        }

        $updateStmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| Update Password
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($currentPassword === '') {
        $errors[] = $currentLang === 'bn' ? 'বর্তমান পাসওয়ার্ড দিন।' : 'Current password is required.';
    }

    if ($newPassword === '') {
        $errors[] = $currentLang === 'bn' ? 'নতুন পাসওয়ার্ড দিন।' : 'New password is required.';
    } elseif (strlen($newPassword) < 6) {
        $errors[] = $currentLang === 'bn'
            ? 'নতুন পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।'
            : 'New password must be at least 6 characters.';
    }

    if ($confirmPassword === '') {
        $errors[] = $currentLang === 'bn' ? 'নতুন পাসওয়ার্ড নিশ্চিত করুন।' : 'Please confirm the new password.';
    } elseif ($newPassword !== $confirmPassword) {
        $errors[] = $currentLang === 'bn' ? 'দুইটি পাসওয়ার্ড মেলেনি।' : 'Passwords do not match.';
    }

    if (empty($errors)) {
        $passwordStmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $passwordStmt->bind_param('i', $userId);
        $passwordStmt->execute();
        $passwordRow = $passwordStmt->get_result()->fetch_assoc();
        $passwordStmt->close();

        if (!$passwordRow || !password_verify($currentPassword, $passwordRow['password'])) {
            $errors[] = $currentLang === 'bn'
                ? 'বর্তমান পাসওয়ার্ড সঠিক নয়।'
                : 'Current password is incorrect.';
        }
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $updatePassStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updatePassStmt->bind_param('si', $hashedPassword, $userId);

        if ($updatePassStmt->execute()) {
            $successMessage = $currentLang === 'bn'
                ? 'পাসওয়ার্ড সফলভাবে আপডেট হয়েছে।'
                : 'Password updated successfully.';
        } else {
            $errors[] = $currentLang === 'bn'
                ? 'পাসওয়ার্ড আপডেট করা যায়নি।'
                : 'Failed to update password.';
        }

        $updatePassStmt->close();
    }
}

$pageTitle = $currentLang === 'bn' ? 'প্রোফাইল' : 'Profile';
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
                                <?php echo e($currentLang === 'bn' ? 'প্রোফাইল সেটিংস' : 'Profile Settings'); ?>
                            </h2>
                            <p class="soft-muted mb-0">
                                <?php echo e($currentLang === 'bn'
                                    ? 'আপনার নাম, ইমেইল, মোবাইল নম্বর এবং পাসওয়ার্ড আপডেট করুন।'
                                    : 'Update your name, email, mobile number, and password.'); ?>
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

        <div class="col-lg-6">
            <div class="card border-0 shadow-lg h-100">
                <div class="card-body p-4">
                    <h4 class="section-title">
                        <?php echo e($currentLang === 'bn' ? 'প্রোফাইল তথ্য আপডেট করুন' : 'Update Profile Information'); ?>
                    </h4>

                    <form method="POST" class="row g-3">
                        <input type="hidden" name="update_profile" value="1">

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <?php echo e($currentLang === 'bn' ? 'নাম' : 'Name'); ?>
                            </label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="<?php echo e($user['name'] ?? ''); ?>"
                            >
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <?php echo e($currentLang === 'bn' ? 'ইমেইল' : 'Email'); ?>
                            </label>
                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?php echo e($user['email'] ?? ''); ?>"
                            >
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <?php echo e($currentLang === 'bn' ? 'মোবাইল নম্বর' : 'Mobile Number'); ?>
                            </label>
                            <input
                                type="text"
                                name="mobile_number"
                                class="form-control"
                                value="<?php echo e($user['mobile_number'] ?? ''); ?>"
                                placeholder="<?php echo e($currentLang === 'bn' ? 'যেমন: 017XXXXXXXX' : 'Example: 017XXXXXXXX'); ?>"
                            >
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary px-4">
                                <?php echo e($currentLang === 'bn' ? 'প্রোফাইল আপডেট করুন' : 'Update Profile'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-lg h-100">
                <div class="card-body p-4">
                    <h4 class="section-title">
                        <?php echo e($currentLang === 'bn' ? 'পাসওয়ার্ড পরিবর্তন করুন' : 'Change Password'); ?>
                    </h4>

                    <form method="POST" class="row g-3">
                        <input type="hidden" name="update_password" value="1">

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <?php echo e($currentLang === 'bn' ? 'বর্তমান পাসওয়ার্ড' : 'Current Password'); ?>
                            </label>
                            <input
                                type="password"
                                name="current_password"
                                class="form-control"
                            >
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <?php echo e($currentLang === 'bn' ? 'নতুন পাসওয়ার্ড' : 'New Password'); ?>
                            </label>
                            <input
                                type="password"
                                name="new_password"
                                class="form-control"
                            >
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <?php echo e($currentLang === 'bn' ? 'নতুন পাসওয়ার্ড নিশ্চিত করুন' : 'Confirm New Password'); ?>
                            </label>
                            <input
                                type="password"
                                name="confirm_password"
                                class="form-control"
                            >
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-dark px-4">
                                <?php echo e($currentLang === 'bn' ? 'পাসওয়ার্ড আপডেট করুন' : 'Update Password'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>