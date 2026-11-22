# Source preparation review

Automated pattern searches covered source, configuration, frontend assets, and .php-inc files; obvious matches were reviewed. This is a credentials check, not a complete security audit or a guarantee that no secrets remain.

Two credential literals removed: weather key in HomeController.php and mail password fallback in config/mail.php. Standard factory/seeder dummy passwords and permission/validation labels were retained.

Removed historical copies:

- `config/reports.php.backup_production`
- `resources/js/components/Roster/Edit.vue.backup-before-SL`
- `resources/js/components/Roster/Edit.vue.bak-dac`
- `resources/js/components/Roster/Create.vue.bak-dac`
- `resources/js/components/Roster/Create.vue.backup-before-SL`
- `resources/views/sidebar/sidebar.blade.php.bak.`
- `resources/views/roster/index.blade.php.backup-before-SL`
- `resources/views/email/attendance_request.blade.php.bak.attfix_20260719_121847`
- `resources/views/employees/edit.blade.php.bak-shift-star-20261011`
- `resources/views/employees/create.blade.php.bak-shift-star-20261011`
- `app/Console/Kernel.php.bak.rostertime_20260719_153024`
- `app/Services/FindEmployeeShiftByPunchDateTime.php.bak-dummy-shift`
- `app/Services/AttendanceStatusResolver.php.backup-`
- `app/Services/AttendanceReportService.php.bak.pdfix_20260719_130236`
- `app/Services/AttendanceStatusResolver.php.backup-before-sep13-fix`
- `app/Jobs/SendAttendanceEmail.php.bak.attfix_20260719_121905`
- `app/Models/User.php.backup-before-roster-weekend-fix`
- `app/Console/Commands/GenerateDailyAttendanceReportCommand.php.backup_production`
- `app/Console/Commands/GenerateDailyAttendanceReportCommand.php.bak.dailyfix_20260719_144621`
- `app/Console/Commands/SendPendingApprovalReminders.php.bak.`
- `app/Http/Controllers/InterviewController.php.bak.`
- `app/Http/Controllers/HomeController.php.bak.20260830153616`
- `app/Http/Controllers/EmployeeController.php.bak-shift-required-20261011-154915`
- `app/Http/Controllers/GradeController.php.bak.`
- `app/Http/Controllers/EmployeeController.php.bak.`
- `app/Http/Controllers/NotificationController.php.bak.notif_20260719_105417`
- `app/Http/Controllers/NotificationController.php.bak.notif_20260719_105306`
- `app/Http/Controllers/HomeController.php.bak.`
- `app/Http/Controllers/EmployeeController.php.bak-shift-required-20261011-154900`
- `app/Http/Controllers/Api/MobileApi/NotificationController.php.bak.notif_20260719_105417`
- `app/Services/Approval/AttendanceRequestService.php.bak.attfix_20260719_121905`
- `app/Actions/EmployeePunched/FindEmployeeShiftByPunchDateTime.php.bak-dummy-shift`
