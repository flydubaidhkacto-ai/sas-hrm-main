@extends('layouts.master')
@section('title', 'Edit Employee')

@section('style')
@endsection

@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Employee</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Employee</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        {!! Toastr::message() !!}
        <div>
            @include('layouts.alerts')
            <form action="{{ route('employees.active.update', ['employee' => $employee->id]) }}" method="POST"
                enctype="multipart/form-data" onkeyup="$('.submit-btn').text('Update')">
                @csrf
                @method('PUT')

                @if((role_can('user') || role_can('user.self.super_department') || is_admin_of_super_department($employee->department?->super_department_id)))
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <button class="nav-link active" id="nav-basic-tab" data-toggle="tab" data-target="#nav-basic"
                            type="button" role="tab" aria-controls="nav-basic" aria-selected="true">Basic
                            Information</button>
                        <button class="nav-link" id="nav-document-tab" data-toggle="tab" data-target="#nav-document"
                            type="button" role="tab" aria-controls="nav-document" aria-selected="false">Bio
                            Data</button>
                        <button class="nav-link" id="nav-emergency-tab" data-toggle="tab" data-target="#nav-emergency"
                            type="button" role="tab" aria-controls="nav-emergency" aria-selected="false">Emergency
                            Contact</button>
                        <button class="nav-link" id="nav-reference-tab" data-toggle="tab" data-target="#nav-reference"
                            type="button" role="tab" aria-controls="nav-reference"
                            aria-selected="false">Reference</button>
                        <button class="nav-link" id="nav-documents-tab" data-toggle="tab" data-target="#nav-documents"
                            type="button" role="tab" aria-controls="nav-documents"
                            aria-selected="false">Documents</button>

                        <button class="nav-link" id="nav-bank-details-tab" data-toggle="tab"
                            data-target="#nav-bank-details" type="button" role="tab" aria-controls="nav-bank-details"
                            aria-selected="false">Bank
                            Details</button>

                        <button class="nav-link" id="nav-role-tab" data-toggle="tab" data-target="#nav-role"
                            type="button" role="tab" aria-controls="nav-role" aria-selected="false">Auth and
                            Roles</button>

                        @if(config('services.have_attendance_location'))
                        <button class="nav-link" id="nav-geo-locations-tab" data-toggle="tab"
                            data-target="#nav-geo-locations" type="button" role="tab" aria-controls="nav-geo-locations"
                            aria-selected="false">Geo Locations</button>
                        @endif
                    </div>
                </nav>
                @endif
                <div class="tab-content" id="nav-tabContent">
                    @if ((role_can('user') || role_can('user.self.super_department') || is_admin_of_super_department($employee->department?->super_department_id)))
                    <div class="tab-pane fade show active" id="nav-basic" role="tabpanel"
                        aria-labelledby="nav-basic-tab">
                        <h3>Basic Information</h3>
                        <div class="row">
                            <div class="col-md-6">

                                <div class="form-group">
                                    <label class="col-form-label">Employee Code:</label>
                                    <input type="text" readonly class="form-control" value="{{ $employee->id }}" />
                                </div>

                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Employee ID:</label>
                                    <input type="text" name="employee_id" class="form-control"
                                        value="{{ $employee->employee_id }}">
                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label class="col-form-label">Employee Name <sup
                                            class="text-danger">*</sup>:</label>
                                    <input type="text" name="name" id="name" class="form-control" required
                                        value="{{ $employee->name }}">
                                    @if ($errors->has('name'))
                                    <span class="text-danger">{{ $errors->first('name') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Department <sup class="text-danger">*</sup>:</label>
                                    <br>
                                    <select class="form-control select2" id="department" name="department" required>
                                        <option value="">-- Select --</option>
                                        @foreach (\App\Models\Department::whereIn('super_department_id', $sup_depts->pluck('id'))->ordered()->get() as $item)
                                        <option value="{{ $item->id }}" {{ $item->id == $employee->department_id ?
                                            'selected' : '' }}>
                                            {{ $item->super_department->name }}:
                                            {{ $item->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('department'))
                                    <span class="text-danger">{{ $errors->first('department') }}</span>
                                    @endif

                                    <a type="button" class="" data-toggle="modal" data-target="#addDepartmentModal">
                                        Add Department
                                    </a>
                                </div>

                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Designation <sup class="text-danger">*</sup>:</label>
                                    <br>
                                    <select class="form-control select2" id="designation" name="designation" required>
                                        <option value="">-- Select --</option>
                                        @foreach (\App\Models\Designation::ordered()->get() as $item)
                                        <option value="{{ $item->id }}" @if ($employee->designation_id == $item->id)
                                            selected @endif>
                                            {{ $item->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('designation'))
                                    <span class="text-danger">{{ $errors->first('designation') }}</span>
                                    @endif

                                    <a type="button" class="" data-toggle="modal" data-target="#addDesignationModal">
                                        Add Designation
                                    </a>
                                </div>

                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Picture:</label>
                                    <input type="file" class="form-control" name="picture_file" accept="image/*"
                                        image_upload_preview=".picture">
                                    <img src="{{ $employee->picture_file ? '/uploaded_files/' . $employee->picture_file : '' }}"
                                        alt="" class="picture mt-2" style="width: 200px;">
                                    @if ($employee->picture_file)
                                    <div class="mt-2 mb-2">
                                        <button class="btn btn-link btn-sm text-danger" type="button" onclick="
                            this.parentElement.previousElementSibling.src = '';
                            $(this).parent().html('<input type=\'hidden\' name=\'remove_picture_file\' value=\'1\'>')">
                                            <i class="fa fa-trash"></i> Remove existing
                                            picture
                                        </button>
                                    </div>
                                    @endif
                                    @if ($errors->has('picture_file'))
                                    <span class="text-danger">{{ $errors->first('picture_file') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">

                                <div class="form-group">
                                    <label for="">Signature:</label>
                                    <input type="file" class="form-control" name="signature_file"
                                        image_upload_preview=".signature" accept="image/*">
                                    <img src="{{ $employee->signature_file ? '/uploaded_files/' . $employee->signature_file : '' }}"
                                        alt="" class="signature mt-2" style="width: 100px;">
                                    @if ($employee->signature_file)
                                    <div class="mt-2 mb-2">
                                        <button class="btn btn-link btn-sm text-danger" type="button"
                                            onclick="
                            this.parentElement.previousElementSibling.src = '';
                            $(this).parent().html('<input type=\'hidden\' name=\'remove_signature_file\' value=\'1\'>')">
                                            <i class="fa fa-trash"></i> Remove existing
                                            signature
                                        </button>
                                    </div>
                                    @endif
                                    @if ($errors->has('signature_file'))
                                    <span class="text-danger">{{ $errors->first('signature_file') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">

                                <div class="form-group">
                                    <label class="col-form-label">
                                        Phone <sup class="text-danger">*</sup>:
                                    </label>
                                    <input class="form-control" type="text" id="phone_number" name="phone_number"
                                        required value="{{ $employee->phone_number }}">
                                    @if ($errors->has('phone_number'))
                                    <span class="text-danger">{{ $errors->first('phone_number') }}</span>
                                    @endif
                                </div>
                            </div>
                            @if(config('services.hide_personal_phone_mail') === false)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">
                                        Personal Phone:
                                    </label>
                                    <input class="form-control" type="text" id="personal_phone" name="personal_phone"
                                        value="{{ $employee->personal_phone }}">
                                    @if ($errors->has('personal_phone'))
                                    <span class="text-danger">{{ $errors->first('personal_phone') }}</span>
                                    @endif
                                </div>
                            </div>
                                @endif
                            <div class="col-md-6">

                                <div class="form-group">
                                    <label class="col-form-label">
                                        Email:
                                    </label>
                                    <input class="form-control" type="email" id="email" name="email"
                                        value="{{ $employee->email }}">
                                    @if ($errors->has('email'))
                                    <span class="text-danger">{{ $errors->first('email') }}</span>
                                    @endif
                                </div>
                            </div>
                            @if(config('services.hide_personal_phone_mail') === false)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">
                                        Personal Email:
                                    </label>
                                    <input class="form-control" type="email" id="personal_email" name="personal_email"
                                        value="{{ $employee->personal_email }}">
                                    @if ($errors->has('personal_email'))
                                    <span class="text-danger">{{ $errors->first('personal_email') }}</span>
                                    @endif
                                </div>
                            </div>
                                @endif
                            <div class="col-md-6">

                                <div class="form-group">
                                    <label for="shift_id">Shift <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="shift_id" name="shift_id" required>
                                        <option value="">-- Select --</option>
                                        @foreach (\App\Models\Shift::where('is_for_student', false)->get() as $item)
                                        <option value="{{ $item->id }}" @if (old('shift_id', $employee->shift_id) == $item->id) selected @endif>
                                            {{ $item->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Employee type <sup class="text-danger">*</sup></label>
                                    <select class="form-control" required name="employee_type" id="employee_type"
                                        onchange="if(this.value == 'Probationary'){ $('.probation-date').removeClass('d-none'); $('.probation-date input').attr('required', true); } else { $('.probation-date').addClass('d-none'); $('.probation-date input').attr('required', false); }">
                                        <option value="Permanent" @if ($employee->employee_type === 'Permanent')
                                            selected @endif>
                                            Permanent</option>
                                        <option value="Contractual" @if ($employee->employee_type === 'Contractual')
                                            selected @endif>
                                            Contractual</option>
                                        <option value="Probationary" @if ($employee->employee_type === 'Probationary')
                                            selected @endif>
                                            Probationary</option>
                                    </select>
                                    @if ($errors->has('employee_type'))
                                    <span class="text-danger">{{ $errors->first('employee_type') }}</span>
                                    @endif
                                </div>
                                <div
                                    class="probation-date @if ($employee->employee_type != 'Probationary') d-none @endif">
                                    <div class="form-group">
                                        <label for="">Probation Period End Date-Time
                                            <sup class="text-danger">*</sup></label>
                                        <input type="datetime-local" class="form-control" name="probation_end_date"
                                            value="{{ $employee->schedule_termination_at ? carbon($employee->schedule_termination_at)->format('Y-m-d\TH:i') : '' }}">
                                        @if ($errors->has('probation_end_date'))
                                        <span class="text-danger">{{ $errors->first('probation_end_date') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Overtime</label>
                                    @php
                                    $can = ($employee->can_overtime ? '1' : '0') == 1;
                                    @endphp
                                    <select id="" class="form-control" required name="can_overtime" id="can_overtime">
                                        <option value="1" @if ($can) selected @endif>
                                            Yes
                                        </option>
                                        <option value="0" @if (!$can) selected @endif>
                                            No
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Paytype</label>
                                    <select id="" class="form-control" required name="paytype" id="paytype">
                                        <option value="hourly" @if ($employee->paytype == 'hourly') selected @endif>
                                            Hourly</option>
                                        <option value="daily" @if ($employee->paytype == 'daily') selected @endif>
                                            Daily
                                        </option>
                                        <option value="monthly" @if ($employee->paytype == 'monthly') selected @endif>
                                            Monthly</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                {{-- <div class="form-group">
                                    <label for="">
                                        Get Late Check-In warning via SMS:
                                    </label>
                                    <select class="form-control" required name="get_attendance_sms_notification"
                                        id="get_attendance_sms_notification">
                                        <option value="1" @if ($employee->get_attendance_sms_notification == 1) selected
                                            @endif>Yes
                                        </option>
                                        <option value="0" @if ($employee->get_attendance_sms_notification == 0) selected
                                            @endif>No
                                        </option>
                                    </select>
                                </div> --}}
                                <div class="form-group">
                                    <label for="">Joining Date <sup class="text-danger">*</sup></label>
                                    <input type="text" class="form-control datetimepicker" required name="join_date"
                                        id="join_date"
                                        value="{{ $employee->join_date ? carbon($employee->join_date)->format('d-m-Y') : '' }}">
                                    @if ($errors->has('join_date'))
                                    <span class="text-danger">{{ $errors->first('join_date') }}</span>
                                    @endif
                                </div>

                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sms_warnings">Get SMS For :</label>
                                    <select class="form-control select2" name="sms_warnings[]" id="sms_warnings"
                                        multiple>
                                        <option disabled>Select Get SMS For </option>

                                        <option value="in" {{ in_array('in', $selectedWarnings) ? 'selected' : '' }}>
                                            In
                                            Message</option>
                                        <option value="out" {{ in_array('out', $selectedWarnings) ? 'selected' : '' }}>
                                            Out
                                            Message</option>
                                        <option value="duty_summary" {{ in_array('duty_summary', $selectedWarnings)
                                            ? 'selected' : '' }}>
                                            Summery Message</option>
                                        <option value="late" {{ in_array('late', $selectedWarnings) ? 'selected' : ''
                                            }}>
                                            Late</option>
                                        <option value="first_in" {{ in_array('first_in', $selectedWarnings) ? 'selected'
                                            : '' }}>
                                            First In
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="admin_of_super_departments">General Admin of Sup. Depts.</label>
                                    <select class="form-control select2" name="admin_of_super_departments[]" id="admin_of_super_departments"
                                        multiple>
                                        <option disabled>Select super departments</option>
                                        @foreach ($sup_depts as $item)
                                            <option value="{{ $item->id }}"
                                                @selected($employee->admin_of_super_departments && in_array($item->id, $employee->admin_of_super_departments))>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @if(config('services.have_work_from_home_request'))
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">
                                        Work from home request
                                    </label>
                                    <select class="form-control" required name="work_from_home_request"
                                        id="work_from_home_request">
                                        <option value="1" @if ($employee->work_from_home_request) selected @endif>
                                            Yes
                                        </option>
                                        <option value="0" @if (!$employee->work_from_home_request) selected @endif>
                                            No
                                        </option>
                                    </select>
                                </div>
                            </div>
                                @endif
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Out Side Duty</label>
                                    <select class="form-control" required name="can_outside_attendence"
                                        id="can_outside_attendence">
                                        <option value="1" @if ($employee->can_outside_attendence) selected @endif>
                                            Yes
                                        </option>
                                        <option value="0" @if (!$employee->can_outside_attendence) selected @endif>
                                            No
                                        </option>
                                    </select>
                                </div>
                            </div>
                            @if(config('services.have_attendance_location'))
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">
                                        Have Geo Location Check-In?
                                    </label>
                                    <select class="form-control" required name="have_geo_location"
                                        id="have_geo_location">
                                        <option value="yes" @if ($employee->have_geo_location) selected @endif>
                                            Yes
                                        </option>
                                        <option value="no" @if (!$employee->have_geo_location) selected @endif>
                                            No
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="geo_location_departments">Geo location admin of Sup. Depts.</label>
                                    <select class="form-control select2" name="geo_location_departments[]" id="geo_location_departments"
                                        multiple>
                                        <option disabled>Select super departments</option>
                                        @foreach ($sup_depts as $item)
                                            <option value="{{ $item->id }}"
                                                @selected($employee->geo_location_departments && in_array($item->id, $employee->geo_location_departments))>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @endif

                            @if(config('app.customer') === 'vector')
                            <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">
                                            From Management?
                                        </label>
                                        <select class="form-control select2" name="from_management">
                                            <option value="1" @if ($employee->from_management) selected @endif>Yes</option>
                                            <option value="0" @if (!$employee->from_management) selected @endif>No</option>
                                        </select>
                                    </div>
                                </div>
                            @endif

                            @if(Route::has('reports.live-location'))
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Live Location Tracking</label>
                                    <select class="form-control" required name="live_location_track"
                                        id="can_outside_attendence">
                                        <option value="1" @if ( $employee->live_location_track ) selected @endif>Yes
                                        </option>
                                        <option value="0" @if (!$employee->live_location_track) selected @endif>No
                                        </option>
                                    </select>
                                </div>
                            </div>
                            @endif


                            <div class="col-md-12">
                                <div class="submit-section">
                                    <button class="btn btn-primary submit-btn">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="nav-document" role="tabpanel" aria-labelledby="nav-document-tab">
                        <h3>Bio Data</h3>
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">
                                        Father's name:
                                    </label>
                                    <input class="form-control" type="text" id="father_name" name="father_name"
                                        value="{{ $employee->father_name }}">
                                    @if ($errors->has('father_name'))
                                    <span class="text-danger">{{ $errors->first('father_name') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Mother's name:
                                    </label>
                                    <input class="form-control" type="text" id="mother_name" name="mother_name"
                                        value="{{ $employee->mother_name }}">
                                    @if ($errors->has('mother_name'))
                                    <span class="text-danger">{{ $errors->first('mother_name') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Address:</label>
                                    <textarea class="form-control" type="text" id="address"
                                        name="address">{{ $employee->address }}</textarea>
                                    @if ($errors->has('address'))
                                    <span class="text-danger">{{ $errors->first('address') }}</span>
                                    @endif
                                </div>
                            </div>
                            @if(config('services.hide_personal_phone_mail') === false)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">
                                        City:
                                    </label>
                                    <select name="city" id="" class="select form-control">
                                        <option name="" id="">Select City</option>
                                        @foreach (config('services.bangladesh_cities') as $item)
                                        <option value="{{ $item }}" @if($employee->city == $item) selected @endif
                                            >
                                            {{ $item }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Permanent Address:</label>
                                    <textarea class="form-control" type="text" id="permanent_address"
                                        name="permanent_address">{{ $employee->permanent_address }}</textarea>
                                    @if ($errors->has('permanent_address'))
                                    <span class="text-danger">{{ $errors->first('permanent_address') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">
                                        Permanent Address City:
                                    </label>
                                    <select name="permanent_city" id="" class="select form-control">
                                        <option name="" id="">Select City</option>
                                        @foreach (config('services.bangladesh_cities') as $item)
                                        <option value="{{ $item }}" @if($employee->permanent_city == $item) selected
                                            @endif
                                            >
                                            {{ $item }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>NID/Passport No <sup class="text-danger">*</sup>.</label>
                                    <input type="text" class="form-control" required min="6" max="15"
                                        id="nid_or_passport" name="nid_or_passport"
                                        value="{{ $employee->nid_or_passport }}">
                                    <img src="{{ $employee->nid_or_passport_file ? '/uploaded_files/' . $employee->nid_or_passport_file : '' }}"
                                        alt="" class="nid-or-passport mt-2" style="width: 200px;">
                                    @if ($employee->nid_or_passport_file)
                                    <div class="mt-2 mb-2">
                                        <button class="btn btn-link btn-sm text-danger" type="button"
                                            onclick="
                            this.parentElement.previousElementSibling.src = '';
                            $(this).parent().html('<input type=\'hidden\' name=\'remove_nid_or_passport_file\' value=\'1\'>')">
                                            <i class="fa fa-trash"></i> Remove existing
                                            NID/Passport
                                        </button>
                                    </div>
                                    @endif
                                    @if ($errors->has('nid_or_passport'))
                                    <span class="text-danger">{{ $errors->first('nid_or_passport') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date of Birth:</label>
                                    <div class="cal-icon">
                                        <input class="form-control datetimepicker" type="text" id="birth_date"
                                            name="birth_date"
                                            value="{{ $employee->birth_date ? $employee->birth_date->format('d-m-Y') : '' }}">
                                    </div>
                                    @if ($errors->has('birth_date'))
                                    <span class="text-danger">{{ $errors->first('birth_date') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Blood group:</label>
                                    <select class="form-control" name="blood_group" id="">
                                        <option value="">--Select--</option>
                                        @if ($employee->blood_group)
                                        <option value="{{ $employee->blood_group }}" selected>
                                            {{ $employee->blood_group }}</option>
                                        @endif
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                    </select>
                                    @if ($errors->has('blood_group'))
                                    <span class="text-danger">{{ $errors->first('blood_group') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Gender</label>
                                    <select class="select form-control" id="gender" name="gender">
                                        <option value="">--Select--</option>
                                        @if ($employee->gender)
                                        <option value="{{ $employee->gender }}" selected>
                                            {{ $employee->gender }}
                                        </option>
                                        @endif
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    @if ($errors->has('gender'))
                                    <span class="text-danger">{{ $errors->first('gender') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Religion</label>
                                    <select class="select form-control" id="religion" name="religion">
                                        <option value="">--Select--</option>
                                        @if ($employee->religion)
                                        <option value="{{ $employee->religion }}" selected>
                                            {{ $employee->religion }}
                                        </option>
                                        @endif
                                        <option value="Islam">Islam</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Christian">Christian</option>
                                        <option value="Buddhist">Buddhist</option>
                                        <option value="Others">Others</option>
                                    </select>
                                    @if ($errors->has('religion'))
                                    <span class="text-danger">{{ $errors->first('religion') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nationality</label>
                                    <select class="select form-control" id="nationality" name="nationality">
                                        @if ($employee->nationality)
                                        <option value="{{ $employee->nationality }}" selected>
                                            {{ $employee->nationality }}</option>
                                        @endif
                                        @foreach (config('services.countries') as $item)
                                        <option value="{{ $item }}">
                                            {{ $item }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('nationality'))
                                    <span class="text-danger">{{ $errors->first('nationality') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Marital Status</label>
                                    <select class="select form-control" id="marital_status" name="marital_status">
                                        <option value="">--Select--</option>
                                        @if ($employee->marital_status)
                                        <option value="{{ $employee->marital_status }}" selected>
                                            {{ $employee->marital_status }}</option>
                                        @endif
                                        <option value="Single">Single</option>
                                        <option value="Married">Married</option>
                                        <option value="Divorced">Divorced</option>
                                        <option value="Others">Others</option>
                                    </select>
                                    @if ($errors->has('marital_status'))
                                    <span class="text-danger">{{ $errors->first('marital_status') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="submit-section">
                                    <button class="btn btn-primary submit-btn">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="nav-emergency" role="tabpanel" aria-labelledby="nav-emergency-tab">

                        <div class="row">
                            <div class="col-md-6">
                                <h3>Emergency Contact No</h3>
                            </div>

                            <div class="col-md-6">

                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Person Name:</label>
                                    <input type="text" class="form-control" name="emergency_name"
                                        id="emergency_name" value="{{ $employee->emergency_name }}">
                                    @if ($errors->has('emergency_name'))
                                    <span class="text-danger">{{ $errors->first('emergency_name') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Relation:</label>
                                    <input type="text" class="form-control" name="emergency_relation"
                                        value="{{ $employee->emergency_relation }}">
                                    @if ($errors->has('emergency_relation'))
                                    <span class="text-danger">{{ $errors->first('emergency_relation') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Phone :</label>
                                    <input type="text" class="form-control" max="11" name="emergency_phone"
                                        id="emergency_phone" value="{{ $employee->emergency_phone }}">
                                    @if ($errors->has('emergency_phone'))
                                    <span class="text-danger">{{ $errors->first('emergency_phone') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Address:</label>
                                    <textarea class="form-control"
                                        name="emergency_address">{{ $employee->emergency_address }}</textarea>
                                    @if ($errors->has('emergency_address'))
                                    <span class="text-danger">{{ $errors->first('emergency_address') }}</span>
                                    @endif
                                </div>
                            </div>



                            <div class="col-md-12">
                                <div class="submit-section">
                                    <button class="btn btn-primary submit-btn">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="nav-reference" role="tabpanel" aria-labelledby="nav-reference-tab">
                        <h3>Reference</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Name:</label>
                                    <input type="text" class="form-control" name="reference_name"
                                        value="{{ $employee->reference_name }}">
                                    @if ($errors->has('reference_name'))
                                    <span class="text-danger">{{ $errors->first('reference_name') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Phone:</label>
                                    <input type="text" class="form-control" name="reference_phone"
                                        value="{{ $employee->reference_phone }}">
                                    @if ($errors->has('reference_phone'))
                                    <span class="text-danger">{{ $errors->first('reference_phone') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="submit-section">
                                    <button class="btn btn-primary submit-btn">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="nav-documents" role="tabpanel" aria-labelledby="nav-role-tab">
                        <h3>Documents</h3>
                        <div class="mt-2">
                            <div class="document-container">
                                @if ($employee->documents->count() > 0)
                                <h4>Already uploaded documents:</h4>
                                @foreach ($employee->documents as $document)
                                <div class="mt-2 mb-3">
                                    <a class="btn btn-sm btn-danger" href="javascript:void(0)" onclick="if(confirm('Are you sure you want to delete this document?')){
                            addDel('{{ $document->id }}');
                            this.parentElement.remove();
                        }">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    &nbsp;
                                    &nbsp;
                                    {{ $loop->iteration }}.
                                    <a href="{{ asset('uploaded_files/' . $document->path) }}" target="_blank">
                                        {{ $document->type }}
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                                @endforeach
                                @endif
                            </div>
                            <button class="mt-2 btn btn-primary btn-sm" type="button" onclick="addDocument()">Add
                                Document</button>
                            <div>
                                {{-- show errors --}}
                                @if ($errors->has('document'))
                                <span class="text-danger">{{ $errors->first('document') }}</span>
                                @endif
                            </div>
                            <div class="submit-section">
                                <button class="btn btn-primary submit-btn">Submit</button>
                            </div>
                        </div>
                        <script>
                            function addDel(id) {
                                    $('#nav-documents').prepend('<input type="hidden" name="document_delete[]" value="' + id + '">');
                                }

                                function addDocument() {
                                    let index = $('.document-container .d-flex').length
                                    $('.document-container').append(`
                <div class="d-flex mt-2">
                    <div style="width: 50px">
                        <button class="btn btn-sm btn-danger" onclick="this.parentElement.parentElement.remove()">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                    <div style="width: 250px">
                        <input type="file" class="form-control"
                            name="document[${index}][file]"
                            onchange="$('.doc-${index}').val(this.files[0].name.split('.')[0])"
                        >
                    </div>
                    <div style="width: 100%">
                        <input type="text" class="form-control doc-${index}"
                            name="document[${index}][type]" placeholder="Document type/name">
                    </div>
                </div>
            `);
                                }
                        </script>
                    </div>

                    <div class="tab-pane fade" id="nav-bank-details" role="tabpanel"
                        aria-labelledby="nav-bank-details-tab">
                        <h3>Bank Details</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Account Holder Name:</label>
                                    <input type="text" class="form-control" name="account_holder_name"
                                        value="{{ $employee->account_holder_name }}">
                                    @if ($errors->has('account_holder_name'))
                                    <span class="text-danger">{{ $errors->first('account_holder_name') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Account Number:</label>
                                    <input type="text" class="form-control" name="account_number"
                                        value="{{ $employee->account_number }}">
                                    @if ($errors->has('account_number'))
                                    <span class="text-danger">{{ $errors->first('account_number') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Bank Name:</label>
                                    <input type="text" class="form-control" name="bank_name"
                                        value="{{ $employee->bank_name }}">
                                    @if ($errors->has('bank_name'))
                                    <span class="text-danger">{{ $errors->first('bank_name') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Branch Name:</label>
                                    <input type="text" class="form-control" name="branch_name"
                                        value="{{ $employee->branch_name }}">
                                    @if ($errors->has('branch_name'))
                                    <span class="text-danger">{{ $errors->first('branch_name') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Routing Number:</label>
                                    <input type="text" class="form-control" name="routing_number"
                                        value="{{ $employee->routing_number }}">
                                    @if ($errors->has('routing_number'))
                                    <span class="text-danger">{{ $errors->first('routing_number') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">TIN Number:</label>
                                    <input type="text" class="form-control" name="tin_number"
                                        value="{{ $employee->tin_number }}">
                                    @if ($errors->has('tin_number'))
                                    <span class="text-danger">{{ $errors->first('tin_number') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Tax Number:</label>
                                    <input type="text" class="form-control" name="tax_number"
                                        value="{{ $employee->tax_number }}">
                                    @if ($errors->has('tax_number'))
                                    <span class="text-danger">{{ $errors->first('tax_number') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="submit-section">
                                    <button class="btn btn-primary submit-btn">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="nav-role" role="tabpanel" aria-labelledby="nav-role-tab">
                        <h3>Role</h3>
                        <div class="row">
                            <div class="col-md-12">
                                @if (role_can('role'))
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <b>
                                                    Role:
                                                </b>
                                                <div style="">
                                                    <select name="role" class="form-control" id="">
                                                        <option value="">-- None --
                                                        </option>
                                                        @foreach (\App\Models\Role::get() as $role)
                                                        <option value="{{ $role->id }}" @if ($role->id ==
                                                            $employee->role_id) selected @endif>
                                                            {{ $role->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mt-2">
                                                    <b>
                                                        Set Password:
                                                    </b>
                                                    <a href="#" class="float-right" data-toggle-password="true"
                                                        data-target="[name=password]">
                                                        Show
                                                    </a>
                                                </div>
                                                <div style="" class="mt-2">
                                                    <input type="password" class="form-control" name="password"
                                                        autocomplete="new-password">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div>
                                                    <b>
                                                        Auth for ZKTeco Device:
                                                    </b>
                                                </div>
                                                <label class="mt-2" for="">User
                                                    Role:</label>
                                                <select name="zk_device_privilege" id="zk_device_privilege"
                                                    class="form-control mt-1">
                                                    <option @if ($employee->zk_device_privilege === 'user') selected
                                                        @endif
                                                        value="user">User</option>
                                                    <option @if ($employee->zk_device_privilege === 'admin') selected
                                                        @endif
                                                        value="admin">Admin</option>
                                                    <option @if ($employee->zk_device_privilege === 'non_ac_user')
                                                        selected @endif
                                                        value="non_ac_user">Non AC User
                                                    </option>
                                                </select>
                                                <label for="" class="mt-2">Password</label>
                                                <input type="text" class="form-control mt-1" name="zk_device_password"
                                                    value="{{ $employee->zk_device_password ?? '' }}">
                                                <label for="" class="mt-2">Card
                                                    No.</label>
                                                <input type="text" class="form-control mt-1" name="zk_device_card"
                                                    value="{{ $employee->zk_device_card ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <div class="col-md-12">
                                <div class="submit-section">
                                    <button onclick="validateForm(event)"
                                        class="btn btn-primary submit-btn">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                        <input type="hidden" name="have_geo_location" value="yes">
                        <script>
                            $(document).ready(function () {
                                $('#nav-geo-locations').addClass('show active')
                            })
                        </script>
                    @endif

                    @if(config('services.have_attendance_location'))
                        @include('employees.edit-geo-locations')
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Add Department Modal -->
<div class="modal custom-modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Department</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addDepartmentForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-form-label">Super Department <sup class="text-danger"></sup>:</label>
                        <br>
                        <select class="form-control select" id="super_department_id" name="super_department_id"
                            required>
                            <option value="">-- Select --</option>
                            @foreach (!role_can('user') ? [] : \App\Models\SuperDepartment::ordered()->get() as $item)
                            <option value="{{ $item->id }}">
                                {{ $item->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="department_name">Department Name</label>
                        <input type="text" id="department_name" name="department_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add designation Modal -->
<div class="modal custom-modal fade" id="addDesignationModal" tabindex="-1" aria-labelledby="addDesignationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add designation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addDesignationForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="designation_name">Designation Name</label>
                        <input type="text" id="designation_name" name="designation_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Designation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Location Selection Modal -->
<div class="modal custom-modal fade" id="locationSelectionModal" tabindex="-1"
    aria-labelledby="locationSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Location</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                    onclick="$('#locationSelectionModal').modal('hide')">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Search Section -->
                <div class="form-group">
                    <label for="location-search">Search Location</label>
                    <input type="text" id="location-search" class="form-control"
                        placeholder="Type to search locations...">
                </div>

                <!-- Location List -->
                <div class="locations-list" style="max-height: 400px; overflow-y: auto;">
                    <div id="locations-loading" class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    <div id="locations-container">
                        <!-- Locations will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"
                    onclick="$('#locationSelectionModal').modal('hide')">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    #home-location-map {
        border: 1px solid #ddd;
        border-radius: 4px;
    }
</style>

<script>
    $('[image_upload_preview]').on('change', function() {
        console.log(URL.createObjectURL(this.files[0]));
        $(this).parent().find(this.getAttribute('image_upload_preview')).attr('src', URL.createObjectURL(this
            .files[0]));
    })
    $('.select2').select2({})

    function validateForm(e) {
        if ($('#name').val() == "") {
            alert('Enter employee Name');
            $('#nav-basic-tab').trigger('click');
            setTimeout(function() {
                $('#name').focus();
            }, 200);
            return false;
        }
        if ($('#department').val() == "") {
            alert('Enter employee department');
            $('#nav-basic-tab').trigger('click');
            setTimeout(function() {
                $('#department').focus();
            }, 200);
            return false;
        }
        if ($('#designation').val() == "") {
            alert('Enter employee designation');
            $('#nav-basic-tab').trigger('click');
            setTimeout(function() {
                $('#designation').focus();
            }, 200);
            return false;
        }
        if ($('#employee_type').val() == "") {
            alert('Enter employee type');
            $('#nav-basic-tab').trigger('click');
            setTimeout(function() {
                $('#employee_type').focus();
            }, 200);
            return false;
        }
        if ($('#phone_number').val() == "") {
            alert('Enter Employee phone number');
            $('#nav-basic-tab').trigger('click');
            setTimeout(function() {
                $('#phone_number').focus();
            }, 200);
            return false;
        }
        if ($('#can_overtime').val() == "") {
            alert('Enter overtime');
            $('#nav-basic-tab').trigger('click');
            setTimeout(function() {
                $('#can_overtime').focus();
            }, 200);
            return false;
        }
        if ($('#get_attendance_sms_notification').val() == "") {
            alert('Enter get late sms');
            $('#nav-basic-tab').trigger('click');
            setTimeout(function() {
                $('#get_attendance_sms_notification').focus();
            }, 200);
            return false;
        }
        if ($('#join_date').val() == "") {
            alert('Enter employee join date');
            $('#nav-basic-tab').trigger('click');
            setTimeout(function() {
                $('#join_date').focus();
            }, 200);
            return false;
        }
        if ($('#can_outside_attendence').val() == "") {
            alert('Enter employee outside duty');
            $('#nav-basic-tab').trigger('click');
            setTimeout(function() {
                $('#can_outside_attendence').focus();
            }, 200);
            return false;
        }
        if ($('#nid_or_passport').val() == "") {
            alert('Enter employee nid/passport');
            $('#nav-document-tab').trigger('click');
            setTimeout(function() {
                $('#nid_or_passport').focus();
            }, 200);
            return false;
        }
        // if ($('#emergency_name').val() == "") {
        //     alert('Enter emergency/person name');
        //     $('#nav-emergency-tab').trigger('click');
        //     setTimeout(function() {
        //         $('#emergency_name').focus();
        //     }, 200);
        //     return false;
        // }
        // if ($('#emergency_phone').val() == "") {
        //     alert('Enter emergency phone');
        //     $('#nav-emergency-tab').trigger('click');
        //     setTimeout(function() {
        //         $('#emergency_phone').focus();
        //     }, 200);
        //     return false;
        // }
    }

    $(document).ready(function() {
        $('#addDepartmentForm').on('submit', function(e) {
            e.preventDefault();
            const department_name = $('#department_name').val();
            const super_department_id = $('#super_department_id').val();
            // alert(department_name)
            $.ajax({
                url: "{{ route('departments.modal.save') }}",
                method: "POST",
                data: {
                    name: department_name,
                    id: super_department_id,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    alert(response.message)
                    const new_department = new Option(
                        `${response.super_department.name}: ${response.department.name}`,
                        response.department.id, true, true);
                    $('#department').append(new_department).trigger('change');

                    $('#addDepartmentModal').modal('hide');
                    $('#addDepartmentModal').removeClass('show');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();

                    $('#department_name').val('');
                    $('#super_department_id').val('').trigger('change');
                },
                error: function(xhr) {
                    alert('Something went wrong. Please try again.');
                }
            });
        });


        $('#addDesignationForm').on('submit', function(e) {
            e.preventDefault();
            const designation_name = $('#designation_name').val();
            // alert(designation_name)
            $.ajax({
                url: "{{ route('employees.designations.modal.save') }}",
                method: "POST",
                data: {
                    name: designation_name,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {

                    alert(response.message);

                    const new_designation = new Option(response.designation.name, response
                        .designation.id, true, true);
                    $('#designation').append(new_designation).trigger('change');


                    $('#addDesignationModal').modal('hide');
                    $('#addDesignationModal').removeClass('show');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();

                    $('#addDesignationForm')[0].reset();
                },
                error: function(xhr) {
                    alert('Something went wrong. Please try again.');
                }
            });
        });

        $('[name="have_geo_location"]').each(function () {
            function toggleView(el) {

                if(el.value === 'yes') {
                    $('#nav-geo-locations-tab').show()
                    $('#nav-geo-locations').show()
                }else {
                    $('#nav-geo-locations-tab').hide()
                    $('#nav-geo-locations').hide()
                }
            }
            $(this).on('change', e => toggleView(e.target))
            toggleView(this)
        })
    });

</script>
@endsection
