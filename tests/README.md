# Targeted WFH regression checks

These checks exercise the real roster controllers, WFH service, punch action,
and rendered Blade views with synthetic employees. They do not start queue
workers, contact attendance devices, or send mail. The fixtures are rolled back.
This is a focused regression harness, not the original exported test suite.

Create a **new, disposable local MySQL/MariaDB database** named
`hrm_wfh_test_fix` and provide its development-only credentials. Install the
locked dependencies first. With those credentials in the environment, run:

```sh
export APP_ENV=testing DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_DATABASE=hrm_wfh_test_fix
export QUEUE_CONNECTION=null MAIL_MAILER=array CACHE_STORE=array SESSION_DRIVER=array
php artisan migrate --force
php tests/Integration/wfh_roster.php
node tests/js/rosterPayload.test.mjs
php artisan migrate:rollback --step=1 --force
php artisan migrate --force
npm run build
```

The PHP runner refuses to boot unless `APP_ENV=testing`, the database name starts
with `hrm_wfh_test_`, and the host is local (`localhost`, `127.0.0.1`, or the
provided development container `hrm-development-db`). It also refuses configured
attendance devices. It returns a nonzero exit status on failure.

The migration rollback command above assumes this is a fresh database and the
last migration is PR #2's WFH column migration. No new migration is needed for
these fixes. Existing no-shift WFH codes are rejected when saved or published;
the views ask for an original shift instead of displaying credited WFH.
Copies clear WFH along with their shifts; select the new shifts and WFH again.
Draft or unmatched assignments display “Remote work pending attendance.”
