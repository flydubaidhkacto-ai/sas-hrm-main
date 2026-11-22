@extends('layouts.master')
@section('title', "Total Duty Report")
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Total Duty Report</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Total Duty Report</li>
                    </ul>
                </div>
            </div>
        </div>

        <form class="mb-3 generate">
            <div class="row filter-row">
                @if(
                    role_can('reports')
                    || user()->admin_of_super_departments
                    || role_can('reports.self.super_department')
                )
                <div class="mt-2 col-sm-6 col-md-3">
                    <label for="">Super Department</label>
                    @if(
                        !role_can('reports')
                        && !user()->admin_of_super_departments
                        && role_can('reports.self.super_department')
                    )
                    <input type="text" class="form-control" value="{{ user()->department?->super_department?->name }}"
                        readonly>
                    <input type="hidden" name="super_department" class="form-control"
                        value="{{ user()->department?->super_department_id }}">
                    <script>
                        window.selected_super_department = '{{ user()->department?->super_department_id }}';
                    </script>
                    @else
                    <select name="super_department" id="" class="form-control super-department-select2">
                        @if(request()->super_department)
                        <option value="{{ request()->super_department }}" selected>
                            {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                        </option>
                        @endif
                    </select>
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
                @elseif(user()->managed_employees->count() > 0)
                <div class="mb-2 col-12">
                    <label for="">Employee</label>
                    <select name="employee" id="" class="form-control init-select2">
                        <option value="{{ user()->id }}">{{ user()->name }}</option>
                        @foreach(user()->managed_employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <input type="hidden" name="employee" value="{{ user()->id }}">
                @endif
                <div class="mt-2 col-sm-6 col-md-3">
                    <div class="form-group form-focus">
                        <div class="cal-icon">
                            <input class="form-control floating datetimepicker" type="text" value="" name="start_date"
                                required>
                        </div>
                        <label class="focus-label">From</label>
                    </div>
                </div>
                <div class="mt-2 col-sm-6 col-md-3">
                    <div class="form-group form-focus">
                        <div class="cal-icon">
                            <input class="form-control floating datetimepicker" type="text" value="" name="end_date"
                                required>
                        </div>
                        <label class="focus-label">To</label>
                    </div>
                </div>
                <div class="mt-2 col-12 mt-2">
                    <button type="submit" class="btn btn-success">
                        Generate
                    </button>
                </div>
            </div>
        </form>
        <div class="mt-2 generator-status w-100 d-none">
            <h5>Status</h5>
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0"
                    aria-valuemax="100">0%</div>
            </div>
            <div class="mt-2 text-warning">
                <i>
                    Please wait while we generate your report
                </i>
            </div>
        </div>

        <div class="result-view">
            <div class="export" style="text-align: end">
                <form action="{{ route('reports.total_duty_report.create_view', ['export' => 'xlsx']) }}"
                    target="_blank" method="POST" class="d-inline-block">
                    @csrf
                    <input type="hidden" name="data" value="">
                    <button type="submit" class="btn btn-sm btn-info">
                        <i class="fa fa-file-excel-o"></i>
                        Excel
                    </button>
                </form>
                <form action="{{ route('reports.total_duty_report.create_view', ['export' => 'pdf']) }}" target="_blank"
                    method="POST" class="d-inline-block">
                    @csrf
                    <input type="hidden" name="data" value="">
                    <button type="submit" class="btn btn-sm btn-info">
                        <i class="fa fa-file-pdf-o"></i>
                        PDF
                    </button>
                </form>
                <form action="{{ route('reports.total_duty_report.create_view', ['export' => 'print']) }}"
                    target="_blank" method="POST" class="d-inline-block">
                    @csrf
                    <input type="hidden" name="data" value="">
                    <button type="submit" class="btn btn-sm btn-info">
                        <i class="fa fa-print"></i>
                        Print
                    </button>
                </form>
            </div>
            <div class="view"></div>
        </div>
    </div>
</div>
<!-- /Page Wrapper -->
@endsection
@section('script')
<script>
    $('.generate').on('submit', function(e){
            e.preventDefault()
            $('.generator-status').addClass('d-none')
            let form = $(this)
            let url = "{{ route("reports.total_duty_report.generate") }}"
            let super_department = form.find('[name=super_department]').val()
            let department = form.find('[name=department]').val()
            let designation = form.find('[name=designation]').val()
            let employee = form.find('[name=employee]').val()

            form.addClass('d-none')
            $('.generator-status').removeClass('d-none')
            $('.generator-status .progress-bar').css('width', '0%')
            $('.generator-status .progress-bar').text('0%')

            // get users list
            $.get(
                "{{ route('employees.select2') }}",
                {
                    super_department,
                    department,
                    designation,
                    employee,
                    no_limit: 1
                },
            )
            .then(async function(res) {
                let users = res
                let data = []
                $('.generator-status .progress-bar').css('width', '1%')
                $('.generator-status .progress-bar').text('1%')
                for(let index = 0; index < users.length; index++) {
                    const user = users[index];
                    await $.ajax({
                        url: url + '?from=' + form.find('[name=start_date]').val() + '&to=' + form.find('[name=end_date]').val() + '&employee=' + user.id,
                        type: 'GET',
                        success: function(response) {
                            data.push(response)
                        },
                    })
                    let percentage = (((index + 1) / users.length) * 100).toFixed(0)
                    $('.generator-status .progress-bar').css('width', (percentage) + '%')
                    $('.generator-status .progress-bar').text((percentage) + '%')
                }
                create_html(data, users)
            })
            .catch(function(error) {
                $('.generator-status').removeClass('d-none')
                $('.generator-status').html(`
                    <div class="alert alert-danger">
                        Failed to generate: ${error.responseJSON.message}
                    </div>
                `)
            })
            return
        })
        function create_html(data, users)
        {
            $('.result-view .export [name=data]').val(JSON.stringify(data))
            $('.generator-status').html(`
                <div class="alert alert-success">
                    Generating view...
                </div>
            `)
            $.post(
                "{{ route('reports.total_duty_report.create_view') }}",
                {
                    data: JSON.stringify(data),
                }
            )
            .then(function(res) {
                $('.generator-status').addClass('d-none')
                console.log(res);
                $('.result-view .view').html(res)
            })
            .catch(function(error) {
                $('.generator-status').removeClass('d-none')
                $('.generator-status').html(`
                    <div class="alert alert-danger">
                        Failed to generate: ${error.responseJSON.message}
                    </div>
                `)
            })
        }
</script>
@endsection
