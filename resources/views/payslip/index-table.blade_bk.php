@php
    $user = auth()->user();
@endphp

<form method="POST">
    @csrf

    {{-- Bulk Approve --}}

    @php
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

            {{--            @if($finalItem->nextStage())--}}
            {{--                <button type="button" class="btn btn-success" id="btnNext">--}}
            {{--                    → Send to {{ $finalItem->computed['nextStageLabel'] }}--}}
            {{--                </button>--}}
            {{--            @endif--}}

            @if($finalItem->nextStage())
                <a href="?action=ready_to_send"
                   class="btn btn-warning">
                    <i class="fa fa-paper-plane mr-1"></i>
                    Go to Ready to Send
                </a>
            @endif
        </div>
    @endif

    <table class="table">
        <thead>
        <tr>
            <th>
                @if($canApprove)
                    <input type="checkbox" id="checkAll"></th>
            @endif

            <th>Employee</th>
            <th>Month</th>
            <th>Salary</th>
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
                <td>
                    <input type="checkbox"
                           name="ids[]"
                           value="{{ $item->id }}"
                            {{ !$item->computed['canApprove'] ? 'disabled' : '' }}>
                </td>

                <td>{{ $item->employee->name }}</td>

                <td>{{ \Carbon\Carbon::parse($item->month_date)->format('M Y') }}</td>

                <td>{{ $item->total_salary }}</td>

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

                    {{-- 🔥 Action By থাকলে দেখাবে --}}
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
                    {{-- 🔥 সর্বশেষ note --}}
                    {{ $item->computed['last_note'] }}
                </td>

                {{-- Actions --}}
                <td>

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
</form>

<script>
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

            // 🔥 confirm আগে
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