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
                        Bulk Loan Entry
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active"><a href="{{ route('loans') }}">Loans</a></li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="{{ route('loans.create') }}" class="btn add-btn m-r-5">Apply Loans</a>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        <div class="mt-2">
            {!! Toastr::message() !!}
            <form action="{{ route('loans.bulk.apply') }}" method="post">
                @csrf
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="">Loan Date</label>
                            <input type="date" class="form-control" name="date" required>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <button class="btn btn-primary">
                        <i class="fa fa-check"></i>
                        Save Loans
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
                                <th>Loan Amount</th>
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
                                    <input type="number" class="form-control" value="0"
                                        name="amount[{{ $employee->id }}]">
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

{{-- data table cdn --}}
<link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let table = new DataTable('table', {
            pageLength: {{ $employees->count() }},
        });
</script>
@endsection
