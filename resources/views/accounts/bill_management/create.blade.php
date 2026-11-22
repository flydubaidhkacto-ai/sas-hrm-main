@extends('layouts.master')
@section('title', 'Bills')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Bills</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('bills.index') }}">Bills</a></li>
                            <li class="breadcrumb-item active">Create Bill</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            {!! Toastr::message() !!}

            <form action="{{ route('bills.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="mt-2 col-sm-6 col-md-6 form-group">
                        <label for="bill_number">Bill Number</label>
                        <input type="text" class="form-control" id="bill_number" name="bill_number"
                            value="{{ old('bill_number') }}" required>
                        @error('bill_number')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mt-2 col-sm-6 col-md-6 form-group">
                        <label for="vendor_id">Vendor</label>
                        <select name="vendor_id" id="vendor_id" class="form-control select2" required>
                            <option value="">Select Vendor</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                        @error('vendor_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="mt-2 col-sm-6 col-md-6 form-group">
                        <label for="due_date">Due Date</label>
                        <input type="text" class="form-control datetimepicker" id="due_date" name="due_date"
                            value="{{ old('due_date') }}" required onchange="calculateDuration()">
                        @error('due_date')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mt-2 col-sm-6 col-md-6 form-group">
                        <label for="amount_due">Due Amount</label>
                        <input type="number" step="0.01" class="form-control" id="amount_due" name="amount_due"
                            value="{{ old('amount_due') }}" required>
                        @error('amount_due')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="mt-2 col-sm-6 col-md-6 form-group">
                        <label for="amount_paid">Paid Amount</label>
                        <input type="number" step="0.01" class="form-control" id="amount_paid" name="amount_paid"
                            value="{{ old('amount_paid') }}" required>
                        @error('amount_paid')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mt-2 col-sm-6 col-md-6 form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control select2" required>
                            <option value="pending">Pending</option>
                            <option value="partially paid">Partially Paid</option>
                            <option value="paid">Paid</option>
                        </select>
                        @error('status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="mt-2 col-sm-6 col-md-6 form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>


                <div class="form-group row mt-2">
                    <div class="mx-auto">
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- /Page Wrapper -->
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });

        calculateDuration = () => {
            let start_date = $('.datetimepicker[name="start_date"]').val();
            let end_date = $('.datetimepicker[name="end_date"]').val();
            if(!start_date || !end_date) return
            start_date = moment(start_date, 'DD-MM-YYYY');
            end_date = moment(end_date, 'DD-MM-YYYY');
            const duration = (end_date.diff(start_date, 'days'));
            const days = duration + 1;
            if(days < 1)
            {
                return document.getElementById('leave_duration').innerText = 'Invalid date';
            }
            document.getElementById('leave_duration').innerText = days + ' ' + (days > 1 ? 'days' : 'day');
        }
    </script>
@endsection
