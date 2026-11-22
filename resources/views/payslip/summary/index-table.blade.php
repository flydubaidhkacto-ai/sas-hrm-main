@php

    $user = auth()->user();

    $finalItem = $payslips->first(
        fn($p) => $p->computed['canFinalApprove']
    );

    $canApprove = $payslips->first(
        fn($p) => $p->computed['canApprove']
    );


@endphp


<style>

    /* ================= TOPBAR ================= */

    .table-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: nowrap;
        gap: 10px;

        margin-bottom: 10px;

        position: sticky;
        top: 0;
        z-index: 120;

        background: #fff;
        padding: 8px 10px;
        border-bottom: 1px solid #eee;
    }

    /* LEFT */

    .table-topbar .left {
        display: flex;
        align-items: center;
        flex: 1;
    }

    .table-topbar .left h4 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        white-space: nowrap;
    }

    /* RIGHT */

    .table-topbar .right {
        display: flex;
        justify-content: flex-end;
        align-items: center;

        flex-shrink: 0;
    }

    /* SEARCH */

    .table-topbar .right input {
        width: 220px;
        min-width: 220px;

        font-size: 13px;
        height: 32px;

        padding: 4px 10px;

        border-radius: 6px;
        border: 1px solid #ccc;
    }

    .table-wrapper {

        max-height: 70vh;

        overflow: auto;

        position: relative;

        border: 1px solid #eee;

        scroll-behavior: smooth;
    }

    .table-wrapper thead th {

        position: sticky;

        top: 0;

        background: #fff;

        z-index: 80;

        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .col-sl {

        width: 60px;

        min-width: 60px;

        max-width: 60px;

        text-align: center;
    }

    .col-employee {

        width: 220px;

        min-width: 220px;

        max-width: 220px;
    }

    /*
    |--------------------------------------------------------------------------
    | STICKY COLUMN
    |--------------------------------------------------------------------------
    */

    .table-wrapper th.col-sl,
    .table-wrapper td.col-sl {

        position: sticky;

        left: 0;

        z-index: 100;

        background: #fff;

        box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
    }

    .table-wrapper th.col-employee,
    .table-wrapper td.col-employee {

        position: sticky;

        left: 60px;

        z-index: 99;

        background: #fff;

        box-shadow: 2px 0 6px rgba(0, 0, 0, 0.06);
    }

    /*
    |--------------------------------------------------------------------------
    | REMOVE FIXED
    |--------------------------------------------------------------------------
    */

    .table-wrapper th.col-code,
    .table-wrapper td.col-code {

        position: static !important;

        left: auto !important;

        z-index: auto !important;

        background: inherit !important;

        box-shadow: none !important;
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE STYLE
    |--------------------------------------------------------------------------
    */

    .table td,
    .table th {

        white-space: nowrap;

        vertical-align: middle;

        font-size: 12px;

        padding: 5px 7px;
    }

    /*
    |--------------------------------------------------------------------------
    | ROW HOVER
    |--------------------------------------------------------------------------
    */

    .table tbody tr:hover {

        background: #f7f9fc;

        cursor: pointer;
    }

    /*
    |--------------------------------------------------------------------------
    | SELECTED ROW
    |--------------------------------------------------------------------------
    */

    .table tbody tr.row-selected td {

        background: #cfe5ff !important;

        transition: background 0.2s ease;
    }

    .table tbody tr.row-selected td.col-sl,
    .table tbody tr.row-selected td.col-employee {

        background: #b8d8ff !important;
    }

    .table tbody tr.row-selected:hover td {

        background: #b8d8ff !important;
    }

    /*
    |--------------------------------------------------------------------------
    | RETURNED ROW
    |--------------------------------------------------------------------------
    */

    .returned-row {

        background-color: #ffe5e5;
    }

    /*
    |--------------------------------------------------------------------------
    | BADGE
    |--------------------------------------------------------------------------
    */

    .badge {

        font-size: 11px;

        padding: 5px 7px;
    }

    /*
    |--------------------------------------------------------------------------
    | BUTTON
    |--------------------------------------------------------------------------
    */

    .btn-sm {

        font-size: 11px;

        padding: 3px 7px;
    }

    /*---------- Only For Print and PDF*/

    .report-toolbar {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    /* base button */
    .btn-report {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;

        height: 32px;
        padding: 4px 10px;

        font-size: 12px;
        font-weight: 600;

        border-radius: 6px;

        min-width: 85px;

        transition: 0.2s ease-in-out;
    }

    /* icons */
    .btn-report i {
        font-size: 13px;
    }

    /* PDF style */
    .btn-pdf {
        background: #e74c3c;
        border: none;
        color: #fff;
    }

    .btn-pdf:hover {
        background: #c0392b;
    }

    /* Print style */
    .btn-print {
        background: #34495e;
        border: none;
        color: #fff;
    }

    .btn-print:hover {
        background: #2c3e50;
    }

</style>

<script>

    const PAYROLL_FILTERS = {
        employee: @json(request('employee')),
        department: @json(request('department')),
        super_department: @json(request('super_department')),
        month: @json(request('month')),
    };

    function getPayrollFilters() {
        return {
            employee: $('select[name="employee"]').val() || '',
            department: $('select[name="department"]').val() || '',
            super_department: $('select[name="super_department"]').val() || '',
            designation: $('select[name="designation"]').val() || '',
            month: $('input[name="month"]').val() || '',
            payment_method: $('select[name="payment_method"]').val() || '',
            status: $('select[name="status"]').val() || '',
            current_stage: $('select[name="current_stage"]').val() || '',
        };
    }

    function exportPdf() {

        let filters = getPayrollFilters();

        let form = $('<form>', {
            method: 'GET',
            action: "{{ route('payslip.summary-pdf') }}"
        });

        Object.keys(filters).forEach(key => {
            form.append($('<input>', {
                type: 'hidden',
                name: key,
                value: filters[key]
            }));
        });

        $('body').append(form);
        form.submit();
    }

    function exportPrint() {

        let filters = getPayrollFilters();

        let form = $('<form>', {
            method: 'GET',
            action: "{{ route('payslip.summary-print') }}",
            target: "_blank"
        });

        Object.keys(filters).forEach(key => {
            form.append($('<input>', {
                type: 'hidden',
                name: key,
                value: filters[key]
            }));
        });

        $('body').append(form);
        form.submit();
    }


</script>