@extends('layouts.master')
@section('title', "Payslip Details")

@section('content')

    @php
        $user = auth()->user();

        $canApprove = $payslip->canApprove($user);
        $canFinal   = $payslip->canFinalApprove($user);
        $canNext    = $payslip->canSendToNext($user);

        $nextLabel = $payslip->nextStageLabel();
        $prevLabel = $payslip->previousStageLabel();


    @endphp

    <div class="page-wrapper">
        <div class="content container-fluid">

            {{-- HEADER --}}
            <div class="page-header d-flex justify-content-between align-items-center">
                <h3>
                    {{ $payslip->employee?->name }} - Payslip
                    ({{ \Carbon\Carbon::parse($payslip->month_date)->format('F Y') }})
                </h3>

                <button class="btn btn-info btnprn">
                    Print <i class="fa fa-print"></i>
                </button>
            </div>

            {{-- PRINT WRAPPER (🔥 VERY IMPORTANT) --}}
            <div id="print">

                <div class="print-header">
                    <h1 class="print-company-name">
                        {{ $company_info['name'] }}
                    </h1>

                    <h4>
                        Payslip of {{ \Carbon\Carbon::parse($payslip->month_date)->format('F, Y') }}
                    </h4>
                </div>

                {{-- STATUS --}}
                <div class="mb-3">
            <span class="badge badge-info">
                {{ $payslip->stageNiceName() }}
            </span>
                    <span class="badge badge-{{ $status['class'] }}">
                        {{ $status['label'] }}
                    </span>

                </div>

                {{-- EMPLOYEE --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <h4>{{ $payslip->employee?->name }}</h4>

                        <p>
                            <b>Department:</b> {{ $payslip->employee?->department?->name }} <br>
                            <b>Designation:</b> {{ $payslip->employee?->designation?->name }} <br>
                            <b>Pay type:</b> {{ ucfirst($payslip->pay_type) }} <br>
                            <b>Shift:</b> {{ implode(", ", $duty_data['all_shift_names'] ?? []) }}
                        </p>
                    </div>
                </div>

                {{-- ATTENDANCE --}}
                <div class="card mb-3">
                    <div class="card-header">Attendance Summary</div>

                    <div class="card-body row text-center">
                        <div class="col">Worked Days <br><b>{{ $duty_data['total_worked_days'] ?? 0 }}</b></div>
                        <div class="col">Worked Hours
                            <br><b>{{ seconds_to_hh_mm($duty_data['total_worked_seconds'] ?? 0) }}</b></div>
                        <div class="col">OT <br><b>{{ seconds_to_hh_mm($duty_data['total_overtime_seconds'] ?? 0) }}</b>
                        </div>
                        <div class="col text-danger">Absent <br><b>{{ $duty_data['total_absent_days'] ?? 0 }}</b></div>
                        <div class="col">Leave <br><b>{{ $duty_data['total_leave_days'] ?? 0 }}</b></div>
                        <div class="col">Weekend <br><b>{{ $duty_data['total_weekends'] ?? 0 }}</b></div>
                    </div>

                    <div class="card-body row text-center">
                        <div class="col">Total Holidays <br><b>{{ $duty_data['total_holidays'] ?? 0 }}</b></div>
                        <div class="col">Total Weekends <br><b>{{ $duty_data['total_weekends'] ?? 0 }}</b></div>
                        <div class="col">Total Late Days: <br><b>{{ $duty_data['total_late_days'] ?? 0 }}</b></div>
                        <div class="col text-danger"> Total Late Hours:
                            <br><b>{{ seconds_to_hh_mm($duty_data['total_late_seconds'] ?? 0) }}</b></div>
                        <div class="col">Leave <br><b>{{ $duty_data['total_leave_days'] ?? 0 }}</b></div>
                    </div>
                </div>

                {{-- SALARY --}}
                <div class="row">

                    {{-- EARN --}}
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">Earnings</div>
                            <table class="table mb-0">
                                @php $totalEarn = 0; @endphp

                                @foreach(($payslip->earn_heads ?? []) as $h)
                                    <tr>
                                        <td>{{ $h['name'] ?? '-' }}</td>
                                        <td>{{ number_format($h['total_amount'] ?? 0, 2) }} Tk</td>
                                    </tr>
                                    @php $totalEarn += ($h['total_amount'] ?? 0); @endphp
                                @endforeach

                                <tr>
                                    <td><b>Total</b></td>
                                    <td><b>{{ number_format($totalEarn, 2) }} Tk</b></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- DEDUCTION --}}
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">Deductions</div>
                            <table class="table mb-0">
                                @php $totalDeduct = 0; @endphp

                                @foreach(($payslip->deductions ?? []) as $h)
                                    <tr>
                                        <td>{{ $h['name'] ?? '-' }}</td>
                                        <td>{{ number_format($h['total_amount'] ?? 0, 2) }} Tk</td>
                                    </tr>
                                    @php $totalDeduct += ($h['total_amount'] ?? 0); @endphp
                                @endforeach

                                <tr>
                                    <td><b>Total</b></td>
                                    <td><b>{{ number_format($totalDeduct, 2) }} Tk</b></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div>

                {{-- NET --}}
                <div class="card mt-3 text-left">
                    <div class="card-body">
                        <h3>{{ number_format($payslip->total_salary, 2) }} Tk</h3>
                        <p>{{ number_to_words($payslip->total_salary) }} Taka Only</p>
                    </div>
                </div>

                {{-- SYSTEM NOTES --}}
                @if($payslip->system_notes_json)
                    <div class="card mt-3 system-notes-block">

                        <div class="card-header d-flex justify-content-between">

                            <a href="{{ route('reports.attendance.employee', ['employee' => $payslip->user_id , 'start_date' => $payslip->start_date, 'end_date' => $payslip->end_date]) }}"
                               class="btn btn-info btn-sm mr-2" target="_blank">
                                Employee Attendance Report
                            </a>

                            <button type="button" class="btn btn-sm btn-warning" onclick="toggleNotes(this)">
                                System Notes
                            </button>
                        </div>

                        <div class="card-body notes-body" style="display:none">
                            <table class="table table-sm">

                                <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Value</th>
                                    <th>Balance</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($payslip->system_notes_json as $note)
                                    <tr
                                            @if(($note['type'] ?? '') == 'earn')
                                            style="background-color: #d2f8c4af;"
                                            @elseif(($note['type'] ?? '') == 'deduct')
                                            style="background-color: #f6d3d3b9;"
                                            @endif
                                    >
                                        <td>{{ ucfirst($note['type'] ?? 'N/A') }}</td>
                                        <td>{{ $note['name'] ?? 'N/A' }}</td>
                                        <td>{{ $note['description'] ?? 'N/A' }}</td>
                                        <td>
                                            @if(isset($note['is_incremental']))
                                                <span
                                                        @if(($note['is_incremental'] ?? false) == true)
                                                        class="text-success"
                                                        @elseif(($note['is_incremental'] ?? false) == false)
                                                        class="text-danger"
                                                                        @endif
                                                                    >
                                                                        {{ isset($note['value']) ? number_format($note['value'], 2) : 'N/A' }} Tk
                                                                    </span>
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td>{{ isset($note['balance']) ? number_format($note['balance'], 2) : 'N/A' }}
                                            Tk
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

            </div> {{-- END PRINT --}}

            <div class="card mt-4">
                <div class="card-header">
                    Approval History
                </div>

                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Stage</th>
                        <th>Approver</th>
                        <th>Label</th>
                        <th>Status</th>
                        <th>Date & Time</th>
                        <th>Note</th>
                    </tr>
                    </thead>

                    <tbody>

                    @foreach($approvalTimeline as $row)

                        <tr class="{{ $row['row_color'] ?? '' }}">

                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $row['role'] }}</td>

                            <td>{{ $row['approver'] }}</td>

                            <td>
                                <b>{{ $row['label'] }}</b>
                            </td>

                            <td>
                                @if($row['status'] === 'sent')
                                    <span class="badge badge-info">Sent</span>

                                @elseif($row['status'] === 'approved' && !empty($row['is_sending_pending']))
                                    <span class="badge badge-success">Approved</span>

                                    @if(!empty($row['is_sending_pending']))
                                        <br><span class="badge badge-warning">Pending Send</span>
                                    @endif

                                @elseif($row['status'] === 'approved')
                                    <span class="badge badge-success">Approved</span>

                                @elseif($row['status'] === 'returned')
                                    <span class="badge badge-warning">Returned</span>

                                @elseif($row['status'] === 'rejected')
                                    <span class="badge badge-danger">Rejected</span>

                                @else
                                    <span class="badge badge-secondary">Pending</span>
                                @endif
                            </td>

                            <td>{{ $row['time'] }}</td>

                            <td>{{ $row['note'] }}</td>

                        </tr>

                    @endforeach

                    </tbody>
                </table>
            </div>

            {{-- 🔥 ACTIONS (BOTTOM) --}}
            <div class="mt-4">

                {{-- COMMENT --}}
                <textarea id="comment" class="form-control mb-2" placeholder="Optional comment..."></textarea>

                @if($canApprove)
                    <button class="btn btn-primary actionBtn"
                            data-url="{{ route('payroll-approval.bulkApprove') }}"
                            data-id="{{ $payslip->id }}">
                        Approve
                    </button>
                @endif

                @if($canFinal)

                    @if($canNext && $payslip->nextStage())
                        <button class="btn btn-success actionBtn"
                                data-url="{{ route('payroll-approval.next', $payslip->id) }}"
                                data-id="{{ $payslip->id }}">
                            → Send to {{ $nextLabel }}
                        </button>
                    @endif

                    @if($payslip->previousStage())
                        <button class="btn btn-warning actionBtn"
                                data-url="{{ route('payroll-approval.back', $payslip->id) }}"
                                data-id="{{ $payslip->id }}">
                            ← Return to {{ $prevLabel }}
                        </button>
                    @endif

                @endif

            </div>

        </div>
    </div>

@endsection

@section('script')

    <script>

        function toggleNotes(btn) {
            let body = btn.closest('.card').querySelector('.notes-body');

            if (!body) return;

            if (body.style.display === 'none' || body.style.display === '') {
                body.style.display = 'block';
                btn.innerText = 'Hide';
            } else {
                body.style.display = 'none';
                btn.innerText = 'Show';
            }
        }

        // AJAX ACTION
        $('.actionBtn').click(function () {

            let url = $(this).data('url');
            let id = $(this).data('id');
            let note = $('#comment').val();

            if (!confirm('Are you sure?')) return;

            $.post(url, {
                ids: [id],
                note: note,
                _token: '{{ csrf_token() }}'
            }).done(res => {
                toastr.success(res.message);
                location.reload();
            }).fail(() => {
                toastr.error('Error');
            });

        });

        // PRINT
        $('.btnprn').click(function (e) {
            e.preventDefault();

            // 🔥 FORCE RESET BEFORE PRINT
            $('.notes-body').hide();
            $('.system-notes-block').removeClass('showing');

            window.print();
        });

    </script>

    <style>

        @media print {

            /* ===== HIDE EVERYTHING ===== */
            body * {
                visibility: hidden !important;
            }

            /* ===== SHOW ONLY PRINT AREA ===== */
            #print, #print * {
                visibility: visible !important;
            }

            #print {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;

                background: #fff;
                padding: 10px 20px !important;
                margin: 0 !important;
            }

            /* ===== REMOVE LEFT MENU / LAYOUT GAP ===== */
            .page-wrapper,
            .content,
            .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            /* ===== SHOW HEADER ONLY IN PRINT ===== */
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 15px;
            }

            /* ===== FORCE HIDE SYSTEM NOTES (CRITICAL FIX) ===== */
            .system-notes-block,
            .system-notes-block * {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                overflow: hidden !important;
            }

            /* ===== REMOVE ALL UI ELEMENTS ===== */
            .btn,
            .btnprn,
            textarea,
            .card-header button,
            .page-header,
            .breadcrumb,
            nav,
            aside,
            .sidebar,
            .header,
            footer {
                display: none !important;
            }

            /* ===== TABLE CLEAN LOOK ===== */
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 12px;
            }

            td, th {
                padding: 5px;
            }

            .page-header .badge,
            .mb-3 .badge {
                display: none !important;
            }
        }

        .print-header {
            display: none;
        }

    </style>

@endsection