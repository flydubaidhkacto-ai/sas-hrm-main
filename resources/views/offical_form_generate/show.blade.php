@extends('layouts.master')
@section('title', 'View Official Form')

@push('style')
<style>
    .form-content {
        border: 1px solid #ddd;
        padding: 20px;
        background: #fff;
        min-height: 400px;
        overflow: auto;
    }
</style>
@endpush

@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">View Official Form</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('official-form-generate.index') }}">Official Forms</a></li>
                            <li class="breadcrumb-item active">View</li>
                        </ul>
                    </div>
                    <div class="col-auto">
                        <button id="download-pdf" class="btn btn-primary">Download as PDF</button>
                    </div>
                </div>
            </div>

            <!-- Form View Section -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ $official_form_generate->title }}</h4>
                        </div>
                        <div class="card-body">
                            <div id="form-content">
                                {!! $official_form_generate->body !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    document.getElementById('download-pdf').addEventListener('click', function() {
        const element = document.getElementById('form-content'); // Get the content to export
        const opt = {
            margin: 1,
            filename: '{{ $official_form_generate->title }}.pdf',
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).save();
    });
</script>
@endpush
