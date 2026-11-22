
@extends('layouts.master')
@section('title', "Attendance Request: Preview")
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">Attendance Request</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('attendance-requests') }}">Requests</a></li>
                            <li class="breadcrumb-item active">Attendance Request: Preview</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            @include('layouts.alerts')
            <div>
                <form action="{{ route('attendance-requests.store') }}" method="POST">
                    @csrf
                    <h4>Confirm Attendance Details:</h4>
                    <div>
                        <table class="table" style="max-width: 600px">
                            <tr>
                                <td>
                                    <b>Shift: </b>
                                </td>
                                <td>
                                     {{ $data['shift']['name'] }} ({{ carbon($data['shift']['start'])->format('h:i A') }} - {{ carbon($data['shift']['end'])->format('h:i A') }})
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Attendance Date: </b>
                                </td>
                                <td>
                                    {{ carbon($data['date'])->format('d M, Y') }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Working Hours: </b>
                                </td>
                                <td>
                                    {{ carbon($data['working_time'])->format('H:i') }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Overtime Hours: </b>
                                </td>
                                <td>
                                    {{ carbon($data['overtime_time'])->format('H:i') }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Late Check in: </b>
                                </td>
                                <td>
                                    @if ($data['got_late_check_in'])
                                        <span class="text-warning">Yes</span>
                                    @else
                                        <span class="text-success">No</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        Confirm
                    </button>
                    <a href="{{ route('attendance-requests.create') }}" class="ml-2 btn btn-link text-danger">
                        Cancel
                    </a>
                </form>
            </div>
        </div>
        <!-- /Page Content -->
    </div>
    <!-- /Page Wrapper -->
@endsection
@section('script')
    <script>

    </script>
@endsection
