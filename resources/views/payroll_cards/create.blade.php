@extends('layouts.master')
@section('title', 'Create Salary Card')

@push('style')
    <style>
        .preview {
            color: gray;
            font-size: 10px;
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <x-page-header :title="'Create Salary card of: ' . $employee->name"
                :breadcrumbs="[
                    ['name' => 'Salary Cards', 'route' => route('payroll-cards.index')],
                    ['name' => 'Create', 'active' => true],
                ]"
            />

            <div id="salary-card-edit"
                employee="{{ json_encode($employee->load('department.super_department', 'designation')) }}"
                card="{{ json_encode([]) }}"
                heads="{{ json_encode($heads) }}"
                deductions="{{ json_encode($deductions) }}"
                grades="{{ json_encode($grades->load('items')) }}"
                employee_fines="{{ json_encode($employee_fines) }}"
                create_mode="1"
                payroll_cards_index="{{ route('payroll-cards.index') }}"
            ></div>
        </div>
    </div>

@endsection

@push('js')
    <script>
    </script>
@endPush
