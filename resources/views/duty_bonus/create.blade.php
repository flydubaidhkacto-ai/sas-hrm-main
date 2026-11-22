@extends('layouts.master')
@section('title', 'Create Auto Duty Bonus')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">

            <x-page-header :title="'Create Auto Duty Bonus'" :breadcrumbs="[
                ['name' => 'Auto Duty Bonus', 'route' => route('half-day-fines.index')],
                ['name' => 'Create', 'active' => true],
            ]" />

            <form id="half-day-fine-form" action="{{ route('auto-duty-bonuses.store') }}" method="POST">
                @csrf
                @method('POST')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Amount Type<sup class="text-danger">*</sup>:</label>
                            <select class="select form-control" id="type" name="type" required>
                                <option value="">--Select--</option>
                                <option value="0">Percent</option>
                                <option value="1">Amount</option>
                            </select>
                            @if ($errors->has('type'))
                                <span class="text-danger">{{ $errors->first('type') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-form-label">Amount<sup class="text-danger">*</sup>:</label>
                            <input type="number" step="0.01" name="amount" class="form-control" min="0.00"
                                placeholder="0.00" required value="{{ old('amount') }}">
                            @if ($errors->has('amount'))
                                <span class="text-danger">{{ $errors->first('amount') }}</span>
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
