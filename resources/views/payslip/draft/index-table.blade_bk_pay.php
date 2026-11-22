@php
    $user = auth()->user();

        $finalItem = $payslips->first(fn($p) => $p->computed['canFinalApprove']);

        $canApprove = $payslips->first(fn($p) => $p->computed['canApprove']);
@endphp

@if($canApprove)
    <button type="button" class="btn btn-primary mb-2 btn-bulk-approve">
        <i class="fa fa-check mr-1"></i> Approve
    </button>
@endif

{{-- Final Approver --}}
@php
    $finalItem = $payslips->first(fn($p) => $p->computed['canFinalApprove']);
@endphp

@if($finalItem)
    <div class="btn-group mb-2">

        @if($finalItem->nextStage())
            <a href="{{ route('payslip.ready_to_send', ['month' => request('month')]) }}"
               class="btn btn-warning">
                <i class="fa fa-paper-plane mr-1"></i>
                Go to Ready to Send
            </a>
        @endif
    </div>
@endif

<div class="table-topbar">

    <div class="left">
        <h4>Total: {{ $payslips->count() }}</h4>
    </div>

    <div class="right">
        <input type="text"
               id="tableSearch"
               class="form-control form-control-sm"
               placeholder="Search.......">
    </div>

</div>

<div class="table-wrapper">
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th class="col-checkbox">
                @if($canApprove)
                    <input type="checkbox" id="checkAll"></th>
            @endif

            <th class="col-sl">S.L</th>
            <th class="col-employee">Employee</th>
            <th class="col-code">Code</th>
            <th>Station</th>
            <th>Department</th>
            <th>Designation</th>
            <th>Month</th>
            <th>Pay Type</th>
            <th>Gross</th>
            <th>Net</th>
            <th>Balance</th>
            <th>Current Stage</th>
            <th>Status</th>
            <th>Payment Method</th>
            <th>Last Action By</th>
            <th>Last Note</th>
            <th>Action</th>
        </tr>
        </thead>

        <tbody>
        @foreach($payslips as $item)
            <tr style="{{ $item->status === 'returned' ? 'background-color:#ffe5e5;' : '' }}">

                {{-- Checkbox --}}
                <td class="col-checkbox">

                    <input type="checkbox"
                           name="ids[]"
                           value="{{ $item->id }}"
                            {{ !$item->computed['canApprove'] ? 'disabled' : '' }}>
                </td>
                <td class="col-sl">
                    {{ $loop->iteration }}
                </td>
                <td class="col-employee">{{ $item->employee->name }}</td>

                <td class="col-code">
                    {{ $item->employee?->id }}
                </td>

                <td>{{ $item->employee?->department?->super_department?->name }}</td>
                <td>{{ $item->employee?->department?->name }}</td>
                <td>{{ $item->employee?->designation?->name }}</td>

                <td>{{ \Carbon\Carbon::parse($item->month_date)->format('M Y') }}</td>
                <td>{{ ucfirst($item->pay_type) }}</td>

                <td>{{ $item->employee?->payroll_card?->gross_salary ?? '-' }}</td>
                <td>{{ number_format($item->total_salary, 2) }}</td>


                <td>{{ $item->payment_data['due_amount'] ?? '-' }}</td>

                <td>
                <span class="badge badge-info">
                    {{ $item->stageNiceName() }}
                </span>
                </td>

                {{-- Status --}}
                <td>
                    <span class="badge badge-{{ $item->computed['status']['class'] ?? 'secondary' }}">
                        {{ $item->computed['status']['label'] ?? 'Pending' }}
                    </span>
                </td>

                <td>
                <span class="badge badge-info">
                    {{ $item->payment_method }}
                </span>
                </td>
                <td>
                    @php
                        $actionBy = $item->computed['last_action_by'] ?? null;
                        $stage = $item->computed['last_stage'] ?? null;

                        // 🔥 color config
                        $actionColor = 'dark';

                        $stageColor = match($stage) {
                            'payslip_prepare'         => 'primary',
                            'payslip_accounts_check'  => 'warning',
                            'payslip_finance_verify'  => 'info',
                            'payslip_md_approve'      => 'success',
                            default                  => 'secondary',
                        };
                    @endphp

                    {{-- 🔥 Action By will be shown if there is--}}
                    @if(!empty($actionBy) && $actionBy !== '-')
                        <span class="badge badge-{{ $actionColor }}">
                        {{ $actionBy }}
                        </span>

                        <span class="badge badge-{{ $stageColor }}">
                        {{ \App\Helpers\PayrollHelper::stageLabel($stage) }}
                        </span>
                    @endif
                </td>

                <td>
                    {{-- 🔥 Last note --}}
                    {{ $item->computed['last_note'] }}
                </td>


                {{-- Actions --}}
                <td>

                    @php
                        $payment = $item->payment_data;
                    @endphp

                    {{-- Paid display --}}
                    @if(($payment['paid_amount'] ?? 0) > 0)
                        <div class="mb-1">
                            Paid: {{ number_format($payment['paid_amount'], 2) }}
                        </div>
                    @endif

                    @php
                        $payload = [
                            "id" => $item->id,

                           "employee" => [
                                "name" => $item->employee?->name ?? '',
                                "department" => [
                                    "name" => $item->employee?->department?->name ?? '',
                                    "super_department" => [
                                        "name" => $item->employee?->department?->super_department?->name ?? ''
                                    ]
                                ],
                                "designation" => [
                                    "name" => $item->employee?->designation?->name ?? ''
                                ],

                                // 🔥 VERY IMPORTANT FIX
                                "payroll_balance" => (float) ($payment['new_balance'] ?? $item->employee?->payroll_balance ?? 0),
                            ],

                            "month" => $item->month_date,
                            "total_salary" => (float) $item->total_salary,

                            // 🔥 SAFE ALWAYS ARRAY ACCESS
                            "previous_balance" => (float) ($payment['previous_balance'] ?? 0),
                            "current_balance"  => (float) ($payment['new_balance'] ?? $item->employee?->payroll_balance ?? 0),
                            "paid_amount"      => (float) ($payment['paid_amount'] ?? 0),
                            "due_amount"       => (float) ($payment['due_amount'] ?? $item->total_salary),
                        ];
                    @endphp

                    {{-- SINGLE BUTTON ONLY (NO DUPLICATE) --}}
                    <button type="button"
                            class="btn btn-sm {{ ($payment['paid_amount'] ?? 0) > 0 ? 'btn-info' : 'btn-success' }}"
                            onclick="startPayment(this)"
                            data='@json($payload)'>

                        @if(($payment['paid_amount'] ?? 0) > 0)
                            <i class="fa fa-pencil"></i> Edit
                        @else
                            <i class="fa fa-money-bill"></i> Pay
                        @endif

                    </button>

                    @if($item->computed['canView'])
                        <a class="btn btn-info btn-sm"
                           href="{{ route('payslip.show', $item->id) }}">
                            View
                        </a>
                    @endif

                    @if($item->computed['canApprove'])
                        <button type="button"
                                class="btn btn-primary btn-sm btn-approve"
                                data-id="{{ $item->id }}">
                            <i class="fa fa-check mr-1"></i> Approve
                        </button>
                    @endif

                    @if($item->computed['canFinalApprove'])

                        {{--                            @if($item->stageNiceName()=="HR")--}}

                        {{--                                <a href="{{ route('payslip.show', $item->id) }}?action=regenerate"--}}
                        {{--                                   class="btn btn-warning btn-sm">--}}
                        {{--                                    <i class="fa fa-refresh"></i> Go to Re-Generate--}}
                        {{--                                </a>--}}
                        {{--                            @endif--}}

                        @if($item->computed['canSendNext'] && $item->nextStage())
                            <button type="button"
                                    class="btn btn-success btn-sm btn-next"
                                    data-id="{{ $item->id }}">
                                → Send to {{ $item->computed['nextStageLabel'] }}
                            </button>
                        @endif

                        {{--                        @if($item->previousStage())--}}
                        {{--                                    <a href="{{ route('payslip.show', $item->id) }}" class="btn btn-warning btn-sm">← Go Return to {{ $item->computed['prevStageLabel'] ?? 'Previous Stage' }}</a>--}}
                        {{--                        @endif--}}

                    @endif

                </td>

            </tr>
        @endforeach

        </tbody>
    </table>
</div>

<style type="text/css">

    /* ================= TABLE WRAPPER ================= */

    .table-wrapper {
        max-height: 70vh;
        overflow: auto;
        position: relative;
        border: 1px solid #eee;
        scroll-behavior: smooth;
    }

    /* ================= HEADER STICKY ================= */

    .table-wrapper thead th {
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 80;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    /* ================= COLUMN WIDTH ================= */

    .col-checkbox {
        width: 45px;
        min-width: 45px;
        max-width: 45px;
        text-align: center;
    }

    .col-sl {
        width: 60px;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
    }

    .col-employee {
        width: 200px;
        min-width: 200px;
        max-width: 200px;
    }

    /* ================= EMPLOYEE INFO ================= */

    .employee-info {
        line-height: 1.2;
    }

    .employee-name {
        font-weight: 600;
        font-size: 11px;
        line-height: 1.2;
        white-space: normal;
        word-break: break-word;
    }

    .employee-code {
        font-size: 10px;
        color: #777;
        margin-top: 2px;
    }

    /* ================= LEFT STICKY ================= */

    /* Checkbox */

    .table-wrapper th.col-checkbox,
    .table-wrapper td.col-checkbox {
        position: sticky;
        left: 0;
        z-index: 100;
        background: #fff;
        box-shadow: 2px 0 4px rgba(0,0,0,0.05);
    }

    /* SL */

    .table-wrapper th.col-sl,
    .table-wrapper td.col-sl {
        position: sticky;
        left: 45px;
        z-index: 99;
        background: #fff;
    }

    /* EMPLOYEE */

    .table-wrapper th.col-employee,
    .table-wrapper td.col-employee {
        position: sticky;
        left: 105px;
        z-index: 98;
        background: #fff;
        box-shadow: 2px 0 6px rgba(0,0,0,0.06);
    }

    /* ================= REMOVE OLD FIXED CODE COLUMN ================= */

    .table-wrapper th.col-code,
    .table-wrapper td.col-code {
        position: static !important;
        left: auto !important;
        z-index: auto !important;
        background: inherit !important;
        box-shadow: none !important;
    }

    /* ================= REMOVE RIGHT FIXED ACTION ================= */

    .table-wrapper th:last-child,
    .table-wrapper td:last-child {
        position: static !important;
        right: auto !important;
        z-index: auto !important;
        background: inherit !important;
        box-shadow: none !important;
    }

    /* ================= TABLE STYLE ================= */

    .table td,
    .table th {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 12px;
        padding: 5px 7px;
    }

    /* ================= ROW HOVER ================= */

    .table tbody tr:hover {
        background: #f7f9fc;
    }

    /* ================= SELECTED ROW ================= */

    /* ================= SELECTED ROW ================= */
    .table tbody tr.row-selected td {
        background: #cfe5ff !important;
        transition: background 0.2s ease;
    }

    /* Sticky columns should not be white.*/
    .table tbody tr.row-selected td.col-checkbox,
    .table tbody tr.row-selected td.col-sl,
    .table tbody tr.row-selected td.col-employee {
        background: #b8d8ff !important;
    }

    /* hover + selected */
    .table tbody tr.row-selected:hover td {
        background: #b8d8ff !important;
    }

    /* checkbox slightly visible */
    .table tbody tr.row-selected input[type="checkbox"] {
        transform: scale(1.08);
    }

    /* ================= CHECKBOX SIZE ================= */

    input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    /* ================= TOPBAR ================= */

    .table-topbar {
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        position: sticky;
        top: 0;
        z-index: 120;
        background: #fff;
        padding: 8px 10px;
        border-bottom: 1px solid #eee;
    }

    /* LEFT SIDE INFO */

    .table-topbar .left {
        flex: 1;
    }

    .table-topbar .left h4 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
    }

    /* RIGHT SIDE SEARCH */

    .table-topbar .right {
        display: flex;
        justify-content: flex-end;
        flex: 0 0 auto;
    }

    /* SEARCH BOX */

    .table-topbar .right input {
        width: 220px;
        font-size: 13px;
        height: 32px;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
    }

    /* ================= BADGE ================= */

    .badge {
        font-size: 11px;
        padding: 5px 7px;
    }

    /* ================= BUTTON ================= */

    .btn-sm {
        font-size: 11px;
        padding: 3px 7px;
    }

    /* ================= SCROLLBAR ================= */

    .table-wrapper::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }

    .table-wrapper::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }

    .table-wrapper::-webkit-scrollbar-track {
        background: #f5f5f5;
    }

</style>

<script>

    $(function () {

        // ================= CHECK ALL =================
        $('#checkAll').on('change', function () {

            let isChecked = this.checked;

            $('input[name="ids[]"]:not(:disabled)').each(function () {
                $(this).prop('checked', isChecked);
                $(this).closest('tr').toggleClass('row-selected', isChecked);
            });
        });

        // ================= SINGLE CHECK =================
        $(document).on('change', 'input[name="ids[]"]', function () {

            let row = $(this).closest('tr');

            row.toggleClass('row-selected', this.checked);

            // auto uncheck "check all" if any unchecked
            if (!this.checked) {
                $('#checkAll').prop('checked', false);
            }
        });

    });


    // ================= SEARCH =================

    function debounce(fn, delay = 200) {
        let timer;

        return function (...args) {
            clearTimeout(timer);

            const context = this;

            timer = setTimeout(() => {
                fn.apply(context, args);
            }, delay);
        };
    }


    $(function () {

        // ================= SEARCH =================

        function debounce(fn, delay = 200) {
            let timer;

            return function (...args) {
                clearTimeout(timer);

                let context = this;

                timer = setTimeout(() => {
                    fn.apply(context, args);
                }, delay);
            };
        }

        const $rows = $('.table-wrapper tbody tr'); // 🔥 IMPORTANT: correct scope

        function searchHandler() {

            let value = $('#tableSearch').val().toLowerCase().trim();

            $rows.each(function () {

                let text = $(this).text().toLowerCase();

                $(this).toggle(text.indexOf(value) !== -1);
            });
        }

        const debouncedSearch = debounce(searchHandler, 200);

        // input event
        $('#tableSearch').on('input', debouncedSearch);

        // prevent Enter submit
        $('#tableSearch').on('keydown', function (e) {
            if (e.key === 'Enter') e.preventDefault();
        });

    });

    $(function () {

        $('#checkAll').on('change', function () {
            $('input[name="ids[]"]').prop('checked', this.checked);
        });

        function getIds() {
            return $('input[name="ids[]"]:checked').map(function () {
                return $(this).val();
            }).get();
        }

        // 🔥 reusable confirm
        function confirmAction(message) {
            return new Promise((resolve) => {
                if (window.confirm(message)) {
                    resolve(true);
                } else {
                    resolve(false);
                }
            });
        }

        async function action(url, ids, btn, message = 'Are you sure?') {

            if (!ids.length) {
                toastr.warning('Select at least one');
                return;
            }

            // 🔥 confirm before
            let ok = await confirmAction(message);
            if (!ok) return;

            $(btn).prop('disabled', true);

            $.post(url, {ids: ids})
                .done(res => {
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 1000);
                })
                .fail(() => toastr.error('Error'))
                .always(() => $(btn).prop('disabled', false));
        }

        // ================= BULK APPROVE =================
        $(document).on('click', '.btn-bulk-approve', function () {
            action(
                "{{ route('payroll-approval.approve') }}",
                getIds(),
                this,
                'Approve selected payslips?'
            );
        });

        // ================= NEXT =================
        $('#btnNext').click(function () {
            action(
                "{{ route('payroll-approval.next') }}",
                getIds(),
                this,
                'Send selected payslips to next stage?'
            );
        });

        // ================= Return =================
        $('#btnReturn').click(function () {
            action(
                "{{ route('payroll-approval.return') }}",
                getIds(),
                this,
                'Move selected payslips return to previous stage?'
            );
        });

        // ================= SINGLE APPROVE =================
        $(document).on('click', '.btn-approve', function () {
            action(
                "{{ route('payroll-approval.approve') }}",
                [$(this).data('id')],
                this,
                'Approve this payslip?'
            );
        });

        // ================= SINGLE NEXT =================
        $(document).on('click', '.btn-next', function () {
            action(
                "{{ route('payroll-approval.next') }}",
                [$(this).data('id')],
                this,
                'Send this payslip to next stage?'
            );
        });

        // ================= SINGLE Return =================
        $(document).on('click', '.btn-return', function () {
            action(
                "{{ route('payroll-approval.return') }}",
                [$(this).data('id')],
                this,
                'Move this payslip return?'
            );
        });

    });
</script>