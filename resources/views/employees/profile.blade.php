@extends('layouts.master')
@section('title', "Employee Profile")
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">

            {{-- include employees tabs --}}
            <div class="mb-3">
                @include('tabs.employees')
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Employee: {{ $employee->name }}</h3>
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
                <form action="{{route('employees.request.edit')}}" method="post">
                    @csrf
                    <div>
                        <div class="row">

                            <div class="col-sm-6">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="col-form-label">Employee Code:</label>
                                            {{ $employee->id }}
                                            <label class="col-form-label">Employee ID:</label>
                                            {{ $employee->employee_id ?? 'Not Assigned' }}

                                        </div>

                                        <div class="form-group">
                                            <label class="col-form-label">Employee Name:</label>
                                            {{ $employee->name}}
                                            <a href="#" onclick="toggleInputField('nameField')"
                                            ><i class="fas fa-edit"></i></a>
                                            <input type="text" id="nameField" name="name" class="form-control d-none mt-3"
                                            placeholder="Enter Your Name">

                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">
                                                Father's name:
                                            </label>
                                            {{ $employee->father_name ?? '--' }}
                                            <a href="#" onclick="toggleInputField('fatherField')"
                                            ><i class="fas fa-edit"></i></a>
                                            <input type="text" id="fatherField" name="father_name" class="form-control d-none mt-3"
                                            placeholder="Enter Your Father Name correctly">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Mother's name:
                                            </label>
                                            {{ $employee->mother_name ?? '--' }}
                                            <a href="#" onclick="toggleInputField('motherField')"
                                            ><i class="fas fa-edit"></i></a>
                                            <input type="text" id="motherField" name="mother_name" class="form-control d-none mt-3"
                                            placeholder="Enter Your Mother Name correctly">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">
                                                Phone:
                                            </label>
                                            {{ $employee->phone_number ?? '--' }}
                                            <a href="#" onclick="toggleInputField('phoneField')"
                                            ><i class="fas fa-edit"></i></a>
                                            <input type="text" id="phoneField" name="phone_number" class="form-control d-none mt-3"
                                            placeholder="Enter Your correct Phone Number ">

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">
                                                Email:
                                            </label>
                                            {{ $employee->email ?? '--' }}
                                            <a href="#" onclick="toggleInputField('emailField')"
                                            ><i class="fas fa-edit"></i></a>
                                            <input type="text" id="emailField" name="email" class="form-control d-none mt-3"
                                            placeholder="Enter Your correct Email ">
                                            <input type="text" value="{{user()->id}}" name='user_id' hidden>

                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Address:</label>
                                    {{ $employee->address ?? '--' }}
                                    <a href="#" onclick="toggleInputField('addressField')"
                                            ><i class="fas fa-edit"></i></a>
                                    <input type="text" id="addressField" name="address" class="form-control d-none mt-3"
                                            placeholder="Enter Your correct address">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date of Birth:</label>

                                            {{ $employee->birth_date ? carbon($employee->birth_date)->format('d F, Y') : '--' }}
                                            <a href="#" onclick="toggleInputField('birth_dateField')"
                                            ><i class="fas fa-edit"></i></a>
                                            <input type="text" id="birth_dateField" name="birth_date" class="form-control datetimepicker d-none mt-3"
                                            placeholder="Enter Your correct Date of Birth ">
                                            @if ($errors->has('birth_date'))
                                                <span class="text-danger">{{ $errors->first('birth_date') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Blood group:</label>
                                            {{ $employee->blood_group ?? '--' }}
                                            <a href="#" onclick="toggleInputField('blood_groupField')"
                                            ><i class="fas fa-edit"></i></a>
                                            <select class="form-control d-none mt-3" name="blood_group" id="blood_groupField">
                                                <option value="" selected>--Select--</option>

                                                <option value="A+">A+</option>
                                                <option value="A-">A-</option>
                                                <option value="B+">B+</option>
                                                <option value="B-">B-</option>
                                                <option value="O+">O+</option>
                                                <option value="O-">O-</option>
                                                <option value="AB+">AB+</option>
                                                <option value="AB-">AB-</option>
                                            </select>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Gender: </label>
                                            {{ $employee->gender ?? '--' }}
                                            <a href="#" onclick="toggleInputField('genderField')"
                                            ><i class="fas fa-edit"></i></a>
                                            {{-- <input type="text" id="genderField" name="genderField" class="form-control d-none mt-3"
                                            placeholder="Enter Your correct Gender"> --}}
                                            <select id="genderField" name="gender" class="form-control d-none mt-3">
                                                <option value="">--Select--</option>

                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Religion: </label>
                                            {{ $employee->religion ?? '--' }}
                                            <a href="#" onclick="toggleInputField('religionField')"
                                            ><i class="fas fa-edit"></i></a>
                                            {{-- <input type="text" id="religionField" name="religionField" class="form-control d-none mt-3"
                                            placeholder="Enter Your correct Religion"> --}}
                                            <select id="religionField" name="religion" class="form-control d-none mt-3">
                                                <option value="">--Select--</option>

                                                <option value="Islam">Islam</option>
                                                <option value="Hindu">Hindu</option>
                                                <option value="Christian">Christian</option>
                                                <option value="Buddhist">Buddhist</option>
                                                <option value="Others">Others</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nationality: </label>
                                            {{ $employee->nationality ?? '--' }}
                                            <a href="#" onclick="toggleInputField('nationalityField')"
                                            ><i class="fas fa-edit"></i></a>
                                            {{-- <input type="text" id="nationalityField" name="nationalityField" class="form-control d-none mt-3"
                                            placeholder="Enter Your correct Nationality"> --}}
                                            <select id="nationalityField" name="nationality" class="form-control d-none mt-3">
                                                <option value="">--Select--</option>
                                                @foreach (config('services.countries') as $item)
                                                    <option value="{{ $item }}">{{ $item }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Marital Status: </label>
                                            {{ $employee->marital_status ?? '--' }}
                                            <a href="#" onclick="toggleInputField('marital_statusField')"
                                            ><i class="fas fa-edit"></i></a>
                                            {{-- <input type="text" id="marital_statusField" name="marital_statusField" class="form-control d-none mt-3"
                                            placeholder="Enter Your correct marital status"> --}}
                                            <select id="marital_statusField" name="marital_status" class="form-control d-none mt-3">
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
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>NID/Passport No: <sup class="text-danger">*</sup>.</label>
                                            {{ $employee->nid_or_passport ?? '--' }}
                                            <a href="#" onclick="toggleInputField('nid_or_passportField')"
                                            ><i class="fas fa-edit"></i></a>
                                            <input type="text" id="nid_or_passportField" name="nid_or_passport" class="form-control d-none mt-3"
                                            placeholder="Enter Your correct marital nid or passport">
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Department:</label>
                                            <br>
                                            <strong>
                                                {{ $employee->department?->super_department?->name ?? '' }}
                                            </strong>
                                            <br>
                                            <i>{{ $employee->department?->name }}</i>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-form-label">Designation:</label>
                                            <br>
                                            {{ $employee->designation?->name }}
                                        </div>
                                        <div class="form-group">
                                            <label for="">Employee type: </label>
                                            {{ $employee->employee_type }}
                                        </div>
                                        <div class="form-group">
                                            <label for="">Overtime: </label>
                                            {{ $employee->can_overtime ? 'Yes' : 'No' }}
                                        </div>
                                        <div class="form-group">
                                            <label for="">Outside Duty: </label>
                                            {{ $employee->can_outside_attendence ? 'Yes' : 'No' }}
                                        </div>
                                        <div class="form-group">
                                            <label for="">Get Late Check-In warning via SMS: </label>
                                            {{ $employee->get_attendance_sms_notification ? 'Yes' : 'No' }}
                                        </div>
                                        <div class="form-group">
                                            <label for="">Joining Date :</label>
                                            {{ carbon($employee->join_date)->format('d F, Y') }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            @if ($employee->picture_file)
                                                <img src="/uploaded_files/{{ $employee->picture_file }}" alt=""
                                                    class="w-100 mt-2">
                                            @endif
                                        </div>
                                        <div class="form-group">
                                            @if ($employee->signature_file)
                                                <label for="">Signature:</label>
                                                <img src="/uploaded_files/{{ $employee->signature_file }}" alt=""
                                                    class="w-100 mt-2">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <hr>
                                        <h4>Reference:</h4>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Name:</label>
                                                    {{ $employee->reference_name ?? '--' }}
                                                    <a href="#" onclick="toggleInputField('reference_nameField')"
                                                    ><i class="fas fa-edit"></i></a>
                                                    <input type="text" id="reference_nameField" name="reference_name" class="form-control d-none mt-3"
                                                    placeholder="Enter Your correct marital reference name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Phone:</label>
                                                    {{ $employee->reference_phone ?? '--' }}
                                                    <a href="#" onclick="toggleInputField('reference_phoneField')"
                                                    ><i class="fas fa-edit"></i></a>
                                                    <input type="text" id="reference_phoneField" name="reference_phone" class="form-control d-none mt-3"
                                                    placeholder="Enter Your correct marital reference phone">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h4>
                            Emergency Contact Info:
                        </h4>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Person Name:</label>
                                            {{ $employee->emergency_name ?? '--' }}
                                            <a href="#" onclick="toggleInputField('emergency_nameField')"
                                            ><i class="fas fa-edit"></i></a>
                                            <input type="text" id="emergency_nameField" name="emergency_name" class="form-control d-none mt-3"
                                            placeholder="Enter Your correct marital emergency name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Relation:</label>
                                            {{ $employee->emergency_relation ?? '--' }}
                                            <a href="#" onclick="toggleInputField('emergency_relationField')"
                                            ><i class="fas fa-edit"></i></a>
                                            <input type="text" id="emergency_relationField" name="emergency_relation" class="form-control d-none mt-3"
                                            placeholder="Enter Your correct marital emergency relation">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Phone :</label>
                                            {{ $employee->emergency_phone ?? '--' }}
                                            <a href="#" onclick="toggleInputField('emergency_phoneField')"
                                            ><i class="fas fa-edit"></i></a>
                                            <input type="text" id="emergency_phoneField" name="emergency_phone" class="form-control d-none mt-3"
                                            placeholder="Enter Your correct marital emergency phone">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Address:</label>
                                            {{ $employee->emergency_address ?? '--' }}
                                            <a href="#" onclick="toggleInputField('emergency_addressField')"
                                            ><i class="fas fa-edit"></i></a>
                                            <input type="text" id="emergency_addressField" name="emergency_address" class="form-control d-none mt-3"
                                            placeholder="Enter Your correct marital emergency address">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <b>Role:</b> {{ $employee->assinged_role?->name ?: 'None' }}
                            </div>
                        </div>

                        <div class="">

                            <input class="btn btn-primary" type="Submit"  value="Edit Request">
                            {{-- <a class="btn btn-primary" href="{{ route('employees.active.edit', $employee->id) }}">
                                <i class="fa fa-edit"></i> Edit Request
                            </a> --}}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script></script>
    <script src="{{ asset('assets/js/jquery.printPage.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.btnprn').printPage();
        });
    </script>
    <script>
        function toggleInputField(fieldId) {
        event.preventDefault();  // Prevent the default anchor behavior
        var inputField = document.getElementById(fieldId);
        inputField.classList.toggle('d-none');
    }
    </script>
@endsection
