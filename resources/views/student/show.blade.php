@extends('layouts.master')
@section('title', "View Student")
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">

             {{-- student tab --}}
             {{-- <div class="mb-3">
                @include('tabs.student')
            </div> --}}
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">View Student</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
                            <li class="breadcrumb-item active">View</li>
                        </ul>
                    </div>
                </div>
            </div>
            @include('layouts.alerts')
            <div class="card shadow-sm p-4">
                <div class="text-center">
                    <h4>Student Profile</h4>
                    @if ($student->picture_file)
                        <img src="{{ asset('/uploaded_files/' . $student->picture_file) }}"
                            alt="Student Picture"
                            class="img-thumbnail rounded-circle mb-3"
                            style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <p class="text-muted">No picture available</p>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Name:</label>
                                <p class="form-control-static">{{ $student->name }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Student ID:</label>
                                <p class="form-control-static">{{ $student->student_id }}</p>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="col-form-label">Class:</label>
                                <p class="form-control-static">{{ $student->class }}</p>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="col-form-label">Department:</label>
                                <p class="form-control-static">
                                    {{ $student?->department?->super_department?->name }}:
                                    {{ $student->department?->name }}
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="" class="col-form-label">Assigned Shifts:</label>
                                @php
                                    $shifts = $student->shifts;
                                @endphp
                                @if ($student->shifts->isNotEmpty())
                                    <ul>
                                        @foreach ($student->shifts as $shift)
                                            <li>{{ $shift->shift?->name }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>No shifts assigned.</p>
                                @endif
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Picture:</label>
                                @if($student->picture)
                                    <img src="{{ $student->picture }}" alt="Student Picture" class="w-100 mt-2">
                                @else
                                    <p class="form-control-static">No picture available</p>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">
                                    Phone <sup class="text-danger">*</sup>:
                                </label>
                                <p class="form-control-static">{{ $student->phone_number }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Admission Date:</label>
                                <p class="form-control-static">{{ carbon($student->join_date)->format('d-m-Y') }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Out Side Punch:</label>
                                <p class="form-control-static">{{ $student->can_outside_attendence ? 'Yes' : 'No' }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Mother's name:</label>
                                <p class="form-control-static">{{ $student->mother_name }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Father's name:</label>
                                <p class="form-control-static">{{ $student->father_name }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label">Address:</label>
                                <p class="form-control-static">{{ $student->address }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Birth:</label>
                                <p class="form-control-static">{{ carbon($student->birth_date)->format('d-m-Y') }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Blood group:</label>
                                <p class="form-control-static">{{ $student->blood_group }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gender:</label>
                                <p class="form-control-static">{{ $student->gender }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Religion:</label>
                                <p class="form-control-static">{{ $student->religion }}</p>
                            </div>
                        </div>
                        @if($student->zk_device_card)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ID Card Number:</label>
                                <p class="form-control-static">{{ $student->zk_device_card }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div>
                        <a class="btn btn-primary" href="{{ route('students.edit', $student->id) }}">
                            <i class="fa fa-pencil m-r-5"></i> Edit
                        </a>
                        <a class="btn btn-danger" id="delete_student_{{ $student->id }}" dusk="delete_student_{{ $student->id }}" href="{{ route('students.delete', $student->id) }}" onclick="return confirm('Are you sure you want to delete this student?')">
                            <i class="fa fa-trash m-r-5"></i> Delete
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
