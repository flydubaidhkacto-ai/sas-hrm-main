@extends('layouts.master')
@section('title', "Payslip Generate")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"> Payslip:
                        @if (request()->reset)
                        Reset
                        @else
                            Generate
                        @endif
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Payslip</li>
                    </ul>
                </div>
            </div>

            @php
                $can = role_can('payslip.generate') || role_can('payslip.generate.self.super_department') || user()->admin_of_super_departments;
            @endphp
            @if(($create_hidden ?? true) && $can)
                <div class="col-auto float-right ml-auto text-right">
                    <a href="{{ route('payslip.create', ['reset' => 1]) }}">
                        Reset payslips
                    </a>
                </div>
            @endif
        </div>
        <!-- /Page Header -->
        {!! Toastr::message() !!}
        <!-- Search Filter -->
        <form @if (request()->reset)
            class="mb-3" action="{{ route('payslip.generate') }}" method="POST"
            @else
            class="mb-3 generate"
            @endif
            >
            @if (request()->reset) @csrf @endif
            <div class="row filter-row">
                <div class="mt-2 col-sm-6 col-md-3">
                    <label for="">Super Department</label>
                    @if(
                        role_can('payslip.generate') || user()->admin_of_super_departments
                    )
                    <select name="super_department" id="" class="form-control super-department-select2">
                        @if(request()->super_department)
                        <option value="{{ request()->super_department }}" selected>
                            {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                        </option>
                        @endif
                    </select>
                    @else
                    <input type="text" class="form-control" value="{{ user()->department?->super_department?->name }}"
                        readonly>
                    <input type="hidden" name="super_department" class="form-control"
                        value="{{ user()->department?->super_department_id }}">
                    <script>
                        window.selected_super_department = '{{ user()->department?->super_department_id }}';
                    </script>
                    @endif
                </div>
                <div class="mt-2 col-sm-6 col-md-3">
                    <label for="">Department</label>
                    <select name="department" id="" class="form-control department-select2">
                        @if(request()->department)
                        <option value="{{ request()->department }}" selected>
                            {{ \App\Models\Department::find(request()->department)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="mt-2 col-sm-6 col-md-3">
                    <label for="">Designation</label>
                    <select name="designation" id="" class="form-control designation-select2">
                        @if(request()->designation)
                        <option value="{{ request()->designation }}" selected>
                            {{ \App\Models\Designation::find(request()->designation)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="mt-2 col-sm-6 col-md-3">
                    <label for="">Employee</label>
                    <select name="employee" id="" class="form-control employee-select2">
                        @if(request()->employee)
                        <option value="{{ request()->employee }}" selected>
                            {{ \App\Models\User::find(request()->employee)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="mt-2 col-sm-6 col-md-3">
                    <label for="">Month</label>
                    <input name="month" type="text" class="form-control month-year-picker"
                        value="{{ now()->subMonth()->format('m-Y') }}" required>
                </div>
                @if (request()->reset)
                <input type="hidden" name="reset" value="1">
                @endif
                <div class="mt-2 col-12 mt-2">
                    <button type="submit" class="btn btn-success">
                        @if (request()->reset)
                        Reset
                        @else
                        Generate
                        @endif
                    </button>
                </div>
            </div>
        </form>
        <div class="mt-2 generator-status w-100 d-none">
            <h5>Status</h5>
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 1%;" aria-valuenow="25" aria-valuemin="0"
                    aria-valuemax="100">1%</div>
            </div>
            <div class="mt-2 text-warning">
                <i>
                    Please wait while we generate your payslip
                </i>
            </div>
        </div>
    </div>
</div>
<!-- /Page Wrapper -->
@endsection
@section('script')
<script>
    $('.generate').on('submit', function(e){
            e.preventDefault()
            let form = $(this)
            let url = "{{ route("payslip.generate") }}"
            let super_department = form.find('.super-department-select2').val()
            let department = form.find('.department-select2').val()
            let designation = form.find('.designation-select2').val()
            let employee = form.find('.employee-select2').val()
            let month = form.find('.month-year-picker').val()
            let paytype = form.find('.paytype').val()
            let data = {
                super_department,
                department,
                designation,
                employee,
                month,
            }
            form.addClass('d-none')
            $('.generator-status').removeClass('d-none')
            $('.generator-status .progress-bar').css('width', '1%')
            $('.generator-status .progress-bar').text('1%')
            // console.log(data);
            $.ajax({
                url,
                type: 'POST',
                data,
                success: async function(response) {
                    if(response.total_user_pages)
                    {
                        for (let index = 1; index <= response.total_user_pages; index++)
                        {
                            let data2 = {
                                ...data,
                                page: index,
                                step_2: 1,
                                paytype,
                            }
                            await $.ajax({
                                url,
                                type: 'POST',
                                data: data2,
                                success: function(res) {
                                    if(res)
                                    {
                                        $('.generator-status .progress-bar').css('width', `${(index / response.total_user_pages) * 100}%`)
                                        $('.generator-status .progress-bar').text(`${Math.round((index / response.total_user_pages) * 100)}%`)
                                    }
                                } ,
                                error: function(error) {
                                    $('.generator-status').html(`
                                        <div class="alert alert-danger">
                                            Failed to generate: ${error.responseJSON.message}
                                        </div>
                                    `)
                                }
                            });
                        }
                        setTimeout(() => {
                            location = "{{ route("payslip.draft") }}?" + form.serialize();
                        }, 2000);
                    }
                    else {
                        alert('No Employee found with a salary card!')
                        setTimeout(() => {
                            location = "{{ route("payslip.draft") }}?" + form.serialize();
                        }, 1000);
                    }
                },
                error: function(error) {
                    $('.generator-status').html(`
                        <div class="alert alert-danger">
                            Failed to generate: ${error.responseJSON.message}
                        </div>
                    `)
                }
            })
        })
</script>
@endsection
