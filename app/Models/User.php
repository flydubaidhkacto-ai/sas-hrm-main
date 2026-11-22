<?php

namespace App\Models;

use App\Traits\GenerateUserPerformanceMethods;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, GenerateUserPerformanceMethods;

    protected $guarded = [];
    protected $hidden = [
        'password',
        'remember_token',
        'zk_device_fingerprint_json',
    ];
    protected $casts = [
        'birth_date' => 'date',
        'email_verified_at' => 'datetime',
        'can_overtime' => 'boolean',
        'can_outside_attendence' => 'boolean',
        'is_active' => 'boolean',
        'get_attendance_sms_notification' => 'boolean',
        'work_from_home_request' => 'boolean',
        'live_location_track' => 'boolean',
        'home_latitude' => 'decimal:8',
        'home_longitude' => 'decimal:8',
        'have_geo_location' => 'boolean',
        'can_create_location' => 'boolean',
        'geo_location_departments' => 'array',
        'admin_of_super_departments' => 'array',
    ];

    static function boot()
    {
        parent::boot();
        static::created(function (self $model) {
            // add to zkteco cloud device if not terminated
            if ($model->is_active) {
                ZktecoCloudCommand::set_add_user_command($model);
            }
        });
        static::updated(function (self $model) {
            // delete from zkteco cloud device if terminated
            if (!$model->is_active) {
                ZktecoCloudCommand::set_delete_user_command($model);
            }
            if ($model->is_active) {
                ZktecoCloudCommand::set_add_user_command($model);
            }
        });
        static::deleting(function (self $model) {
            $model->payslips()->delete();
            $model->payroll_card()->delete();
            $model->attendance()->delete();
            $model->in_outs()->delete();
            $model->leaves()->delete();
            $model->leave_balances()->delete();
            $model->documents()->delete();
            $model->fines()->delete();
        });
    }

    function fines()
    {
        return $this->hasMany(Fine::class);
    }
    function designation()
    {
        return $this->belongsTo(Designation::class);
    }
    function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'class_id');
    }
    function department()
    {
        return $this->belongsTo(Department::class);
    }
    function location()
    {
        return $this->belongsTo(Location::class);
    }
    function attendance()
    {
        return $this->hasMany(Attendance::class, 'employee_id', 'id');
    }
    function shift()
    {
        return $this->belongsTo(Shift::class)->withTrashed();
    }
    function attendance_monthly()
    {
        return $this->hasMany(AttendanceMonthly::class);
    }
    function payroll_card()
    {
        return $this->hasOne(PayrollCard::class);
    }
    function payslips()
    {
        return $this->hasMany(Payslip::class);
    }
    function in_outs()
    {
        return $this->hasMany(InOutRecord::class);
    }
    function leaves()
    {
        return $this->hasMany(LeaveApplication::class, 'employee_id', 'id');
    }
    function assinged_role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
    function leave_balances()
    {
        return $this->hasMany(LeaveBalance::class);
    }
    function performances()
    {
        return $this->hasMany(PerformanceOfMonth::class);
    }
    function zk_fingerprints()
    {
        return $this->hasMany(UserZkDeviceFingerprint::class, 'user_id', 'id');
    }
    function documents()
    {
        return $this->hasMany(UserDocument::class);
    }
    function shift_schedules()
    {
        return $this->hasMany(ShiftSchedule::class);
    }
    function auto_rewards()
    {
        return $this->hasMany(AutoReward::class);
    }
    function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Many-to-many relationship with locations for employee assignments
     */
    function assigned_locations()
    {
        return $this->belongsToMany(Location::class, 'employee_location')
                    ->withPivot('distance_km')
                    ->withTimestamps();
    }

    function assigned_locations_areas()
    {
        $ids = [];
        foreach($this->assigned_locations as $loc) {
            $ids[] = $loc->id;
            if($loc->parent) {
                $ids[] = $loc->parent->id;
                if($loc->parent->parent) {
                    $ids[] = $loc->parent->parent->id;
                }
            }
        }
        return $ids;
    }

    /**
     * This will be used when a employee is not in the same sup department as
     * his manager. very rare but can happen. so the Role management will not be sufficient.
     * In that case use this function
     */
    public function managed_employees()
    {
        return $this->belongsToMany(self::class, 'extra_sup_department_employee_management', 'head_user_id', 'employee_id');
    }
    public function managed_by()
    {
        return $this->belongsToMany(self::class, 'extra_sup_department_employee_management', 'employee_id', 'head_user_id');
    }
    function geo_location_checkins()
    {
        return $this->hasMany(GeoLocationCheckIn::class);
    }

    function weekly_schedules()
    {
        return $this->hasMany(EmployeeWeeklySchedule::class);
    }

    function is_super_admin()
    {
        return $this->role == 'super_admin' || role_can('superadmin', $this);
    }
    function is_student()
    {
        return $this->role == 'student';
    }
    static function students()
    {
        return self::query()->where(function ($q) {
            $q->where('role', 'student');
        });
    }
    static function employees()
    {
        return self::query()->where(function ($q) {
            $q->where('role', 'employee');
        });
    }
    static function active_employees()
    {
        return self::employees()->where('is_active', true);
    }
    static function employees_and_students()
    {
        return self::query()->whereIn('role', ['employee', 'student', 'guest'])
            ->where('is_active', true);
    }
    static function terminated_employees()
    {
        return self::employees()->where('is_active', false);
    }

    public function shifts()
    {
        return $this->hasMany(StudentShift::class, 'student_id');
    }

    public function live_location()
    {
        return $this->hasMany(LiveLocation::class);
    }

    public function noticeView()
    {
        return $this->hasMany(NoticeView::class);
    }

    public function isWorkingDay(Carbon $date)
    {
        $holidays = Holiday::where('date', $date)->exists();
        return $this->shift?->{strtolower($date->format('l'))}
            && !$holidays
            && !$this->leaves()->where(function ($q2) use ($date) {
                    $q2->where('start_date', "<=", $date)
                        ->where('end_date', ">=", $date);
                })
                ->where('status', 'approved')
                ->exists()
            && !ShiftScheduleItem::where('employee_id', $this->id)
                ->where('date', $date)
                ->exists();
    }


    static function absent(Carbon|null $date = null)
    {
        if (!$date) $date = now();
        $q = self::active_employees()
            ->whereDoesntHave('attendance', function ($q) use ($date) {
                $q->whereDate('date', $date);
            });

        // make sure Today is not marked as absent and shift is not started yet
        if ($date->isToday()) {
            $q->whereHas('shift', function ($q) use ($date) {
                $q->where('start', '<=', now()->format('H:i:s'));
            });
        }

        // check leave of the date
        $q->whereDoesntHave('leaves', function ($q) use ($date) {
            $q->where(function ($q2) use ($date) {
                $q2->where('start_date', "<=", $date)
                    ->where('end_date', ">=", $date);
            })
            ->where('status', 'approved');
        });

        // check weekend of the date by shift
        $q->whereHas('shift', function ($q) use ($date) {
            $q->where(strtolower($date->format('l')), true);
        });
        // Exclude weekends assigned in active rosters.
        $dateKey = $date->format('Y-m-d');

        $rosterWeekendIds = \App\Models\RosterItem::with('roster')
            ->whereDate('date', $dateKey)
            ->whereHas('roster', function ($query) use ($dateKey) {
                $query->where('status', 'active')
                    ->whereDate('start', '<=', $dateKey)
                    ->whereDate('end', '>=', $dateKey);
            })
            ->get()
            ->filter(function ($item) use ($dateKey) {
                $duties = $item->roster?->no_shift_duty ?? [];

                return ($duties[$item->group][$dateKey] ?? null) === 'W';
            })
            ->pluck('employee_id')
            ->unique()
            ->values()
            ->all();

        $q->whereNotIn('users.id', $rosterWeekendIds);
        // Exclude today's roster duties that have not started.
        if ($date->isToday()) {
            $now = now();

            $futureRosterIds = \App\Models\RosterItem::with('shift')
                ->whereDate('date', $dateKey)
                ->whereHas('roster', function ($query) use ($dateKey) {
                    $query->where('status', 'active')
                        ->whereDate('start', '<=', $dateKey)
                        ->whereDate('end', '>=', $dateKey);
                })
                ->whereHas('shift', function ($query) {
                    $query->where('display_only', false);
                })
                ->get()
                ->filter(function ($item) use ($dateKey, $now) {
                    if (!$item->shift?->start) {
                        return false;
                    }

                    $startsAt = \Carbon\Carbon::parse(
                        $dateKey . ' ' . $item->shift->start
                    );

                    return $now->lt($startsAt);
                })
                ->pluck('employee_id')
                ->unique()
                ->values()
                ->all();

            $q->whereNotIn('users.id', $futureRosterIds);
        }
        return $q->whereRaw("not exists (select * from holidays where date = '{$date->format('Y-m-d')}')");
    }
    static function currently_inside()
    {
        return self::active_employees()
            ->whereHas('attendance', function ($query) {
                $query->whereDate('date', today())
                    ->whereHas('used_in_out_records', function ($q) {
                        $q->havingRaw('MOD(COUNT(in_out_records.id), 2) != 0') //odd count
                            ->groupBy('attendance_id');
                    });
            });
    }
    static function currently_outside()
    {
        return self::active_employees()
            ->whereHas('attendance', function ($query) {
                $query->whereDate('date', today())
                    ->whereHas('used_in_out_records', function ($q) {
                        $q->havingRaw('MOD(COUNT(in_out_records.id), 2) = 0') // even
                            ->groupBy('attendance_id');
                    });
            });
    }
    function total_working_days(?Carbon $from = null, ?Carbon $to = null)
    {
        if (!$from) $from = now()->startOfMonth();
        if (!$to) $to = now()->endOfMonth();
        $holidays = Holiday::whereBetween('date', [$from, $to])->get()->pluck('date')->toArray();
        $working_days = [];
        $tmp = $from->copy();
        for ($i = 0; $i < $to->day; $i++) {
            if ($this->shift->{strtolower($tmp->format('l'))} && !in_array($tmp->format('Y-m-d'), $holidays)) {
                $working_days[] = $tmp->format('Y-m-d');
            }
            $tmp = $tmp->addDay();
        }
        return ($working_days);
    }
    static function user_count()
    {
        return self::where('role', '!=', 'super_admin')
            ->where('is_active', true)
            ->count();
    }
    // public function notifications()
    // {
    //     return $this->belongsToMany(Notification::class);
    // }

    public function rel_to_sms_notifications(): HasMany
    {
        return $this->hasMany(SmsNotification::class);
    }


    // Relationship: Get chats where user is sender
    public function sentChats(): HasMany
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    // Relationship: Get chats where user is receiver
    public function receivedChats(): HasMany
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }

    // Relationship: Get all chats where the user is involved
    public function chats()
    {
        return $this->hasMany(Chat::class, 'sender_id')
            ->orWhere('receiver_id', $this->id);
    }

    public function chat_members()
    {
        return $this->hasMany(ChatMember::class);
    }

    public function deductionItemEntries()
    {
        return $this->hasMany(DeductionItemEntry::class, 'employee_id', 'id');
    }
    public function long_absent()
    {
        return $this->hasOne(LongAbsentUser::class);
    }
    function locations_admin()
    {
        return $this->belongsToMany(Location::class, 'location_user_admin');
    }
    function locations_admin_areas()
    {
        $ids = [];
        foreach($this->locations_admin as $loc) {
            $ids[] = $loc->id;
            if($loc->parent) {
                $ids[] = $loc->parent->id;
                if($loc->parent->parent) {
                    $ids[] = $loc->parent->parent->id;
                }
            }
        }
        return $ids;
    }
    function addStudentId()
    {
        $last = User::students()->orderBy('student_id', 'desc')->first()?->student_id;
        $this->student_id = ($last ? $last : 1000) + 1;
        $this->save();
    }

    /**
     * Device sessions relationship
     */
    function device_sessions()
    {
        return $this->hasMany(DeviceSession::class);
    }

    /**
     * Get active device sessions
     */
    function active_device_sessions()
    {
        return $this->device_sessions()->where('last_activity', '>=', now()->subDays(30));
    }

    /**
     * Get current device session
     */
    function current_device_session()
    {
        return $this->device_sessions()->where('is_current', true)->first();
    }

    /**
     * Terminate all device sessions except current one
     */
    function terminateOtherDeviceSessions($currentDeviceId = null)
    {
        $query = $this->device_sessions();

        if ($currentDeviceId) {
            $query->where('device_id', '!=', $currentDeviceId);
        }

        foreach ($query->get() as $session) {
            $session->terminate();
        }
    }
    
}
