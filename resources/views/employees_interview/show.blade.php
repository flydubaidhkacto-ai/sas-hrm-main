@extends('layouts.master')
@section('title', "View Employee")
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Employee: {{ $interview_employee->name }}</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Employee</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            {!! Toastr::message() !!}
            <div>
                @include('layouts.alerts')
                <div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Employee Code:</label>
                                        {{ $interview_employee->id }}

                                        {{-- <label style="padding-left: 30px;" class="col-form-label">Employee ID:</label> --}}
                                        {{-- {{ $interview_employee->employee_id ?? 'Not Assigned' }} --}}
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">
                                            Phone:
                                        </label>
                                        {{ $interview_employee->phone_number ?? '--' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">
                                            Email:
                                        </label>
                                        {{ $interview_employee->email ?? '--' }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Address:</label>
                                {{ $interview_employee->address ?? '--' }}
                            </div>

                            <div class="form-group">
                                <label for="">Interview Date :</label>
                                {{ carbon($interview_employee->interview_date)->format('d F, Y') }}
                            </div>

                            <div class="form-group">
                                <label for="">Expected Salary:</label>
                                {{ $interview_employee->expected_salary }}
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Department:</label>
                                        <br>
                                        <strong>
                                            {{ $interview_employee->department?->super_department?->name ?? '' }}
                                        </strong>
                                        <br>
                                        <i>{{ $interview_employee->department?->name }}</i>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Designation:</label>
                                        <br>
                                        {{ $interview_employee->designation?->name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    @if ($interview_employee->Interview_document->count() > 0)
                        <h4>Documents</h4>
                        @foreach ($interview_employee->Interview_document as $document)
                            <div class="mt-2">
                                {{$loop->iteration}}.
                                <a href="{{asset('uploaded_files/' . $document->path)}}" target="_blank">
                                    {{$document->type}}
                                    <i class="fa fa-download"></i>
                                </a>
                            </div>
                        @endforeach
                    @endif

                    @if(
                        (role_can('interview_comment') || role_can('interview_comment_see'))
                        && $comments->count()
                    )
                        <hr>
                        @foreach($comments as $comment)
                            <div class="card mb-2">
                                <div class="card-body">
                                    <strong for="">
                                        Comment:
                                    </strong>

                                    @if(role_can('user') || role_can('user.self.super_department'))
                                            <a class="btn btn-danger float-right btn-sm" href="{{ route('employees.interview.comment.delete', $comment->id) }}">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                    @endif

                                    <p class="mb-2">{{ $comment->comment }}</p>
                                    <strong for="">Rating:</strong>
                                    <span>{{ $comment->rating }}</span>
                                    <p class="m-0"><strong><i class="fa fa-user"> </i> {{ $comment->employee?->name }} ({{ $comment->employee_id }})</strong></p>

                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if(role_can('interview_comment') && !$my_comment)
                        <hr>
                        <h2>Submit Your Comment</h2>
                        <form action="{{ route('employees.interview.comment') }}" method="post">
                            @csrf
                            <input type="hidden" name="interview_id" id="" value="{{ $interview_employee->id }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Rating:</label>
                                        <input type="number" class="form-control" name="rating" id="rating" min="0">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="">Comment:</label>
                                        <textarea name="comment" class="form-control" placeholder="Enter your comment here..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                            </div>
                        </form>
                    @endif

                    @if(role_can('user') || role_can('user.self.super_department'))
                        <div class="mt-5">
                            <a class="btn btn-primary" href="{{ route('employees.interview.edit', $interview_employee->id) }}">
                                <i class="fa fa-edit"></i> Edit Interview Employee
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script></script>
    <script src="{{asset('assets/js/jquery.printPage.js')}}"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('.btnprn').printPage();
        });
    </script>
@endsection
