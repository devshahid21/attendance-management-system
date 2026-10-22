# Technical Report

**Title:** Company Attendance Management System  
**Developer:** Muhammad Shahid  
**Type:** New educational rebuild of a university FYP concept, not a historical source archive or deployed company system.

## 1. Requirements
Provide simple authenticated attendance management for a small company. Admins maintain departments and employee accounts, inspect/correct records, retain correction reasons and export monthly CSV. Employees view only their own profile and history, check in and check out. No payroll, biometrics, GPS, AI or shift scheduling.

## Technology generation and provenance

The reconstruction targets Laravel 10, PHP 8.1/8.2, MySQL 8, Blade and Bootstrap 5.3.2, a generation available in 2023. This does not establish the exact historical FYP stack; compatible dependency patch versions can be newer. Current implementation and testing dates are not backdated. Use only as an isolated educational demonstration because the framework/runtime generation is legacy.

## 2. Architecture
Traditional Laravel 10 MVC, using HTTP/console kernels, RouteServiceProvider, an exception handler and property-based Eloquent casts. Routes in routes/web.php -> role middleware -> controllers -> Eloquent models -> MySQL tables -> Blade-rendered HTML with Bootstrap. CSRF tokens protect form submissions. Unique email, code and daily attendance database constraints defend data integrity. Authentication uses session guard and hashed passwords.

## 3. Attendance calculations
Timezone Asia/Karachi. Day status is resolved in order: non-working, future, actual check-in status, pending today, absent on a completed active working day, inactive when not active. A day is late if arrival time exceeds office start plus configured grace minutes. At most one entry per employee/calendar date; checking out updates the existing row only.

## 4. Implemented source features
Auth controllers, role middleware, department management, employee account creation and editing, deactivate/reactivate, daily attendance, employee history, admin date filters, correction history (including missing-day corrections), monthly aggregates and CSV export. Responsive Blade screens and example seed accounts are included.

## 5. Limitations
No real-world deployment, no historical employment timeline, no pagination on monthly aggregates, no overnight shifts or holiday calendar beyond weekdays. For smaller local demos, this scope keeps the code straightforward. Production rollout would additionally require hardening, monitoring, backups and real user acceptance.

## 6. Tests and environment
GitHub Actions successfully ran Composer install, route listing, Blade compilation and **6 feature tests / 33 assertions** (PHP 8.4, in-memory SQLite) on commit b8b82d6312e57450ed58085d9f53b8aaeee8c0be. Tests cover authorization, forged employee ID, duplicate check-in, check-out rules, status calculations and audited missing-day corrections. On 2026-10-22 a separate CI job also completed **MySQL 8 migrations and demo seeding** successfully. These are historical baseline checks, not verification of the Laravel 10 conversion. The compatibility workflow now targets PHP 8.1 and runs feature tests against SQLite and MySQL 8. Read the current pull request Actions logs for actual results. Manual browser acceptance remains unverified.
