# Manual Acceptance and Demonstration Checklist

All items **unchecked** until a tester executes them on a local Laravel/MySQL installation.

- [ ] Run composer install, php artisan key:generate, php artisan migrate --seed and php artisan serve without error.
- [ ] Admin signs in using demo credentials and signs out.
- [ ] Create a department; rename it; deletion of populated department is refused.
- [ ] Create an employee with a unique code and a password confirmation; sign in with new credentials.
- [ ] Verify employee cannot navigate to /employees, /attendance, /reports or another employee profile URL.
- [ ] Check in once on a configured working day; a second attempt shows an error and leaves one row.
- [ ] Check out once; repeat checkout is rejected; check-out without check-in is rejected.
- [ ] Check in before office start/grace => Present; after grace => Late.
- [ ] Current unmarked working day shows Pending; elapsed working day => Absent; future => Future; weekends => Non-working.
- [ ] Admin filters by date, employee and department and sees matching records.
- [ ] Admin corrects an existing row with a required reason; saved history includes before/after and admin.
- [ ] Admin adds a missing past working-day record with a reason; duplicate insertion is rejected.
- [ ] Deactivate employee; attendance history remains in the database; inactive employee cannot check in.
- [ ] Monthly report shows present/late/absent and CSV opens with the same filter.
- [ ] Revisit pages on narrow/mobile screen for navigation and table usability.
- [ ] Run php artisan test and record actual pass/fail counts.

## Demonstration walkthrough
1. Admin login -> add department -> add employee with fake data.
2. Log out -> employee login -> profile -> check in -> check out -> history.
3. Admin login -> attendance by date -> correction history -> monthly report -> CSV.
4. Explain unique daily index, role checks, absence calculation, and no payroll/biometrics.

Record environment, tester, date, and actual results separately when tests run.

## Automated verification (GitHub Actions)

On 2026-10-22, CI completed successfully using PHP 8.4 and SQLite: Composer install, route listing, Blade compilation, **6 passing tests and 33 assertions**. On the same date a separate GitHub Actions job also completed MySQL 8 migrations and seeders successfully. This is separate from the unchecked manual browser scenarios above.

## Laravel 10 / PHP 8.1 conversion

The earlier results above belong to the Laravel 13 baseline. The compatibility workflow runs PHP 8.1 with SQLite and MySQL 8, including login/logout and guest redirection tests in addition to the existing attendance tests. Record only actual current workflow outcomes; manual items remain unchecked. This is a current reconstruction using a 2023-era stack, not a backdated original project.

## Leave and sample data
- [ ] Run php artisan migrate --seed in local environment; check 4 departments, 8 employees and sample history for the previous 20 working days.
- [ ] Employee submits a leave request; pending and approved overlapping requests are rejected.
- [ ] Employee can see only their own leave and cannot approve requests.
- [ ] Admin approves/rejects pending leave; rejection requires a reason; reviewed decisions cannot be overwritten.
- [ ] Approved leave displays separately from absence in daily attendance, employee history, monthly report and CSV.
- [ ] Existing attendance takes precedence over leave; future and non-working days are not counted as leave.
- [ ] Re-run demo seeding without overwriting corrections, passwords or existing requests.
- [ ] Check Leave requests / My leave pages on desktop and mobile.
