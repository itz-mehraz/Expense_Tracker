# Expense Tracker

<p align="center">
  <strong>PHP + MySQL personal finance app — বাংলা / English, light &amp; dark theme, BDT-focused dashboard, categories, CSV export, and Chart.js analytics.</strong>
</p>

<p align="center">
  <img src="" alt="App banner — add your screenshot here" width="min(920px, 100%)" />
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap" />
  <img src="https://img.shields.io/badge/Chart.js-Analytics-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white" alt="Chart.js" />
</p>

---

## Table of contents

1. [Overview](#overview)
2. [Features](#features)
3. [Screenshots &amp; assets](#screenshots--assets)
4. [Requirements](#requirements)
5. [Project structure](#project-structure)
6. [Installation (local)](#installation-local)
7. [Database schema](#database-schema)
8. [Configuration](#configuration)
9. [Internationalization &amp; theme](#internationalization--theme)
10. [Security notes](#security-notes)
11. [Deployment notes](#deployment-notes)
12. [Troubleshooting](#troubleshooting)
13. [Roadmap ideas](#roadmap-ideas)
14. [Author &amp; license](#author--license)

---

## Overview

This project is a **session-based** web application for recording **income** and **expenses** in **Bangladeshi Taka (৳)**. Users manage their own **categories** and **transactions**, filter lists and charts, optionally **export CSV**, and update **profile** data.

**Defaults:** Bangla (`bn`) first, timezone **Asia/Dhaka** (`config/app.php`). Guests can **open the dashboard in preview mode**; adding data and exports require login.

---

## Features

| Area | What it does |
|------|----------------|
| **Auth** | Sign up, login, logout; passwords hashed with `password_hash()` |
| **Dashboard** | Totals, today’s summary, tip, add transaction, filterable list, doughnut chart, AJAX refresh via `dashboard_filter.php` |
| **CSV** | `export_csv.php` — UTF-8 BOM, same GET filters as dashboard, up to 10k rows |
| **Categories** | CRUD per user |
| **Profile** | Name, email, mobile, password |
| **UI** | Bootstrap 5, custom theme in `assets/css/style.css` (imports `assets/style.css`), mobile nav bar (hidden on home), optional guest home without large footer |

---

## Screenshots &amp; assets

**Add your own images:** set the `src` in the banner above, and in the gallery below, to a URL or a path (e.g. `screenshots/dashboard.png`). Leaving `src=""` is intentional so you can paste paths when ready.

### Recommended files (repo `screenshots/` folder)

| # | Suggested filename | What to capture |
|---|-------------------|-----------------|
| 1 | `screenshots/01-home.png` | Landing / home (BN or EN) |
| 2 | `screenshots/02-login.png` | Login |
| 3 | `screenshots/03-signup.png` | Sign up |
| 4 | `screenshots/04-dashboard.png` | Dashboard overview |
| 5 | `screenshots/05-chart-filters.png` | Chart + filter strip |
| 6 | `screenshots/06-transactions-mobile.png` | Mobile transaction cards |
| 7 | `screenshots/07-categories.png` | Categories |
| 8 | `screenshots/08-profile.png` | Profile |
| 9 | `screenshots/09-dark-mode.png` | Same screen in dark theme |

### Gallery (replace `src=""` when you have files)

<table>
  <tr>
    <td align="center" width="33%">
      <img src="" alt="Home" width="100%" /><br /><sub>Home</sub>
    </td>
    <td align="center" width="33%">
      <img src="" alt="Login" width="100%" /><br /><sub>Login</sub>
    </td>
    <td align="center" width="33%">
      <img src="" alt="Signup" width="100%" /><br /><sub>Signup</sub>
    </td>
  </tr>
  <tr>
    <td align="center">
      <img src="" alt="Dashboard" width="100%" /><br /><sub>Dashboard</sub>
    </td>
    <td align="center">
      <img src="" alt="Chart and filters" width="100%" /><br /><sub>Chart &amp; filters</sub>
    </td>
    <td align="center">
      <img src="" alt="Categories" width="100%" /><br /><sub>Categories</sub>
    </td>
  </tr>
  <tr>
    <td align="center">
      <img src="" alt="Profile" width="100%" /><br /><sub>Profile</sub>
    </td>
    <td align="center">
      <img src="" alt="Mobile view" width="100%" /><br /><sub>Mobile</sub>
    </td>
    <td align="center">
      <img src="" alt="Dark mode" width="100%" /><br /><sub>Dark mode</sub>
    </td>
  </tr>
</table>

**Logo:** place `assets/images/logo.png` (navbar + footer use it).

---

## Requirements

- **PHP** 8.0+ (mysqli, session, JSON)
- **MySQL** 5.7+ / MariaDB (InnoDB, utf8mb4)
- **Web server** with PHP (Apache via XAMPP, nginx + php-fpm, etc.)
- Modern browser (ES5+ for small scripts; Chart.js on dashboard)

---

## Project structure

```text
expense-tracker/
├── assets/
│   ├── css/style.css      # Theme overrides + imports ../style.css
│   ├── style.css          # Base layout & components
│   ├── js/app.js          # e.g. password visibility toggle
│   └── images/logo.png    # Brand (add if missing)
├── config/
│   ├── app.php            # Session, lang, theme, helpers, merged UI strings
│   └── db.php             # mysqli connection
├── includes/
│   ├── header.php
│   ├── navbar.php
│   └── footer.php
├── lang/
│   ├── bn.php
│   └── en.php
├── categories.php
├── dashboard.php
├── dashboard_filter.php   # AJAX JSON for filtered chart + table HTML
├── delete_transaction.php
├── edit_transaction.php
├── export_csv.php
├── index.php
├── login.php
├── logout.php
├── profile.php
├── signup.php
└── README.md
```

---

## Installation (local)

### 1. Clone or copy the project

Place the folder under your web root, e.g.:

```text
/Applications/XAMPP/xamppfiles/htdocs/expense-tracker
```

### 2. Create the database

Create a database named e.g. `expense_tracker` in phpMyAdmin or the MySQL CLI.

### 3. Import schema

Run the SQL from [Database schema](#database-schema) (below) in order: `users` → `categories` → `expenses`.

### 4. Configure `config/db.php`

Set host, user, password, database name, and port. Example for XAMPP:

```php
<?php
$host = '127.0.0.1';
$port = 3306;
$username = 'root';
$password = '';
$database = 'expense_tracker';

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die('Database connection failed.');
}

$conn->set_charset('utf8mb4');
```

### 5. Open in browser

```text
http://localhost/expense-tracker/
```

Register a user, add categories, then add transactions on the dashboard.

---

## Database schema

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Configuration

| File | Role |
|------|------|
| `config/db.php` | Database credentials only |
| `config/app.php` | `date_default_timezone_set('Asia/Dhaka')`, language &amp; theme session keys, merged `$langExtrasEn` / `$langExtrasBn`, helpers (`e()`, `format_bdt()`, `safe_internal_path()`, …) |

**Production:** turn off in-browser PHP errors in `config/app.php` (and entry scripts) by removing or guarding:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## Internationalization &amp; theme

- Copy lives in `lang/bn.php` and `lang/en.php`.
- Extra UI strings (footer, landing, tips, nav labels) merge in `config/app.php` after loading the language file.
- Switch: `?lang=bn` / `?lang=en` and `?theme=light` / `?theme=dark` (session-backed).

---

## Security notes

- Passwords: `password_hash()` / `password_verify()`
- SQL: **prepared statements** with bound parameters
- Ownership: category and expense queries scoped by `user_id`
- **CSV** and **AJAX filter** require an authenticated session
- `safe_internal_path()` restricts `?next=` redirects to paths under `/expense-tracker/`

---

## Deployment notes

1. Upload files to the host document root (or subdirectory).
2. Create MySQL database and import the schema.
3. Point `config/db.php` at the host’s MySQL hostname and credentials.
4. Ensure PHP `mysqli` and `session` are enabled.
5. Use HTTPS in production; tighten cookie flags if you add `session.cookie_secure` etc.

---

## Troubleshooting

| Problem | Things to check |
|---------|------------------|
| DB connection | Host, port, user, password, database name, firewall |
| Login fails | User row exists, password column holds bcrypt hash |
| Chart empty | No rows for filters; Chart.js script loaded |
| 404 on `/expense-tracker/` | Apache `DocumentRoot` / alias; folder name matches URLs in PHP |

---

## Roadmap ideas

- Pagination for long transaction lists  
- Budgets / monthly targets  
- PDF or Excel export  
- Email password reset  
- Admin / multi-tenant (if ever needed)

---

## Author &amp; license

**Author:** Khondokar Ahmed Mehraz — GitHub: [itz-mehraz](https://github.com/itz-mehraz)

Educational / portfolio use. Add your **repository URL** and **license** (e.g. MIT) here when you publish.

---

## Quick checklist after clone

1. [ ] Create DB + run SQL  
2. [ ] Edit `config/db.php`  
3. [ ] Add `assets/images/logo.png`  
4. [ ] Fill README `src=""` images (or relative paths)  
5. [ ] Register user → categories → transactions  
6. [ ] Disable `display_errors` for production  
