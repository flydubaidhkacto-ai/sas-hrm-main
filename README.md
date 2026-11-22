# SAS HRM

Pro HR & Payroll source export for private development. Laravel 11 / PHP 8.2+.

## Initial development setup

1. Provide PHP 8.2+ with the extensions required by composer.lock, Composer 2, and Node/npm compatible with the lockfile.
2. Copy `.env.example` to `.env`. Use a disposable MySQL database and its development-only credentials.
3. Run `composer install --no-interaction --prefer-dist`.
4. Run `php artisan key:generate`.
5. Inspect migrations before running `php artisan migrate` on the disposable database. Do not seed real staff data.
6. Run `npm ci` and `npm run build`.
7. Run `php artisan serve --host=0.0.0.0 --port=8000`.

No production database, uploads, .env, vendor, or node_modules is included. This export also omitted the original top-level lang and tests directories and some public assets. Retrieve those separately if needed, after reviewing their contents. No test suite has been fabricated.

Application boot, migrations and dependency installation have not been verified in the preparation environment because PHP/Composer and a database were unavailable. Proprietary licensing or missing files may require additional setup.

## Preparation changes

- Removed historical .bak/.backup copies (see SETUP-REVIEW.md).
- Replaced the weather API key with services.openweathermap.key.
- Removed the literal MAIL_PASSWORD fallback. Set secrets only in .env or environment settings.
- Added .gitignore, blank development .env.example, runtime directory placeholders and AGENTS.md.

These changes apply to this copy only. Before any later production deployment, configure the weather key and confirm MAIL_PASSWORD is set. Rotate the previously shared weather key and the mail credential if the fallback value is active.

Keep this repository private. Review and test branches before deployment.
