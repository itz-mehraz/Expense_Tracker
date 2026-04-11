<?php
declare(strict_types=1);

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker | Home</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="card shadow-sm border-0 p-4">
                    <h1 class="mb-3">Expense Tracker</h1>
                    <p class="text-muted mb-4">
                        A simple and clean web application to manage your daily expenses,
                        categories, and personal spending records.
                    </p>

                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="signup.php" class="btn btn-primary px-4">Create Account</a>
                        <a href="login.php" class="btn btn-outline-primary px-4">Login</a>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3 text-start">
                        <div class="col-md-4">
                            <div class="feature-box p-3 rounded">
                                <h5>Track Expenses</h5>
                                <p class="mb-0 text-muted">Add, edit, and delete your daily expenses easily.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-box p-3 rounded">
                                <h5>Manage Categories</h5>
                                <p class="mb-0 text-muted">Keep expenses organized with your own categories.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-box p-3 rounded">
                                <h5>View Summary</h5>
                                <p class="mb-0 text-muted">See a quick overview of your expense activity.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-center text-muted mt-4 mb-0">
                    Final Project - Expense Tracker System
                </p>
            </div>
        </div>
    </div>
</body>
</html>