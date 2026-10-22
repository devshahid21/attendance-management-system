# Company Attendance Management System

**Developer:** Muhammad Shahid  
**Project type:** Newly rebuilt **educational demonstration** inspired by an earlier BS Information Technology FYP. **Not** historical FYP source code or a deployed company product.

A small-company server-rendered application with **Laravel 13**, **PHP 8.3+**, **MySQL 8+**, **Blade**, and **Bootstrap 5.3.3** (CDN). No React, separate API, payroll, biometrics, GPS, AI or complex shift handling.

## Features

- Admin and employee login/logout, role middleware, server-side validation and CSRF.
- Admin: departments, employee CRUD (deactivation preserves records), individual login creation, day/department/employee filters, attendance corrections with required reasons and history (including creating missing-day records), monthly CSV export.
- Employee: personal profile, one daily check-in, single check-out, monthly history/summary.
- Attendance: Asia/Karachi, Monday–Friday by default, office start 09:00, 15-minute grace; configurable in .env.
- Absence is **derived** for elapsed working days without a record, never inserted as a separate absence row.
- All demo users/data are fictional and passwords are hashed by the User model.

## Installation

Prerequisites: PHP **8.3+** with required Laravel extensions, Composer 2, MySQL 8+, and internet connection for Composer and Bootstrap CSS.

1. Clone the repository and open a terminal in it.
2. Run: composer install
3. Copy .env.example to .env and update DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME and DB_PASSWORD.
4. Create an empty MySQL database named company_attendance.
5. Run: php artisan key:generate
6. Run: php artisan migrate --seed
7. Run: php artisan serve
8. Visit http://127.0.0.1:8000/login

For Windows PowerShell: Copy-Item .env.example .env

Run tests: php artisan test

Note: This repository contains source files but does **not** commit vendor/ or composer.lock; Composer will resolve compatible dependencies.

### Demo-only credentials

| Role | Email | Password |
| --- | --- | --- |
| Admin | admin@example.test | DemoAdmin123! |
| Employee | ayesha@example.test | DemoEmployee123! |
| Employee | bilal@example.test | DemoEmployee123! |

**Never use these credentials in production.** Seed synthetic data only in development. Change passwords or remove seed accounts before any deployment.

## Configure attendance

In .env:
- APP_TIMEZONE=Asia/Karachi
- OFFICE_START=09:00
- OFFICE_GRACE_MINUTES=15
- WORKING_DAYS=1,2,3,4,5 (ISO weekdays Monday=1, Sunday=7)

Then run: php artisan config:clear

## Routes (web pages)

- /login, /logout, /
- Admin: /departments, /employees, /attendance, /reports, /reports/export
- Employee: /my-profile, /my-attendance, POST /check-in, POST /check-out

All write actions use HTML forms with CSRF, route validation, and role checks. CSV respects active report filters.

## Database ER diagram

See [docs/ER_DIAGRAM.md](docs/ER_DIAGRAM.md).

## Technical report and acceptance

- [docs/TECHNICAL_REPORT.md](docs/TECHNICAL_REPORT.md)
- [docs/ACCEPTANCE_CHECKLIST.md](docs/ACCEPTANCE_CHECKLIST.md)

## Known limitations and verification status

GitHub Actions on Ubuntu with **PHP 8.4** successfully installed Composer packages, registered routes, compiled Blade templates and ran **6 passing feature tests (33 assertions)** using in-memory SQLite. See the [verified run](https://github.com/devshahid21/attendance-management-system/actions/runs/39780175894). The separate local tool environment lacked Composer and PDO database extensions; **MySQL migrations, end-to-end browser flows, and a production deployment remain unverified**.

Additional limitations:
- Employee deactivation preserves attendance but the simple active flag does not track historical employment effective dates, so older absent totals for a now-inactive user can change.
- Admin can record a missing past working day with a required reason; corrections never silently overwrite an existing day.
- Correction history records each edit and reason, not immutable external audit storage.
- No pagination on monthly report (suitable for a small demo); day and employee lists are paginated.
- Only same-day check-out; no overnight shifts.
- Demo CSS is loaded from Bootstrap CDN; no offline asset build.
- Seeders use known demonstration passwords; do not run against live data.
