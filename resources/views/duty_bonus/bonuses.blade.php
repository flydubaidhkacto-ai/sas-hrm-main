@extends('layouts.master')
@section('title', 'Auto Duty Bonus Records')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">

            <x-page-header :title="'Auto Duty Bonus Records'" :breadcrumbs="[ ['name' => 'Dashboard', 'route' => route('home')], ['name' => 'Auto Duty Bonus', 'route' => route('auto-duty-bonuses.index')], ['name' => 'Bonuses', 'active' => true] ]" />

            <div class="mt-2">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Code</th>
                            <th>Emp. ID</th>
                            <th>Employee</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($duty_bonuses as $duty_bonus)
                            <tr>
                                <td>{{ $duty_bonus->id }}</td>
                                <td>{{ $duty_bonus->user->code }}</td>
                                <td>{{ $duty_bonus->user->employee_id }}</td>
                                <td>{{ $duty_bonus->user->name }}</td>
                                <td>{{ $duty_bonus->amount }}</td>
                                <td>{{ $duty_bonus->date->format('d-m-Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

