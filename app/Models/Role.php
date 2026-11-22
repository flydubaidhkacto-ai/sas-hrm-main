<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Role extends Model
{
    use HasFactory;

    static public $permissions = [
        "superadmin" => 'Super Admin',
        'api_key' => "Manage API Keys",
        'holidays' => "Manage Holidays",
        'company.edit' => "Edit Company Info",

        'attendance' => "Attendance Admin",

        'deduction' => "Deduction Admin",
        'super_department' => "Manage Super Department",
        'designation' => "Manage Designation",

        'earn_head' => "Earn Heads Admin",

        'leave' => "Leave Admin",
        'leave.apply' => 'Apply Leave',

        'leave.balance-assign' => "Assign Leave Balance",
        'leave.balance-assign.self.super_department' => "Assign Leave Balance of His Super Department",

        'leave_type' => "Manage Leave Types",

        'emp-expenses' => "Emp. Daily Expenses Admin",
        'emp-expenses.self' => "View and Entry of His Own Daily Expenses",

        'overtime_schedule' => "Manage Overtime Schedule",

        'payroll_card' => "Salary Card Admin",
        'payroll_card.self.super_department' => "Salary Card Manager of His Super Department",
        'payroll_card.show.self' => "View Own Salary Cards",

        'payslip' => "Payslip Admin",

        "payslip.generate" => "Payslip generate",
        "payslip.generate.self.super_department" => "Payslip generate of His Sup. Dept.",

        'payslip.self.show' => "View His own Payslip",

        'payslip.show' => "View Payslip",
        'payslip.tax' => "View Tax Report",
        'payslip.pf' => 'Manage Payslip Provident Fund',

        'rewards' => "Manage Rewards",
        'rewards.self.super_department' => "Manage Rewards of His Super Department",

        'role' => "Assign and Manage Roles",
        'shift' => "Manage Shift",

        'user' => "Employees Admin",
        'user.self.super_department' => "Employees Admin of His Super department",

        'reports' => "View All Reports",
        'reports.self' => "View Only His Reports",
        'reports.self.super_department' => "View Reports from His Super Department",

        'dashboard' => "View All data in Dashboard",
        'dashboard.self' => "View Only His data in Dashboard",
        'dashboard.self.super_department' => "View data in Dashboard from His Super Department",

        'attendance-requests' => 'Manage Attendance Requests',
        'attendance-requests.apply' => 'Apply Attendance Requests',

        'notifications' => 'Manage Notification',

        'promotion-apply' => 'Manage who can apply for employee promotion',
        'promotion-approve' => 'Manage promotion approval',
        'final-promotion-approve' => 'Manage final aproval promotion',

        'bulk-sms' => 'Send Bulk message',
        'bonuses' => "Manage Bonuses",
        'loans' => "Manage Loans",
        'loans.self' => 'Manage His Own Loans',
        'fines' => "Manage Fines",
        'fines.self' => 'Manage His Own Fines',

        'student_attendance' => 'Students Admin',
        'shift_scheduling' => 'Shift Scheduling Admin',
        'roster' => 'Roster Admin',
        'roster.view' => 'View Rosters',
        'asset-manage' => 'Asset Admin',
        'asset-request' => 'Asset Request',
        'grade' => 'Salary Grade Admin',

        'inventory' => 'Inventory Admin',
        'inventory-item-request' => 'Inventory Item Request',

        'letter' => 'Letter Admin',

        //backup
        'backup' => 'Backup',

        'vehicle-management' => 'Vehicle Management',
        'provident_fund' => 'Provident Fund balance',
        'notice_admin' => 'Notice Admin',
        'interview_comment' => 'Interview Comment',
        'interview_comment_see' => 'View Interview Comment',

        'approval-processes' => 'Approval Processes',

        'official-forms' => 'Official Forms',
        'official-forms.templates' => 'Official Forms Templates',
        'letters' => 'Letters',
        'letters.templates' => 'Letters Templates',
        'auto-duty-bonuses' => 'Auto Duty Bonus',

        'emp-bills' => 'Emp. Bills Admin',

        'live-location-tracking' => 'Live Location Tracking Admin',
        'admin-accounts' => 'Manage Admin Account',
        'data.query.request' => 'Data Query Request',

        'payslip.payment' => 'Payslip Payments',
        'provident_fund_payment' => 'Provident Fund Payments',

        'edit-remarks' => 'Edit My Remarks',

        'zkteco-cloud' => "Zkteco Cloud",
        'billing' => "Billing & License",
        'locations' => 'Geo Location Admin',
        'locations.location' => 'Geo Location: Manage Locations',
        'guest_management' => 'Guest Management',

        'station_leave' => "Station Leave Admin",
        'apply_station_leave' => 'Apply Station Leave',

        'payroll_approval_history' => 'Payroll Approval History',
        'payroll_payment_report' => 'Payroll Payment Report',
        'payroll_summary_report' => 'Payroll Summary Report',


    ];
    protected $casts = [
        'permissions' => 'array',
        'permissions_relations' => 'array',
    ];
    protected $guarded = [];

    function users()
    {
        return $this->hasMany(User::class);
    }
    static function initInitialRoles()
    {
        $roles = [
            'Student' => [
                'student_portal',
                'dashboard.self',
            ],
            'Employee' => [
                'leave.apply',
                'emp-expenses.self',
                'payroll_card.show.self',
                'payslip.self.show',
                'reports.self',
                'dashboard.self',
                'attendance-requests.apply',
                'loans.self',
                'fines.self',
                'apply_station_leave'
            ],
            'General Admin' => array_keys(self::$permissions),
        ];
        foreach ($roles as $name => $permissions) {
            $role = Role::where(['name' => $name])->first();
            if (!$role) {
                $role = new Role();
                $role->name = $name;
                $role->permissions = $permissions;
                $role->save();
            }
        }
    }
    static function can(
      
      
        string $permission,
        ?\App\Models\User $user = null,
        \App\Models\SuperDepartment|int|null $target_sup_department = null,
        \App\Models\Department|int|null $target_department = null,
    ) {
        if (!$user) $user = user();
        if (!$user) return 0;
        if($user->role === 'super_admin') return true;
        $role = $user->assinged_role;
        if (!$role || !$role->permissions) return 0;

        if(in_array('superadmin', $role->permissions))
        {
            return true;
        }
        $have_per = in_array($permission, $role->permissions);
        if (str_contains($have_per, '.super_department') && $target_sup_department) {
            $have_per = $have_per && $user->department?->super_department_id == (
                is_int($target_sup_department) ? $target_sup_department : $target_sup_department->id
            );
        } else
        if (str_contains($have_per, '.super_department') && $target_department) {
            if (is_int($target_department)) $target_department = \App\Models\Department::findOrFail($target_department);
            $have_per = $have_per && $user->department?->super_department_id == $target_department->super_department_id;
            if (!$have_per) {
                // $have_per =  user()->managed_employees->pluck('id')
            }
        }

        return $have_per;
    }
    // static function authorize(bool $return_bool, string $permission, ?\App\Models\User $user = null, $user_model_query = null): bool|array
    // {
    //     $can = false;
    //     if(!$user) $user = user();
    //     $user_model_query->where('id', 1);

    //     return $can;
    // }
}
