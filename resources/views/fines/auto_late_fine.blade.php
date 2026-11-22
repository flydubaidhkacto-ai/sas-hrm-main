@extends('layouts.master')
@section('title', "Auto Late/Early-out fine")
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Auto Late/Early-out fine
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('fines.index') }}">Fine</a></li>
                            <li class="breadcrumb-item active">Set auto late/early-out fines</li>
                        </ul>
                    </div>
                </div>
            </div>
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <form action="{{ route('fines.auto-late-fine.store') }}" method="POST">
                @csrf
                <div class="">
                    <div>
                        If employee
                        <select name="type" id="" required>
                            <option value="late">gets Late</option>
                            <option value="early-out">do Early-out</option>
                        </select>
                        for <input type="number" name="late_days_amount" value="{{old('late_days_amount')}}" style="width: 80px" required> days
                        <select name="late_days" id="" required>
                            <option value="consecutive_days">Consecutively</option>
                            <option value="any_days_on_month">Any day in the month</option>
                        </select>
                    </div>
                    <div class="mt-1">
                        create a fine of <input type="number" name="fine_amount" value="{{old('fine_amount')}}" style="width: 100px"> Tk (Fixed)
                        --- OR ---
                    {{-- </div>
                    <div class="mt-1"> --}}
                        the Salary of <input type="number" name="fine_amount_days" value="{{old('fine_amount_days')}}" style="width: 100px"> working days
                    </div>
                    <div class="mt-1">
                        and the fine will be <select name="fine_status" id="" required>
                            <option value="approved">Approved</option>
                            <option value="pending" selected>Pending</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"> <i class="fa fa-plus"></i> Add</button>
                </div>
            </form>
            <div class="mt-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th>S.L</th>
                            <th>Rule</th>
                            <th>
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div>
                                        If employee
                                        @if($item->type === 'late')
                                            gets late
                                        @else
                                            do early-out
                                        @endif
                                        for {{$item->late_days_amount}} days
                                        {{ $item->late_days == 'consecutive_days' ? 'Consecutively' : 'Any day in the month' }}
                                    </div>
                                    <div class="mt-1">
                                        create a fine of
                                        @if ($item->fine_amount_type === 'fixed')
                                            {{ $item->fine_amount }} Tk (Fixed)
                                        @else
                                            the amount same as his/her salary of {{ $item->fine_amount }} working days
                                        @endif
                                    </div>
                                    <div class="mt-1">
                                        and the fine will be
                                        {{ $item->fine_status == 'approved' ? 'Approved' : 'Pending' }}
                                    </div>
                                </td>
                                <td>
                                    <form action="{{ route('fines.auto-late-fine.delete', $item->id) }}" method="post">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
    </script>
@endSection
