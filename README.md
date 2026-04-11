# Expense Tracker Pro

<p align="center">
  <img src="https://raw.githubusercontent.com/itz-mehraz/Expense_Tracker/main/screenshots/dashboard.png" alt="Expense Tracker Pro Banner" width="100%">
</p>

<p align="center">
  <strong>A modern PHP + MySQL income and expense tracking system with Bangla/English support, dark mode, filters, profile management, category management, and analytics chart.</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap">
  <img src="https://img.shields.io/badge/Chart.js-Analytics-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white" alt="Chart.js">
  <img src="https://img.shields.io/badge/Responsive-Mobile%20Friendly-0EA5E9?style=for-the-badge" alt="Responsive">
</p>

---

## Overview

**Expense Tracker Pro** is a complete web-based personal finance management system built with **PHP, MySQL, Bootstrap, HTML, CSS, and JavaScript**.

This project helps users manage:

- Income
- Expenses
- Categories
- Payment methods
- Profile information
- Password updates
- Advanced transaction filtering
- Interactive chart analytics
- Mobile-friendly transaction management

The application supports both **Bangla** and **English**, includes **Light/Dark Mode**, and provides a clean responsive UI for personal finance tracking.

---

## Features

### Authentication

- User registration
- User login
- Secure password hashing
- Logout support
- Session-based authentication

### Dashboard

- Clean overview section
- Total income
- Total expense
- Net balance
- This month income and expense
- Total categories
- Total entries

### Transactions

- Add income
- Add expense
- Edit transaction
- Delete transaction
- Mobile-friendly transaction cards
- Desktop transaction table

### Category Management

- Add category
- Edit category
- Delete category
- Category usage count
- Per-user category ownership

### Profile Management

- Update name
- Update email
- Update mobile number
- Change password

### Filters

- Keyword filter
- Category filter
- Transaction type filter
- Date presets:
  - Today
  - Yesterday
  - Last 7 Days
  - This Month
  - Last Month
  - Custom date range

### Analytics

- Doughnut chart
- Filtered chart section
- Chart expand / minimize
- Filter-ready analytics area

### UI / UX

- Bangla / English language switch
- Light / Dark mode
- Mobile optimized
- Modern premium dashboard layout
- Clean responsive cards and forms

---

## Technology Stack

| Layer    | Technology                           |
| -------- | ------------------------------------ |
| Frontend | HTML5, CSS3, Bootstrap 5, JavaScript |
| Backend  | PHP                                  |
| Database | MySQL                                |
| Chart    | Chart.js                             |
| Server   | XAMPP / InfinityFree / cPanel        |

---

## Project Structure

```text
expense-tracker/
│
├── assets/
│   └── style.css
│
├── config/
│   ├── app.php
│   └── db.php
│
├── includes/
│   ├── footer.php
│   ├── header.php
│   └── navbar.php
│
├── lang/
│   ├── bn.php
│   └── en.php
│
├── screenshots/
│   ├── dashboard.png
│   ├── login.png
│   ├── signup.png
│   ├── profile.png
│   ├── categories.png
│   ├── filter.png
│   └── graph.png
│
├── categories.php
├── dashboard.php
├── dashboard_filter.php
├── dashboard_chart_filter.php
├── delete_transaction.php
├── edit_transaction.php
├── index.php
├── login.php
├── logout.php
├── profile.php
├── signup.php
└── README.md
```

---

## Screenshot File Names

Upload your screenshots using these **exact names** inside the `screenshots` folder:

```text
screenshots/dashboard.png
screenshots/login.png
screenshots/signup.png
screenshots/profile.png
screenshots/categories.png
screenshots/filter.png
screenshots/graph.png
```

> Important:
>
> - Keep all names **lowercase**
> - Keep all extensions as **.png**
> - Upload inside the **screenshots** folder in the repo root

---

## Preview Gallery

<table>
  <tr>
    <td align="center">
      <img src="https://raw.githubusercontent.com/itz-mehraz/Expense_Tracker/main/screenshots/login.png" alt="Login Page" width="100%">
      <br><strong>Login</strong>
    </td>
    <td align="center">
      <img src="https://raw.githubusercontent.com/itz-mehraz/Expense_Tracker/main/screenshots/signup.png" alt="Signup Page" width="100%">
      <br><strong>Signup</strong>
    </td>
    <td align="center">
      <img src="https://raw.githubusercontent.com/itz-mehraz/Expense_Tracker/main/screenshots/profile.png" alt="Profile Page" width="100%">
      <br><strong>Profile</strong>
    </td>
  </tr>
  <tr>
    <td align="center">
      <img src="https://raw.githubusercontent.com/itz-mehraz/Expense_Tracker/main/screenshots/categories.png" alt="Categories Page" width="100%">
      <br><strong>Categories</strong>
    </td>
    <td align="center">
      <img src="https://raw.githubusercontent.com/itz-mehraz/Expense_Tracker/main/screenshots/filter.png" alt="Filter Section" width="100%">
      <br><strong>Advanced Filter</strong>
    </td>
    <td align="center">
      <img src="https://raw.githubusercontent.com/itz-mehraz/Expense_Tracker/main/screenshots/graph.png" alt="Graph Section" width="100%">
      <br><strong>Analytics Graph</strong>
    </td>
  </tr>
</table>

---

## Local Installation Guide

### 1. Clone the repository

```bash
git clone https://github.com/itz-mehraz/Expense_Tracker.git
```

### 2. Move the project into your local server directory

Example for XAMPP on macOS:

```text
/Applications/XAMPP/xamppfiles/htdocs/expense-tracker
```

### 3. Start Apache and MySQL

Using XAMPP:

- Start Apache
- Start MySQL

### 4. Create the database

Create a database named:

```text
expense_tracker
```

### 5. Run the SQL schema

```sql
CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    mobile_number VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE categories (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_categories_user_id (user_id),
    CONSTRAINT fk_categories_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE expenses (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    transaction_type ENUM('expense','income') NOT NULL DEFAULT 'expense',
    payment_method ENUM('bkash','nagad','cash','bank','card','other') NOT NULL DEFAULT 'cash',
    title VARCHAR(150) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    note TEXT DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_expenses_user_id (user_id),
    KEY idx_expenses_category_id (category_id),
    KEY idx_expenses_date (expense_date),
    CONSTRAINT fk_expenses_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_expenses_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 6. Configure database connection

Open:

```text
config/db.php
```

For local development, use:

```php
<?php
$host = '127.0.0.1';
$port = 3306;
$username = 'root';
$password = 'YOUR_LOCAL_DB_PASSWORD';
$database = 'expense_tracker';

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die('Database connection failed.');
}

$conn->set_charset('utf8mb4');
```

Replace:

```text
YOUR_LOCAL_DB_PASSWORD
```

with your local MySQL password.

### 7. Open in browser

```text
http://localhost/expense-tracker/
```

---

## InfinityFree Deployment Guide

Use your InfinityFree database credentials like this:

```php
<?php
$host = 'sqlXXX.infinityfree.com';
$port = 3306;
$username = 'YOUR_INFINITYFREE_DB_USERNAME';
$password = 'YOUR_INFINITYFREE_VPANEL_PASSWORD';
$database = 'YOUR_INFINITYFREE_DB_NAME';

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die('Database connection failed.');
}

$conn->set_charset('utf8mb4');
```

### Deployment Steps

1. Create InfinityFree hosting account
2. Create MySQL database
3. Import the SQL schema
4. Upload project files into:

```text
htdocs/expense-tracker/
```

5. Update `config/db.php`
6. Open:

```text
https://your-subdomain/expense-tracker/
```

---

## How to Use

### Register

Create a new user account from the signup page.

### Login

Login using your email and password.

### Add Categories

Go to category management and add categories such as:

- Groceries
- Rent
- Mobile Recharge
- Internet Bill
- Medicine
- Salary
- Freelance Income
- bKash
- Nagad

### Add Transactions

From the dashboard:

- Add income
- Add expense
- Select category
- Select payment method
- Add date
- Add optional note

### Manage Transactions

- Edit transaction
- Delete transaction
- Search transactions
- Filter by category
- Filter by type
- Filter by date

### Update Profile

Go to profile page and update:

- Name
- Email
- Mobile number
- Password

### View Analytics

Use the chart section to visualize:

- Income
- Expense
- Filtered results
- Date-based financial patterns

---

## Supported Payment Methods

- bKash
- Nagad
- Cash
- Bank
- Card
- Other

---

## Supported Languages

- বাংলা
- English

---

## Theme Support

- Light Mode
- Dark Mode

---

## Security Highlights

- `password_hash()` for password storage
- `password_verify()` for login validation
- Session-based authentication
- Prepared statements for database queries
- Per-user data ownership checks
- Protected routes for authenticated users

---

## Future Improvements

- Monthly bar chart
- Category-wise pie chart
- Export to PDF
- Export to Excel
- Budget goal module
- Savings tracker
- Admin panel
- Notification system
- Password reset via email
- Transaction pagination

---

## Troubleshooting

### Database connection failed

Check:

- Host
- Username
- Password
- Database name
- Port

### Login not working

Check:

- User exists in database
- Password hash is stored correctly
- Correct credentials are used

### Chart not showing

Check:

- Chart.js is loaded
- Database has records
- Active filter is not returning zero data

### Raw PHP errors visible

Disable this in production:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## Author

**Khondokar Ahmed Mehraz**

GitHub: [itz-mehraz](https://github.com/itz-mehraz)

---

## License

This project is created for educational, learning, and portfolio purposes.

---

## Quick Setup Summary

1. Clone the repository
2. Create the database
3. Run the SQL schema
4. Update `config/db.php`
5. Start Apache and MySQL
6. Open project in browser
7. Register user
8. Add categories
9. Add income and expense
10. Upload screenshots using the exact file names

---

## Final Screenshot Reminder

Use these exact file names:

```text
screenshots/dashboard.png
screenshots/login.png
screenshots/signup.png
screenshots/profile.png
screenshots/categories.png
screenshots/filter.png
screenshots/graph.png
```
