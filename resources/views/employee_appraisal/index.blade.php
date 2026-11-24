@extends('layouts.master')

@section('title', 'Employee Appraisal')

@push('style')
    <style>
        .employee-appraisal .appraisal-table {
            border-collapse: collapse;
            width: 100%;
        }

        .employee-appraisal .appraisal-table th,
        .employee-appraisal .appraisal-table td {
            border: 1px solid #8b929b;
            vertical-align: middle;
        }

        .employee-appraisal .section-heading {
            background: #dce1e6;
            border: 1px solid #8b929b;
            font-size: 15px;
            font-weight: 700;
            margin: 22px 0 0;
            padding: 9px;
            text-align: center;
            text-transform: uppercase;
        }

        .employee-appraisal .mark-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .employee-appraisal .mark-grid input {
            left: -9999px;
            position: absolute;
        }

        .employee-appraisal .mark-grid label {
            cursor: pointer;
            margin: 0;
        }

        .employee-appraisal .mark-grid span {
            align-items: center;
            border: 1px solid #6c757d;
            display: inline-flex;
            height: 30px;
            justify-content: center;
            min-width: 32px;
            padding: 0 5px;
        }

        .employee-appraisal .mark-grid input:checked + span {
            background: #343a40;
            border-color: #343a40;
            color: #fff;
        }

        .employee-appraisal .signature-pad {
            background: #fff;
            border: 1px solid #6c757d;
            cursor: crosshair;
            display: block;
            height: 90px;
            touch-action: none;
            width: 100%;
        }

        .employee-appraisal textarea {
            min-width: 100%;
        }

        @media print {
            body,
            .main-wrapper {
                background: #fff !important;
            }

            .employee-appraisal .no-print,
            .header,
            .sidebar,
            .license-warning {
                display: none !important;
            }

            .employee-appraisal .page-wrapper {
                margin: 0 !important;
                padding: 0 !important;
            }

            .employee-appraisal .content {
                padding: 0 !important;
            }

            .employee-appraisal .print-section {
                break-inside: avoid;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $ethicsCriteria = [
            ['Appearance', 'Neglects grooming, dress code, or personal hygiene.', 'Maintains acceptable grooming and follows the dress code.', 'Consistently maintains excellent grooming and personal hygiene.'],
            ['Job Knowledge, Skills & Abilities', 'Lacks knowledge or skills required for the role.', 'Demonstrates adequate knowledge and skills for assigned work.', 'Demonstrates strong expertise and applies it beyond expectations.'],
            ['Quality of Work', 'Work is often inaccurate, incomplete, or below standard.', 'Work is accurate and meets expected quality standards.', 'Work is consistently thorough, accurate, and high quality.'],
            ['Productivity', 'Produces less than the expected amount of work.', 'Maintains an acceptable and consistent level of productivity.', 'Consistently delivers high output efficiently.'],
            ['Attendance', 'Attendance or punctuality needs improvement.', 'Has good attendance and follows the work schedule.', 'Is consistently punctual and dependable.'],
            ['Communication Skills', 'Communication is unclear or affects work.', 'Communicates effectively in routine work situations.', 'Communicates exceptionally in verbal and written interactions.'],
            ['Work Relationship / Behavior', 'Has difficulty working cooperatively with others.', 'Maintains positive and effective working relationships.', 'Promotes cooperation and positive work relationships.'],
            ['Safety and Health', 'Does not consistently follow safety requirements.', 'Follows safety rules and maintains good health practices.', 'Exceeds safety expectations and promotes a safe workplace.'],
            ['Work Supervision / Independability', 'Requires frequent supervision to complete duties.', 'Works dependably with normal supervision.', 'Is highly conscientious, dependable, and self-directed.'],
            ['Job Ethics', 'Needs improvement in professional conduct or integrity.', 'Follows expected ethical and professional standards.', 'Consistently demonstrates exemplary integrity and conduct.'],
        ];

        $performanceStandards = [
            ['Job Knowledge', 'Demonstrates knowledge required to perform assigned duties.'],
            ['Quality of Work', 'Completes accurate, thorough, and reliable work.'],
            ['Quantity of Work', 'Completes an appropriate volume of work within expected timelines.'],
            ['Initiative', 'Identifies work needs and takes appropriate action.'],
            ['Planning and Organization', 'Plans tasks and uses time and resources effectively.'],
            ['Problem Solving', 'Identifies issues and develops practical solutions.'],
            ['Dependability', 'Follows through on commitments and accepts responsibility.'],
            ['Teamwork', 'Cooperates with colleagues and contributes to shared goals.'],
            ['Communication', 'Shares information clearly and professionally.'],
            ['Overall Conduct', 'Meets organizational standards for professional conduct.'],
        ];

        $reviewLevels = ['Supervisor', 'Head of Department', 'Human Resources', 'Managing Director'];
    @endphp

    <div class="page-wrapper employee-appraisal">
        <div class="content container-fluid">
            <div class="page-header no-print">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Employee Appraisal</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">HR Management</li>
                            <li class="breadcrumb-item active">Employee Appraisal</li>
                        </ul>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="fa fa-print"></i> Print / Save PDF
                        </button>
                    </div>
                </div>
            </div>

            <form id="employee-appraisal-form" class="card">
                <div class="card-body">
                    <header class="mb-4 border-bottom pb-3 text-center">
                        <h2 class="h4 font-weight-bold">EMPLOYEE APPRAISAL FORM</h2>
                        <p class="mb-0 text-muted">Annual Performance Review</p>
                    </header>

                    <section class="print-section">
                        <table class="appraisal-table table-sm">
                            <tbody>
                                <tr>
                                    <th class="bg-light p-2">Staff Name</th>
                                    <td class="p-1"><input name="staff_name" value="{{ user()->name }}" class="form-control border-0"></td>
                                    <th class="bg-light p-2">Designation</th>
                                    <td class="p-1"><input name="designation" class="form-control border-0"></td>
                                </tr>
                                <tr>
                                    <th class="bg-light p-2">Department</th>
                                    <td class="p-1"><input name="department" class="form-control border-0"></td>
                                    <th class="bg-light p-2">Station / Location</th>
                                    <td class="p-1"><input name="station_location" class="form-control border-0"></td>
                                </tr>
                                <tr>
                                    <th class="bg-light p-2">Staff ID</th>
                                    <td class="p-1"><input name="staff_id" value="{{ user()->employee_id ?? '' }}" class="form-control border-0"></td>
                                    <th class="bg-light p-2">Date of Joining</th>
                                    <td class="p-1"><input name="date_of_joining" type="date" class="form-control border-0"></td>
                                </tr>
                                <tr>
                                    <th class="bg-light p-2">Last AP Date</th>
                                    <td class="p-1"><input name="last_appraisal_date" type="date" class="form-control border-0"></td>
                                    <th class="bg-light p-2">Appraisal Period</th>
                                    <td class="p-1"><input name="appraisal_period" class="form-control border-0"></td>
                                </tr>
                            </tbody>
                        </table>
                    </section>

                    <section class="print-section">
                        <h3 class="section-heading">Section One: Employee Comment</h3>
                        <p class="border border-top-0 mb-0 p-2 small">Please write below any other information that should inform this appraisal.</p>
                        <textarea name="employee_comments" rows="4" class="form-control rounded-0" placeholder="Employee comments..."></textarea>
                        <div class="border p-3">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex justify-content-between mb-1">
                                        <strong>Employee Signature</strong>
                                        <button type="button" data-clear-signature="employee_signature" class="btn btn-link btn-sm no-print p-0">Clear</button>
                                    </div>
                                    <canvas id="employee_signature" class="signature-pad"></canvas>
                                </div>
                                <div class="col-md-4">
                                    <label for="employee_signature_date">Date</label>
                                    <input id="employee_signature_date" name="employee_signature_date" type="date" class="form-control">
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="print-section">
                        <h3 class="section-heading">Section Two: Job Ethics</h3>
                        <div class="table-responsive">
                            <table class="appraisal-table table-sm">
                                <thead>
                                    <tr class="bg-light">
                                        <th class="p-2 text-center">No.</th>
                                        <th class="p-2">Criteria</th>
                                        <th class="p-2">Below Standard</th>
                                        <th class="p-2">Meets Standard</th>
                                        <th class="p-2">Exceeds Standard</th>
                                        <th class="p-2">Mark</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ethicsCriteria as $index => $criterion)
                                        <tr>
                                            <td class="p-2 text-center">{{ $index + 1 }}</td>
                                            <td class="p-2"><strong>{{ $criterion[0] }}</strong></td>
                                            <td class="p-2">{{ $criterion[1] }}</td>
                                            <td class="p-2">{{ $criterion[2] }}</td>
                                            <td class="p-2">{{ $criterion[3] }}</td>
                                            <td class="p-2">
                                                <select name="ethics_mark_{{ $index }}" class="form-control form-control-sm">
                                                    <option value="">--</option>
                                                    @for ($mark = 1; $mark <= 10; $mark++)
                                                        <option value="{{ $mark }}">{{ str_pad((string) $mark, 2, '0', STR_PAD_LEFT) }}</option>
                                                    @endfor
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="p-2">
                                                <label for="ethics_comment_{{ $index }}" class="mb-1"><strong>Comments:</strong></label>
                                                <textarea id="ethics_comment_{{ $index }}" name="ethics_comment_{{ $index }}" rows="2" class="form-control"></textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="print-section">
                        <h3 class="section-heading">Section Two: Job Performance Standards</h3>
                        <div class="table-responsive">
                            <table class="appraisal-table table-sm">
                                <thead>
                                    <tr class="bg-light">
                                        <th class="p-2 text-center">No.</th>
                                        <th class="p-2">Job Description / Performance</th>
                                        <th class="p-2 text-center">Satisfactory</th>
                                        <th class="p-2 text-center">Not Satisfactory</th>
                                        <th class="p-2">Scale of Marking</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($performanceStandards as $index => $standard)
                                        <tr>
                                            <td class="p-2 text-center">{{ $index + 1 }}</td>
                                            <td class="p-2"><strong>{{ $standard[0] }}</strong><br>{{ $standard[1] }}</td>
                                            <td class="p-2 text-center"><input type="radio" name="performance_status_{{ $index }}" value="satisfactory" aria-label="Satisfactory"></td>
                                            <td class="p-2 text-center"><input type="radio" name="performance_status_{{ $index }}" value="not_satisfactory" aria-label="Not satisfactory"></td>
                                            <td class="p-2">
                                                <select name="performance_mark_{{ $index }}" class="form-control form-control-sm">
                                                    <option value="">--</option>
                                                    @for ($mark = 1; $mark <= 10; $mark++)
                                                        <option value="{{ $mark }}">{{ str_pad((string) $mark, 2, '0', STR_PAD_LEFT) }}</option>
                                                    @endfor
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="p-2">
                                                <label for="performance_comment_{{ $index }}" class="mb-1"><strong>Comments:</strong></label>
                                                <textarea id="performance_comment_{{ $index }}" name="performance_comment_{{ $index }}" rows="2" class="form-control"></textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="print-section">
                        <h3 class="section-heading">Section Three: Overall Work Performance</h3>
                        <table class="appraisal-table table-sm">
                            <thead>
                                <tr class="bg-light">
                                    <th class="p-2">Performance Rating</th>
                                    <th class="p-2 text-center">Needs Improvement</th>
                                    <th class="p-2 text-center">Meets Requirements</th>
                                    <th class="p-2 text-center">Exceeds Requirements</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="p-2"><strong>Overall Work Performance</strong></td>
                                    @foreach (['needs_improvement', 'meets_requirements', 'exceeds_requirements'] as $rating)
                                        <td class="p-2 text-center"><input type="radio" name="overall_rating" value="{{ $rating }}"></td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="p-2"><strong>Scale of Marking</strong></td>
                                    <td colspan="3" class="p-2">
                                        <select name="overall_mark" class="form-control">
                                            <option value="">Select a mark</option>
                                            @for ($mark = 1; $mark <= 10; $mark++)
                                                <option value="{{ $mark }}">{{ str_pad((string) $mark, 2, '0', STR_PAD_LEFT) }}</option>
                                            @endfor
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="p-2"><strong>Recommendation</strong></td>
                                    <td colspan="3" class="p-2">
                                        @foreach (['Promotion', 'Recommended for Increment', 'No Increment'] as $recommendation)
                                            <label class="mr-3"><input type="radio" name="recommendation" value="{{ $recommendation }}"> {{ $recommendation }}</label>
                                        @endforeach
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="p-2">
                                        <label for="overall_comments"><strong>Comments:</strong></label>
                                        <textarea id="overall_comments" name="overall_comments" rows="3" class="form-control"></textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </section>

                    <section class="print-section">
                        <h3 class="section-heading">Section Four: Appraiser's Comment</h3>
                        <textarea name="appraiser_comments" rows="4" class="form-control rounded-0" placeholder="Appraiser's comments..."></textarea>

                        @foreach ($reviewLevels as $index => $level)
                            <div class="mt-3 border">
                                <h4 class="bg-light border-bottom h6 mb-0 p-2 font-weight-bold">{{ $level }}</h4>
                                <div class="p-3">
                                    <div class="table-responsive">
                                        <table class="appraisal-table table-sm mb-3">
                                            <thead>
                                                <tr class="bg-light">
                                                    <th class="p-2">Result</th>
                                                    <th class="p-2 text-center">Poor<br>0-40</th>
                                                    <th class="p-2 text-center">Average<br>41-60</th>
                                                    <th class="p-2 text-center">Above Average<br>61-80</th>
                                                    <th class="p-2 text-center">High Average<br>81-90</th>
                                                    <th class="p-2 text-center">Excellent<br>91-100</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="p-2"><strong>Rating</strong></td>
                                                    @for ($grade = 1; $grade <= 5; $grade++)
                                                        <td class="p-2 text-center"><input type="radio" name="review_level_{{ $index }}_rating" value="{{ $grade }}"></td>
                                                    @endfor
                                                </tr>
                                                <tr>
                                                    <td class="p-2"><strong>Recommendation</strong></td>
                                                    <td colspan="2" class="p-2 text-center"><label><input type="radio" name="review_level_{{ $index }}_recommendation" value="promotion"> Promotion</label></td>
                                                    <td class="p-2 text-center"><label><input type="radio" name="review_level_{{ $index }}_recommendation" value="not_recommended"> Not recommended</label></td>
                                                    <td colspan="2" class="p-2 text-center"><label><input type="radio" name="review_level_{{ $index }}_recommendation" value="increment"> Increment</label></td>
                                                </tr>
                                                <tr>
                                                    <td class="p-2"><strong>Increment</strong></td>
                                                    @foreach (['1-Step', '2-Steps', '3-Steps', '4-Steps', 'No Increment'] as $step)
                                                        <td class="p-2 text-center"><label><input type="radio" name="review_level_{{ $index }}_increment" value="{{ $step }}"> {{ $step }}</label></td>
                                                    @endforeach
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <label for="review_level_{{ $index }}_comments"><strong>Comments:</strong></label>
                                    <textarea id="review_level_{{ $index }}_comments" name="review_level_{{ $index }}_comments" rows="2" class="form-control"></textarea>

                                    <div class="row mt-3">
                                        <div class="col-md-8">
                                            <div class="d-flex justify-content-between mb-1">
                                                <strong>{{ $level }} Signature</strong>
                                                <button type="button" data-clear-signature="review_signature_{{ $index }}" class="btn btn-link btn-sm no-print p-0">Clear</button>
                                            </div>
                                            <canvas id="review_signature_{{ $index }}" class="signature-pad"></canvas>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="review_signature_date_{{ $index }}">Date</label>
                                            <input id="review_signature_date_{{ $index }}" name="review_signature_date_{{ $index }}" type="date" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </section>

                    <div class="no-print d-flex justify-content-end mt-4">
                        <button type="reset" class="btn btn-default mr-2">Clear Form</button>
                        <button type="button" class="btn btn-primary" onclick="window.print()">Print / Save PDF</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.signature-pad').forEach(function (canvas) {
                var context = canvas.getContext('2d');
                var drawing = false;
                var lastPoint = null;
                var ratio = window.devicePixelRatio || 1;
                var rect = canvas.getBoundingClientRect();

                canvas.width = Math.round(rect.width * ratio);
                canvas.height = Math.round(rect.height * ratio);
                context.scale(ratio, ratio);
                context.lineWidth = 2.5;
                context.lineCap = 'round';
                context.lineJoin = 'round';
                context.strokeStyle = '#0f172a';

                function point(event) {
                    var bounds = canvas.getBoundingClientRect();

                    return {
                        x: event.clientX - bounds.left,
                        y: event.clientY - bounds.top
                    };
                }

                canvas.addEventListener('pointerdown', function (event) {
                    event.preventDefault();
                    drawing = true;
                    lastPoint = point(event);
                    canvas.setPointerCapture(event.pointerId);
                });

                canvas.addEventListener('pointermove', function (event) {
                    if (!drawing || !lastPoint) {
                        return;
                    }

                    event.preventDefault();
                    var nextPoint = point(event);
                    context.beginPath();
                    context.moveTo(lastPoint.x, lastPoint.y);
                    context.lineTo(nextPoint.x, nextPoint.y);
                    context.stroke();
                    lastPoint = nextPoint;
                });

                function stopDrawing() {
                    drawing = false;
                    lastPoint = null;
                }

                canvas.addEventListener('pointerup', stopDrawing);
                canvas.addEventListener('pointercancel', stopDrawing);

                var clearButton = document.querySelector('[data-clear-signature="' + canvas.id + '"]');
                if (clearButton) {
                    clearButton.addEventListener('click', function () {
                        context.clearRect(0, 0, canvas.width / ratio, canvas.height / ratio);
                    });
                }
            });
        });
    </script>
@endpush
