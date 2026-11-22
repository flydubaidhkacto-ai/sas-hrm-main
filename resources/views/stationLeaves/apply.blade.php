@extends('layouts.master')
@section('title', "Apply Leave")
@section('content')
    @php

            @endphp
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Apply Station Leave
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('leaves.history') }}">Leaves</a></li>
                            <li class="breadcrumb-item active">Apply</li>
                        </ul>
                    </div>
                </div>
            </div>
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <form action="{{ route('station.leave.apply.save') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="card">
                    <div class="card-body">

                        {{-- ================= Employee Information ================= --}}
                        <div class="mb-4 pb-3 border-bottom">
                            <h5 class="text-muted mb-3">Employee Information</h5>

                            @if(role_can('leave') || user()->admin_of_super_departments)

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Super Department <span
                                                    class="text-danger">*</span></label>
                                        <select name="super_department" id="super_department" class="form-control super-department-select2"
                                                required>
                                            @if(old('super_department'))
                                                <option value="{{ old('super_department') }}" selected>
                                                    {{ \App\Models\SuperDepartment::find(old('super_department'))?->name }}
                                                </option>
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Department <span class="text-danger">*</span></label>
                                        <select name="department" class="form-control department-select2" required>
                                            @if(old('department'))
                                                <option value="{{ old('department') }}" selected>
                                                    {{ \App\Models\Department::find(old('department'))?->name }}
                                                </option>
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Designation</label>
                                        <select name="designation" class="form-control designation-select2">
                                            @if(old('designation'))
                                                <option value="{{ old('designation') }}" selected>
                                                    {{ \App\Models\Designation::find(old('designation'))?->name }}
                                                </option>
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        Select Employee <span class="text-danger">*</span>
                                    </label>
                                    <select name="employee_id" class="form-control employee-select2" required>
                                        @if(old('employee_id'))
                                            <option value="{{ old('employee_id') }}" selected>
                                                {{ \App\Models\User::find(old('employee_id'))?->name }}
                                            </option>
                                        @endif
                                    </select>
                                    @error('employee_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                            @elseif(\App\Models\ApprovalProcess::canApproveEmployees('station_leave', user()))

                                <div class="mb-3">
                                    <label class="form-label">
                                        Select Employee <span class="text-danger">*</span>
                                    </label>
                                    <select name="employee_id" class="form-control init-select2" required>
                                        <option value="">Select an employee</option>

                                        <option value="{{ user()->id }}"
                                                {{ old('employee_id') == user()->id ? 'selected' : '' }}>
                                            {{ user()->name }}
                                        </option>

                                        @foreach(\App\Models\ApprovalProcess::canApproveEmployees('station_leave', user())['employees'] as $employee)
                                            <option value="{{ $employee->id }}"
                                                    {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                            @else
                                <input type="hidden" name="employee_id" value="{{ user()->id }}">
                            @endif
                        </div>

                        {{-- ================= Station Leave Details ================= --}}
                        <div class="mb-4">
                            <h5 class="text-muted mb-3">Station Leave Details</h5>

                            <div class="mb-3">
                                <label class="form-label">Station Name <span class="text-danger">*</span></label>
                                <input type="text" name="station" class="form-control"
                                       value="{{ old('station') }}" required>
                                @error('station')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">From Date <span class="text-danger">*</span></label>
                                    <input type="text" name="start_date"
                                           class="form-control datetimepicker"
                                           value="{{ old('start_date') }}" required>
                                    @error('start_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">To Date <span class="text-danger">*</span></label>
                                    <input type="text" name="end_date"
                                           class="form-control datetimepicker"
                                           value="{{ old('end_date') }}">
                                    @error('end_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Present Address <span class="text-danger">*</span></label>
                                <input type="text" name="present_address" class="form-control"
                                       value="{{ old('present_address') }}" required>

                                @error('present_address')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Address During Out of Station <span
                                            class="text-danger">*</span></label>
                                <input type="text" name="address_during_out_of_station" id="address_during_out_of_station" class="form-control"
                                       value="{{ old('address_during_out_of_station') }}" required>

                                @error('address_during_out_of_station')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_number" class="form-control"
                                           value="{{ old('contact_number') }}" required>

                                    @error('contact_number')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Alternative Number</label>
                                    <input type="text" name="alternative_number" class="form-control"
                                           value="{{ old('alternative_number') }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reason for Station Leave <span
                                            class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control" rows="3"
                                          required>{{ old('reason') }}</textarea>

                                @error('reason')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks_by_employee" class="form-control"
                                          rows="3">{{ old('remarks_by_employee') }}</textarea>
                            </div>

                            @if(config('services.leave_has_attachment'))
                                <div class="mb-3">
                                    <label class="form-label">Attachment</label>
                                    <input type="file" name="attachment" class="form-control">
                                    @error('attachment')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        {{-- ================= Submit ================= --}}
                        <div class="text-end pt-3 border-top">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fa fa-paper-plane"></i> Submit
                            </button>
                        </div>

                    </div>
                </div>
            </form>


        </div>
    </div>
@endsection
@section('script')

    <script>
        $(document).ready(function () {

            $('#super_department').on('change', function () {
                let selectedText = $(this).find('option:selected').text();

                if (selectedText) {
                    $('input[name="station"]').val(selectedText.trim());
                }
            });

            function loadEmployeeInfo(employeeId) {
                if (!employeeId) return;

                $.ajax({
                    url: "{{ route('employees.ajax.employee.employeeByID', ':id') }}"
                        .replace(':id', employeeId),
                    type: 'GET',
                    success: function (res) {

                        if (res.present_address) {
                            $('input[name="present_address"]').val(res.present_address);
                        }

                        if (res.station) {
                            $('input[name="station"]').val(res.station);
                        }

                        if (res.contact_number) {
                            $('input[name="contact_number"]').val(res.contact_number);
                        }
                    }
                });
            }

            /* =========================
               ON PAGE LOAD (hidden input)
            ========================== */
            let hiddenEmployeeId = $('input[name="employee_id"]').val();
            if (hiddenEmployeeId) {
                loadEmployeeInfo(hiddenEmployeeId);
            }

            /* =========================
               ON CHANGE (select dropdown)
            ========================== */
            $('select[name="employee_id"]').on('change', function () {
                let employeeId = $(this).val();
                loadEmployeeInfo(employeeId);
            });


        });
    </script>


@endsection

