@extends('layouts.master')
@section('title', "Ticket Details")
@section('content')
<!-- Page Wrapper -->

<style>
    .chat-box {
    max-height: 400px;
    overflow-y: auto;
    padding: 10px;
    background: #f5f5f5;
}

.message {
    display: flex;
    margin-bottom: 8px;
}

.text-start {
    justify-content: flex-start;
}

.text-end {
    justify-content: flex-end;
}

.bg-primary {
    background-color: #007bff !important;
    color: white !important;
}

.bg-light {
    background-color: #e9ecef !important;
}

</style>

<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        Ticket Details
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
                        <li class="breadcrumb-item active">
                            Ticket Details
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        @include('layouts.alerts')
        {!! Toastr::message() !!}
        <h2>Ticket: {{ $ticket->title }}</h2>

        <div class="chat-box border p-3 rounded" style="height: 400px; overflow-y: scroll; background-color: #f5f5f5;">
            @if($messages && count($messages) > 0)
                @foreach($messages as $message)
                    <div class="message mb-3 {{ $message->is_sender ? 'text-end' : 'text-start' }}">
                        <div>
                            <div class="p-2 d-inline-block rounded {{ $message->is_sender ? 'bg-primary text-white' : 'bg-light' }}">
                                {{ $message->message }}
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">
                                    {{ $message->ticketReplyUser->name }} • {{ $message->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center text-muted">
                    No messages yet
                </div>
            @endif
        </div>

        @if($ticket->status == 'solved' || $ticket->status == 'rejected')
            <form action="{{ route('tickets.reply.store', $ticket->id) }}" method="POST" class="mt-3">
                @csrf
                <div class="input-group">
                    <input type="text" name="description" class="form-control" placeholder="Type your message..." required readonly>
                    <button type="submit" class="btn btn-secondary" disabled><i class="fa fa-paper-plane"></i></button>
                </div>
            </form>
        @else
            <form action="{{ route('tickets.reply.store', $ticket->id) }}" method="POST" class="mt-3">
                @csrf
                <div class="input-group">
                    <input type="text" name="description" class="form-control" placeholder="Type your message..." required>
                    <button type="submit" class="btn btn-success"><i class="fa fa-paper-plane"></i></button>
                </div>
            </form>
        @endif
    </div>
</div>
<style>
    .preview {
        color: gray;
        font-size: 10px
    }
</style>
@endsection
@section('script')
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@endSection
