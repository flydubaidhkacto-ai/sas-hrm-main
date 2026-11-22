<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <title>Print Leave Application</title> --}}
    <style>
        @media print {
            @page {
                size: portrait;
                margin: 10mm;
            }

            body {
                font-family: Arial, sans-serif;
                padding: 20px;
                background: #fff;
                color: #000;
            }
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            max-width: 500px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            margin-top: 10px;
        }

        .sub-title {
            font-size: 18px;
        }

        .employee-section {
            display: flex;
            margin-bottom: 20px;
        }

        .employee-image {
            width: 200px;
            margin-right: 20px;
        }

        .employee-image img {
            width: 100%;
            height: auto;
        }

        .employee-info {
            flex: 1;
        }

        .leave-details {
            clear: both;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    @php

    dd('rajan');

        $balance = \App\Models\LeaveBalance::employee_balance(
            $leave->employee_id,
            $leave->leave_type_id,
            $leave->start_date->year,
        );
        $info = company_data();
    @endphp

    <div class="header">
        <div class="title">{{ $info['name'] ?? 'Hr payroll' }}</div>
        <div class="sub-title">Employee Leave Application</div>
    </div>

    <div class="employee-section">
        <div class="employee-image">
            <img src="/uploaded_files/{{ $leave->employee?->picture_file }}" alt="">
        </div>
        <div class="employee-info">
            <h4>{{ $leave->employee?->name }}</h4>
            <p class="mb-0">
                {{ $leave->employee?->department?->super_department?->name }}:
                {{ $leave->employee?->department?->name }}
            </p>
            <p class="mb-0">{{ $leave->employee?->designation?->name }}</p>
            <p class="text-muted mb-0">{{ $leave->employee?->email }}</p>
            <p class="text-muted mb-0">{{ $leave->employee?->phone }}</p>
            <p class="text-muted mb-0">{{ $leave->employee?->address }}</p>
        </div>
    </div>
    <div class="leave-details">
        <div style="margin-bottom: 10px; margin-top: 10px;">
            <label for="">Leave Type:</label>
            <span style="font-size: 15px;">
                <span class="badge"
                    style="font-size: 15px;">{{ $leave->leave_type?->name ?? ($leave->type ?? 'N/A') }}</span>
                @if ($leave->leave_type)
                    <span
                        @if ($leave->leave_type?->is_paid) class="text-success"
                @else
                class="text-danger" @endif>
                        ({{ $leave->leave_type?->is_paid ? 'Paid' : 'Unpaid' }})
                    </span>
                @endif
            </span>
        </div>
        <div style="margin-bottom: 10px; margin-top: 10px;">
            @php
                $can_approve = $leave->can_approve_this();
            @endphp
            @if ($leave->is_hourly)
                <span class="badge badge-info" style="margin-bottom: 10px;">
                    Hourly Leave
                </span>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <div style="flex: 2;">
                        <label for="">Date:</label>
                        <span>{{ carbon($leave->start_date)->format('d-m-Y') }}</span>

                    </div>
                    <div style="flex: 2;">
                        <label for="">Start Time:</label>
                        <span>{{ carbon($leave->start_time)->format('H:i') }}</span>

                    </div>
                    <div style="flex: 2;">
                        <label for="">End Time:</label>
                        <span>{{ carbon($leave->end_time)->format('H:i') }}</span>
                    </div>
                </div>
            @else
                <div style="display: flex; gap: 10px;">
                    <div style="flex: 2;">
                        <label for="">Start Date:</label>
                        <span>{{ carbon($leave->start_date)->format('d-m-Y') }}</span>
                    </div>
                    <div style="flex: 4;">
                        <label for="">End Date:</label>
                        <span>{{ carbon($leave->end_date)->format('d-m-Y') }}</span>
                        <span class="leave_duration"></span>
                    </div>
                </div>
            @endif

            <div style="margin-top: 10px;">
                <b>Total: </b>
                @if ($leave->is_hourly)
                    {{ carbon($leave->start_time)->diffInHours($leave->end_time) }} hours
                @else
                    @php
                        $duration = $leave->start_date->diffInDays($leave->end_date) + 1;
                    @endphp
                    {{ $duration }} {{ $duration > 1 ? 'days' : 'day' }}
                @endif
            </div>
        </div>
        <div class="mb-2">
            <b for="">Reason:</b>
            <p>
                {{ $leave->title }}
            </p>
        </div>
        <div class="mb-2 d-none">
            <i for="">Description:</i>
            <div>{!! str_replace("\n", '<br>', $leave->description) !!}</div>
        </div>
        <div style="margin-top: 10px;">
            <div>
                Leave Balance: {{ $balance['used'] }}/{{ $balance['limit'] }}
            </div>

            <div style="margin-top: 10px;">
                <ul class="stepper">
                    @foreach ($leave->getApprovalSteps() ?: [] as $order => $step)
                        <li
                            class="step @if ($step['status'] === 'approved') completed @elseif($step['status'] === 'rejected') rejected @else pending @endif">
                            <div class="step-icon">
                                @if ($step['status'] === 'approved')
                                    <i class="fas fa-check"></i>
                                @elseif($step['status'] === 'rejected')
                                    <i class="fas fa-times"></i>
                                @else
                                    <i class="fas fa-hourglass-start"></i>
                                @endif
                            </div>
                            <div class="step-content">
                                <div class="step-title">
                                    Step {{ $order }}: {{ $step['approver']?->name }}
                                </div>
                                <div class="step-description">
                                    {{-- can approve --}}
                                    @if ($step['can_approve'])
                                        <div style="margin-top: 10px;">
                                            <form
                                                action="{{ route('approvals.approve', ['module' => 'leave', 'module_id' => $leave->id]) }}"
                                                method="post">
                                                @csrf
                                                <input type="hidden" name="approval_step_id"
                                                    value="{{ $step['step']->id }}">
                                                <input type="hidden" name="approval_process_type" value="leave">
                                                <button name="approve" value="approve" class="btn btn-sm btn-primary">
                                                    <i class="fa fa-check"></i>
                                                    Approve
                                                </button>
                                                <button name="reject" value="reject" class="btn btn-sm btn-danger">
                                                    <i class="fa fa-times"></i>
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div>
                                            {{ $step['status'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();
            setTimeout(() => window.close(), 500);
        };
    </script>
</body>

</html>
