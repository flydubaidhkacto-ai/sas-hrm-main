<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\EmployeeList;
use App\Models\Department;
use App\Models\Designation;
use App\Models\InterviewComment;
use App\Models\InterviewDocument;
use App\Models\Shift;
use App\Models\User;
use App\Models\UserDocument;
use App\Models\UserEdit;
use App\Models\InterviewEmployee;
use App\Models\UserZkDeviceFingerprint;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Exports\InterviewEmployeeList;

class InterviewController extends Controller
{
    public function interviewEmployees(Request $request)
    {
        abort_if(!(role_can('user') || role_can('user.self.super_department') || role_can('promotion-apply') || role_can('interview_comment') || role_can('interview_comment_see')), 403);
        // $employees = User::active_employees()->where('employee_type', '=', 'Probationary')->where('schedule_termination_at', '>', now());
        $employees = InterviewEmployee::orderBy('id');

        if (
            role_can('user')
            || role_can('user.self.super_department')
        ) {
            if (
                !role_can('user')
                && role_can('user.self.super_department')
            ) {
                $employees->whereHas('department', function ($q) {
                    $q->where('super_department_id', user()->department?->super_department_id);
                });
            }
        }
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
                $q->where('id', $request->employee);
            });
        }

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return InterviewEmployeeList::download_excel($employees->get(), 'Inetrview Employees.xlsx');
            }
            if ($as == 'pdf') {
                return InterviewEmployeeList::download_pdf($employees->get(), 'Interview Employees.pdf');
            }
            // if ($as == 'docx') {
            //     return InterviewEmployeeList::download_docx($employees->get(), 'Employees.docx');
            // }
            if ($as == 'print') {
                return InterviewEmployeeList::print($employees->get());
            }
        }

        // dd($employees);
        return view('employees_interview.list', [
            'employees' => $employees->get()
        ]);
    }

    public function create()
    {
        abort_if(!(role_can('user') || role_can('user.self.super_department')), 403);
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
        return view('employees_interview.create');
    }
    public function store(Request $request)
    {
        abort_if(!(role_can('user') || role_can('user.self.super_department') || role_can('promotion-apply')), 403);
        $data = $request->validate([
            'name' => 'required|max:255',
            'phone_number' => 'required|string|max:15|unique:users|min:9',
            'email' => 'required|email|unique:interview_employees',
            'address' => 'required',
            'expected_salary' => 'required|numeric',
            'rating' => 'nullable|between:0.00,10.00',
            'notes' => 'nullable'
        ]);

        $request->validate([
            'department' => 'required|exists:departments,id',
            'designation' => 'required|exists:designations,id',
            'interview_date' => 'required|date',
            'document' => 'nullable|array',
            'document.*.file' => 'nullable|file|max:30000',
            'document.*.type' => 'nullable|string|max:255',
        ]);

        $interview_date = Carbon::createFromFormat('d-m-Y', $request->input('interview_date'))->format('Y-m-d');
        $data['department_id'] = $request->department;
        $data['designation_id'] = $request->designation;
        $data['interview_date'] = $interview_date;

        // dd($data);
        $interview_employee = InterviewEmployee::create($data);
        // dd($request->document);
        if ($request->has('document')) {
            foreach ($request->document as $index => $document) {
                if (!empty($document['file']) && !empty($document['type'])) {
                    $filePath = $document['file']->store('employees/documents');

                    InterviewDocument::create([
                        'interview_id' => $interview_employee->id,
                        'path' => $filePath,
                        'type' => $document['type'],
                    ]);
                }
            }
        }

        Toastr::success('Interview Employee created successfully', 'Success');
        return redirect()->route('employees.interview.index');
    }

    public function show($id)
    {
        abort_if(!(role_can('user') || role_can('user.self.super_department') || role_can('interview_comment') || role_can('interview_comment_see')), 403);

        $interview_employee = InterviewEmployee::findOrFail($id);

        $my_comment = InterviewComment::where('interview_id', $interview_employee->id)
            ->where('employee_id', user()->id)
            ->first();

        $comments = collect();
        if (role_can('interview_comment_see') || role_can('interview_comment')) {
            $comments = InterviewComment::where('interview_id', $interview_employee->id)->get();
        }
        if ($my_comment && $comments->isEmpty()) $comments->push($my_comment);

        return view('employees_interview.show', compact('interview_employee', 'my_comment', 'comments'));
    }


    public function edit($id)
    {
        abort_if(!(role_can('user') || role_can('user.self.super_department')), 403);
        $employee = InterviewEmployee::findOrFail($id);
        return view('employees_interview.edit', [
            'employee' => $employee,
        ]);
    }

    public function update(Request $request)
    {
        abort_if(!(role_can('user') || role_can('user.self.super_department')), 403);
        $data = $request->validate([
            'name' => 'required|max:255',
            'phone_number' => 'required|string|max:15|unique:users,phone_number,' . $request->id,
            'email' => 'required|email|unique:users,email,' . $request->id,
            'address' => 'required',
            'expected_salary' => 'required|numeric',
            'rating' => 'nullable|between:0.00,10.00',
            'notes' => 'nullable'
        ]);
        $request->validate([
            'department' => 'required|exists:departments,id',
            'designation' => 'required|exists:designations,id',
            'interview_date' => 'required|date',
            'document' => 'nullable|array',
            'document.*.file' => 'nullable|file|max:30000',
            'document.*.type' => 'nullable|string|max:255',
        ]);
        $interview_date = Carbon::createFromFormat('d-m-Y', $request->input('interview_date'))->format('Y-m-d');
        $data['department_id'] = $request->department;
        $data['designation_id'] = $request->designation;
        $data['interview_date'] = $interview_date;
        $employee = InterviewEmployee::findOrFail($request->id);

        ($employee->update($data));
        if ($request->document_delete) {
            foreach ($request->document_delete as $document_id) {
                $doc = InterviewDocument::find($document_id);
                if ($doc) {
                    Storage::delete($doc->path);
                    $doc->delete();
                }
            }
        }

        if ($request->document) {
            foreach ($request->document as $index => $document) {
                if (!empty($document['file']) && !empty($document['type'])) {
                    $filePath = $document['file']->store('employees/documents');

                    InterviewDocument::create([
                        'interview_id' => $employee->id,
                        'path' => $filePath,
                        'type' => $document['type'],
                    ]);
                }
            }
        }
        Toastr::success('Interview Employee updated successfully', 'Success');
        return redirect()->route('employees.interview.index');
    }

    public function delete($id)
    {
        $employee = InterviewEmployee::findOrFail($id);
        if ($employee) {
            $employee->delete();
            Toastr::success('Interview Employee deleted successfully', 'Success');
            return redirect()->route('employees.interview.index');
        }
    }

    public function activeEmployee(Request $request)
    {
        abort_if(!(role_can('user') || role_can('user.self.super_department')), 403);
        $request->validate([
            'employee_id' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($value && is_numeric($value)) {
                        $existsAsId = User::where('id', $value)->exists();
                        $existsAsEmployeeId = User::where('employee_id', $value)->exists();
                        if ($existsAsId || $existsAsEmployeeId) {
                            $fail("The Employee ID '{$value}' is already in use. Please choose a different one.");
                        }
                    }
                },
            ],
            'join_date' => 'required|date',
        ]);
        $interview_employee = InterviewEmployee::findOrFail($request->interview_employee_id);


        $join_date = Carbon::createFromFormat('d-m-Y', $request->input('join_date'))->format('Y-m-d');

        $is_phone_check = User::where('phone_number', $interview_employee->phone_number)->exists();
        $is_email_check = User::where('email', $interview_employee->email)->exists();

        if ($is_phone_check) {
            Toastr::error('Employee already exists with this phone number', 'Error');
            return redirect()->route('employees.interview.index');
        }
        if ($is_email_check) {
            Toastr::error('Employee already exists with this email', 'Error');
            return redirect()->route('employees.interview.index');
        }

        $data['name'] = $interview_employee->name;
        $data['phone_number'] = $interview_employee->phone_number;
        $data['email'] = $interview_employee->email;
        $data['address'] = $interview_employee->address;
        $data['department_id'] = $interview_employee->department_id;
        $data['designation_id'] = $interview_employee->designation_id;
        $data['join_date'] = $join_date;
        $data['employee_id'] = $request->employee_id;
        $data['status'] = 1;
        $data['employee_type'] = 'Permanent';
        $data['role'] = 'employee';
        $data['role_id'] = 4;

        if (
            !role_can('user')
            && user()->department?->super_department_id != Department::findOrFail($data['department_id'])->super_department_id
        ) {
            abort(403, "Please get permission from Administrator");
        }
        // dd($data);

        $employee = User::create($data);

        $interview_ids = InterviewDocument::where('interview_id', $request->interview_employee_id)
            ->pluck('id');
        if ($interview_ids) {
            foreach ($interview_ids as $index => $interview_id) {
                $document_info = InterviewDocument::find($interview_id);

                userDocument::create([
                    'user_id' => $employee->id,
                    'path' => $document_info->path,
                    'type' => $document_info->type,
                ]);
            }
        }

        // dd($interviewIds);
        InterviewDocument::where('interview_id', $request->interview_employee_id)->delete();

        $interview_employee->delete();

        Toastr::success('Interview employee move to active employee successfully', 'Success');
        return redirect()->route('employees.interview.index');
    }

    public function probationEmployee(Request $request)
    {
        abort_if(!(role_can('user') || role_can('user.self.super_department')), 403);
        $request->validate([
            'employee_id' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($value && is_numeric($value)) {
                        $existsAsId = User::where('id', $value)->exists();
                        $existsAsEmployeeId = User::where('employee_id', $value)->exists();
                        if ($existsAsId || $existsAsEmployeeId) {
                            $fail("The Employee ID '{$value}' is already in use. Please choose a different one.");
                        }
                    }
                },
            ],
            'join_date' => 'required|date',
        ]);
        $interview_employee = InterviewEmployee::findOrFail($request->interview_employee_id);


        $join_date = Carbon::createFromFormat('d-m-Y', $request->input('join_date'))->format('Y-m-d');
        $probation_end_date = Carbon::createFromFormat('d-m-Y', $request->input('probation_end_date'))->format('Y-m-d');

        $is_phone_check = User::where('phone_number', $interview_employee->phone_number)->exists();
        $is_email_check = User::where('email', $interview_employee->email)->exists();

        if ($is_phone_check) {
            Toastr::error('Employee already exists with this phone number', 'Error');
            return redirect()->route('employees.interview.index');
        }
        if ($is_email_check) {
            Toastr::error('Employee already exists with this email', 'Error');
            return redirect()->route('employees.interview.index');
        }

        $data['name'] = $interview_employee->name;
        $data['phone_number'] = $interview_employee->phone_number;
        $data['email'] = $interview_employee->email;
        $data['address'] = $interview_employee->address;
        $data['department_id'] = $interview_employee->department_id;
        $data['designation_id'] = $interview_employee->designation_id;
        $data['join_date'] = $join_date;
        $data['employee_id'] = $request->employee_id;
        $data['schedule_termination_at'] = $probation_end_date;
        $data['status'] = 1;
        $data['employee_type'] = 'Probationary';
        $data['role'] = 'employee';
        $data['role_id'] = 4;

        if (
            !role_can('user')
            && user()->department?->super_department_id != Department::findOrFail($data['department_id'])->super_department_id
        ) {
            abort(403, "Please get permission from Administrator");
        }
        // dd($data);

        $employee = User::create($data);

        $interview_ids = InterviewDocument::where('interview_id', $request->interview_employee_id)
            ->pluck('id');
        if ($interview_ids) {
            foreach ($interview_ids as $index => $interview_id) {
                $document_info = InterviewDocument::find($interview_id);

                userDocument::create([
                    'user_id' => $employee->id,
                    'path' => $document_info->path,
                    'type' => $document_info->type,
                ]);
            }
        }

        // dd($interviewIds);
        InterviewDocument::where('interview_id', $request->interview_employee_id)->delete();

        $interview_employee->delete();

        Toastr::success('Interview employee move to probation employee successfully', 'Success');
        return redirect()->route('employees.interview.index');
    }

    public function CommentStore(Request $request)
    {
        abort_if((!role_can('interview_comment')), 403);
        $request->validate([
            'rating' => 'nullable',
            'comment' => 'nullable|string',
        ]);

        $data['comment'] = $request->comment;
        $data['rating'] = $request->rating;
        $data['interview_id'] = $request->interview_id;
        $data['employee_id'] = user()->id;

        // dd($data);

        InterviewComment::create($data);
        Toastr::success('Comment added successfully', 'Success');
        return redirect()->route('employees.interview.index');
    }
    public function CommentDestroy($id, Request $request)
    {
        abort_if((!role_can('user') || !role_can('user.self.super_department')), 403);

        InterviewComment::destroy($id);
        Toastr::success('Comment deleted successfully', 'Success');
        return redirect()->back()->with('success', 'Comment deleted successfully');
    }
}
