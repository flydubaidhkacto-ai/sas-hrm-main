@extends('layouts.master')
@section('title', 'Create Employee')

@section('style')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

<style>
    /* Fix Leaflet map container sizing issues */
    #home-location-map {
        width: 100% !important;
        height: 250px !important;
        max-width: 100% !important;
        position: relative !important;
        z-index: 1 !important;
    }

    /* Ensure map tiles load properly */
    .leaflet-container {
        max-width: 100% !important;
        max-height: 100% !important;
    }

    /* Fix for map in tabs */
    .tab-pane #home-location-map {
        width: 100% !important;
        height: 250px !important;
    }
</style>
@endsection

@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">

        {{-- include employees tabs --}}
        {{-- <div class="mb-3">
            @include('tabs.employees')
        </div> --}}

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Create Employee</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Employee</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->



        @include('layouts.alerts')
        <form id="employee-form" action="{{ route('employees.active.save') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
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
                        type="button" role="tab" aria-controls="nav-reference" aria-selected="false">Reference</button>
                    <button class="nav-link" id="nav-documents-tab" data-toggle="tab" data-target="#nav-documents"
                        type="button" role="tab" aria-controls="nav-documents" aria-selected="false">Documents</button>

                    <button class="nav-link" id="nav-bank-details-tab" data-toggle="tab" data-target="#nav-bank-details"
                        type="button" role="tab" aria-controls="nav-bank-details" aria-selected="false">Bank
                        Details</button>

                    <button class="nav-link" id="nav-role-tab" data-toggle="tab" data-target="#nav-role" type="button"
                        role="tab" aria-controls="nav-role" aria-selected="false">Auth and Roles</button>

                    {{-- @if(config('services.have_attendance_location'))
                    <button class="nav-link" id="nav-geo-locations-tab" data-toggle="tab"
                        data-target="#nav-geo-locations" type="button" role="tab" aria-controls="nav-geo-locations"
                        aria-selected="false">Geo Locations</button>
                    @endif --}}
                </div>
            </nav>

            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-basic" role="tabpanel" aria-labelledby="nav-basic-tab">
                    <h3>Basic Information</h3>
                    <div class="row">
                        <div class="col-md-6 mx-auto">
                            <div class="form-group">
                                <label class="col-form-label">Employee ID:</label>
                                <input type="text" name="employee_id" class="form-control"
                                    autocomplete="new-password" />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Name<sup class="text-danger">*</sup>:</label>
                                <input type="text" name="name" id="name" class="form-control" required
                                    value="{{ old('name') }}">
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
                                    <option value="{{ $item->id }}" {{ $item->id == old('department') ? 'selected' : ''
                                        }}>
                                        {{ $item->super_department->name }}:
                                        {{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('department'))
                                <span class="text-danger">{{ $errors->first('department') }}</span>
                                @endif
                                </select>
                                <a type="button" class="" data-toggle="modal" data-target="#addDepartmentModal">
                                    Add Department
                                </a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Picture:</label>
                                <input type="file" class="form-control" name="picture_file" accept="image/*"
                                    image_upload_preview=".picture">
                                <img src="" alt="" class="picture mt-2" style="width: 300px">
                                @if ($errors->has('picture_file'))
                                <span class="text-danger">{{ $errors->first('picture_file') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Designation <sup class="text-danger">*</sup>:</label>
                                <br>
                                <select class="form-control select2" id="designation" name="designation" required>
                                    <option value="">-- Select --</option>
                                    @foreach (\App\Models\Designation::ordered()->get() as $item)
                                    <option value="{{ $item->id }}" @if (old('designation')==$item->id) selected
                                        @endif>{{ $item->name }}
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
                                <label for="">Signature:</label>
                                <input type="file" class="form-control" name="signature_file"
                                    image_upload_preview=".signature" accept="image/*">
                                <img src="" alt="" class="signature mt-2" style="width: 200px">
                                @if ($errors->has('signature_file'))
                                <span class="text-danger">{{ $errors->first('signature_file') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Employee type <sup class="text-danger">*</sup></label>
                                <select class="form-control" required name="employee_type" id="employee_type"
                                    onchange="if(this.value == 'Probationary') { $('.probation-date').removeClass('d-none'); $('.probation-date input').attr('required', true); } else { $('.probation-date').addClass('d-none'); $('.probation-date input').attr('required', false); }">
                                    <option value="Permanent" @if (old('employee_type')==='Permanent' ) selected @endif>
                                        Permanent</option>
                                    <option value="Contractual" @if (old('employee_type')==='Contractual' ) selected
                                        @endif>
                                        Contractual</option>
                                    <option value="Probationary" @if (old('employee_type')==='Probationary' ) selected
                                        @endif>
                                        Probationary</option>
                                </select>
                                @if ($errors->has('employee_type'))
                                <span class="text-danger">{{ $errors->first('employee_type') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 probation-date @if (old('employee_type') != 'Probationary') d-none @endif">
                            <div class="form-group">
                                <label for="">Probation Period End Date-Time <sup class="text-danger">*</sup></label>
                                <input type="datetime-local" class="form-control" name="probation_end_date"
                                    value="{{ old('probation_end_date') }}">
                                @if ($errors->has('probation_end_date'))
                                <span class="text-danger">{{ $errors->first('probation_end_date') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">
                                    Phone <sup class="text-danger">*</sup>:
                                </label>
                                <input class="form-control" type="text" id="phone_number" name="phone_number" required
                                    value="{{ old('phone_number') }}">
                                @if ($errors->has('phone_number'))
                                <span class="text-danger">{{ $errors->first('phone_number') }}</span>
                                @endif
                            </div>
                        </div>
                        @if(config('services.hide_personal_phone_mail') === false)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Personal Phone:</label>
                                <input type="text" class="form-control" required max="11" name="personal_phone"
                                    id="personal_phone" value="{{ old('personal_phone') }}">
                                @if ($errors->has('personal_phone'))
                                <span class="text-danger">{{ $errors->first('personal_phone') }}</span>
                                @endif
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Overtime</label>
                                <select class="form-control" required name="can_overtime" id="can_overtime">
                                    <option value="0" @if (old('can_overtime')==='0' ) selected @endif>No
                                    <option value="1" @if (old('can_overtime')==='1' ) selected @endif>Yes
                                    </option>
                                    </option>
                                </select>
                            </div>
                        </div>



                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">
                                    Email:
                                </label>
                                <input class="form-control" type="email" id="email" name="email"
                                    value="{{ old('email') }}">
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
                                    value="{{ old('personal_email') }}">
                                @if ($errors->has('personal_email'))
                                <span class="text-danger">{{ $errors->first('personal_email') }}</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">
                                    Get SMS For :
                                </label>
                                <select class="form-control select2" name="sms_warnings[]" id="sms_warnings" multiple>
                                    <option disabled>Select Get SMS For </option>
                                    {{-- <option value="fine">Fine</option> --}}
                                    {{-- <option value="loan">Loan</option> --}}
                                    {{-- <option value="reward">Reward</option> --}}
                                    <option value="in">In Message</option>
                                    <option value="out">Out Message</option>
                                    <option value="duty_summary">Summery Message</option>
                                    <option value="late">Late</option>
                                    <option value="first_in">First In</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Joining Date <sup class="text-danger">*</sup></label>
                                <div class="cal-icon">
                                    <input type="text" class="form-control datetimepicker" type="text" id="join_date"
                                        required name="join_date" value="{{ old('join_date') }}">
                                </div>
                                @if ($errors->has('join_date'))
                                <span class="text-danger">{{ $errors->first('join_date') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">
                                    Pay Type
                                </label>
                                <select class="form-control" required name="paytype" id="paytype">
                                    <option value="daily">
                                        Daily
                                    </option>
                                    <option value="hourly">
                                        Hourly
                                    </option>
                                    <option value="monthly">
                                        Monthly
                                    </option>
                                </select>
                            </div>
                            @if(config('services.have_work_from_home_request'))
                            <div class="form-group">
                                <label for="">
                                    Work from home request
                                </label>
                                <select class="form-control" required name="work_from_home_request"
                                    id="work_from_home_request">
                                    <option value="1">
                                        Yes
                                    </option>
                                    <option value="0" selected>
                                        No
                                    </option>
                                </select>
                            </div>
                            @endif
                            <div class="form-group">
                                <label for="">Out Side Duty</label>
                                <select class="form-control" required name="can_outside_attendence"
                                    id="can_outside_attendence">
                                    <option value="1" @if (old('can_outside_attendence')==='1' ) selected @endif>Yes
                                    </option>
                                    <option value="0" @if (old('can_outside_attendence')==='0' ) selected @else selected
                                        @endif>No
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="shift_id">Shift <span class="text-danger">*</span></label>
                                <select class="form-control" id="shift_id" name="shift_id" required>
                                    <option value="">-- Select --</option>
                                    @foreach (\App\Models\Shift::where('is_for_student', false)->get() as $item)
                                    <option value="{{ $item->id }}" @if (old('shift_id') == $item->id) selected @endif>
                                        {{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @if(Route::has('reports.live-location'))
                            <div class="form-group">
                                <label for="">Live Location Tracking</label>
                                <select class="form-control" required name="live_location_track"
                                    id="can_outside_attendence">
                                    <option value="0" @if (old('live_location_track')==='0' ) selected @endif>No</option>
                                    <option value="1" @if (old('live_location_track')==='1' ) selected @endif>Yes</option>
                                </select>
                            </div>
                            @endif
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
                                    value="{{ old('father_name') }}">
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
                                    value="{{ old('mother_name') }}">
                                @if ($errors->has('mother_name'))
                                <span class="text-danger">{{ $errors->first('mother_name') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Address:</label>
                                <textarea class="form-control" type="text" id="address"
                                    name="address">{{ old('address') }}</textarea>
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
                                    @foreach ([] as $item)
                                    <option value="{{ $item }}">
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
                                    name="permanent_address"></textarea>
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
                                    @foreach ([] as $item)
                                    <option value="{{ $item }}">
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
                                <input type="text" class="form-control" required min="6" max="15" id="nid_or_passport"
                                    name="nid_or_passport" value="{{ old('nid_or_passport') }}">
                                @if ($errors->has('nid_or_passport'))
                                <span class="text-danger">{{ $errors->first('nid_or_passport') }}</span>
                                @endif
                            </div>
                        </div>

                        <!--<div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>
                                                                                    NID/Passport Picture:
                                                                                    <br>
                                                                                    <small>
                                                                                        <i>Max file size is 300KB (png, jpg, jpeg)</i>
                                                                                    </small>
                                                                                </label>
                                                                                <input type="file" accept="image/*" class="form-control"
                                                                                    image_upload_preview=".nid-img" id="nid_or_passport_file"
                                                                                    name="nid_or_passport_file">
                                                                                @if ($errors->has('nid_or_passport_file'))
    <span class="text-danger">{{ $errors->first('nid_or_passport_file') }}</span>
    @endif
                                                                                <img src="" alt="" class="w-100 nid-img mt-2">
                                                                            </div>
                                                                        </div>-->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Birth: <sup class="text-danger">*</sup></label>
                                <div class="cal-icon">
                                    <input class="form-control datetimepicker" type="text" id="birth_date"
                                        name="birth_date" value="{{ old('birth_date') }}">
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
                                    @if (old('blood_group'))
                                    <option value="{{ old('blood_group') }}" selected>
                                        {{ old('blood_group') }}</option>
                                    @endif
                                    <option value="A+" selected>A+</option>
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
                                    @if (old('gender'))
                                    <option value="{{ old('gender') }}" selected>{{ old('gender') }}</option>
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
                                    @if (old('religion'))
                                    <option value="{{ old('religion') }}" selected>{{ old('religion') }}
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
                                    @if (old('nationality'))
                                    <option value="{{ old('nationality') }}" selected>
                                        {{ old('nationality') }}</option>
                                    @endif
                                    @foreach (config('services.countries') as $item)
                                    <option value="{{ $item }}">{{ $item }}</option>
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
                                    @if (old('marital_status'))
                                    <option value="{{ old('marital_status') }}" selected>
                                        {{ old('marital_status') }}</option>
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
                    </div>
                </div>
                <div class="tab-pane fade" id="nav-emergency" role="tabpanel" aria-labelledby="nav-emergency-tab">
                    <h3>Emergency Contact</h3>
                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group">
                                <label class="col-form-label">Person Name :</label>
                                <input type="text" class="form-control" name="emergency_name"
                                    id="emergency_name" value="{{ old('emergency_name') }}">
                                @if ($errors->has('emergency_name'))
                                <span class="text-danger">{{ $errors->first('emergency_name') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Relation:</label>
                                <input type="text" class="form-control" name="emergency_relation"
                                    value="{{ old('emergency_relation') }}">
                                @if ($errors->has('emergency_relation'))
                                <span class="text-danger">{{ $errors->first('emergency_relation') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Phone :</label>
                                <input type="text" class="form-control" max="11" name="emergency_phone"
                                    id="emergency_phone" value="{{ old('emergency_phone') }}">
                                @if ($errors->has('emergency_phone'))
                                <span class="text-danger">{{ $errors->first('emergency_phone') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Address:</label>
                                <textarea class="form-control"
                                    name="emergency_address">{{ old('emergency_address') }}</textarea>
                                @if ($errors->has('emergency_address'))
                                <span class="text-danger">{{ $errors->first('emergency_address') }}</span>
                                @endif
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
                                    value="{{ old('reference_name') }}">
                                @if ($errors->has('reference_name'))
                                <span class="text-danger">{{ $errors->first('reference_name') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Phone:</label>
                                <input type="text" class="form-control" name="reference_phone"
                                    value="{{ old('reference_phone') }}">
                                @if ($errors->has('reference_phone'))
                                <span class="text-danger">{{ $errors->first('reference_phone') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="nav-documents" role="tabpanel" aria-labelledby="nav-role-tab">
                    <h3>Documents</h3>
                    <div class="mt-2">
                        <div class="document-container">

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
                        function addDocument() {
                                let index = $('.document-container').children().length;
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

                <div class="tab-pane fade" id="nav-bank-details" role="tabpanel" aria-labelledby="nav-bank-details-tab">
                    <h3>Bank Details</h3>
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Account holder name<sup
                                        class="text-danger"></sup>:</label>
                                <input type="text" name="account_holder_name" id="account_holder_name"
                                    class="form-control" value="{{ old('account_holder_name') }}">
                                @if ($errors->has('account_holder_name'))
                                <span class="text-danger">{{ $errors->first('account_holder_name') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Account Number<sup class="text-danger"></sup>:</label>
                                <input type="text" name="account_number" id="account_number" class="form-control"
                                    value="{{ old('account_number') }}">
                                @if ($errors->has('account_number'))
                                <span class="text-danger">{{ $errors->first('account_number') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Bank Name<sup class="text-danger"></sup>:</label>
                                <input type="text" name="bank_name" id="bank_name" class="form-control"
                                    value="{{ old('bank_name') }}">
                                @if ($errors->has('bank_name'))
                                <span class="text-danger">{{ $errors->first('bank_name') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Branch Name<sup class="text-danger"></sup>:</label>
                                <input type="text" name="branch_name" id="branch_name" class="form-control"
                                    value="{{ old('branch_name') }}">
                                @if ($errors->has('branch_name'))
                                <span class="text-danger">{{ $errors->first('branch_name') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Routing Number<sup class="text-danger"></sup>:</label>
                                <input type="text" name="routing_number" id="routing_number" class="form-control"
                                    value="{{ old('routing_number') }}">
                                @if ($errors->has('routing_number'))
                                <span class="text-danger">{{ $errors->first('routing_number') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">TIN Number<sup class="text-danger"></sup>:</label>
                                <input type="text" name="tin_number" id="tin_number" class="form-control"
                                    value="{{ old('tin_number') }}">
                                @if ($errors->has('tin_number'))
                                <span class="text-danger">{{ $errors->first('tin_number') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Tax Number<sup class="text-danger"></sup>:</label>
                                <input type="text" name="tax_number" id="tax_number" class="form-control"
                                    value="{{ old('tax_number') }}">
                                @if ($errors->has('tax_number'))
                                <span class="text-danger">{{ $errors->first('tax_number') }}</span>
                                @endif
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
                                                    <option value="">-- None --</option>
                                                    @foreach (\App\Models\Role::get() as $role)
                                                    <option value="{{ $role->id }}" @if ($role->id == old('role'))
                                                        selected @endif>
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
                                                    autocomplete="awfawefwe" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div>
                                                <b>
                                                    Auth for ZKTeco Device:
                                                </b>
                                            </div>
                                            <label class="mt-2" for="">User Role:</label>
                                            <select name="zk_device_privilege" id="zk_device_privilege"
                                                class="form-control mt-1">
                                                <option selected value="user">User</option>
                                                <option value="admin">Admin</option>
                                                <option value="non_ac_user">Non AC User</option>
                                            </select>
                                            <label for="" class="mt-2">Password</label>
                                            <input type="text" class="form-control mt-1" name="zk_device_password"
                                                value="{{ old('zk_device_password') ?? '' }}">
                                            <label for="" class="mt-2">Card No.</label>
                                            <input type="text" class="form-control mt-1" name="zk_device_card"
                                                value="{{ old('zk_device_card') ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-12">
                            <div class="submit-section">
                                <button onclick="validateForm(event)" type="submit"
                                    class="btn btn-primary submit-btn">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>

                @if(config('services.have_attendance_location'))
                <div class="tab-pane fade" id="nav-geo-locations" role="tabpanel"
                    aria-labelledby="nav-geo-locations-tab">
                    <h3>Geo Locations</h3>
                    <div class="row">
                        <!-- Home Address Coordinates (Left Side) -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Home Address Coordinates</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="home_latitude">Latitude <span class="text-muted">(Home
                                                Address)</span></label>
                                        <input type="number" step="any" name="home_latitude" id="home_latitude"
                                            class="form-control" placeholder="Enter latitude"
                                            value="{{ old('home_latitude') }}">
                                        @if ($errors->has('home_latitude'))
                                        <span class="text-danger">{{ $errors->first('home_latitude') }}</span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="home_longitude">Longitude <span class="text-muted">(Home
                                                Address)</span></label>
                                        <input type="number" step="any" name="home_longitude" id="home_longitude"
                                            class="form-control" placeholder="Enter longitude"
                                            value="{{ old('home_longitude') }}">
                                        @if ($errors->has('home_longitude'))
                                        <span class="text-danger">{{ $errors->first('home_longitude') }}</span>
                                        @endif
                                    </div>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-info btn-sm"
                                            onclick="getCurrentLocation()">
                                            <i class="fa fa-map-marker"></i> Get Current Location
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="pickFromMap()">
                                            <i class="fa fa-map"></i> Pick from Map
                                        </button>
                                    </div>
                                    <div id="home-location-map" style="height: 250px; margin-top: 15px; display: none;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assigned Locations (Right Side) -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Assigned Locations</h5>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openLocationModal()">
                                        <i class="fa fa-plus"></i> Add Location
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="assigned-locations-list">
                                        <!-- Assigned locations will be displayed here -->
                                        <p class="text-muted text-center">No locations assigned yet</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </form>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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

                <!-- Hierarchy Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="country-filter">Country</label>
                        <select id="country-filter" class="form-control">
                            <option value="">All Countries</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="division-filter">Division</label>
                        <select id="division-filter" class="form-control">
                            <option value="">All Divisions</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="city-filter">City</label>
                        <select id="city-filter" class="form-control">
                            <option value="">All Cities</option>
                        </select>
                    </div>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    $('[image_upload_preview]').on('change', function() {
            console.log(URL.createObjectURL(this.files[0]));
            $(this).parent().find(this.getAttribute('image_upload_preview')).attr('src', URL.createObjectURL(this
                .files[0]));
        })
        $('.select2').select2({})

        function validateForm(e) {
            const invalidInputs = $('#employee-form :invalid');
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
            // } else
            if (invalidInputs.length > 0) {
                alert('Please fill all required fields!');
                // Get the first invalid input
                const firstInvalid = invalidInputs[0];

                // Find which tab pane contains the invalid input
                const tabPane = $(firstInvalid).closest('.tab-pane');
                const tabId = tabPane.attr('id');

                // Activate the corresponding tab
                $(`[data-target="#${tabId}"]`).tab('show');

                // Focus the invalid input
                firstInvalid.focus();

                // Optionally scroll to the invalid input
                $('html, body').animate({
                    scrollTop: $(firstInvalid).offset().top - 100
                }, 200);
            }
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

});

// Add tab change event listeners to fix map display issues
$('button[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    if ($(e.target).attr('data-target') === '#nav-geo-locations') {
        // Delay to ensure the tab is fully visible before invalidating map size
        setTimeout(function() {
            if (homeLocationMap) {
                homeLocationMap.invalidateSize();

                // Recenter map if coordinates exist
                const lat = $('#home_latitude').val();
                const lng = $('#home_longitude').val();
                if (lat && lng) {
                    homeLocationMap.setView([parseFloat(lat), parseFloat(lng)], 15);
                }
            }
        }, 100);
    }
});

// Global variables for geo-location functionality
let homeLocationMap;
let homeLocationMarker;
let assignedLocationIds = [];

// Pick location from map for home address
function pickFromMap() {
    $('#home-location-map').show();

    // Small delay to ensure container is visible
    setTimeout(function() {
        // Initialize map with default coordinates if not already initialized
        if (!homeLocationMap) {
            const defaultLat = 23.8103; // Dhaka, Bangladesh
            const defaultLng = 90.4125;
            homeLocationMap = L.map('home-location-map').setView([defaultLat, defaultLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: 'ProHR'
            }).addTo(homeLocationMap);

            // Force map to calculate proper size
            homeLocationMap.invalidateSize();

            // Add click event to map
            homeLocationMap.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                $('#home_latitude').val(lat);
                $('#home_longitude').val(lng);

                // Remove existing marker
                if (homeLocationMarker) {
                    homeLocationMarker.remove();
                }

                // Add new marker
                homeLocationMarker = L.marker([lat, lng], {draggable: true})
                    .addTo(homeLocationMap)
                    .bindPopup('Home Address Location - Drag to adjust')
                    .openPopup();

                // Add drag event to marker
                homeLocationMarker.on('dragend', function(event) {
                    const pos = event.target.getLatLng();
                    $('#home_latitude').val(pos.lat);
                    $('#home_longitude').val(pos.lng);
                });
            });

            toastr.info('Click on the map to set home address location');
        } else {
            // If map already exists, just invalidate size to fix display issues
            homeLocationMap.invalidateSize();
        }
    }, 100);
}

// Get current location for home address
function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            $('#home_latitude').val(lat);
            $('#home_longitude').val(lng);

            showHomeLocationMap(lat, lng);
            toastr.success('Location detected successfully!');
        }, function(error) {
            toastr.error('Error getting location: ' + error.message);
        });
    } else {
        toastr.error('Geolocation is not supported by this browser.');
    }
}

// Show home location on map
function showHomeLocationMap(lat, lng) {
    $('#home-location-map').show();

    // Small delay to ensure container is visible
    setTimeout(function() {
        if (homeLocationMap) {
            homeLocationMap.remove();
        }

        homeLocationMap = L.map('home-location-map').setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'ProHR'
        }).addTo(homeLocationMap);

        // Force map to calculate proper size
        homeLocationMap.invalidateSize();

        if (homeLocationMarker) {
            homeLocationMarker.remove();
        }

        homeLocationMarker = L.marker([lat, lng], {draggable: true})
            .addTo(homeLocationMap)
            .bindPopup('Home Address Location')
            .openPopup();

        homeLocationMarker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            $('#home_latitude').val(pos.lat);
            $('#home_longitude').val(pos.lng);
        });
    }, 100);
}

// Open location selection modal
function openLocationModal() {
    $('#locationSelectionModal').modal('show');
    loadLocationsForModal();
}

// Load locations for modal
function loadLocationsForModal() {
    $('#locations-loading').show();
    $('#locations-container').empty();

    $.ajax({
        url: '{{ route("locations.hierarchy") }}',
        method: 'GET',
        success: function(response) {
            $('#locations-loading').hide();
            populateLocationModal(response);
        },
        error: function() {
            $('#locations-loading').hide();
            toastr.error('Error loading locations');
        }
    });
}

// Populate location modal with hierarchy
function populateLocationModal(locations) {
    let html = '';

    function renderLocationTree(items, level = 0) {
        let result = '';
        items.forEach(function(location) {
            const indent = '&nbsp;'.repeat(level * 4);
            const isAssigned = assignedLocationIds.includes(location.id);
            const disabled = isAssigned ? 'disabled' : '';
            const badgeClass = isAssigned ? 'badge-secondary' : 'badge-primary';

            result += `
                <div class="location-item p-2 border-bottom" data-location-id="${location.id}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            ${indent}<strong>${location.name}</strong>
                            <span class="badge ${badgeClass} ml-2">${location.type}</span>
                            ${isAssigned ? '<span class="badge badge-success ml-1">Assigned</span>' : ''}
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary ${disabled}"
                                onclick="selectLocation(${location.id}, '${location.name}', '${location.hierarchy || location.name}')"
                                ${disabled}>
                            ${isAssigned ? 'Already Added' : 'Select'}
                        </button>
                    </div>
                </div>
            `;

            if (location.children && location.children.length > 0) {
                result += renderLocationTree(location.children, level + 1);
            }
        });
        return result;
    }

    html = renderLocationTree(locations);
    $('#locations-container').html(html);
}

// Select a location from modal
function selectLocation(locationId, locationName, locationHierarchy) {
    const distanceInput = `
        <div class="assigned-location-item mb-3 p-3 border rounded" data-location-id="${locationId}">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="mb-1">${locationName}</h6>
                    <small class="text-muted">${locationHierarchy}</small>
                    <div class="mt-2">
                        <label class="mb-1">Distance (KM):</label>
                        <input type="number" step="0.01" name="location_distances[${locationId}]"
                               class="form-control form-control-sm" style="width: 100px; display: inline-block;"
                               value="0.00" required>
                        <input type="hidden" name="assigned_location_ids[]" value="${locationId}">
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeAssignedLocation(${locationId})">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
    `;

    // Add to assigned locations list
    if ($('#assigned-locations-list p.text-muted').length) {
        $('#assigned-locations-list').html('');
    }
    $('#assigned-locations-list').append(distanceInput);

    // Add to assigned location IDs array
    assignedLocationIds.push(locationId);

    // Close modal
    $('#locationSelectionModal').modal('hide');
    toastr.success('Location assigned successfully!');
}

// Remove assigned location
function removeAssignedLocation(locationId) {
    $(`[data-location-id="${locationId}"]`).remove();
    assignedLocationIds = assignedLocationIds.filter(id => id !== locationId);

    if ($('#assigned-locations-list').children().length === 0) {
        $('#assigned-locations-list').html('<p class="text-muted text-center">No locations assigned yet</p>');
    }

    toastr.info('Location removed from assignment.');
}

// Search functionality for location modal
$('#location-search').on('keyup', function() {
    const searchTerm = $(this).val().toLowerCase();
    $('.location-item').each(function() {
        const locationName = $(this).find('strong').text().toLowerCase();
        if (locationName.includes(searchTerm)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

// Initialize coordinates map if values exist
$(document).ready(function() {
    const lat = $('#home_latitude').val();
    const lng = $('#home_longitude').val();

    if (lat && lng) {
        // Only show map if the geo-locations tab is active
        if ($('#nav-geo-locations').hasClass('active')) {
            showHomeLocationMap(parseFloat(lat), parseFloat(lng));
        }
    }
});
</script>
@endsection
