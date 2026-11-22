@extends('layouts.master')
@section('title', 'Template List')
@push('style')
    {{--  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">  --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">

@endpush
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Template
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('letters.template.index') }}">Template</a></li>
                            <li class="breadcrumb-item active">Template List</li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a href="{{ route('letters.template.create') }}" class="btn add-btn m-r-5">Create Template</a>
                    </div>
                </div>
            </div>
            @include('layouts.alerts')
            <div class="row">
                <div class="col-md-12">
                    {{ $dataTable->table(['id' => 'template-table']) }}
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>

    {!! str_replace("http:", is_app_local() ? 'http:' : "https:", $dataTable->scripts(attributes: ['type' => 'module'])) !!}
@endpush
