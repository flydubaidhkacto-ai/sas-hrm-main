@extends('layouts.master')
@section('title', "Promotion")
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Promotion
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item active">Apply Promotion</li>
                        </ul>
                    </div>
                </div>
            </div>
            <form action="{{ route('promotion.apply.save') }}" method="POST">
                @csrf
                <div class="">
                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <label for="">Employee Id</label>
                            <input type="text" value="{{ $employee->id }}" name="employee_id" class="form-control"
                                readonly>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <label for="">Employee Name</label>
                            <input type="text" value="{{ $employee->name }}" name="name" id=""
                                class="form-control" readonly>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <label for="">Current Designation</label>
                            <input type="text" name="current_designation"
                                value="{{ $employee->designation ? $employee->designation?->name : '' }}" id=""
                                class="form-control" readonly>
                        </div>

                    </div>
                    {!! Toastr::message() !!}
                    <div class="mb-2 mt-2">
                        <label for="">New Designation</label>
                        <select name="new_designation" id="" class="form-control">
                            <option value="">Select Designation</option>
                            @foreach ($designations as $item)
                                <option value="{{ $item->id }}"> {{ $item->name }} </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2 ml-4 mt-2">
                        <div class="row">
                            <div class="col-md-12">
                                <input class="form-check-input" type="checkbox" value="" id="contentCheckbox">
                                <label class="form-check-label" for="contentCheckbox">
                                    For transfer to different department
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="hiddenContent" style="display: none;">

                        <div class="mb-2">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="">Current Department</label>
                                    <input type="text" value="{{ $employee->department?->name }}" name="curent_deparment"
                                        class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="">New Department</label>
                            <select name="new_departmnet" id="" class="form-control">
                                <option value="">Select Department</option>
                                @foreach ($department as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
        const checkbox = document.getElementById('contentCheckbox');
        const hiddenContent = document.getElementById('hiddenContent');

        checkbox.addEventListener('change', function() {

            if (this.checked) {
                hiddenContent.style.display = 'block';
            } else {
                hiddenContent.style.display = 'none';
            }

        });
    </script>
@endSection
