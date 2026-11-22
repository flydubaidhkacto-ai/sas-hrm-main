<?php

namespace App\Http\Controllers;

use App\Actions\EmployeePunched;
use App\Exports\AbsentReport;
use App\Exports\AttendanceReport;
use App\Exports\EmployeeAttendanceReport;
use App\Exports\Excel\TotalDutyReport;
use App\Exports\InOutSummeryReport;
use App\Exports\Excel\MachineLogReport;
use App\Exports\Excel\MissedCheckoutReport;
use App\Exports\LateReport;
use App\Jobs\EmployeePunchedJob;
use App\Jobs\GenerateLongAbsentReport;
use App\Models\Attendance;
use App\Models\AttendanceMonthly;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\InOutRecord;
use App\Models\LeaveApplication;
use App\Models\LiveLocation;
use App\Models\Setting;
use App\Models\User;
use App\Traits\GenerateTotalDutyOfEmployee;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use App\Services\AttendanceReportService;
use App\Services\AttendanceReportGenerateService;


class ReportController extends Controller
{
    use GenerateTotalDutyOfEmployee;

    function create_fake_data()
    {
        InOutRecord::query()->delete();
        Attendance::query()->delete();
        $date = Carbon::now()->subMonth();
        $user = 1002; // User::active_employees()->first()?->id ?? 1;
        for ($i = 0; $i < 10; $i++) {
            $ins = [
                $date->format('Y-m-d') . ' 0' . rand(4, 9) . ':' . rand(10, 59) . ':00',
                $date->format('Y-m-d') . ' 0' . rand(17, 23) . ':' . rand(10, 59) . ':00',
                $date->format('Y-m-d') . ' 0' . rand(4, 9) . ':' . rand(10, 59) . ':00',
                $date->format('Y-m-d') . ' 0' . rand(17, 23) . ':' . rand(10, 59) . ':00',
                $date->format('Y-m-d') . ' 0' . rand(4, 9) . ':' . rand(10, 59) . ':00',
                $date->format('Y-m-d') . ' 0' . rand(17, 23) . ':' . rand(10, 59) . ':00',
            ];
            for ($i = 0; $i < rand(0, count($ins)); $i++) {
                if ($date->year > 2027) {
                    $i = 11;
                }
                InOutRecord::create([
                    'user_id' => $user,
                    'status' => 1,
                    'date_time' => $ins[rand(1, count($ins)) - 1],
                    'device_uid' => $i
                ]);
            }
            $date->addDay();
        }
    }

    function in_out_summery(Request $request)
    {
        abort_if(!(
            role_can('reports')
            || role_can('reports.self.super_department')
            || role_can('reports.self')
            || user()->managed_employees->count() > 0
            || user()->admin_of_super_departments
        ), 403);
        $from = carbon($request->start_date)->startOfDay();
        $to = carbon($request->end_date)->endOfDay();
        $data = Attendance::with('employee.designation')
            ->with(['employee.designation', 'employee.department.super_department', 'shift']);
        if ($from->format('Y-m-d') === $to->format('Y-m-d')) {
            $data->whereDate('date', $from->format('Y-m-d'));
        } else {
            $data->whereDate('date', '>=', $from)
                ->whereDate('date', '<=', $to);
        }

        if (
            !role_can('reports') &&
            user()->admin_of_super_departments
        ) {
            $data->whereHas('employee', function ($query) {
                return $query->whereHas('department', function ($query) {
                    return $query->whereIn('super_department_id', user()->admin_of_super_departments);
                });
            });
        } else
            if (
                !role_can('reports')
                && !role_can('reports.self.super_department')
                && role_can('reports.self')
                && user()->managed_employees->count() == 0
            ) {
                $data->where('employee_id', user()->id);
            } else
                if (
                    !role_can('reports')
                    && (
                        role_can('reports.self.super_department')
                        || user()->managed_employees->count() > 0
                    )
                ) {
                    $data->whereHas('employee', function ($query) {
                        return $query->whereHas('department', function ($query) {
                            return $query->where('super_department_id', user()->department ?->super_department_id);
                })
                            ->orWhereIn('id', user()->managed_employees->pluck('id'));
                    });
                }

        if ($request->employee)
            $data->where('employee_id', $request->employee);
        if ($request->department) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('department_id', $request->department);
            });
        } elseif ($request->super_department) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->whereHas('department', function ($query) use ($request) {
                    return $query->where('super_department_id', $request->super_department);
                })
                    ->orWhereIn('id', user()->managed_employees->pluck('id'));
            });
        }
        if ($request->designation) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('designation_id', $request->designation);
            });
        }
        if ($request->export) {
            $as = $request->export;
            if ($as == 'pdf') {
                return InOutSummeryReport::download_pdf($data->get(), 'In and Out Summery.pdf');
            }
            if ($as == 'print') {
                return InOutSummeryReport::print($data->get());
            }
            if ($as == 'xlsx') {
                return InOutSummeryReport::download_excel($data->get(), 'In and Out Summery.xlsx');
            }
            // dd($as);
        }

        return view('reports.in_out_summery', [
            'data' => $data->get(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    function machine_log(Request $request)
    {
        $data = InOutRecord::with(['employee.designation', 'employee.department.super_department'])
            ->orderBy('date_time', 'desc');

        abort_if(!(
            role_can('reports')
            || role_can('reports.self.super_department')
            || role_can('reports.self')
            || user()->admin_of_super_departments
        ), 403);

        if (
            !role_can('reports') &&
            user()->admin_of_super_departments
        ) {
            $data->whereHas('employee', function ($query) {
                return $query->whereHas('department', function ($query) {
                    return $query->whereIn('super_department_id', user()->admin_of_super_departments);
                });
            });
        } else
            if (
                !role_can('reports')
                && !role_can('reports.self.super_department')
                && role_can('reports.self')
                && user()->managed_employees->count() == 0
            ) {
                $data->where('user_id', user()->id);
            } else
                if (
                    !role_can('reports')
                    && (
                        role_can('reports.self.super_department')
                        || user()->managed_employees->count() > 0
                    )
                ) {
                    $data = $data->whereHas('employee', function ($query) {
                        return $query->whereHas('department', function ($query) {
                            return $query->where('super_department_id', user()->department ?->super_department_id);
                })
                            ->orWhereIn('id', user()->managed_employees->pluck('id'));
                    });
                }

        if ($request->employee)
            $data = $data->where('user_id', $request->employee);
        if (!$request->employee && user()->managed_employees->count() > 0)
            $data = $data->where('user_id', user()->id);

        if ($request->department) {
            $data = $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('department_id', $request->department);
            });
        } elseif ($request->super_department) {
            $data = $data->whereHas('employee', function ($query) use ($request) {
                return $query->whereHas('department', function ($query) use ($request) {
                    return $query->where('super_department_id', $request->super_department);
                })
                    ->orWhereIn('id', user()->managed_employees->pluck('id'));
            });
        }
        if ($request->designation) {
            $data = $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('designation_id', $request->designation);
            });
        }

        if ($request->start_date)
            $data = $data->where('date_time', '>=', Carbon::parse($request->start_date));
        if ($request->end_date)
            $data = $data->where('date_time', '<=', Carbon::parse($request->end_date)->endOfDay());
        if ($request->outside) {
            $data->whereNotNull('location');
        }

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return (new MachineLogReport($data))
                    ->download("Machine Log Report.xlsx");
            }
        }

        $data = $data
            ->with(['device', 'cloud_device'])
            ->paginate(100);
        return view('reports.machine-log', [
            'data' => $data
        ]);
    }

    function attendance(Request $request)
    {
        $from = $request->start_date ? carbon($request->start_date) : now()->startOfDay();
        if (config('app.customer') !== 'vector') $from->startOfDay();
        $to = $request->end_date ? carbon($request->end_date)->endOfDay() : now();
        if (config('app.customer') !== 'vector') $to->startOfDay();

        $data = Attendance::orderBy('date')
            ->with(['employee.designation', 'employee.department.super_department', 'shift']);

        if (config('app.customer') === 'vector' && $request->from_time && $request->to_time) {
            $data->where(function ($q) use ($request) {
                if ($request->from_time < $request->to_time) {
                    $q->whereTime('check_in', '>=', $request->from_time)
                        ->whereTime('check_in', '<=', $request->to_time);
                } else {
                    // Range crosses midnight
                    $q->whereTime('check_in', '>=', $request->from_time)
                        ->whereTime('check_in', '<=', $request->to_time);
                }
            });
        }
        if ($from->format('Y-m-d') === $to->format('Y-m-d')) {
            $data->whereDate('date', $from->format('Y-m-d'));
        } else {
            $data->whereDate('date', '>=', $from)
                ->whereDate('date', '<=', $to);
        }

        abort_if(!(
            role_can('reports')
            || user()->admin_of_super_departments
            || role_can('reports.self.super_department')
            || role_can('reports.self')
        ), 403);

        if (
            $request->attendance_ids && $request->shift_id && (
                role_can('reports')
                || role_can('reports.self.super_department')
                || user()->admin_of_super_departments
            )
        ) {
            $data = Attendance::whereIn('id', explode(',', $request->attendance_ids))
                ->get();
            foreach ($data as $attendance) {
                $prev_shift = $attendance->employee->shift_id;
                $attendance->employee()->update([
                    'shift_id' => $request->shift_id
                ]);
                $attendance->shift_id = $request->shift_id;
                $attendance->save();

                $inouts = $attendance->used_in_out_records;
                if ($inouts->count() > 0) {
                    \App\Actions\EmployeePunched\Execute::run($inouts->last(), $attendance);
                }

                $attendance->employee()->update([
                    'shift_id' => $prev_shift
                ]);
            }
            Toastr::success('Shift updated successfully :)', 'Success');
            return redirect()->back();
        }
        if (
            !role_can('reports') &&
            user()->admin_of_super_departments
        ) {
            $data->whereHas('employee', function ($query) {
                return $query->whereHas('department', function ($query) {
                    return $query->whereIn('super_department_id', user()->admin_of_super_departments);
                });
            });
        } else
            if (
                !role_can('reports')
                && !role_can('reports.self.super_department')
                && role_can('reports.self')
                && user()->managed_employees->count() == 0
            ) {
                // dd(2);
                $data->where('employee_id', user()->id);
            } else
                if (
                    !role_can('reports')
                    && (
                        role_can('reports.self.super_department')
                        || user()->managed_employees->count() > 0
                    )
                ) {
                    $data = $data->whereHas('employee', function ($query) {
                        return $query->whereHas('department', function ($query) {
                            return $query->where('super_department_id', user()->department ?->super_department_id);
                })
                            ->orWhereIn('id', user()->managed_employees->pluck('id'));
                    });
                }
        if ($request->employee)
            $data = $data->where('employee_id', $request->employee);
        if ($request->department) {
            $data = $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('department_id', $request->department);
            });
        } elseif ($request->super_department) {
            $data = $data->whereHas('employee', function ($query) use ($request) {
                return $query->whereHas('department', function ($query) use ($request) {
                    return $query->where('super_department_id', $request->super_department)
                        ->orWhereIn('employee_id', user()->managed_employees->pluck('id'));
                });
            });
        }
        if ($request->designation) {
            $data = $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('designation_id', $request->designation);
            });
        }
        if ($request->duty_type === 'Outside Duty Punch') {
            $data->whereHas('used_in_out_records', function ($q) {
                $q->whereNotNull('location');
            });
            // $data->where('duty_type', 'like', 'Punch from outside%');
        } else
            if ($request->duty_type) {
                // dd($request->duty_type);
                $data->where('duty_type', $request->duty_type);
            }

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return AttendanceReport::download_excel($data->get(), 'Attendance report.xlsx');
            }
            if ($as == 'pdf') {
                return AttendanceReport::download_pdf($data->get(), 'Attendance report.pdf');
            }
            if ($as == 'docx') {
                return AttendanceReport::download_docx($data->get(), 'Attendance report.docx');
            }
            if ($as == 'print') {
                return AttendanceReport::print($data->get());
            }
        }

        $data = $data->get();

        return view('reports.attendance', [
            'data' => $data,
            'from' => $from,
            'to' => $to,
            'missed_checkout' => false,
            'shifts' => \App\Models\Shift::all(),
        ]);
    }

    function missed_attendance(Request $request)
    {
        $from = carbon($request->start_date)->startOfDay();
        $to = carbon($request->end_date)->endOfDay();
        $data = Attendance::orderBy('date')
            ->with(['employee.designation', 'employee.department.super_department', 'shift']);
        if ($from->format('Y-m-d') === $to->format('Y-m-d')) {
            $data->whereDate('date', $from->format('Y-m-d'));
        } else {
            $data->whereDate('date', '>=', $from)
                ->whereDate('date', '<=', $to);
        }

        abort_if(!(
            role_can('reports')
            || user()->admin_of_super_departments
            || role_can('reports.self.super_department')
            || role_can('reports.self')
        ), 403);

        if (
            !role_can('reports') &&
            user()->admin_of_super_departments
        ) {
            $data->whereHas('employee', function ($query) {
                return $query->whereHas('department', function ($query) {
                    return $query->whereIn('super_department_id', user()->admin_of_super_departments);
                });
            });
        } else
            if (
                !role_can('reports')
                && !role_can('reports.self.super_department')
                && role_can('reports.self')
                && user()->managed_employees->count() == 0
            ) {
                $data->where('employee_id', user()->id);
            } else
                if (
                    !role_can('reports')
                    && (
                        role_can('reports.self.super_department')
                        || user()->managed_employees->count() > 0
                    )
                ) {
                    $data = $data->whereHas('employee', function ($query) {
                        return $query->whereHas('department', function ($query) {
                            return $query->where('super_department_id', user()->department ?->super_department_id);
                })
                            ->orWhereIn('id', user()->managed_employees->pluck('id'));
                    });
                }
        if ($request->employee)
            $data = $data->where('employee_id', $request->employee);
        if ($request->department) {
            $data = $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('department_id', $request->department);
            });
        } elseif ($request->super_department) {
            $data = $data->whereHas('employee', function ($query) use ($request) {
                return $query->whereHas('department', function ($query) use ($request) {
                    return $query->where('super_department_id', $request->super_department)
                        ->orWhereIn('employee_id', user()->managed_employees->pluck('id'));
                });
            });
        }
        if ($request->designation) {
            $data = $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('designation_id', $request->designation);
            });
        }
        if ($request->duty_type === 'Outside Duty Punch') {
            $data->whereHas('used_in_out_records', function ($q) {
                $q->whereNotNull('location');
            });
            // $data->where('duty_type', 'like', 'Punch from outside%');
        } else
            if ($request->duty_type) {
                // dd($request->duty_type);
                $data->where('duty_type', $request->duty_type);
            }

        $data->withCount('used_in_out_records')
            ->having('used_in_out_records_count', '=', 1);


        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return (new MissedCheckoutReport($data))
                    ->download("Missed check-out Report.xlsx");
            }
        }

        $data = $data->paginate(300);

        return view('reports.missed-checkout', [
            'data' => $data,
            'from' => $from,
            'to' => $to,
            'shifts' => \App\Models\Shift::all(),
            'missed_checkout' => true,
        ]);
    }

    function attendance_employee(Request $request)
    {
        $request->validate([
            'employee' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        $from = carbon($request->start_date)->startOfDay();
        $to = carbon($request->end_date)->endOfDay();
        abort_if(!(
            role_can('reports')
            || user()->admin_of_super_departments
            || role_can('reports.self.super_department')
            || role_can('reports.self')
            || user()->managed_employees->count() > 0
        ), 403);

        if (
            !role_can('reports')
            && !role_can('reports.self.super_department')
            && !user()->admin_of_super_departments
            && role_can('reports.self')
            && user()->managed_employees->count() == 0
        ) {
            $employee = user();
        } else {
            $employee = User::find($request->employee);
        }

        $data = [];
        if ($employee) {
            $data = $this->generate_total_duty_of_employee($employee, $from, $to);
            // dd($data);
            if ($request->export) {
                $as = $request->export;
                if ($as == 'pdf') {
                    return EmployeeAttendanceReport::download_pdf($data, $employee, $from, $to, 'Employee Attendance report.pdf');
                }
                if ($as == 'print') {
                    return EmployeeAttendanceReport::print($data, $employee, $from, $to);
                }
            }
        }

        return view('reports.attendance-employee', [
            '_employee' => $employee,
            'data' => $data,
            'from' => $from,
            'to' => $to,
        ]);
    }

    function late(Request $request)
    {
        $from = $request->start_date ? carbon($request->start_date)->startOfDay() : carbon()->subDay()->startOfDay();
        $to = $request->end_date ? carbon($request->end_date)->endOfDay() : now()->endOfDay();

        $att = Attendance::query()
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->when(config('app.customer') === 'vector', function ($query) {
                return $query->whereHas('employee', function ($query2) {
                    return $query2->where('from_management', false);
                });
            });

        abort_if(!(
            role_can('reports')
            || user()->admin_of_super_departments
            || role_can('reports.self.super_department')
            || role_can('reports.self')
            || user()->managed_employees->count() > 0
        ), 403);
        if (
            !role_can('reports') &&
            user()->admin_of_super_departments
        ) {
            $att->whereHas('employee', function ($query) {
                return $query->whereHas('department', function ($query) {
                    return $query->whereIn('super_department_id', user()->admin_of_super_departments);
                });
            });
        } else
            if (
                !role_can('reports')
                && !role_can('reports.self.super_department')
                && role_can('reports.self')
                && user()->managed_employees->count() == 0
            ) {
                $att->where('employee_id', user()->id);
            } else
                if (
                    !role_can('reports')
                    && (
                        role_can('reports.self.super_department')
                        || user()->managed_employees->count() > 0
                    )
                ) {
                    $att = $att->whereHas('employee', function ($query) {
                        return $query->whereHas('department', function ($query) {
                            return $query->where('super_department_id', user()->department ?->super_department_id);
                })
                            ->orWhereIn('id', user()->managed_employees->pluck('id'));
                    });
                }
        if ($request->employee)
            $att = $att->where('employee_id', $request->employee);
        if ($request->department) {
            $att = $att->whereHas('employee', function ($query) use ($request) {
                return $query->where('department_id', $request->department);
            });
        } elseif ($request->super_department) {
            $att = $att->whereHas('employee', function ($query) use ($request) {
                return $query->whereHas('department', function ($query) use ($request) {
                    return $query->where('super_department_id', $request->super_department);
                })
                    ->orWhereIn('id', user()->managed_employees->pluck('id'));
            });
        }
        if ($request->designation) {
            $att = $att->whereHas('employee', function ($query) use ($request) {
                return $query->where('designation_id', $request->designation);
            });
        }

        $att = $att->with(['employee.designation', 'employee.department.super_department', 'shift'])
            ->where('got_late_check_in', true)
            ->orderBy('date', 'asc')
            ->get();

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return LateReport::download_excel($att, 'Late report.xlsx');
            }
            if ($as == 'pdf') {
                return LateReport::download_pdf($att, 'Late report.pdf');
            }
            if ($as == 'docx') {
                return LateReport::download_docx($att, 'Late report.docx');
            }
            if ($as == 'print') {
                return LateReport::print($att);
            }
        }


        return view('reports.late', [
            'data' => $att,
            'start_date' => $from->format('d-m-Y'),
            'end_date' => $to->format('d-m-Y'),
//            'start_date' => $from->toDateString(),
//            'end_date' => $to->toDateString(),

            'limit' => Setting::where('key', 'reports.late.min_late_minutes')->first() ?->value ?: 5,
        ]);
    }

    function absent(Request $request)
    {
        //dd($request->all());

        $from = $request->start_date ? carbon($request->start_date)->startOfDay() : carbon()->startOfDay();
        $to = $request->end_date ? carbon($request->end_date)->endOfDay() : now()->endOfDay();

        //  dd($to);

        abort_if(!(
            role_can('reports')
            || user()->admin_of_super_departments
            || role_can('reports.self.super_department')
            || role_can('reports.self')
            || user()->managed_employees->count() > 0
        ), 403);
        $employees = [];
        $currentDate = $from->copy();
        while ($currentDate->lte($to)) {
            $tmp_users = User::absent($currentDate)
                ->when(config('app.customer') === 'vector', function ($query) {
                    return $query->where('from_management', false);
                });
            if (
                !role_can('reports') &&
                user()->admin_of_super_departments
            ) {
                $tmp_users->whereHas('department', function ($query) {
                    return $query->whereIn('super_department_id', user()->admin_of_super_departments);
                });
            } else
                if (
                    !role_can('reports')
                    && !role_can('reports.self.super_department')
                    && role_can('reports.self')
                    && user()->managed_employees->count() == 0
                ) {
                    $tmp_users->where('id', user()->id);
                } else
                    if (
                        !role_can('reports')
                        && (
                            role_can('reports.self.super_department')
                            || user()->managed_employees->count() > 0
                        )
                    ) {
                        $tmp_users->whereHas('department', function ($query) {
                            return $query->where('super_department_id', user()->department ?->super_department_id);
                })
                            ->orWhereIn('id', user()->managed_employees->pluck('id'));
                    }

            if ($request->employee) {
                $tmp_users->where('id', $request->employee);
            }
            if ($request->department) {
                $tmp_users->where('department_id', $request->department);
            } elseif ($request->super_department) {
                $tmp_users->whereHas('department', function ($query) use ($request) {
                    return $query->where('super_department_id', $request->super_department);
                })
                    ->orWhereIn('id', user()->managed_employees->pluck('id'));
            }
            if ($request->designation) {
                $tmp_users->where('designation_id', $request->designation);
            }

            $tmp_users->with(['department.super_department', 'designation'])
                ->orderBy('name', 'asc');

            $tmp_users = $tmp_users
                ->get();
            $employees[$currentDate->format('Y_m_d')] = $tmp_users;
            $currentDate->addDay(1);
        }

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return AbsentReport::download_excel($employees, $from, $to, 'Absent report.xlsx');
            }
            if ($as == 'pdf') {
                return AbsentReport::download_pdf($employees, $from, $to, 'Absent report.pdf');
            }
            if ($as == 'docx') {
                return AbsentReport::download_docx($employees, $from, $to, 'Absent report.docx');
            }
            if ($as == 'print') {
                return AbsentReport::print($employees, $from, $to);
            }
        }

        // dd($employees);
        return view('reports.absent', [
            'employees' => $employees,
            'start_date' => $from,
            'end_date' => $to,
        ]);
    }

    function absent_long(Request $request)
    {
        abort_if(!(
            role_can('reports')
            || user()->admin_of_super_departments
            || role_can('reports.self.super_department')
            || role_can('reports.self')
            || user()->managed_employees->count() > 0
        ), 403);

        $tmp_users = User::active_employees();
        if (
            !role_can('reports') &&
            user()->admin_of_super_departments
        ) {
            $tmp_users->whereHas('department', function ($query) {
                return $query->whereIn('super_department_id', user()->admin_of_super_departments);
            });
        } else
            if (
                !role_can('reports')
                && !role_can('reports.self.super_department')
                && role_can('reports.self')
                && user()->managed_employees->count() == 0
            ) {
                $tmp_users->where('id', user()->id);
            } else
                if (
                    !role_can('reports')
                    && (
                        role_can('reports.self.super_department')
                        || user()->managed_employees->count() > 0
                    )
                ) {
                    $tmp_users->whereHas('department', function ($query) {
                        return $query->where('super_department_id', user()->department ?->super_department_id);
            })
                        ->orWhereIn('id', user()->managed_employees->pluck('id'));
                }

        if ($request->employee) {
            $tmp_users->where('id', $request->employee);
        }
        if ($request->department) {
            $tmp_users->where('department_id', $request->department);
        } elseif ($request->super_department) {
            $tmp_users->whereHas('department', function ($query) use ($request) {
                return $query->where('super_department_id', $request->super_department);
            })
                ->orWhereIn('id', user()->managed_employees->pluck('id'));
        }
        if ($request->designation) {
            $tmp_users->where('designation_id', $request->designation);
        }

        $tmp_users = $tmp_users
            ->with(['department.super_department', 'designation', 'long_absent'])
            ->orderBy('name', 'asc')
            ->whereHas('long_absent')
            ->get();

        return view('reports.absent-long', [
            'employees' => $tmp_users,
        ]);
    }

    function total_duty_report(Request $request)
    {
        abort_if(!(
            role_can('reports')
            || user()->admin_of_super_departments
            || role_can('reports.self.super_department')
            || role_can('reports.self')
        ), 403);

        return view('reports.total_duty_report', []);
    }

    function total_duty_report_generate(Request $request)
    {
        abort_if(!(
            role_can('reports')
            || user()->admin_of_super_departments
            || role_can('reports.self.super_department')
            || role_can('reports.self')
            || user()->managed_employees->count() > 0
        ), 403);
        $request->validate([
            'employee' => 'required|exists:users,id',
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $from = $request->from ? carbon($request->from)->startOfDay() : carbon()->startOfDay();
        $to = $request->to ? carbon($request->to)->endOfDay() : now()->endOfDay();
        $user = User::with([
            'department.super_department',
            'designation',
        ])
            ->findOrFail($request->employee);

        // if (
        //     !role_can('reports')
        //     && !role_can('reports.self.super_department')
        //     && role_can('reports.self')
        // ) {
        //     abort_if($user->id != user()->id, 403);
        // } else
        // if (
        //     !role_can('reports')
        //     && role_can('reports.self.super_department')
        // ) {
        //     abort_if($user->department?->super_department_id != user()->department?->super_department_id, 403);
        // }

        return $this->generate_total_duty_of_employee($user, $from, $to);
    }

    function total_duty_report_create_view(Request $request)
    {
        abort_if(!(
            role_can('reports')
            || user()->admin_of_super_departments
            || role_can('reports.self.super_department')
            || role_can('reports.self')
        ), 403);
        $data = json_decode($request->data, true);
        if ($request->export) {
            $data = json_decode($request->data, true);
            // dd($request->all());
            $html = view('reports.total_duty_report_create_view', [
                'data' => $data,
                'for_export' => true,
            ])
                ->render();
            if ($request->export == 'xlsx') {
                return (new TotalDutyReport($data))->download('Total duty report.xlsx');
            } else if ($request->export == 'pdf') {
                $dompdf = new Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                return $dompdf->stream('Total duty report.pdf');
            } else if ($request->export == 'print') {
                $html .= "<script>
                    window.print();
                    window.onafterprint = function() {
                        // Close the tab after printing is done
                        window.close();
                    };
                    let mediaQueryList = window.matchMedia('print');
                    mediaQueryList.addListener(function(mql) {
                        if (!mql.matches) {
                            if (window && !window.closed) {
                                window.close();
                            }
                        }
                    });
                    </script>";
                return $html;
            }
        }
        $html = view('reports.total_duty_report_create_view', ['data' => $data])
            ->render();
        return $html;
    }

    public function live_location(Request $request)
    {
        $request->validate([
            'user_id' => 'integer|exists:users,id',
            'start_date' => 'date',
            'end_date' => 'date'
        ]);

        $query = LiveLocation::query()
            ->where('user_id', $request->user_id)
            ->where('date_time', '>=', Carbon::parse($request->start_date)->format('Y-m-d H:i:s'))
            ->where('date_time', '<=', Carbon::parse($request->end_date)->format('Y-m-d H:i:s'));

        // Order results by date_time in ascending order
        $live_location = $query
            ->orderBy('date_time', 'asc')
            ->get([
                // 'id',
                'user_id',
                'latitude',
                'longitude',
                'date_time',
            ]);

        // If it's an AJAX request, return JSON
        if ($request->ajax()) {
            return $live_location->map(function ($item) {
                $item->date_time = Carbon::parse($item->date_time)->format('d M h:i:s a');
                return $item;
            });
        }

        return view('reports.live_location');
    }

    public function regenerate_attendance_report(Request $request)
    {
        abort_if(!role_can('superadmin'), 403);
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);
        $from = Carbon::parse($request->from_date)->startOfDay();
        $to = Carbon::parse($request->to_date)->endOfDay();

        InOutRecord::query()
            ->where('date_time', '>=', $from->copy()->subDays(1)->format('Y-m-d H:i:s'))
            ->where('date_time', '<=', $to->copy()->addDays(1)->format('Y-m-d H:i:s'))
            ->update(['added' => 0]);
        Attendance::query()
            ->where('date', '>=', $from->copy()->format('Y-m-d'))
            ->where('date', '<=', $to->copy()->format('Y-m-d'))
            ->delete();
        foreach (
            InOutRecord::query()
                ->where('date_time', '>=', $from->copy()->subDays(1)->format('Y-m-d H:i:s'))
                ->where('date_time', '<=', $to->copy()->addDays(1)->format('Y-m-d H:i:s'))
                ->get() as $in
        ) {
            EmployeePunchedJob::dispatch($in);
        }
        Toastr::success('Attendance regeneration has been requested!');
        return redirect()->back();
    }


    public function attendanceExportReportTest(Request $request)
    {
        $from = "2026-03-01";
        $to = "2026-03-12";

        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        $employee = 'all'; //all, Employee ID
        $shiftType = 'all'; //all, roster, regular
        $attendanceType = 'all'; //all, late, absent, ontime
        $superDepartmentId = 5; // all, id
        $reportBladeName = "reports.department-wise-attendance-reports-pdf";
        // $reportBladeName = "reports.attendance-reports-pdf"; // For Details Attendance Reports

        $payloadAttendanceData = [
            'from' => $from,
            'to' => $to,
            'employee' => $employee,
            'attendanceType' => $attendanceType,
            'shiftType' => $shiftType,
            'superDepartmentId' => $superDepartmentId,
        ];

        $data = app(AttendanceReportService::class)
            ->attendanceData(
                $payloadAttendanceData
            );

//        $dataArray = iterator_to_array($data); // generator থেকে array তে convert
//        dd($dataArray);

        $formattedFrom = $from->format('F d, Y');
        $formattedTo = $to->format('F d, Y');

        $dateRange = $formattedFrom === $formattedTo
            ? $formattedFrom
            : $formattedFrom . ' To ' . $formattedTo;

        $reportTitle = "All Attendance Report";
        $reportName = "all_attendance_report";

        $payloadPdfData = [
            'data' => $data,
            'reportTitle' => $reportTitle,
            'reportName' => $reportName,
            'dateRange' => $dateRange,
            'employee' => $employee,
            'attendanceType' => $attendanceType,
            'shiftType' => $shiftType,
            'reportBladeName' => $reportBladeName,
        ];

//        $excel = app(AttendanceReportGenerateService::class)
//            ->excelSave($payloadPdfData);

//        $excel = app(AttendanceReportGenerateService::class)
//            ->excelDownload($payloadPdfData);
//        return $excel;


        $pdf = app(AttendanceReportGenerateService::class)
            ->pdfSave($payloadPdfData);

//        $pdf = app(AttendanceReportGenerateService::class)
//            ->pdfDownload($payloadPdfData);
//        return $pdf;

    }


    public function payrollDemoReport(Request $request)
    {
        $from = "2026-02-01";
        $to = "2026-02-28";

        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        $employee = 'all'; //all, Employee ID
        $shiftType = 'all'; //all, roster, regular
        $attendanceType = 'all'; //all, late, absent, ontime
        $superDepartmentId = 4; // all, id

        $payloadAttendanceData = [
            'from' => $from,
            'to' => $to,
            'employee' => $employee,
            'attendanceType' => $attendanceType,
            'shiftType' => $shiftType,
            'superDepartmentId' => $superDepartmentId,
        ];

        $data = app(AttendanceReportService::class)
            ->attendanceData(
                $payloadAttendanceData
            );

        return view('reports.payroll.bank-salary-payment', compact('data'));

    }


}
