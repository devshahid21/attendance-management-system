# Technical Report

**Title:** Company Attendance Management System  
**Developer:** Muhammad Shahid  
**Type:** New educational rebuild of a university FYP concept, not a historical source archive or deployed company system.

## 1. Requirements
Provide simple authenticated attendance management for a small company. Admins maintain departments and employee accounts, inspect/correct records, retain correction reasons and export monthly CSV. Employees view only their own profile and history, check in and check out. No payroll, biometrics, GPS, AI or shift scheduling.

## 2. Architecture
Traditional Laravel MVC. Routes in routes/web.php -> role middleware -> controllers -> Eloquent models -> MySQL tables -> Blade-rendered HTML with Bootstrap. CSRF tokens protect form submissions. Unique email, code and daily attendance database constraints defend data integrity. Authentication uses session guard and hashed passwords.

## 3. Attendance calculations
Timezone Asia/Karachi. Day status is resolved in order: non-working, future, actual check-in status, pending today, absent on a completed active working day, inactive when not active. A day is late if arrival time exceeds office start plus configured grace minutes. At most one entry per employee/calendar date; checking out updates the existing row only.

## 4. Implemented source features
Auth controllers, role middleware, department management, employee account creation and editing, deactivate/reactivate, daily attendance, employee history, admin date filters, correction history, monthly aggregates and CSV export. Responsive Blade screens and example seed accounts are included.

## 5. Limitations
No real-world deployment, no historical employment timeline, admin cannot yet create a missing-day record via correction, no pagination on monthly aggregates, no overnight shifts or holiday calendar beyond weekdays. For smaller local demos, this scope keeps the code straightforward. Production rollout would additionally require hardening, monitoring, backups and real user acceptance.

## 6. Tests and environment
Feature test source covers role separation, forged employee ID, duplicate check-in, check-out-before-in and repeated checkout, late threshold, future/current/past/nonworking classification. **Not executed** in this session: Composer missing and database extensions absent. Do not interpret test source as passing results. See README and acceptance checklist for local execution steps.
