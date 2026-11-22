<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Salary Structure</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-size: 11px;
            font-family: Arial, sans-serif;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table th, table td {
            border: 1px solid #000 !important;
            padding: 4px;
            vertical-align: middle;
        }

        table th, table td {
            text-align: center;
        }

        /* Right-align numeric cells */
        .salary, .deduction {
            text-align: right;
        }

        /* Department / Group headers */
        .group-title {
            font-weight: bold;
            background: #e9ecef;
            padding: 4px 8px;
            text-align: left;
        }

        /* Total row */
        .total-row {
            font-weight: bold;
            background: #f1f1f1;
        }

        /* Signature styling */
        .signature-line {
            border-bottom: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px auto;
        }

        .signature-label {
            margin-top: 2px;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
        }

        @media print {
            .no-print {
                display: none;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">

    <!-- Print Button -->
    <div class="text-end no-print mb-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm">Print</button>
    </div>

    <!-- Header -->
    <div class="text-center mb-2">
        <h5 class="fw-bold">Sky Aviation Services Ltd.</h5>
        <h5 class="fw-bold">Bank Salary Payment For The Month of February 2026</h5>
    </div>

    <!-- Salary Table -->
    <table class="table table-bordered">
        <thead>
        <tr>
            <td colspan="20" class="group-title">
                <span style="float: left;"><b>Station:</b> DAC CTO</span> <span style="float: right;"><b>Ref:</b> HR/SR-02/2026 <br><b>Date:</b> 01-03-26</span>
            </td>
        </tr>

        <tr>
            <th rowspan="2">Sl</th>
            <th rowspan="2">Staff ID</th>
            <th rowspan="2">Name of Staff</th>
            <th rowspan="2">Designation</th>
            <th rowspan="2">Date of Joining</th>
            <th rowspan="2">Total Working Days</th>
            <th rowspan="2">Grade</th>

            <th colspan="5" class="salary">Salary (BDT)</th>
            <th colspan="6" class="deduction">Deduction (BDT)</th>

            <th rowspan="2" class="salary">Net Payable</th>
            <th rowspan="2">Remarks</th>
        </tr>

        <tr>
            <th class="salary">Basic</th>
            <th class="salary">House Rent</th>
            <th class="salary">Allowance</th>
            <th class="salary">OT</th>
            <th class="salary">Gross</th>

            <th class="deduction">Income Tax</th>
            <th class="deduction">Personal Loan</th>
            <th class="deduction">Absent/Others</th>
            <th class="deduction">Others</th>
            <th class="deduction">PF (7%)</th>
            <th class="deduction">Total Deduction</th>
        </tr>
        </thead>

        <tbody>
        <!-- Department Admin -->
        <tr>
            <td colspan="20" class="group-title">Department: Admin</td>
        </tr>

        <tr>
            <td>1</td>
            <td>GS-101</td>
            <td>John Doe</td>
            <td>Supervisor</td>
            <td>01-01-2020</td>
            <td>26</td>
            <td>A</td>
            <td class="salary">20,000</td>
            <td class="salary">8,000</td>
            <td class="salary">2,000</td>
            <td class="salary">2,000</td>
            <td class="salary">30,000</td>
            <td class="deduction">1,000</td>
            <td class="deduction">1,000</td>
            <td class="deduction">1,000</td>
            <td class="deduction">0</td>
            <td class="deduction">1,400</td>
            <td class="deduction">2,400</td>
            <td class="salary">27,600</td>
            <td>-</td>
        </tr>

        <tr>
            <td>2</td>
            <td>GS-102</td>
            <td>Jane Smith</td>
            <td>Officer</td>
            <td>05-03-2021</td>
            <td>26</td>
            <td>B</td>
            <td class="salary">18,000</td>
            <td class="salary">7,000</td>
            <td class="salary">1,500</td>
            <td class="salary">26,500</td>
            <td class="salary">53,000</td>
            <td class="deduction">800</td>
            <td class="deduction">500</td>
            <td class="deduction">500</td>
            <td class="deduction">500</td>
            <td class="deduction">1,200</td>
            <td class="deduction">1,200</td>
            <td class="salary">24,000</td>
            <td>-</td>
        </tr>

        <!-- Department HR -->
        <tr>
            <td colspan="20" class="group-title">Department: HR</td>
        </tr>

        <tr>
            <td>3</td>
            <td>HR-01</td>
            <td>Rahim Uddin</td>
            <td>HR Manager</td>
            <td>10-02-2019</td>
            <td>26</td>
            <td>A</td>
            <td class="salary">25,000</td>
            <td class="salary">10,000</td>
            <td class="salary">2,000</td>
            <td class="salary">37,000</td>
            <td class="salary">74,000</td>
            <td class="deduction">1,500</td>
            <td class="deduction">1,500</td>
            <td class="deduction">1,500</td>
            <td class="deduction">1,500</td>
            <td class="deduction">0</td>
            <td class="deduction">1,750</td>
            <td class="salary">33,750</td>
            <td>-</td>
        </tr>

        <!-- Total Row -->
        <tr class="total-row">
            <td colspan="7">Total</td>
            <td class="salary">63,000</td>
            <td class="salary">25,000</td>
            <td class="salary">5,500</td>
            <td class="salary">65,500</td>
            <td class="salary">137,500</td>
            <td class="deduction">3,300</td>
            <td class="deduction">3,000</td>
            <td class="deduction">3,000</td>
            <td class="deduction">2,000</td>
            <td class="deduction">2,600</td>
            <td class="deduction">5,350</td>
            <td class="salary">85,350</td>
            <td>-</td>
        </tr>

        </tbody>
    </table>


    <!-- Signature Section -->
    <div class="row mt-5">
        <!-- Prepared By -->
        <div class="col-3 text-center">
            <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 10px auto;"></div>
            <strong>Prepared By</strong>
            <div class="text-start mt-2" style="font-size: 11px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span>Name & Sign:</span> <span
                            style=" flex:1; margin-left:5px;"></span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span>Staff ID:</span> <span style=" flex:1; margin-left:5px;"></span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Date:</span> <span style=" flex:1; margin-left:5px;"></span>
                </div>
            </div>
        </div>

        <!-- Checked By -->
        <div class="col-3 text-center">
            <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 10px auto;"></div>
            <strong>Checked By</strong>
            <div class="text-start mt-2" style="font-size: 11px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span>Name & Sign:</span> <span
                            style=" flex:1; margin-left:5px;"></span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span>Staff ID:</span> <span style=" flex:1; margin-left:5px;"></span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Date:</span> <span style=" flex:1; margin-left:5px;"></span>
                </div>
            </div>
        </div>

        <!-- Verified By -->
        <div class="col-3 text-center">
            <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 10px auto;"></div>
            <strong>Verified By</strong>
            <div class="text-start mt-2" style="font-size: 11px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span>Name & Sign:</span> <span
                            style=" flex:1; margin-left:5px;"></span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span>Staff ID:</span> <span style=" flex:1; margin-left:5px;"></span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Date:</span> <span style=" flex:1; margin-left:5px;"></span>
                </div>
            </div>
        </div>

        <!-- Approved By -->
        <div class="col-3 text-center">
            <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 10px auto;"></div>
            <strong>Approved By</strong>
            <div class="text-start mt-2" style="font-size: 11px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span>Name & Sign:</span> <span
                            style=" flex:1; margin-left:5px;"></span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span>Staff ID:</span> <span style=" flex:1; margin-left:5px;"></span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Date:</span> <span style=" flex:1; margin-left:5px;"></span>
                </div>
            </div>
        </div>
    </div>

</div>
</body>
</html>