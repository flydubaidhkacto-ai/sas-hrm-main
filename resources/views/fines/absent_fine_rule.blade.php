@extends('layouts.master')
@section('title', "Create Reward")
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper" onmousemove="calculateDuration()">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Absent Fine ( Monthly )
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('fines-absent.index') }}">Absent Fine</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <form action="{{ route('fines-absent.store') }}" method="POST">
                @csrf
                <div class="">
                   
                    <div class="row">   
                        <div class="col-sm-6 col-md-3">
                            <label for="">Absent Type</label>
                            <select name="absent_type" class="form-control">
                                <option value="0">Continously</option>
                                <option value="1">Random</option>
                            </select>                       
                        </div>

                        <div class="mb-2">
                            <label for="">Total Days</label>
                            <input type="number" name="days" class="form-control" value="0">
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <label for="">Fine Type</label>
                            <select name="fine_type" class="form-control">
                                <option value="0">Percent</option>
                                <option value="1">Amount</option>
                            </select>
                        </div>
                        
                        <div class="mb-2">
                            <label for="">Amount: (Tk / Percent)</label>
                            <input type="number" name="amount" class="form-control" value="">
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Store</button>
                </div>
            </form>
        </div>

        <div class="mt-3">
            <table class="table">
                <thead>
                    <tr>
                        <th>S.L</th>
                        <th>Absent Type</th>
                        <th>Total Days</th>
                        <th>Fine Type</th>
                        <th>Amount ( Tk / Percent )</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($absentFineRules as $key => $absentFineRule)
                        <td>{{ $key+1 }}</td>
                        <td>{{ $absentFineRule->absent_type == 1 ? "Conttinously" : "Random" }}</td>
                        <td>{{ $absentFineRule->days }}</td>
                        <td>{{ $absentFineRule->fine_type == 1 ? "Percent" : "Amount" }}</td>
                        <td>{{ $absentFineRule->amount }}</td>
                        <td>
                            <form action="" method="post">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('script')
    <script>

    </script>
@endSection
