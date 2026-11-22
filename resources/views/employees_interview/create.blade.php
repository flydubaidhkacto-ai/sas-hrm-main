@extends('layouts.master')
@section('title', "Create Interview Employee")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Create Interview Employee</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('employees.interview.index') }}">Interview
                                Employees</a></li>
                        <li class="breadcrumb-item active">Create Interview Employee</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->



        @include('layouts.alerts')
        <form id="employee-form" action="{{ route('employees.interview.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6 mx-auto">
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
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-form-label">Designation <sup class="text-danger">*</sup>:</label>
                        <br>
                        <select class="form-control select2" id="designation" name="designation" required>
                            <option value="">-- Select --</option>
                            @foreach (\App\Models\Designation::ordered()->get() as $item)
                            <option value="{{ $item->id }}" @if (old('designation')==$item->id) selected @endif>{{
                                $item->name }}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('designation'))
                        <span class="text-danger">{{ $errors->first('designation') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-form-label">
                            Email: <sup class="text-danger">*</sup>:
                        </label>
                        <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}">
                        @if ($errors->has('email'))
                        <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Interview Date <sup class="text-danger">*</sup></label>
                        <div class="cal-icon">
                            <input type="text" class="form-control datetimepicker" type="text" id="interview_date"
                                required name="interview_date" value="{{ old('interview_date') }}">
                        </div>
                        @if ($errors->has('interview_date'))
                        <span class="text-danger">{{ $errors->first('interview_date') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-form-label">
                            Expected Salary <sup class="text-danger">*</sup>:
                        </label>
                        <input class="form-control" type="number" id="expected_salary" name="expected_salary"
                            value="{{ old('expected_salary') }}">
                        @if ($errors->has('expected_salary'))
                        <span class="text-danger">{{ $errors->first('expected_salary') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-form-label">Address <sup class="text-danger">*</sup>:</label>
                        <textarea class="form-control" type="text" id="address"
                            name="address">{{ old('address') }}</textarea>
                        @if ($errors->has('address'))
                        <span class="text-danger">{{ $errors->first('address') }}</span>
                        @endif
                    </div>
                </div>

                {{-- <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-form-label">
                            Rating :
                        </label>
                        <input class="form-control" type="number" step="0.1" id="rating" value="{{ old('rating') }}"
                            name="rating" max="10.00" min="0.00" required>
                        @if ($errors->has('rating'))
                        <span class="text-danger">{{ $errors->first('rating') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-form-label">Notes :</label>
                        <textarea class="form-control" type="text" id="notes" name="notes">{{ old('notes') }}</textarea>
                        @if ($errors->has('notes'))
                        <span class="text-danger">{{ $errors->first('notes') }}</span>
                        @endif
                    </div>
                </div> --}}
            </div>
            <div class="row">
                <div class="col-md-12">
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