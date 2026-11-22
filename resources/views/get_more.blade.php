@extends('layouts.master')
@section('title', "Upgrade and get more")
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            @php
                $module = request()->get('module') ?: 'outside_duty';
            @endphp
            <div style="width: 90%; max-width: 400px; margin: 0 auto; text-align: center">
                @if ($module == 'outside_duty')
                    <div>
                        <h1>
                            Outside Duty
                        </h1>
                        <p>
                            Let your Employees Check-In and Check-Out from anywhere in the world. No need come to the office to start working! They can login to the system and directly check-in/check-out. The system will store their location in the database.
                        </p>
                        <div style="text-align: start">
                            <b>Useful for:</b>
                            <ul>
                                <li>Working from home Employees</li>
                                <li>Saleman Employees</li>
                            </ul>
                        </div>
                    </div>
                @elseif($module == 'employee_portal')
                    <div>
                        <h1>
                            Employee Portal
                        </h1>
                        <p>
                            Give your Employees access to their personal account. With custom roles and permissions they can manage/view system data as well as their own data.
                        </p>
                    </div>
                @elseif($module == 'employee_expenses')
                    <div>
                        <h1>
                            Employee Expenses
                        </h1>
                        <p>
                            Give your Employees access to input their daily expenses for sales work.
                        </p>
                        <div class="text-left">
                            <b>Useful for:</b>
                            <ul>
                                <li>Salesman Employees</li>
                            </ul>
                        </div>
                    </div>
                @elseif($module == 'loans')
                    <div>
                        <h1>
                            Loans
                        </h1>
                        <p>
                            Keep track of your employee loans.
                        </p>
                    </div>
                @elseif($module == 'fines')
                    <div>
                        <h1>
                            Fines
                        </h1>
                        <p>
                            Keep track of your employee fines.
                        </p>
                    </div>
                @elseif($module == 'sms')
                    <div>
                        <h1>
                            SMS Notification
                        </h1>
                        <p>
                            Automatically send SMS notification to your employees for sensitive Information.
                        </p>
                        <div class="text-left">
                            <b>Effected Features:</b>
                            <ul>
                                <li>Leave Approve/Rejection</li>
                                <li>Loan Approve/Rejection</li>
                                <li>Fine Approve</li>
                                <li>
                                    Shift change warning.
                                </li>
                                <li>
                                    Promotion congratulations.
                                </li>
                                <li>
                                    and Many More...
                                </li>
                            </ul>
                        </div>
                        <div class="text-left">
                            <b>API Support:</b>
                            <br>
                            <i>
                                Any SMS gateway can be used with our customized Setup. You might need to see the documentation of your provider.
                            </i>
                        </div>
                    </div>
                @elseif($module == 'employee_performance')
                    <div>
                        <h1>
                            Employee Performance
                        </h1>
                        <p>
                            Keep track of your employees performance and productivity for every month.
                        </p>
                    </div>
                @elseif($module == 'payroll')
                    <div>
                        <h1>
                            Payroll
                        </h1>
                        <p>
                            Automate your employees monthly/daily/hourly salary at the end of the month. Calculate your employees salary for every seconds they worked without any hassle.
                        </p>
                        <div class="text-left">
                            <b>Features:</b>
                            <ul>
                                @foreach (config('services.modules.payroll') as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                            <br>
                            <b>Useful for:</b>
                            <ul>
                                <li>Companies that don't want to use an accounting system or HR person to calculate and track their employees salary</li>
                            </ul>
                        </div>
                    </div>
                @else

                @endif
                <div class="mt-4">
                    <div class="submit-section">
                        <a class="btn btn-primary submit-btn"
                            href="https://app.prohrbd.com/buy/step-1?modules[]={{ ( $module) }}"
                            target="_blank"
                        >
                            Start Now for only +{{
                                config("services.modules_pricing.{$module}")
                            }} Tk /Emp/Mon
                            <i class="fa fa-external-link"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->
@endsection
