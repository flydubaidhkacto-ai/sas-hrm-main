@extends('layouts.master')
@section('title', "Edit Salary Card")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Edit Salary card of: {{ $employee->name }}
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payroll-cards.index') }}">Salary Cards</a></li>
                        <li class="breadcrumb-item active">
                            Edit
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="salary-card-edit"
            employee="{{ json_encode($employee->load('department.super_department', 'designation')) }}"
            card="{{ json_encode($card) }}"
            heads="{{ json_encode($heads) }}"
            deductions="{{ json_encode($deductions) }}"
            grades="{{ json_encode($grades->load('items')) }}"
            employee_fines="{{ json_encode($employee_fines) }}"
            create_mode="0"
            payroll_cards_index="{{ route('payroll-cards.index') }}"
        ></div>
    </div>
</div>
<style>
    .preview {
        color: gray;
        font-size: 10px;
        display: none;
    }
</style>
@endsection
@section('script')
<script>
</script>
@endSection
