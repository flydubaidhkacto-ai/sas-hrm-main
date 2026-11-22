@extends('layouts.master')
@section('title', 'Edit Interview Employee')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Interview Employee</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('employees.interview.index') }}">Interview
                                Employees</a></li>
                        <li class="breadcrumb-item active">Edit Interview Employee</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <form action="{{ route('employees.interview.update') }}" method="POST" enctype="multipart/form-data"
            onkeyup="$('.submit-btn').text('Update')">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{ $employee->id }}">
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <div class="form-group">
                        <label class="col-form-label">Employee Name <sup class="text-danger">*</sup>:</label>
                        <input type="text" name="name" id="name" class="form-control" required
                            value="{{ $employee->name }}">
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
                            value="{{ $employee->phone_number }}">
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
                            <option value="{{ $item->id }}" {{ $item->id == $employee->department_id ? 'selected' : ''
                                }}>
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
                            <option value="{{ $item->id }}" @if ($employee->designation_id == $item->id) selected
                                @endif>
                                {{ $item->name }}
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
                            Email:
                        </label>
                        <input class="form-control" type="email" id="email" name="email" value="{{ $employee->email }}">
                        @if ($errors->has('email'))
                        <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Interview Date <sup class="text-danger">*</sup></label>
                        <input type="text" class="form-control datetimepicker" required name="interview_date"
                            id="interview_date"
                            value="{{ $employee->interview_date ? carbon($employee->interview_date)->format('d-m-Y') : '' }}">
                        @if ($errors->has('interview_date'))
                        <span class="text-danger">{{ $errors->first('interview_date') }}</span>
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

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-form-label">
                            Expected Salary <sup class="text-danger">*</sup>:
                        </label>
                        <input class="form-control" type="number" id="expected_salary" name="expected_salary"
                            value="{{ $employee->expected_salary }}">
                        @if ($errors->has('expected_salary'))
                        <span class="text-danger">{{ $errors->first('expected_salary') }}</span>
                        @endif
                    </div>
                </div>

                {{-- <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-form-label">
                            Rating :
                        </label>
                        <input class="form-control" type="number" step="0.1" id="rating" value="{{ $employee->rating }}"
                            name="rating" max="10.00" min="0.00" required>
                        @if ($errors->has('rating'))
                        <span class="text-danger">{{ $errors->first('rating') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-form-label">Notes :</label>
                        <textarea class="form-control" type="text" id="notes"
                            name="notes">{{ $employee->notes }}</textarea>
                        @if ($errors->has('notes'))
                        <span class="text-danger">{{ $errors->first('notes') }}</span>
                        @endif
                    </div>
                </div> --}}
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="mt-2" id="nav-documents">
                        <h3>Documents</h3>
                        <div class="document-container">
                            @if ($employee->Interview_document->count() > 0)
                            <h4>Already uploaded documents:</h4>
                            @foreach ($employee->Interview_document as $document)
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
                                // alert(id)
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
            </div>
        </form>
    </div>
</div>
@endsection
@section('script')
<script>
    $('[image_upload_preview]').on('change', function() {
            console.log(URL.createObjectURL(this.files[0]));
            $(this).parent().find(this.getAttribute('image_upload_preview')).attr('src', URL.createObjectURL(this
                .files[0]));
        })
        $('.select2').select2({})
</script>
@endsection