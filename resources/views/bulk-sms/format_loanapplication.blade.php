@extends('layouts.master')
@section('title', "Formated SMS")
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">
                        SMS: Format Loan Application Message
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item active">Formated SMS</li>
                    </ul>
                </div>
            </div>
        </div>
        <a href="{{ route('sms.format.list') }}" class="btn btn-info btn-sm">
            <i class="fa fa-arrow-left" aria-hidden="true"></i> Go Back
        </a>
        <br>
        <br>
        @php
        $message = App\Models\Setting::get_loan_sms_formating();
        @endphp

        <form action="{{ route('loan_application.format.store') }}" class="message" method="POST">
            @csrf
            <div class="row">
                <div class="form-group col-sm-6 col-md-6">
                    <label for="preview-text">Type Your Message</label>
                    <textarea class="form-control" id="preview-text" name="textTemplate" rows="10">{{
                             $message
                        }}</textarea>
                    {!! Toastr::message() !!}
                    <input type="text" id="name" value="{{ user()->name }}" hidden>
                    <div class="mt-3">
                        <button type="submit" id="msg" class="btn btn-primary">Update</button>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6">
                    <div class="card" style="width: 100%;">
                        <div class="card-body">
                            <h5 class="card-title">SMS Preview</h5>
                            <p class="card-text" id="preview"></p>
                        </div>
                    </div>
                    <div class="card bg-light">
                        <div class="card-header">Variables</div>
                        <div class="card-body">
                            <p class="card-text">
                                [Company] = Company Name,<br>
                                [Name] = Loan employeer name,<br>
                                [Date] = Date of Loan,<br>
                                [Amount] = Loan amount,<br>
                                [Approved] = Approve / Reject
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.getElementById('preview-text');
            const preview = document.getElementById('preview');

            function updatePreview() {
                const name = document.getElementById('name').value;
                let text = textarea.value;
                text = text.replace(/\[Company\]/g, 'Company Name')
                    .replace(/\[Date\]/g, 'Date of Loan')
                    .replace(/\[Name\]/g, 'Loan employeer name')
                    .replace(/\[Amount\]/g, 'Loan Amount')
                    .replace(/\[Approved\]/g, 'Approve / Reject')
                    .replace(/\n/g, '<br>');
                preview.innerHTML = text;
            }

            // Initial update
            updatePreview();
            // Update preview on every input
            textarea.addEventListener('keyup', updatePreview);
        });
</script>
@endsection