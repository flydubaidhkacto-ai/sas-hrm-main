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
                @php
                      $backUrl = $returnUrl ?? route('payslip.draft');
                @endphp

                <a href="{{ $backUrl }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
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
                        <div class="col">Total Working Days <br><b>{{ $duty_data['total_working_days'] ?? 0 }}</b></div>
                        <div class="col text-success">Present Days <br><b>{{ $duty_data['total_worked_days'] ?? 0 }}</b></div>
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
                        <div class="col text-danger">Late: <br><b>{{ $duty_data['total_late_days'] ?? 0 }}</b></div>
                        <div class="col text-success">Approved Late: <br><b>{{ $duty_data['approved_late_days'] ?? 0 }}</b></div>
                        <div class="col">Total Late: <br><b>{{ $duty_data['approved_late_days']+$duty_data['total_late_days'] ?? 0 }}</b></div>
                        <div class="col text-danger"> Total Late Hours:
                            <br><b>{{ seconds_to_hh_mm($duty_data['total_late_seconds'] ?? 0) }}</b></div>
                    </div>
                </div>

                {{-- SALARY --}}
                <div class="row">

                    {{-- EARN --}}
                    <div class="col-md-6">

                        @php
                            $user = auth()->user();

                            $isLocked = $payslip->isLocked();
                            $isHistory = $isHistory ?? false;

                            $isRestricted = $payslip->isViewMdAndHR($user);

                            $canManagePayslip =
                                !$isLocked
                                && !$isHistory
                                && $isRestricted;

                            // HR final approver check
                            $isHrFinalApprover =
                                $payslip->payroll_stage === 'payslip_prepare'
                                && $payslip->canFinalApprove($user);
                        @endphp

                        @if($canManagePayslip)
                        <a href="{{ route('payroll-cards.edit', $payslip->payrollCard?->id) }}" class="btn btn-primary" target="_blank">
                            <i class="fa fa-edit"></i>
                            Go to Edit
                        </a>
                        @endif

                        @if($canManagePayslip)

                            <div class="card">
                                <div class="card-header">Earnings (Editable)</div>

                                <table class="table">
                                    @foreach($payslip->earn_heads as $i => $h)
                                        <tr>
                                            <td>{{ $h['name'] ?? '-' }}</td>

                                            <td>

                                                @php
                                                    $readonlyCategories = ['basic', 'allowance'];

                                                    $isReadonly = in_array(($h['category'] ?? ''), $readonlyCategories);
                                                @endphp

                                                <input type="number"
                                                       class="form-control earn-input"
                                                       data-index="{{ $i }}"
                                                       value="{{ $h['total_amount'] ?? 0 }}"
                                                        {{ $isReadonly ? 'readonly' : '' }}>
                                            </td>

                                        </tr>

                                    @endforeach
                                        <tr>
                                            <td><b>Total</b></td>
                                            <td><b>{{ number_format($payslip->total_earn_heads, 2) }} Tk</b></td>
                                        </tr>
                                </table>

                            </div>

                            <div class="card mt-3">
                                <div class="card-body">

                                    <div class="form-group">
                                        <label>
                                            <b>Remarks <span class="text-danger">*</span></b>
                                        </label>

                                        <textarea
                                                id="update_remarks"
                                                class="form-control"
                                                rows="3"
                                                placeholder="Write update remarks..."
                                        ></textarea>

                                        <small class="text-muted">
                                            Remarks is required for payslip update
                                        </small>
                                    </div>

                                    <button class="btn btn-success" id="updatePayslip">
                                        <i class="fa fa-save"></i>
                                        Update
                                    </button>

                                </div>
                            </div>

                        @else

                        <div class="card">
                            <div class="card-header">Earnings</div>
                            <table class="table mb-0">

                                @foreach(($payslip->earn_heads ?? []) as $h)
                                    <tr>
                                        <td>{{ $h['name'] ?? '-' }}</td>
                                        <td>{{ number_format($h['total_amount'] ?? 0, 2) }} Tk</td>
                                    </tr>

                                @endforeach

                                <tr>
                                    <td><b>Total</b></td>
                                    <td><b>{{ number_format($payslip->total_earn_heads, 2) }} Tk</b></td>
                                </tr>
                            </table>
                        </div>
                        @endif
                    </div>

                    {{-- DEDUCTION --}}
                    <div class="col-md-6">

                        @if($canManagePayslip)

                            <div class="card">
                                <div class="card-header">Deductions (Editable)</div>

                                <table class="table">

                                    @foreach($payslip->deductions as $i => $d)
                                        <tr>
                                            <td>{{ $d['name'] ?? '-' }}</td>

                                            <td>
                                                <input type="number"
                                                       class="form-control deduct-input"
                                                       data-index="{{ $i }}"
                                                       value="{{ $d['total_amount'] ?? 0 }}"
                                            </td>
                                        </tr>

                                    @endforeach

                                        <tr>
                                            <td><b>Total</b></td>
                                            <td><b>{{ number_format($payslip->total_deductions, 2) }} Tk</b></td>
                                        </tr>
                                </table>
                            </div>


                        @else
                        <div class="card">
                            <div class="card-header">Deductions</div>
                            <table class="table mb-0">


                                @foreach(($payslip->deductions ?? []) as $h)
                                    <tr>
                                        <td>{{ $h['name'] ?? '-' }}</td>
                                        <td>{{ number_format($h['total_amount'] ?? 0, 2) }} Tk</td>
                                    </tr>

                                @endforeach

                                <tr>
                                    <td><b>Total</b></td>
                                    <td><b>{{ number_format($payslip->total_deductions, 2) }} Tk</b></td>
                                </tr>
                            </table>
                        </div>
                        @endif

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
                        <th>Action Stage</th>
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

                            <td>
                                {{ $row['role'] }}
                                {{-- 🔥 ADD THIS --}}
{{--                                @if(!empty($row['is_current']))--}}
{{--                                    <br>--}}
{{--                                    <span class="badge badge-primary">Current Stage</span>--}}
{{--                                @endif--}}

                                @if($payslip->stageNiceName()==$row['role'])
                                    <br>
                                    <span class="badge badge-primary">Current Stage</span>
                                @endif
                            </td>

                            <td>{{ $row['approver'] }}</td>

                            <td>
                                <b>{{ $row['label'] }}</b>
                            </td>

                            <td>
                                @if($row['status'] === 'sent')
                                    <span class="badge badge-info">Sent to {{$row['to_stage']}}</span>

                                @elseif($row['status'] === 'approved' && !empty($row['is_ready_to_send']))
                                    <span class="badge badge-success">Approved</span>

                                    @if(!empty($row['is_ready_to_send']))
                                        <br><span class="badge badge-warning">Ready to Send</span>
                                    @endif

                                @elseif($row['status'] === 'approved')
                                    <span class="badge badge-success">Approved</span>

                                @elseif($row['status'] === 'done')
                                    <span class="badge badge-success">Done</span>

                                @elseif($row['status'] === 'returned')
                                    <span class="badge badge-warning">Returned from {{$row['role']}}</span>

                                @elseif($row['status'] === 'rejected')
                                    <span class="badge badge-danger">Rejected</span>

                                @elseif($row['status'] === 'regenerated')
                                    <span class="badge badge-info">Regenerated from {{$row['role']}}</span>

                                @elseif($row['status'] === 'updated')
                                    <span class="badge badge-info">Updated from {{$row['role']}}</span>

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

            @php
                $user = auth()->user();

                // ================= LOCK CHECK =================
                $isLocked = $payslip->isLocked();
                $isSuper  = $payslip->isSuperAdmin($user);

                // ================= BASE PERMISSIONS =================
                $hasApproveAccess = !$isLocked && $payslip->canApprove($user);

                $hasNextAccess = !$isLocked
                    && $canFinal
                    && $canNext
                    && $payslip->nextStage();

                // ================= RETURN =================
                $hasReturnAccess = false;

                if (!$isLocked && $canFinal) {

                    if ($payslip->payroll_stage == 'payslip_md_approve') {
                        $hasReturnAccess = count($payslip->allPreviousStages()) > 0;
                    } else {
                        $hasReturnAccess = $payslip->previousStage() ? true : false;
                    }
                }

                // ================= REGENERATE =================
                $hasReGenerateAccess =
                    !$isLocked
                    && !$isHistory
                    && in_array($payslip->payroll_stage, [
                        'payslip_prepare',
                        'payslip_md_approve'
                    ]);

                // ================= FINAL FLAG =================
                $showActions =
                    !$isLocked && (
                        $hasApproveAccess ||
                        $hasNextAccess ||
                        $hasReturnAccess ||
                        $hasReGenerateAccess
                    );

                $canSetPaymentMethod = !$isLocked && $payslip->canSetPaymentMethod($user);

            @endphp


            {{-- 🔥 ACTIONS (BOTTOM) --}}
            @if($showActions && !$isHistory)

                <div class="mt-4">

                    {{-- ✅ PAYMENT METHOD (ONLY HR) --}}
                    <div class="row">


                        @if($canSetPaymentMethod)
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">
                                        <i class="fa fa-credit-card mr-1 text-success"></i>
                                        Payment Method
                                    </label>

                                    <select id="payment_method" class="form-control">
                                        <option value="bank" {{ $payslip->payment_method == 'bank' ? 'selected' : '' }}>
                                            🏦 Bank
                                        </option>
                                        <option value="cash" {{ $payslip->payment_method == 'cash' ? 'selected' : '' }}>
                                            💵 Cash
                                        </option>
                                    </select>

                                    <small class="text-muted">
                                        Only HR & MD can change this
                                    </small>
                                </div>
                            </div>
                        @endif


                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold d-flex justify-content-between">
                            <span>
                                <i class="fa fa-comment mr-1 text-primary"></i> Comment
                            </span>

                        </label>

                        <textarea id="comment"
                                  class="form-control comment-box"
                                  rows="2"
                                  placeholder="Write a comment..."></textarea>

                        <small class="text-muted">
                            💡 Tip: Add reason when returning or regenerating
                        </small>
                    </div>

                    {{-- ================= APPROVE ================= --}}
                    @if($hasApproveAccess)
                        <button class="btn btn-primary actionBtn"
                                data-url="{{ route('payroll-approval.approve') }}"
                                data-id="{{ $payslip->id }}">
                            <i class="fa fa-check mr-1"></i> Approve
                        </button>
                    @endif


                    {{-- ================= FINAL ACTIONS ================= --}}
                    @if($canFinal)

                        {{-- NEXT --}}
                        @if($hasNextAccess)
                            <button class="btn btn-success actionBtn"
                                    data-url="{{ route('payroll-approval.next', $payslip->id) }}"
                                    data-id="{{ $payslip->id }}">
                                → Send to {{ $nextLabel }}
                            </button>
                        @endif


                        {{-- RETURN --}}
                        @if($hasReturnAccess)

                            {{-- MD → MULTIPLE RETURN --}}
                            @if($payslip->payroll_stage == 'payslip_md_approve')

                                @foreach($payslip->allPreviousStages() as $stage)
                                    <button class="btn btn-warning actionBtn mb-1"
                                            data-url="{{ route('payroll-approval.return') }}"
                                            data-id="{{ $payslip->id }}"
                                            data-stage="{{ $stage }}">
                                        ← Return to {{ \App\Helpers\PayrollHelper::stageLabel($stage) }}
                                    </button>
                                @endforeach

                            @else

                                {{-- NORMAL USER → SINGLE RETURN --}}
                                <button class="btn btn-warning actionBtn"
                                        data-url="{{ route('payroll-approval.return') }}"
                                        data-id="{{ $payslip->id }}"
                                        data-stage="{{ $payslip->previousStage() }}">
                                    ← Return to {{ $prevLabel }}
                                </button>

                            @endif

                        @endif

                    @endif

                    {{-- RE-GENERATE --}}
                    @if($canManagePayslip)
                        <button type="button"
                                class="btn btn-danger actionBtn"
                                data-url="{{ route('payroll-approval.reGenerate', $payslip->id) }}"
                                data-id="{{ $payslip->id }}">
                            <i class="fa fa-refresh mr-1"></i> Re-Generate
                        </button>


                    @endif

                </div>

            @endif

            @if($isLocked)
                <div class="alert alert-success mt-3">
                    <b>Final Approved</b> — No further actions allowed.
                </div>
            @endif

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

        $('.actionBtn').click(function () {

            let url = $(this).data('url');
            let id = $(this).data('id');
            let note = $('#comment').val();
            let targetStage = $(this).data('stage');

            let paymentMethod = null;

            // ✅ only if field exists
            if ($('#payment_method').length) {
                paymentMethod = $('#payment_method').val();
            }

            // ✅ detect action type
            let isNoteRequired = url.includes('return') || url.includes('re-generate');

            // ✅ FRONTEND VALIDATION (UX only)
            if (isNoteRequired && (!note || note.trim().length < 2)) {
                toastr.error('Comment is required (min 2 characters)');
                $('#comment').focus();
                return;
            }


            if (!confirm('Are you sure?')) return;

            const RETURN_URL = @json($returnUrl ?? route('payslip.draft'));


            $.post(url, {
                ids: [id],
                note: note,
                target_stage: targetStage,
                payment_method: paymentMethod,
                return_url: RETURN_URL,
                _token: '{{ csrf_token() }}'
            })
                .done(function (res) {

                    if (!res.status) {
                        toastr.error(res.message || 'Action failed');
                        return;
                    }

                    toastr.success(res.message || 'Success');

                    let redirectUrl = res.redirect ?? RETURN_URL;

                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 800);
                })
                .fail(function (err) {

                    // ✅ VALIDATION ERROR (Laravel 422)
                    if (err.status === 422 && err.responseJSON?.errors) {

                        let errors = err.responseJSON.errors;
                        let messages = [];

                        Object.values(errors).forEach(errArr => {
                            messages.push(errArr[0]);
                        });

                        toastr.error(messages.join('<br>'));

                        return;
                    }

                    // ✅ CUSTOM BACKEND ERROR
                    if (err.responseJSON?.message) {
                        toastr.error(err.responseJSON.message);
                        return;
                    }

                    // ❌ UNKNOWN ERROR
                    toastr.error('Something went wrong');

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

        $('#updatePayslip').click(function () {

            let earnHeadsOriginal = @json($payslip->earn_heads);
            let deductionOriginal = @json($payslip->deductions);

            let remarks = $('#update_remarks').val();

            // ✅ REQUIRED VALIDATION
            if (!remarks || remarks.trim().length < 2) {

                toastr.error('Remarks is required (min 2 characters)');

                $('#update_remarks').focus();

                return;
            }

            let earn_heads = earnHeadsOriginal.map((item, i) => {
                return {
                    ...item,
                    total_amount: $('.earn-input[data-index="' + i + '"]').val()
                };
            });

            let deductions = deductionOriginal.map((item, i) => {
                return {
                    ...item,
                    total_amount: $('.deduct-input[data-index="' + i + '"]').val()
                };
            });

            $.post("{{ route('payroll-approval.updateHeads') }}", {
                _token: "{{ csrf_token() }}",
                payslip_id: "{{ $payslip->id }}",
                note: remarks,
                earn_heads: earn_heads,
                deductions: deductions
            })
                .done(function (res) {
                    if (res.status) {
                        toastr.success(res.message);
                        location.reload();
                    } else {
                        toastr.error(res.message);
                    }
                })
                .fail(function () {
                    toastr.error('Update failed');
                });

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

        .comment-box {
            border-radius: 8px;
            resize: none;
            transition: all 0.2s ease;
        }

        .comment-box:focus {
            border-color: #007bff;
            box-shadow: 0 0 6px rgba(0, 123, 255, .2);
        }

        .comment-box.is-invalid {
            border-color: #dc3545;
        }

    </style>

@endsection