@extends('layouts.master')
@section('title', 'Create Half Day Fine')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">

            <x-page-header :title="'Create Half Day Fine'" :breadcrumbs="[
                ['name' => 'Half Day Fine', 'route' => route('half-day-fines.index')],
                ['name' => 'Create', 'active' => true],
            ]" />

            <form id="half-day-fine-form" action="{{ route('half-day-fines.store') }}" method="POST">
                @csrf
                @method('POST')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Minutes<sup class="text-danger">*</sup>:</label>
                            <input type="number" step="0.01" name="minutes" class="form-control" min="0.00"
                                placeholder="0.00" required value="{{ old('minutes') }}">
                            @if ($errors->has('minutes'))
                                <span class="text-danger">{{ $errors->first('minutes') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Deduction Amount<sup class="text-danger">*</sup>:</label>
                            <input type="number" step="0.01" name="deduction_amount" class="form-control" min="0.00"
                                placeholder="0.00" required value="{{ old('deduction_amount') }}">
                            @if ($errors->has('deduction_amount'))
                                <span class="text-danger">{{ $errors->first('deduction_amount') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Amount Type<sup class="text-danger">*</sup>:</label>
                            <select class="select form-control" id="amount_type" name="amount_type" required>
                                <option value="">--Select--</option>
                                <option value="fixed">Fixed</option>
                                <option value="basicPercent">Basic Percent</option>
                                {{--  <option value="workingHour">Working Hour</option>  --}}
                            </select>
                            @if ($errors->has('amount_type'))
                                <span class="text-danger">{{ $errors->first('amount_type') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 text-center">
                        <button class="btn btn-primary px-5">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
