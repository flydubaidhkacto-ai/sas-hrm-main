<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeList;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Shift;
use App\Models\SuperDepartment;
use App\Models\User;
use App\Models\UserDocument;
use App\Models\UserEdit;
use App\Models\UserZkDeviceFingerprint;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class EmployeeController extends Controller
{
    private function employeeListCondition(Builder $employees): Builder
    {
        abort_if(!(
            role_can('user')
            || role_can('user.self.super_department')
            || role_can('locations')
            || user()->geo_location_departments
            || is_admin_of_super_department()
        ), 403);

        $restrictedDepartments = [];
        if (
            role_can('user')
            || role_can('user.self.super_department')
            || is_admin_of_super_department()
        ) {
            if (
                !role_can('user')
                && !role_can('user.self.super_department')
                && is_admin_of_super_department()
            ) {
                $restrictedDepartments = array_merge($restrictedDepartments, user()->admin_of_super_departments ?: []);
            }else
            if (
                !role_can('user')
                && role_can('user.self.super_department')
            ) {
                $employees->whereHas('department', function ($q) {
                    $q->where('super_department_id', user()->department?->super_department_id);
                });
            }
        }
        if (
            !role_can('user')
            && !role_can('user.self.super_department')
            && user()->geo_location_departments
        ) {
            $restrictedDepartments = array_merge($restrictedDepartments, user()->geo_location_departments ?: []);
        }
        if($restrictedDepartments) {
            $employees->whereHas('department', function ($q) use($restrictedDepartments) {
                $q->whereIn('super_department_id', $restrictedDepartments);
            });
        }
        return $employees;
    }
    // all employee list
    public function list(Request $request)
    {
        $employees = $this->employeeListCondition(
            User::active_employees()->where('employee_type', '!=', 'Probationary')
        );
        if ($request->department) {
            $employees->where('department_id', $request->department);
        } else
        if ($request->super_department) {
            $employees->whereHas('department', function ($q) use ($request) {
                $q->where('super_department_id', $request->super_department);
            });
        }

        if ($request->designation) {
            $employees->where('designation_id', $request->designation);
        }

        if ($request->employee) {
            $employees->where(function ($q) use ($request) {
                $q->where('users.id', $request->employee);
            });
        }

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return EmployeeList::download_excel($employees->get(), 'Employees.xlsx');
            }
            if ($as == 'pdf') {
                return EmployeeList::download_pdf($employees->get(), 'Employees.pdf');
            }
            if ($as == 'docx') {
                return EmployeeList::download_docx($employees->get(), 'Employees.docx');
            }
            if ($as == 'print') {
                return EmployeeList::print($employees->get());
            }
        }
        // Group employees by super department (station) and department (flat list per dept, like XLSX format)
        $employees = $employees->with(['department.super_department', 'designation'])
            ->leftJoin('designations', 'designations.id', '=', 'users.designation_id')
            ->leftJoin('departments', 'departments.id', '=', 'users.department_id')
            ->leftJoin('super_departments', 'super_departments.id', '=', 'departments.super_department_id')
            ->orderBy('super_departments.order')
            ->orderBy('departments.order')
            ->orderBy('designations.order')
            ->orderBy('users.id')
            ->select('users.*')
            ->get();
        $groupedEmployees = [];
        foreach ($employees as $employee) {
            $station = $employee->department?->super_department?->name ?? 'Other';
            $deptName = $employee->department?->name ?? 'No Department';
            $groupedEmployees[$station][$deptName][] = $employee;
        }
        return view('employees.list', [
            'employees' => $employees,
            'groupedEmployees' => $groupedEmployees,
        ]);
    }
    public function list_terminated(Request $request)
    {
        $employees = $this->employeeListCondition(User::terminated_employees());

        if ($request->designation) {
            $employees->where('designation_id', $request->designation);
        }
        if ($request->employee) {
            $employees->where(function ($q) use ($request) {
                $q->where('users.id', $request->employee);
            });
        }
        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return EmployeeList::download_excel($employees->get(), 'Employees.xlsx');
            }
            if ($as == 'pdf') {
                return EmployeeList::download_pdf($employees->get(), 'Employees.pdf');
            }
            if ($as == 'docx') {
                return EmployeeList::download_docx($employees->get(), 'Employees.docx');
            }
            if ($as == 'print') {
                return EmployeeList::print($employees->get());
            }
        }

        // dd($employees);
        return view('employees.list', [
            'employees' => $employees->orderBy('id', 'asc')->get(),
            'create_hidden' => false
        ]);
    }
    function create()
    {
        abort_if(!(role_can('user') || role_can('user.self.super_department') || is_admin_of_super_department()), 403);
        
        // check if shift, department and designation exists
        Shift::initFirst();
        Department::initFirst();
        Designation::initFirst();
        if (Department::count() == 0) {
            Toastr::warning('Please add department first :)', 'Warning');
            return redirect()->route('departments.index');
        }
        if (Designation::count() == 0) {
            Toastr::warning('Please add designation first :)', 'Warning');
            return redirect()->route('employees.designations.index');
        }
        return view('employees.create', [
            'sup_depts' => SuperDepartment::query()->ordered()
                ->when(!role_can('user'), function ($q) {
                    if (user()->admin_of_super_departments) {
                        $q->whereIn('id', user()->admin_of_super_departments);
                    } else
                    if (role_can('user.self.super_department') && user()->department?->super_department_id) {
                        $q->where('id', user()->department->super_department_id);
                    } else {
                        // $q->whereIn('id', []);
                    }
                })
                ->get(),
        ]);
    }

    // save data employee
    public function save(Request $request)
    {
        abort_if(!(role_can('user') || role_can('user.self.super_department') || is_admin_of_super_department()), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15|unique:users|min:9',
            'personal_phone' => 'nullable|string|max:15|min:9',
            'email' => 'nullable|string|email|max:255|unique:users',
            'personal_email' => 'nullable|string|email|max:255',
            'nid_or_passport' => 'nullable|string|max:255|unique:users',
            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:' . date('Y-m-d', strtotime('-16 years')),
            ],
            'gender' => 'nullable|string|max:255',
            'department' => 'required|exists:departments,id',
            'designation' => 'required|exists:designations,id',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'permanent_address' => 'nullable|string|max:255',
            'permanent_city' => 'nullable|string|max:255',
            'blood_group' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|max:255',
            'nid_or_passport' => 'nullable|string|max:255',
            'nid_or_passport_file' => 'nullable|image|mimes:png,jpg,jpeg|max:300',
            'employee_type' => 'required|string|max:255',
            'join_date' => 'required|date',
            'picture_file' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'signature_file' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'reference_name' => 'nullable|string|max:255',
            'reference_phone' => 'nullable|string|max:255',
            'emergency_name' => 'nullable|string|max:255',
            'emergency_relation' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:255',
            'emergency_address' => 'nullable|string|max:255',
            'employee_id' => 'nullable|string|max:255|unique:users',
            'role' => 'nullable|exists:roles,id',
            'password' => 'nullable|string|min:1|max:100',
            'shift_id' => 'required|exists:shifts,id',
            'paytype' => 'nullable|string|in:hourly,daily,monthly',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'account_holder_name' => 'nullable|string',
            'branch_name' => 'nullable|string',
            'routing_number' => 'nullable|string|max:255',
            'tin_number' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            // Geo-location fields
            'home_latitude' => 'nullable|numeric|between:-90,90',
            'home_longitude' => 'nullable|numeric|between:-180,180',
            'assigned_location_ids' => 'nullable|array',
            'assigned_location_ids.*' => 'exists:locations,id',
            'location_distances' => 'nullable|array',
            'location_distances.*' => 'nullable|numeric|min:0',
        ]);
        $request->validate([
            'probation_end_date' => 'nullable|date|after:join_date',
            'join_date' => 'required|date',
            'document' => 'nullable|array',
            'document.*.file' => 'nullable|file|max:30000',
            'document.*.type' => 'nullable|string|max:255',
        ]);

        unset($data['password']);
        $data['role'] = 'employee';
        $data['department_id'] = $request->department;
        $data['designation_id'] = $request->designation;
        unset($data['department']);
        unset($data['designation']);
        $data['can_overtime'] = $request->can_overtime == 1;
        $data['can_outside_attendence'] = $request->can_outside_attendence == 1;
        $data['work_from_home_request'] = $request->work_from_home_request == 1;
        $data['join_date'] = carbon($data['join_date']);

        if (
            is_numeric($data['employee_id'])
            && !User::where('id', $data['employee_id'])->exists()
        ) {
            $data['id'] = $data['employee_id'];
        }

        if (role_can('role') && $request->role) {
            $data['role_id'] = $request->role;
        }

        if (
            !role_can('user')
            && user()->department?->super_department_id != Department::findOrFail($data['department_id'])->super_department_id
        ) {
            abort(403, "Please get permission from Administrator");
        }

        $data['can_overtime'] = $request->can_overtime == 1;
        if ($request->hasFile('picture_file')) {
            $data['picture_file'] = Storage::put('employees/avatars', $request->file('picture_file'));
        }
        if ($request->hasFile('nid_or_passport_file')) {
            $data['nid_or_passport_file'] = Storage::put('employees/nid-or-passports', $request->file('nid_or_passport_file'));
        }
        if ($request->hasFile('signature_file')) {
            $data['signature_file'] = Storage::put('employees/signatures', $request->file('signature_file'));
        }

        if ($request->zk_device_privilege && in_array($request->zk_device_privilege, ['admin', 'user', 'non_ac_user'])) {
            $data['zk_device_privilege'] = $request->zk_device_privilege;
        }
        $data['zk_device_password'] = $request->zk_device_password;
        $data['zk_device_card'] = $request->zk_device_card;
        $data['live_location_track'] = $request->live_location_track == 1;
        if ($request->probation_end_date) {
            $data['schedule_termination_at'] = carbon($request->probation_end_date);
        }
        // Remove geo-location pivot fields from user data as they are handled separately
        unset($data['assigned_location_ids']);
        unset($data['location_distances']);

        // dd($request->all(), $data);
        $employee = User::create($data);
        if ($request->password) {
            $employee->update([
                'password' => Hash::make($request->password)
            ]);
        }

        if ($request->has('sms_warnings')) {
            $employee->rel_to_sms_notifications()->createMany(
                array_map(fn($type) => ['types' => $type], $request->input('sms_warnings'))
            );
        }

        if ($request->document) {
            foreach ($request->document as $index => $document) {
                if (($document['file'] ?? null) && ($document['type'] ?? null)) {
                    UserDocument::create([
                        'user_id' => $employee->id,
                        'path' => Storage::put('employees/documents', $document['file']),
                        'type' => $document['type'],
                    ]);
                }
            }
        }

        // Handle geo-location assignments if the feature is enabled
        if (config('services.have_attendance_location') && $request->assigned_location_ids) {
            $locationAssignments = [];
            foreach ($request->assigned_location_ids as $locationId) {
                $distance = $request->location_distances[$locationId] ?? 0.00;
                $locationAssignments[$locationId] = ['distance_km' => $distance];
            }
            $employee->assigned_locations()->sync($locationAssignments);
        }

        Toastr::success('Add new employee successfully :)', 'Success');
        return redirect()->route('employees.active.list');
    }

    // function view($employee, Request $request)
    // {
    //     return view('employees.edit', [
    //         'employee' => $employee,
    //         'in_view_mode' => $request->view == 1
    //     ]);
    // }

    function show($employee)
    {
        $employee = User::with(['rel_to_sms_notifications', 'assigned_locations'])->findOrFail($employee);
        abort_if(!(
            role_can('user')
            || role_can('user.self.super_department')
            || role_can('locations')
            || in_array($employee->department?->super_department_id, user()->geo_location_departments ?: [])
            || in_array($employee->department?->super_department_id, user()->admin_of_super_departments ?: [])
        ), 403);

        $selectedWarnings = $employee->rel_to_sms_notifications->pluck('types')->toArray();

        return view('employees.show', [
            'employee' => $employee,
            'selectedWarnings' => $selectedWarnings
        ]);
    }

    function print($employee)
    {
        $employee = User::employees()->findOrFail($employee);
        abort_if(!(
            role_can('user')
            || role_can('user.self.super_department')
            || role_can('locations')
            || in_array($employee->department?->super_department_id, user()->geo_location_departments ?: [])
            || in_array($employee->department?->super_department_id, user()->admin_of_super_departments ?: [])
        ), 403);

        // print_r($employee);
        return view('employees.print', [
            'employee' => $employee,
        ]);
    }
    public function edit($employee, Request $request)
    {
        $employee = User::findOrFail($employee);

        abort_if(!(
            role_can('user')
            || role_can('user.self.super_department')
            || role_can('locations')
            || in_array($employee->department?->super_department_id, user()->geo_location_departments ?: [])
            || in_array($employee->department?->super_department_id, user()->admin_of_super_departments ?: [])
        ), 403);

        $selectedWarnings = $employee->rel_to_sms_notifications->pluck('types')->toArray();

        return view('employees.edit', [
            'employee' => $employee,
            'in_view_mode' => $request->view == 1,
            'selectedWarnings' => $selectedWarnings,
            'sup_depts' => SuperDepartment::query()->ordered()
                ->when(!role_can('user'), function ($q) {
                    if (user()->admin_of_super_departments) {
                        $q->whereIn('id', user()->admin_of_super_departments);
                    } else
                    if (role_can('user.self.super_department') && user()->department?->super_department_id) {
                        $q->where('id', user()->department->super_department_id);
                    } else {
                        // $q->whereIn('id', []);
                    }
                })
                ->get(),
        ]);
    }
    // update record employee
    public function update($employee, Request $request)
    {
        $employee = User::findOrFail($employee);
        abort_if(!(
            role_can('user')
            || role_can('user.self.super_department')
            || role_can('locations')
            || in_array($employee->department?->super_department_id, user()->geo_location_departments ?: [])
            || in_array($employee->department?->super_department_id, user()->admin_of_super_departments ?: [])
        ), 403);

        if(
            role_can('user') || role_can('user.self.super_department') || is_admin_of_super_department($employee->department?->super_department_id)
        ) {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:15|min:9|unique:users,phone_number,' . $employee->id,
                'personal_phone' => 'nullable|string|max:15|min:9',
                'email' => 'nullable|string|email|max:255|unique:users,email,' . $employee->id,
                'personal_email' => 'nullable|string|email|max:255',
                'nid_or_passport' => 'nullable|string|max:255|unique:users,nid_or_passport,' . $employee->id,
                'birth_date' => [
                    'nullable',
                    'date',
                    'before_or_equal:' . date('Y-m-d', strtotime('-16 years')),
                ],
                'gender' => 'nullable|string|max:255',
                'department' => 'required|exists:departments,id',
                'designation' => 'required|exists:designations,id',
                'father_name' => 'nullable|string|max:255',
                'mother_name' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'permanent_address' => 'nullable|string|max:255',
                'permanent_city' => 'nullable|string|max:255',
                'blood_group' => 'nullable|string|max:255',
                'religion' => 'nullable|string|max:255',
                'nationality' => 'nullable|string|max:255',
                'marital_status' => 'nullable|string|max:255',
                'nid_or_passport' => 'nullable|string|max:255',
                'nid_or_passport_file' => 'nullable|image|mimes:png,jpg,jpeg|max:300',
                'employee_type' => 'required|string|max:255',
                'join_date' => 'required|date',
                'picture_file' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
                'signature_file' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
                'reference_name' => 'nullable|string|max:255',
                'reference_phone' => 'nullable|string|max:255',
                'emergency_name' => 'nullable|string|max:255',
                'emergency_relation' => 'nullable|string|max:255',
                'emergency_phone' => 'nullable|string|max:255',
                'emergency_address' => 'nullable|string|max:255',
                'employee_id' => 'nullable|string|max:255|unique:users,employee_id,' . $employee->id,
                'role' => 'nullable|exists:roles,id',
                'password' => 'nullable|string|min:1|max:100',
                'shift_id' => 'required|exists:shifts,id',
                'paytype' => 'nullable|string|in:hourly,daily,monthly',
                'bank_name' => 'nullable|string|max:255',
                'account_number' => 'nullable|string|max:255',
                'account_holder_name' => 'nullable|string',
                'branch_name' => 'nullable|string',
                'routing_number' => 'nullable|string|max:255',
                'tin_number' => 'nullable|string|max:255',
                'tax_number' => 'nullable|string|max:255',
                'geo_location_departments' => 'nullable|array|max:255',
                'admin_of_super_departments' => 'nullable|array|max:255',
            ]);
            $data['admin_of_super_departments'] = $data['admin_of_super_departments'] ?? null;
            $request->validate([
                'probation_end_date' => 'nullable|date|after:join_date',
                'join_date' => 'required|date',
                'document' => 'nullable|array',
                'document.*.file' => 'nullable|file|max:30000',
                'document.*.type' => 'nullable|string|max:255',
            ]);
            unset($data['password']);
            $data['department_id'] = $request->department;
            $data['designation_id'] = $request->designation;
            unset($data['department']);
            unset($data['designation']);
            $data['can_overtime'] = $request->can_overtime == 1;
            $data['can_outside_attendence'] = $request->can_outside_attendence == 1;
            $data['work_from_home_request'] = $request->work_from_home_request == 1;
            $data['join_date'] = carbon($data['join_date']);
            $data['role'] = 'employee';
            if (role_can('role')) {
                $data['role_id'] = $request->role;
            }
            $data['have_geo_location'] = $request->have_geo_location == 'yes';

            // dd($request->all());
            if ($request->hasFile('picture_file')) {
                $data['picture_file'] = Storage::put('employees/avatars', $request->file('picture_file'));
            }
            if ($request->hasFile('nid_or_passport_file')) {
                $data['nid_or_passport_file'] = Storage::put('employees/nid-or-passports', $request->file('nid_or_passport_file'));
            }

            if ($request->hasFile('signature_file')) {
                $data['signature_file'] = Storage::put('employees/signatures', $request->file('signature_file'));
            }

            if ($request->remove_signature_file) {
                Storage::delete($employee->signature_file);
                $employee->update([
                    'signature_file' => null
                ]);
            }
            if ($request->remove_nid_or_passport_file) {
                Storage::delete($employee->nid_or_passport_file);
                $employee->update([
                    'nid_or_passport_file' => null
                ]);
            }
            if ($request->remove_picture_file) {
                Storage::delete($employee->picture_file);
                $employee->update([
                    'picture_file' => null
                ]);
            }

            if ($request->zk_device_privilege && in_array($request->zk_device_privilege, ['admin', 'user', 'non_ac_user'])) {
                $data['zk_device_privilege'] = $request->zk_device_privilege;
            }
            $data['zk_device_password'] = $request->zk_device_password;
            $data['zk_device_card'] = $request->zk_device_card;
            $data['live_location_track'] = $request->live_location_track == 1;
            if ($request->probation_end_date) {
                $data['schedule_termination_at'] = carbon($request->probation_end_date);
            }
            if ($request->employee_type != 'Probationary') {
                $data['schedule_termination_at'] = null;
            }

            $data['from_management'] = $request->from_management == 1;
            ($employee->update($data));

            if ($request->document_delete) {
                foreach ($request->document_delete as $document_id) {
                    $doc = UserDocument::find($document_id);
                    if ($doc) {
                        Storage::delete($doc->path);
                        $doc->delete();
                    }
                }
            }

            $employee->rel_to_sms_notifications()->delete();
            if ($request->has('sms_warnings')) {
                $employee->rel_to_sms_notifications()->createMany(
                    array_map(fn($type) => ['types' => $type], $request->input('sms_warnings'))
                );
            }

            if ($request->document) {
                foreach ($request->document as $index => $document) {
                    if (($document['file'] ?? null) && ($document['type'] ?? null)) {
                        UserDocument::create([
                            'user_id' => $employee->id,
                            'path' => Storage::put('employees/documents', $document['file']),
                            'type' => $document['type'],
                        ]);
                    }
                }
            }

            if ($request->password) {
                $employee->update([
                    'password' => Hash::make($request->password)
                ]);
            }
        }
        $extras = $request->validate([
            // Geo-location fields
            'home_latitude' => 'nullable|numeric|between:-90,90',
            'home_longitude' => 'nullable|numeric|between:-180,180',
            'assigned_location_ids' => 'nullable|array',
            'assigned_location_ids.*' => 'exists:locations,id',
            'location_distances' => 'nullable|array',
            'location_distances.*' => 'nullable|numeric|min:0',
            'geo_location_type' => 'nullable|string|max:255',
            'have_geo_location' => 'nullable|string|max:255',
        ]);
        $data['home_latitude'] = $extras['home_latitude'] ?? null;
        $data['home_longitude'] = $extras['home_longitude'] ?? null;
        $data['geo_location_type'] = $extras['geo_location_type'] ?? null;
        $data['have_geo_location'] = ($extras['have_geo_location'] ?? null) == 'yes';
        $data['can_create_location'] = $request->can_create_location == 1;

        $employee->update($data);

        // Handle geo-location assignments if the feature is enabled
        if (config('services.have_attendance_location')) {
            if ($request->assigned_location_ids) {
                $locationAssignments = [];
                foreach ($request->assigned_location_ids as $locationId) {
                    $distance = $request->location_distances[$locationId] ?? 0.00;
                    $locationAssignments[$locationId] = ['distance_km' => $distance];
                }
                $employee->assigned_locations()->sync($locationAssignments);
            } else {
                // If no locations are assigned, clear all assignments
                $employee->assigned_locations()->detach();
            }
            if($request->locations_admin) {
                $employee->locations_admin()->sync($request->locations_admin);
            }
        }

        Toastr::success('Employee updated successfully :)', 'Success');
        return redirect()->route('employees.active.show', $employee);
    }
    // delete record
    public function delete(User $employee)
    {
        abort_if(!(
            role_can('user')
            || role_can('user.self.super_department')
            || in_array($employee->department?->super_department_id, user()->admin_of_super_departments ?: [])
        ), 403);
        $employee->forceDelete();
        Toastr::success('Delete record successfully :)', 'Success');
        return redirect()->route('employees.terminate.list');
    }
    // employee search
    public function search(Request $request)
    {
        // abort_if(!(role_can('user') || role_can('user.self.super_department')), 403);
        $term = $request->search ?? '';
        $users = $this->employeeListCondition(User::active_employees())
            ->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', '%' . $term . '%')
                    ->orWhere('phone_number', 'LIKE', '%' . $term . '%')
                    ->orWhere('email', 'LIKE', '%' . $term . '%')
                    ->orWhere('id', $term)
                    ->orWhere('employee_id', $term);
            });
        if ($request->super_department) {
            $users->whereHas('department', function ($query) use ($request) {
                return $query->where('super_department_id', $request->super_department);
            })
                ->orWhereIn('id', user()->managed_employees->pluck('id'));
        } elseif ($request->department) {
            $users->whereHas('department_id', $request->department);
        }

        $users = $users->limit(100)
            ->with(['designation', 'department.super_department', 'shift'])
            ->get();
        return $users;
    }
    /** page designations */
    public function designationsIndex()
    {
        abort_if(!role_can('designation'), 403, "Please get permission from Administrator");
        return view('designations', [
            'designations' => Designation::orderBy('order')->paginate(1000)
        ]);
    }
    function saveRecordDesignations(Request $request)
    {
        abort_if(!role_can('designation'), 403, "Please get permission from Administrator");
        $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);
        $newDesignation = Designation::create([
            'name' => $request->name,
            'order' => $request->order ?? 0,
        ]);
        Toastr::success('Add new designation successfully :)', 'Success');
        return redirect()->back();
    }

    function saveModalDesignations(Request $request)
    {
        abort_if(!role_can('designation'), 403, "Please get permission from Administrator");
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $newDesignation = Designation::create([
            'name' => $request->name,
        ]);
        // Toastr::success('Add new designation successfully :)', 'Success');
        // return redirect()->back();

        return response()->json([
            'message' => 'Designation added successfully',
            'designation' => $newDesignation,
        ]);
    }
    function updateRecordDesignations(Request $request)
    {
        abort_if(!role_can('designation'), 403, "Please get permission from Administrator");
        $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);
        // dd($request->d_id);
        (Designation::findOrFail($request->d_id))->update([
            'name' => $request->name,
            'order' => $request->order ?? 0,
        ]);
        Toastr::success('updated record successfully :)', 'Success');
        return redirect()->back();
    }
    function deleteRecordDesignations(Request $request)
    {
        abort_if(!role_can('designation'), 403, "Please get permission from Administrator");
        Designation::destroy($request->id);
        Toastr::success('Delete record successfully :)', 'Success');
        return redirect()->back();
    }
    function updateDesignationOrder(Request $request)
    {
        abort_if(!role_can('designation'), 403, "Please get permission from Administrator");
        $request->validate([
            'id' => 'required|exists:designations,id',
            'order' => 'required|integer|min:0',
        ]);
        Designation::findOrFail($request->id)->update([
            'order' => $request->order
        ]);
        Toastr::success('Designation order updated successfully :)', 'Success');
        return redirect()->back();
    }
    function assign_shift($employee, Request $request)
    {
        $request->validate([
            'shift' => 'required|exists:shifts,id',
        ]);
        User::findOrFail($employee)->update([
            'shift_id' => $request->shift
        ]);
        Toastr::success('Assigned successfully :)', 'Success');
        return redirect()->back();
    }
    function terminate(Request $request)
    {
        $u = User::findOrFail($request->employee_id);
        abort_if(!(
            role_can('user')
            || role_can('user.self.super_department')
            || in_array($u->department?->super_department_id, user()->admin_of_super_departments ?: [])
        ), 403);

        $request->validate([
            'schedule_termination' => 'nullable|date:Y-m-d\TH:i', // format "2024-10-30T09:14" and future time
            'schedule_re_activation' => 'nullable|date:Y-m-d\TH:i',
        ]);
        if ($request->schedule_termination || $request->schedule_re_activation) {
            $u->update([
                'schedule_termination_at' => $request->schedule_termination,
                'schedule_re_activation_at' => $request->schedule_re_activation,
            ]);
            if ($request->schedule_termination && carbon($request->schedule_termination)->isPast()) {
                $u->update([
                    'is_active' => false,
                ]);
                Toastr::success('Terminate successfully :)', 'Success');
            }
        } else {
            if ($u->is_active) {
                $u->update([
                    'is_active' => false
                ]);
                Toastr::success('Terminate successfully :)', 'Success');
            } else {
                $u->update([
                    'is_active' => true
                ]);
                Toastr::success('Employee activated :)', 'Success');
            }
        }
        return redirect()->back();
    }

    function profile($id)
    {
        abort_if(
            user()->id != $id && !role_can('user')
            && !role_can('user.self.super_department')
            && !is_admin_of_super_department(User::find($id)?->department?->super_department_id)
            , 403);
        $employee = User::employees()->findOrFail($id);
        return view('employees.profile', [
            'employee' => $employee,
        ]);
    }

    function edit_request(Request $request)
    {
        $employee = $request->user_id;
        $data = $request->validate([
            'user_id' => 'required',
            'name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:15|min:9|unique:users,phone_number,' . $employee,
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $employee,
            'nid_or_passport' => 'nullable|string|max:255|unique:users,nid_or_passport,' . $employee,
            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:' . date('Y-m-d', strtotime('-16 years')),
            ],
            'gender' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'blood_group' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|max:255',
            'reference_name' => 'nullable|string|max:255',
            'reference_phone' => 'nullable|string|max:255',
            'emergency_name' => 'nullable|string|max:255',
            'emergency_relation' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:255',
            'emergency_address' => 'nullable|string|max:255',

        ]);




        $value = collect($data)->filter(function ($item) {
            return !is_null($item);
        })->forget('user_id')->toJson();


        $userEdit = UserEdit::where('user_id', $data['user_id'])->first();

        // Check if the user edit exists
        if ($userEdit) {
            UserEdit::where('user_id', $data['user_id'])->update([
                'new_values' => $value,
            ]);

            Toastr::success('Employee updated request Updated :)', 'Success');
        } else {
            UserEdit::create([
                'user_id' => $data['user_id'],
                'new_values' => $value,
            ]);

            Toastr::success('Employee updated requested send successfully :)', 'Success');
        }

        return redirect()->back();
    }



    public function show_edit_request()
    {
        $edits = UserEdit::where('approve', 0)->get();
        // dd()
        return view('employees.edit_request', compact('edits'));
    }

    public function show_edit_approve_request()
    {
        $edits = UserEdit::where('approve', 1)->get();
        // dd($edits);
        return view('employees.approve_edit_request', compact('edits'));
    }

    public function view_edit_request($id)
    {
        $edit = UserEdit::find($id);
        abort_if($edit->approve == 1, 403);
        $arrayData = json_decode($edit->new_values, true);
        // $arrayFieldString = collect($arrayData['new_values'])->toJson();
        // $arrayField = json_decode($arrayFieldString, true);
        // dd($arrayData);
        $employee = User::find($edit->user_id);
        // dd($edit->nameField);
        return view('employees.edit_request_item', compact('arrayData', 'employee', 'id'));
    }

    public function view_approve_edit_request($id)
    {
        abort_if(!role_can('editRequest-finall-approve'), 403);
        $edit = UserEdit::find($id);

        $arrayData = json_decode($edit->new_values, true);
        // $arrayFieldString = collect($arrayData['new_values'])->toJson();
        // $arrayField = json_decode($arrayFieldString, true);
        // dd($arrayData);
        $employee = User::find($edit->user_id);
        // dd($edit->nameField);
        return view('employees.approve_edit_request_item', compact('arrayData', 'employee', 'id'));
    }

    public function primary_approve($id, Request $request)
    {
        $value = $request->data;
        // $request->dd();
        // dd();
        $edit_id = $request->edit_id;

        UserEdit::where('id', $edit_id)->update([
            'new_values' => $value,
            'approve' => 1,
            'primary_approved_by' => user()->id,
        ]);

        Toastr::success('Employee updated requested send successfully :)', 'Success');
        return redirect(route('employees.request.edit.show'));
    }

    public function final_approve($id, Request $request)
    {
        abort_if(!role_can('editRequest-finall-approve'), 403);
        // $employee = User::find($id);
        $value = json_decode($request->data_value, true);
        $update = [];
        foreach ($value as $key => $item) {
            $update[$key]   = $item;
        }

        $edit_id = $request->edit_id;
        $user_eidts = UserEdit::where('id', $edit_id)->first();
        $employee = User::find($user_eidts->user_id);
        // dd( $employee);
        $employee->update($update);
        UserEdit::where('id', $edit_id)->delete();
        // dd('ok');

        Toastr::success('Employee updated requested send successfully :)', 'Success');
        return redirect()->back();
    }

    public function destroy_edit_request($id)
    {
        $user_eidts = UserEdit::where('id', $id)->first();
        $user_eidts->delete();
        Toastr::success('Sucessfully Deleted', 'success');
        return redirect('/');
    }
    public function delete_fingerprint($fingerprint)
    {
        abort_if(!role_can('user') && !role_can('user.self.super_department') && !is_admin_of_super_department(), 403);
        UserZkDeviceFingerprint::findOrFail($fingerprint)->delete();
        Toastr::success('Fingerprint deleted successfully :)', 'Success');
        return redirect()->back();
    }

    public function probationList(Request $request)
    {
        $employees = $this->employeeListCondition(
            User::active_employees()->where('employee_type', '=', 'Probationary')
        );

        if ($request->department) {
            $employees->where('department_id', $request->department);
        } else
        if ($request->super_department) {
            $employees->whereHas('department', function ($q) use ($request) {
                $q->where('super_department_id', $request->super_department);
            });
        }

        if ($request->designation) {
            $employees->where('designation_id', $request->designation);
        }

        if ($request->employee) {
            $employees->where(function ($q) use ($request) {
                $q->where('users.id', $request->employee);
            });
        }

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return EmployeeList::download_excel($employees->get(), 'Employees.xlsx');
            }
            if ($as == 'pdf') {
                return EmployeeList::download_pdf($employees->get(), 'Employees.pdf');
            }
            if ($as == 'docx') {
                return EmployeeList::download_docx($employees->get(), 'Employees.docx');
            }
            if ($as == 'print') {
                return EmployeeList::print($employees->get());
            }
        }
        // dd($employees);
        return view('employees.probation_list', [
            'employees' => $employees->orderBy('id')->get()
        ]);
    }

    public function employeeByID($id)
    {
        $employee = User::with('department.super_department')
            ->where('is_active', true)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'present_address' => $employee->address,
            'contact_number' => $employee->phone_number,
            'station' => $employee->department?->super_department?->name,
        ]);
    }
}
