@extends('layouts.master')
@section('title', 'Yearly Leave Balance')
@section('content')
{{-- message --}}
{!! Toastr::message() !!}
<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-md-7">
                    <h3 class="page-title">
                        Yearly Leave Balance (
                        <select name="year" id=""
                            onchange="location = '{{ route('leaves.balance') }}?year=' + this.value">
                            @foreach ([-3, -2, -1, 0, 1, 2, 3] as $i)
                            <option value="{{ date('Y') + $i }}" {{ $year==date('Y') + $i ? 'selected' : '' }}>
                                {{ date('Y') + $i }}
                            </option>
                            @endforeach
                        </select>
                        ):
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">
                            Yearly Leave Balance
                        </li>
                    </ul>
                </div>
                <div class="col-12">
                    @if (role_can('leave') || role_can('leave.balance-assign'))
                    <form method="POST" style="" class="" action="{{ route('leaves.balance.update.default') }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        @csrf
                        <div class="row" onchange="$(this).find('button').removeClass('d-none')"
                            onkeydown="$(this).find('button').removeClass('d-none')">
                            @foreach ($types->where('is_editable', true)->filter(fn($t) => ($t->affects_leave_balance ?? true)) as $type)
                            <div class="col-md-4">
                                <label for="">{{ $type->name }}:</label>
                                <input type="number" step="0.01" name="types[{{ $type->id }}]" id=""
                                    class="form-control" min="0" max="365"
                                    value="{{ \App\Models\LeaveBalance::employee_balance(0, $type->id, $year)['limit'] }}">
                            </div>
                            @endforeach
                            <div class="col-sm-12 mt-1">
                                <button type="submit" class="btn btn-sm btn-success d-none">Save</button>
                            </div>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        @include('layouts.alerts')
        <div>
            <form action="" class="row filter-row mb-4">
                @if (role_can('reports') || role_can('reports.self.super_department'))
                @if (!role_can('reports') && role_can('reports.self.super_department'))
                <div class="mb-2 col-sm-6 col-md-3">
                    <label for="">Super Department</label>
                    <input type="text" value="{{ user()->department?->super_department?->name }}" class="form-control"
                        readonly>
                    <input type="hidden" name="super_department" value="{{ user()->department?->super_department_id }}"
                        class="form-control">
                    <script>
                        window.selected_super_department = '{{ user()->department?->super_department_id }}';
                    </script>
                </div>
                @else
                <div class="mb-2 col-sm-6 col-md-3">
                    <label for="">Super Department</label>
                    <select name="super_department" id="" class="form-control super-department-select2">
                        @if (request()->super_department)
                        <option value="{{ request()->super_department }}" selected>
                            {{ \App\Models\SuperDepartment::find(request()->super_department)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                @endif
                <div class="mb-2 col-sm-6 col-md-3">
                    <label for="">Department</label>
                    <select name="department" id="" class="form-control department-select2">
                        @if (request()->department)
                        <option value="{{ request()->department }}" selected>
                            {{ \App\Models\Department::find(request()->department)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="mb-2 col-sm-6 col-md-3">
                    <label for="">Designation</label>
                    <select name="designation" id="" class="form-control designation-select2">
                        @if (request()->designation)
                        <option value="{{ request()->designation }}" selected>
                            {{ \App\Models\Designation::find(request()->designation)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="mb-2 col-sm-6 col-md-3">
                    <label for="">Employee</label>
                    <select name="employee" id="" class="form-control employee-select2">
                        @if (request()->employee)
                        <option value="{{ request()->employee }}" selected>
                            {{ \App\Models\User::find(request()->employee)?->name }}
                        </option>
                        @endif
                    </select>
                </div>
                @endif
                <div class="col-sm-6 col-md-3 mt-1">
                    <button type="submit" class="btn btn-sm btn-success">Search</button>
                </div>
            </form>
        </div>
        <div class="mt-2">
            <div class="table-responsive">
                <div class="exports">
                    {{-- @include('layouts.exports_excel') --}}
                    <div style="text-align: end" class="mb-2">
                        {{-- @if ($no_docx ?? false)
                        @else
                        <a href="{{ add_query_to_current_route(['export' => 'docx']) }}" class="btn btn-sm btn-info"
                            target="_blank">
                            <i class="fa fa-file-word-o"></i>
                            Docx
                        </a>
                        @endif --}}
                        {{-- <a href="{{ add_query_to_current_route(['export' => 'xlsx']) }}"
                            class="btn btn-sm btn-info" target="_blank">
                            <i class="fa fa-file-excel"></i>
                            Excel
                        </a> --}}
                        <a href="{{ add_query_to_current_route(['export' => 'pdf']) }}" class="btn btn-sm btn-info"
                            target="_blank">
                            <i class="fa fa-file-pdf"></i>
                            PDF
                        </a>
                        <a href="{{ add_query_to_current_route(['export' => 'print']) }}" class="btn btn-sm btn-info"
                            target="_blank">
                            <i class="fa fa-print"></i>
                            Print
                        </a>
                    </div>
                </div>
                @include('leaves.list_balance', [
    'employees' => $employees,
    'types' => $types,
    'year' => $year
])

<div class="mt-3 d-flex justify-content-between align-items-center flex-wrap">
    <div class="mb-2">
        Showing {{ $employees->firstItem() ?? 0 }}
        to {{ $employees->lastItem() ?? 0 }}
        of {{ $employees->total() }} employees
    </div>

    <div class="mb-2">
        {{ $employees->onEachSide(1)->links('pagination::bootstrap-4') }}
    </div>
</div>
                <div class="mt-2">
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->
@endsection
@section('script')
<script></script>
@endsection