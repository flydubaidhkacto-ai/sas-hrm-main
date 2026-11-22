@extends('layouts.master')
@section('title', "SMS Message")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        SMS: History
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item active">SMS History</li>
                    </ul>
                </div>
            </div>
        </div>
        <div>
            <div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ref.</th>
                            <th>To</th>
                            <th>Message</th>
                            <th>Sent At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $message)
                        <tr>
                            <td>
                                {{ $message->id }}
                            </td>
                            <td>
                                {{ $message->recived_numbers }}
                            </td>
                            <td>
                                {!! str_replace("\n", "<br />", $message->text_body) !!}
                            </td>
                            <td>
                                {{ $message->created_at->format('d M, Y h:i A') }}
                            </td>
                            <td>
                                {{ $message->status }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {!! $history->links() !!}
        </div>
    </div>
</div>
@endsection


