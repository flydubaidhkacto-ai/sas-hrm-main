@extends('layouts.master')
@section('title', 'Edit Half Day Fine')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">

            <x-page-header :title="'Edit Half Day Fine'" :breadcrumbs="[
                ['name' => 'Half Day Fine', 'route' => route('half-day-fines.index')],
                ['name' => 'Edit', 'active' => true],
            ]" />

            <form id="half-day-fine-form" action="{{ route('half-day-fines.update', $half_day_fine->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Minutes<sup class="text-danger">*</sup>:</label>
                            <input type="number" step="0.01" name="minutes" class="form-control" min="0.00"
                                placeholder="0.00" required value="{{ $half_day_fine->minutes }}">
                            @if ($errors->has('minutes'))
                                <span class="text-danger">{{ $errors->first('minutes') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Deduction Amount<sup class="text-danger">*</sup>:</label>
                            <input type="number" step="0.01" name="deduction_amount" class="form-control" min="0.00"
                                placeholder="0.00" required value="{{ $half_day_fine->deduction_amount }}">
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
                                <option value="fixed" {{ $half_day_fine->amount_type == 'fixed' ? 'selected' : '' }}>Fixed
                                </option>
                                <option value="basicPercent"
                                    {{ $half_day_fine->amount_type == 'basicPercent' ? 'selected' : '' }}>Basic Percent
                                </option>
                                {{--  <option value="workingHour" {{ $half_day_fine->amount_type == 'workingHour' ? 'selected' : '' }}>Working Hour</option>  --}}
                            </select>
                            @if ($errors->has('amount_type'))
                                <span class="text-danger">{{ $errors->first('amount_type') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 text-center">
                        <button class="btn btn-primary px-5">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
