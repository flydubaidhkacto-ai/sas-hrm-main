@extends('layouts.master')
@section('title', 'Loans')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Bulk Deduction Entry: {{ ucfirst($type) }}
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active"><a href="{{ route('deductions.index') }}">Deductions</a></li>
                    </ul>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        <div class="mt-2">
            {{-- <form method="get" action="{{ route('loans') }}" class="row filter-row mb-4">
                @if (role_can('loans') || \App\Models\Loan::can_approve('loan', user()))
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
                @else
                <select name="employee" id="" class="form-control d-none">
                    <option value="{{ user()->id }}" selected>
                        {{ user()->name }}
                    </option>
                </select>
                @endif
                <div class="mb-2 col-sm-6 col-md-3">
                    <button type="submit" class="btn btn-success btn-block"> Search </button>
                </div>
            </form> --}}
            {!! Toastr::message() !!}
            <form action="{{ route('deductions.bulk.store') }}" method="post">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="">Entry Date</label>
                            <input type="date" class="form-control" name="date" required>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <button class="btn btn-primary">
                        <i class="fa fa-check"></i>
                        Save
                    </button>
                </div>
                <div class="table-responsive mt-2">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ref.</th>
                                <th>Emp. ID</th>
                                <th>Employee</th>
                                <th>Sup. Dept</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employees as $employee)
                            <tr>
                                <td>
                                    {{ $employee->id }}
                                </td>
                                <td>
                                    {{ $employee->employee_id }}
                                </td>
                                <td>
                                    <h2 class="table-avatar">
                                        <a href="{{ route('employees.active.show', $employee->id) }}">
                                            {{ $employee?->name }}
                                        </a>
                                    </h2>
                                </td>
                                <td>
                                    {{ $employee->department?->super_department?->name }}
                                </td>
                                <td>
                                    {{ $employee->department?->name }}
                                </td>
                                <td>
                                    {{ $employee->designation?->name }}
                                </td>
                                <td>
                                    <input type="number" class="form-control" min="0" value="0"
                                        name="amount[{{ $employee->id }}]">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#deduction-item-entries-{{ $employee->id }}">
                                        <i class="fa fa-list"></i>
                                        Saved Items in last 30 days ({{ $employee->deductionItemEntries()
                                        ->whereBetween('date', [now()->subDays(30), now()])
                                        ->where('name', $type)->count() }})
                                    </button>
                                </td>
                            </tr>
                            @empty
                            {{-- <tr>
                                <th colspan="6" class="text-center">
                                    No Data Found
                                </th>
                            </tr> --}}
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </form>
        </div>
        <div class="mt-2">
            {{-- {{ $data->withQueryString()->links() }} --}}
        </div>
    </div>
</div>
</div>

@foreach ($employees as $employee)
<div class="modal fade" id="deduction-item-entries-{{ $employee->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Saved Items in last 30 days
                </h5>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ref.</th>
                            <th>Date</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (
                        $employee->deductionItemEntries()
                        ->whereBetween('date', [now()->subDays(30), now()])
                        ->where('name', $type)->get() as $item
                        )
                        <tr>
                            <td>
                                {{ $item->id }}
                            </td>
                            <td>{{ $item->date }}</td>
                            <td>{{ $item->amount }}</td>
                        </tr>
                        @empty

                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endforeach


{{-- data table cdn --}}
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('table', {
            pageLength: {{ $employees->count() }},
        });
</script>
@endsection
