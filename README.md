# Company Attendance Management System

**Developer:** Muhammad Shahid  
**Project type:** Newly rebuilt **educational demonstration** inspired by an earlier BS Information Technology FYP. **Not** historical FYP source code or a deployed company product.

A small-company server-rendered application with **Laravel 10**, **PHP 8.1 / 8.2**, **MySQL 8+**, **Blade**, and **Bootstrap 5.3.2** (local assets). No React, separate API, payroll, biometrics, GPS, AI or complex shift handling.

## 2023-era compatibility

This branch reconstructs the FYP concept using a framework/runtime generation available in 2023: Laravel 10 (released 14 February 2023), PHP 8.1/8.2, MySQL 8 and Bootstrap 5.3.2. It is a current reconstruction, not proof that these were the original project's exact versions. Package constraints may resolve later compatible patch releases; this is not a frozen 2023 dependency archive. Migration names, Git history and verification dates retain their real dates.

Laravel 10 and PHP 8.1 are legacy releases. Use this branch for an isolated educational demonstration, not an internet-facing production deployment.

The Laravel 10 structure uses HTTP/console kernels, an exception handler, a route service provider and property-based Eloquent casts. The dependency installer used by CI is Composer 2.8.

## Features

- Admin and employee login/logout, role middleware, server-side validation and CSRF.
- Admin: departments, employee CRUD (deactivation preserves records), individual login creation, day/department/employee filters, attendance corrections with required reasons and history (including creating missing-day records), monthly CSV export.
- Employee: personal profile, one daily check-in, single check-out, monthly history/summary, and leave requests.
- Leave management: casual/sick/annual requests; admin approval/rejection with reviewer/date/note; overlap protection; approved leave shown separately in reports/CSV. No paid-leave balances.
- Attendance: Asia/Karachi, Monday–Friday by default, office start 09:00, 15-minute grace; configurable in .env.
- Absence is **derived** for elapsed working days without a record, never inserted as a separate absence row.
- All demo users/data are fictional and passwords are hashed by the User model.

## Portals and access

- Admin: team directory, employee creation/editing and activation, departments, daily attendance/corrections, leave approvals, monthly reports/CSV, and a read-only Roles & Access overview.
- Employee: own dashboard, profile, attendance, check-in/out and leave requests. Inactive employees retain history access but cannot submit attendance or new leave.
- Both roles: own password change, validated against the current password. Employee creation cannot grant administrator access. Login attempts are rate-limited.
- The responsive interface includes a mobile navigation drawer, keyboard focus states, validation feedback and private-access error pages.

## Installation

Prerequisites: PHP **8.1 or 8.2** with required Laravel extensions, Composer 2, MySQL 8+, and internet access for the initial Composer installation. UI assets are included locally.

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

## Demo data and leave management

Run `php artisan migrate --seed` against your local demo database after checking out this branch. For an existing installation, `php artisan migrate` adds the leave table without resetting attendance; `php artisan db:seed` adds synthetic examples. Do not use migrate:fresh on an existing database you want to preserve.

The seeder creates **4 departments, 8 fictional employees and 1 admin**, attendance from the **previous 20 working days**, and **3 leave requests** (approved/pending/rejected). Dates are relative to installation time; no real company information is used. Repeated seeding preserves existing accounts, corrections and records. It is blocked outside local/testing environments.

All employee demo accounts use `DemoEmployee123!`: ayesha, bilal, sara, usman, hina, omar, zain and maryam at `example.test`. Admin credentials remain as listed above. Use the previous working day for Ayesha's approved-leave example and today's month/previous month for attendance reports.

Employees select **My leave** to submit requests. Admins select **Leave requests** to review them; rejected requests require a note. Requests are limited to 31 calendar days and must contain a working day. Approved leave does not count as absence. Actual attendance takes precedence if both exist, and future/non-working days are not counted as leave. There are no quotas or payroll adjustments.

## Configure attendance

In .env:
- APP_TIMEZONE=Asia/Karachi
- OFFICE_START=09:00
- OFFICE_GRACE_MINUTES=15
- WORKING_DAYS=1,2,3,4,5 (ISO weekdays Monday=1, Sunday=7)

Then run: php artisan config:clear

## Routes (web pages)

- /login, /logout, /, /leaves
- Employee: POST /leaves; Admin: PATCH /leaves/{leaveRequest}
- Admin: /departments, /employees, /attendance, /reports, /reports/export
- Employee: /my-profile, /my-attendance, POST /check-in, POST /check-out

All write actions use HTML forms with CSRF, route validation, and role checks. CSV respects active report filters.

## Database ER diagram

See [docs/ER_DIAGRAM.md](docs/ER_DIAGRAM.md).

## Technical report and acceptance

- [docs/TECHNICAL_REPORT.md](docs/TECHNICAL_REPORT.md)
- [docs/ACCEPTANCE_CHECKLIST.md](docs/ACCEPTANCE_CHECKLIST.md)

## Known limitations and verification status

Previous Laravel 13 verification: [baseline run](https://github.com/devshahid21/attendance-management-system/actions/runs/39780512128), 6 tests / 33 assertions on PHP 8.4 and SQLite, plus MySQL migration/seeding checks. Those results do not verify this Laravel 10 conversion.

The current workflow runs dependency installation, route registration, Blade compilation and feature tests on PHP 8.1 with SQLite, and migration/seeding plus feature tests with MySQL 8. See the pull request's actual Actions results for current pass/fail status. Local PHP feature checks and the GitHub Actions jobs verify the implementation. The browser UI workflow exercises actual form submissions and responsive navigation; see the latest run for results. Production deployment is not verified.

Additional limitations:
- Employee deactivation preserves attendance but the simple active flag does not track historical employment effective dates, so older absent totals for a now-inactive user can change.
- Admin can record a missing past working day with a required reason; corrections never silently overwrite an existing day.
- Correction history records each edit and reason, not immutable external audit storage.
- No pagination on monthly report (suitable for a small demo); day and employee lists are paginated.
- Only same-day check-out; no overnight shifts.
- Bootstrap CSS, custom CSS, icons and JavaScript are local assets; no frontend build step is required.
- Seeders use known demonstration passwords; do not run against live data.

## Browser UI verification

The optional `ui-smoke` GitHub Actions job uses Chrome and Playwright to exercise desktop/mobile portals, CSRF rejection, department/employee creation, attendance, leave approval, account activation, CSV download and password change. Screenshots and a results JSON are saved as the `portal-ui-checks` workflow artifact. Node/Playwright are test tools only; running the application requires no Node build.

For a local browser check with Chrome installed: install Playwright into an isolated test-tools directory, set `NODE_PATH` to its node_modules directory, start the seeded demo at http://127.0.0.1:8000, and run `node tests/browser/smoke.cjs`. Use a disposable demo database: the browser scenario intentionally creates and edits synthetic records.
