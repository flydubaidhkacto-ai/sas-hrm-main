<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="HR & Payroll System for Employee Management">
    <meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern, accounts, invoice, html5, responsive, CRM, Projects">
    <meta name="robots" content="noindex, nofollow">
    <title>Employee Details - {{ $employee->name }}</title>

    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap.min.css') }}">
    <style>
        body {
            background: #fff;
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .print-container {
            max-width: 1140px;
            margin: 20px auto;
            padding: 30px;
            background: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .company-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .company-address {
            color: #6c757d;
            font-size: 16px;
        }

        .employee-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .employee-photo {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .employee-basic-info {
            margin-left: 30px;
        }

        .employee-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .employee-position {
            font-size: 18px;
            color: #6c757d;
        }

        .info-section {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .section-title {
            color: #2c3e50;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5px;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .info-value {
            color: #2c3e50;
            font-size: 16px;
        }

        .document-img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin-top: 10px;
        }

        @media print {
            body {
                background: #fff;
            }
            .print-container {
                box-shadow: none;
                margin: 0;
                padding: 15px;
            }
            .info-section {
                break-inside: avoid;
            }
        }
    </style>
</head>
@php
    $info = company_data();
@endphp
<body>
    <div class="print-container">
        <div class="company-header">
            <h1 class="company-name">{{$info['name'] ?? 'Pro HR'}}</h1>
            <p class="company-address">{{$info['address'] ?? ''}}</p>
        </div>

        <div class="employee-header">
            <div class="text-center">
                @if ($employee->picture_file)
                    <img src="/uploaded_files/{{ $employee->picture_file }}" class="employee-photo" alt="Employee Photo">
                @endif
            </div>
            <div class="employee-basic-info">
                <h2 class="employee-name">{{ $employee->name }}</h2>
                <p class="employee-position">
                    {{ $employee->designation?->name ?? '--' }} |
                    {{ $employee->department?->name ?? '--' }}
                </p>
                <p class="employee-id">Employee Code: {{ $employee->id ?? '--' }}</p>
                <p class="employee-id">Employee ID: {{ $employee->employee_id ?? '--' }}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="info-section">
                    <h3 class="section-title">Personal Information:</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label class="info-label">Full Name:</label>
                            <span class="info-value">{{ $employee->name }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Phone:</label>
                            <span class="info-value">{{ $employee->phone_number ?? '--' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Email:</label>
                            <span class="info-value">{{ $employee->email ?? '--' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Father's name:</label>
                            <span class="info-value">{{ $employee->father_name ?? '--' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Mother's name:</label>
                            <span class="info-value">{{ $employee->mother_name ?? '--' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Date of Birth:</label>
                            <span class="info-value">{{ $employee->birth_date ? carbon($employee->birth_date)->format('d F, Y') : '--' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Gender:</label>
                            <span class="info-value">{{ $employee->gender ?? '--' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Address:</label>
                            <span class="info-value">{{ $employee->address ?? '--' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Blood Group:</label>
                            <span class="info-value">{{ $employee->blood_group ?? '--' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Religion:</label>
                            <span class="info-value">{{ $employee->religion ?? '--' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Nationality:</label>
                            <span class="info-value">{{ $employee->nationality ?? '--' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Marital Status:</label>
                            <span class="info-value">{{ $employee->marital_status ?? '--' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">NID/Passport No:</label>
                            <span class="info-value">{{ $employee->nid_or_passport ?? '--' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="info-section">
                    <h3 class="section-title">Employment Details:</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label class="info-label">Joining Date:</label>
                            <span class="info-value">{{ $employee->join_date ? carbon($employee->join_date)->format('d F, Y') : '--' }}
                            </span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Department:</label>
                            <span class="info-value">{{ $employee->department?->name ?? '--' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Designation:</label>
                            <span class="info-value">{{ $employee->designation?->name ?? '--' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Employee Type:</label>
                            <span class="info-value">{{ $employee->employee_type ?? '--' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Salary:</label>
                            <span class="info-value">{{ $employee->salary ?? '--' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Overtime:</label>
                            <span class="info-value">{{ $employee->can_overtime ? 'Yes' : 'No' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Outside Duty:</label>
                            <span class="info-value">{{ $employee->can_outside_attendence ? 'Yes' : 'No' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Get Late Check-In warning via SMS:</label>
                            <span class="info-value">{{ $employee->get_attendance_sms_notification ? 'Yes' : 'No' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3 class="section-title">Emergency Contact Info:</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label class="info-label">Person Name:</label>
                    <span class="info-value">{{ $employee->emergency_name ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <label class="info-label">Relation</label>
                    <span class="info-value">{{ $employee->emergency_relation ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <label class="info-label">Phone</label>
                    <span class="info-value">{{ $employee->emergency_phone ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <label class="info-label">Address</label>
                    <span class="info-value">{{ $employee->emergency_address ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <label class="info-label">Role</label>
                    <span class="info-value">{{ $employee->assinged_role?->name ?: 'None' }}</span>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3 class="section-title">Bank Details</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label class="info-label">Account Holder Name:</label>
                    <span class="info-value">{{ $employee->account_holder_name ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <label class="info-label">Account Number:</label>
                    <span class="info-value">{{ $employee->account_number ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <label class="info-label">Bank Name:</label>
                    <span class="info-value">{{ $employee->bank_name ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <label class="info-label">Bank Branch:</label>
                    <span class="info-value">{{ $employee->bank_branch ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <label class="info-label">Routing Number:</label>
                    <span class="info-value">{{ $employee->routing_number ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <label class="info-label">TIN Number:</label>
                    <span class="info-value">{{ $employee->tin_number ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <label class="info-label">Tax Number:</label>
                    <span class="info-value">{{ $employee->tax_number ?? '--' }}</span>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3 class="section-title">Reference:</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label class="info-label">Name:</label>
                    <span class="info-value">{{ $employee->reference_name ?? '--' }}</span>
                </div>
                <div class="info-item">
                    <label class="info-label">Phone:</label>
                    <span class="info-value">{{ $employee->reference_phone ?? '--' }}</span>
                </div>
            </div>
        </div>

        @if($employee->nid_or_passport_file)
        <div class="info-section">
            <h3 class="section-title">Documents</h3>
            <div class="info-item">
                <div class="info-label">NID/Passport Picture:</div>
                <img src="/uploaded_files/{{ $employee->nid_or_passport_file }}" class="document-img" alt="National ID">
            </div>
        </div>
        @endif
    </div>
</body>
</html>
