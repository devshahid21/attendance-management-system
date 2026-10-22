# Setup and integration

This repository currently contains a starter attendance **module**, not a complete Laravel application. It is intended to be incorporated into an existing Laravel project only after checking compatibility.

## Requirements
- Existing Laravel application with working authentication (`auth` middleware).
- PHP / Composer versions appropriate for that Laravel version.
- MySQL database configured in a local `.env` (never commit secrets).

## Merge into your existing project
1. Make a backup and work on a separate Git branch.
2. Compare the supplied `app/Models/`, `app/Http/Controllers/`, and `database/migrations/` files with your own application. Resolve conflicts; do not overwrite existing employee or attendance tables blindly.
3. From your existing Laravel application's `routes/web.php`, include:

   ```php
   require __DIR__.'/attendance.php';
   ```

4. Run `php artisan migrate:status` to review pending migrations, then `php artisan migrate` if correct.
5. Run `php artisan route:list --path=attendance` to confirm routes.
6. Sign in using your existing Laravel authentication before testing the endpoints.

## Endpoints
All under `/attendance` and guarded by Laravel's `auth` middleware:
- `GET /attendance/employees` — paginated employees
- `POST /attendance/employees` — add employee (`employee_code`, `name`, optional `email` and `department`)
- `POST /attendance/employees/{employee}/check-in` — check in today
- `POST /attendance/employees/{employee}/check-out` — check out today
- `GET /attendance/report?from=2026-10-01&to=2026-10-31` — paginated report

These POST routes use the `web` middleware group and require a CSRF token when accessed from browser forms.

## Important limitations
- No frontend views or login scaffolding yet.
- No role-based admin authorization; any authenticated user can currently manage attendance. **Do not deploy publicly before adding roles and permissions.**
- No timezone policy, leave/holiday handling, or overnight shifts yet.
- No tests or CI configured; code has not been executed against a Laravel installation.
- Concurrent check-ins should receive stronger handling before production use.
- Do not put real employee personal information in test data or commits.
