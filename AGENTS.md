# HRM development instructions

Laravel 11, PHP 8.2+, timezone Asia/Dhaka. Install from composer.lock and package-lock.json.
Use only a disposable development database and synthetic employee data. Never connect to production, SMTP, attendance devices, or production queues. Do not start schedule:run or queue workers during initial inspection.
Preserve proprietary licensing code and .php-inc files. Do not bypass license checks.
Keep changes focused. For attendance changes check overnight shifts, off days, missing punches, grace periods, and display_only shifts. For leave/payroll changes check permissions and balances.
Report checks actually run and blockers. Never claim an unrun check passed.
Work on a branch and propose changes for review; do not deploy automatically.
