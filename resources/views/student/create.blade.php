@extends('layouts.master')
@section('title', "Create Student")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        {{-- student tab --}}
        {{-- <div class="mb-3">
            @include('tabs.student')
        </div> --}}

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Create Student</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ul>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        <form id="employee-form" action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data"
            autocomplete="new-password">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-form-label">Name<sup class="text-danger">*</sup>:</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                        @if ($errors->has('name'))
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>
                {{-- <div class="col-md-6 mx-auto">
                    <div class="form-group">
                        <label class="col-form-label">Student ID:</label>
                        <input type="text" name="employee_id" class="form-control" autocomplete="new-password"
                            value="{{ old('employee_id') }}" />
                    </div>
                </div> --}}
                <div class="col-md-3 mx-auto">
                    <div class="form-group">
                        <label class="col-form-label">Class:</label>
                        <select class="form-control select2" name="class_id">
                            <option value="">-- Select Class --</option>
                            @foreach(\App\Models\StudentClass::all() as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('class_id'))
                            <span class="text-danger">{{ $errors->first('class_id') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="col-form-label">Department <sup class="text-danger">*</sup>:</label>
                        <br>
                        <select class="form-control select2" id="department" name="department" required>
                            <option value="">-- Select --</option>
                            @foreach (!role_can('user') ? user()->department?->super_department?->departments()->ordered()->get()
                            ?? [] : \App\Models\Department::ordered()->get() as $item)
                            <option value="{{ $item->id }}" {{ $item->id == old('department') ? 'selected' : '' }}>
                                {{ $item->super_department->name }}:
                                {{ $item->name }}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('department'))
                        <span class="text-danger">{{ $errors->first('department') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="col-form-label">Roll (optional):</label>
                        <input type="text" name="roll" class="form-control" value="{{ old('roll') }}">
                        @if ($errors->has('roll'))
                            <span class="text-danger">{{ $errors->first('roll') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="col-form-label">Registration (optional):</label>
                        <input type="text" name="registration" class="form-control" value="{{ old('registration') }}">
                        @if ($errors->has('registration'))
                            <span class="text-danger">{{ $errors->first('registration') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-form-label">Shift Name:</label>
                        <select class="form-control select2" id="shift_id" name="shift_id[]" required multiple>
                            <option value="">-- Select --</option>
                            @foreach ($shifts as $item)
                            <option value="{{ $item->id }}" {{ $item->id == old('shift_id')? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('shift_id'))
                        <span class="text-danger">{{ $errors->first('shift_id') }}</span>
                        @endif
                    </div>
                </div>

                {{-- <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Picture:</label>
                        <input type="file" class="form-control" name="picture_file" accept="image/*"
                            image_upload_preview=".picture">
                        <img src="" alt="" class="w-100 picture mt-2">
                        @if ($errors->has('picture_file'))
                        <span class="text-danger">{{ $errors->first('picture_file') }}</span>
                        @endif
                    </div>
                </div> --}}

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Admission Date <sup class="text-danger">*</sup></label>
                        <div class="cal-icon">
                            <input type="text" class="form-control datetimepicker" type="text" id="join_date" required
                                name="join_date" value="{{ old('join_date') }}">
                        </div>
                        @if ($errors->has('join_date'))
                        <span class="text-danger">{{ $errors->first('join_date') }}</span>
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

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-form-label">Father's name:
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
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Date of Birth:</label>
                        <div class="cal-icon">
                            <input class="form-control datetimepicker" type="text" id="birth_date" name="birth_date"
                                value="{{ old('birth_date') }}">
                        </div>
                        @if ($errors->has('birth_date'))
                        <span class="text-danger">{{ $errors->first('birth_date') }}</span>
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
                        <label>Login Password: (optional)</label>
                        <input class="form-control" type="password" id="password" name="password"
                            autocomplete="new-password" />
                        @if ($errors->has('password'))
                        <span class="text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>ID Card Number: (optional)</label>
                        <input class="form-control" type="" id="id_card_number" name="id_card_number"
                            autocomplete="new-password" value="{{ old('id_card_number') }}" />
                        @if ($errors->has('id_card_number'))
                        <span class="text-danger">{{ $errors->first('id_card_number') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Out Side Punch</label>
                        <select class="form-control" required name="can_outside_attendence">
                            <option value="1" @if (old('can_outside_attendence')==='1' ) selected @endif>Yes
                            </option>
                            <option value="0" @if (old('can_outside_attendence')==='0' ) selected @endif>No
                            </option>
                        </select>
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

            </div>
            <div class="row">
                <div class="col-md-12">
                    <button class="btn-block btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@section('script')
<script>
    $('[image_upload_preview]').on('change', function() {
            console.log(URL.createObjectURL(this.files[0]));
            $(this).parent().find(this.getAttribute('image_upload_preview')).attr('src', URL.createObjectURL(this.files[0]));
        })
        $('.select2').select2({})
</script>
@endsection
