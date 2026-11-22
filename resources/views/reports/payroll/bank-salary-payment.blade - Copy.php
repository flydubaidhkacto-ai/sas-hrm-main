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
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table th, table td {
            border: 1px solid #000 !important;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        .group-title {
            text-align: left;
            font-weight: bold;
            background: #f2f2f2;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">

    <div class="text-end no-print mb-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm">Print</button>
    </div>

    <div class="text-center mb-2">
        <h5 class="fw-bold">Sky Aviation Services Ltd.</h5>
        <h5 class="fw-bold">Bank Salary Payment For The Month of February 2026</h5>
    </div>

    <table class="table table-bordered">

        <thead>

        <!-- ===================== STATION 1 ===================== -->

        <tr>
            <td colspan="20" class="group-title">
                <span style="float: left;"><b>Station:</b> DAC CTO</span>  <span style="float: right;"><b>Ref:</b> HR/SR-02/2026 <br><b>Date:</b> 01-03-26</span>
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

            <th colspan="5">Salary (BDT)</th>
            <th colspan="6">Deduction (BDT)</th>

            <th rowspan="2">Net payable salary for Feb '26(BDT)</th>
            <th rowspan="2">Remarks</th>
        </tr>

        <tr>
            <th>Basic Salary</th>
            <th>House Rent</th>
            <th>Allowance</th>
            <th>OT</th>
            <th>Gross Salary</th>

            <th>Income Tax</th>
            <th>Personal Loan</th>
            <th>Absent/Others</th>
            <th>Others</th>
            <th>PF (7%)</th>
            <th>Total Deduction</th>
        </tr>

        </thead>

        <tbody>




        <!-- Department -->
        <tr>
            <td colspan="20" class="group-title">
                Department: Admin
            </td>
        </tr>

        <tr>
            <td>1</td>
            <td>GS-101</td>
            <td>John Doe</td>
            <td>Supervisor</td>

            <td>01-01-2020</td>

            <td>26</td>
            <td>A</td>

            <td>20,000</td>
            <td>8,000</td>
            <td>2,000</td>
            <td>2,000</td>
            <td>30,000</td>

            <td>1,000</td>
            <td>1,000</td>
            <td>1,000</td>
            <td>0</td>
            <td>1,400</td>
            <td>2,400</td>

            <td>27,600</td>
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

            <td>18,000</td>
            <td>7,000</td>
            <td>1,500</td>
            <td>26,500</td>

            <td>800</td>
            <td>500</td>
            <td>500</td>
            <td>500</td>
            <td>1,200</td>
            <td>1,200</td>
            <td>2,500</td>

            <td>24,000</td>
            <td>-</td>
        </tr>

        <!-- Another Department under same Super Department -->

        <tr>
            <td colspan="20" class="group-title">
                Department: HR
            </td>
        </tr>

        <tr>
            <td>3</td>
            <td>HR-01</td>
            <td>Rahim Uddin</td>
            <td>HR Manager</td>

            <td>10-02-2019</td>

            <td>26</td>
            <td>A</td>

            <td>25,000</td>
            <td>10,000</td>
            <td>2,000</td>
            <td>37,000</td>

            <td>1,500</td>
            <td>1,500</td>
            <td>1,500</td>
            <td>1,500</td>
            <td>0</td>
            <td>1,750</td>
            <td>3,250</td>

            <td>33,750</td>
            <td>-</td>
        </tr>

        <!-- Total Row -->
        <tr class="total-row">
            <td colspan="5">Total</td>
            <td class="salary">63,000</td>
            <td class="salary"></td>
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
            <td class="salary">85,350</td>
            <td class="salary">85,350</td>
            <td>-</td>
        </tr>

        <!-- ===================== STATION 2 ===================== -->

        </tbody>
    </table>

    <!-- Signature Section -->

    <div class="row mt-5">
        <div class="col-3 text-center">
            <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 5px auto;"></div>
            <strong>Prepared By</strong><br>
            Name & Sign:<br>
            Staff ID:<br>
            Date:
        </div>

        <div class="col-3 text-center">
            <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 5px auto;"></div>
            <strong>Checked By</strong><br>
            Name & Sign:<br>
            Staff ID:<br>
            Date:
        </div>

        <div class="col-3 text-center">
            <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 5px auto;"></div>
            <strong>Verified By</strong><br>
            Name & Sign:<br>
            Staff ID:<br>
            Date:
        </div>

        <div class="col-3 text-center">
            <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 5px auto;"></div>
            <strong>Approved By</strong><br>
            Name & Sign:<br>
            Staff ID:<br>
            Date:
        </div>
    </div>

</div>
</body>
</html>