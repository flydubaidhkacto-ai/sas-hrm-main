<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Designation;
use App\Models\SuperDepartment;
use App\Models\User;
use Illuminate\Http\Request;

class Select2Controller extends Controller
{
    function select2_super_departments(Request $request)
    {
        $q = $request->q ?? '';

        $user = user(); // SAFE INIT

        $departments = SuperDepartment::where(function ($query) use($q) {
            $query->where('name', 'like', "%$q%");
        })->ordered();

        $departments->when($user && !role_can('user'), function ($query) use ($user) {

            if(is_admin_of_super_department() && !empty($user->admin_of_super_departments)) {
                $query->whereIn('id', $user->admin_of_super_departments);
            }

            elseif(optional($user->managed_employees)->count()) {
                $query->whereIn(
                    'id',
                    $user->managed_employees->pluck('department.super_department_id')->filter()->unique()
                );
            }
        });

        $departments = $departments->limit(20)->get();

        return response()->json(
            $departments->map(function ($d) {
                return [
                    'id' => $d->id,
                    'text' => $d->name,
                ];
            })
        );
    }
    function select2_departments(Request $request)
    {
        $q = $request->q ?? '';
        $user = user();

        $departments = Department::where(function ($query) use ($q) {
            $query->where('name', 'like', "%$q%");
        });

        $departments->when($user && !role_can('user'), function ($query) use ($user) {

            if (is_admin_of_super_department() && !empty($user->admin_of_super_departments)) {
                $query->whereIn('super_department_id', $user->admin_of_super_departments);
            }

            elseif (optional($user->managed_employees)->count()) {
                $query->whereIn(
                    'super_department_id',
                    $user->managed_employees->pluck('department.super_department_id')->filter()->unique()
                );
            }
        });

        if ($request->super_department) {
            $departments->where('super_department_id', $request->super_department);
        }

        $departments = $departments
            ->ordered()
            ->limit(20)
            ->get();

        return response()->json(
            $departments->map(fn($d) => [
                'id' => $d->id,
                'text' => $d->name,
            ])
        );
    }
    function select2_designations(Request $request)
    {
        $q = $request->q ?? '';
        $departments = Designation::where(function ($query) use($q) {
                $query->where('name', 'like', "%$q%");
            })->ordered();
        $departments = $departments
            ->limit(20)
            ->get();

        $data = [];
        foreach($departments as $department)
        {
            $data[] = [
                'id' => $department->id,
                'text' => $department->name,
            ];
        }
        return $data;
    }
    function select2_employees(Request $request)
    {
        $q = $request->q ?? '';
        $user = user();

        $employees = User::active_employees();

        if ($request->employee) {
            $employees->where('id', $request->employee);
        } else {
            $employees->where(function ($query) use ($q) {
                $query->where('name', 'like', "%$q%")
                    ->orWhere('phone_number', 'like', "%$q%")
                    ->orWhere('id', 'like', "%$q%")
                    ->orWhere('employee_id', 'like', "%$q%");
            });
        }

        if ($user && !role_can('reports') && !empty($user->admin_of_super_departments)) {

            $employees->where(function ($query) use ($user) {

                $query->whereHas('department', function ($q) use ($user) {
                    $q->whereIn('super_department_id', $user->admin_of_super_departments);
                })
                    ->orWhereIn('id', optional($user->managed_employees)->pluck('id')->filter())
                    ->orWhere('id', $user->id);
            });
        }

        if ($request->department) {
            $employees->where('department_id', $request->department);
        }

        if ($request->super_department) {
            $employees->whereHas('department', function ($q) use ($request) {
                $q->where('super_department_id', $request->super_department);
            });
        }

        if ($request->designation) {
            $employees->where('designation_id', $request->designation);
        }

        $employees = $employees->limit($request->no_limit ? null : 100)->get();

        return response()->json(
            $employees->map(fn($e) => [
                'id' => $e->id,
                'text' => $e->name,
            ])
        );
    }
    function select2_students(Request $request)
    {
        $q = $request->q ?? '';

        $students = User::students();

        if ($request->student) {
            $students->where('id', $request->student);
        } else {
            $students->where(function ($query) use ($q) {
                $query->where('name', 'like', "%$q%")
                    ->orWhere('phone_number', 'like', "%$q%")
                    ->orWhere('id', 'like', "%$q%")
                    ->orWhere('employee_id', 'like', "%$q%");
            });
        }

        if ($request->department) {
            $students->where('department_id', $request->department);
        }

        if ($request->designation) {
            $students->where('designation_id', $request->designation);
        }

        $students = $students->limit($request->no_limit ? null : 20)->get();

        return response()->json(
            $students->map(fn($s) => [
                'id' => $s->id,
                'text' => $s->name,
            ])
        );
    }
}
