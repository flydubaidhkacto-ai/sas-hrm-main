
@extends('layouts.master')
@section('title', "Notice Board")
@section('content')
<style>
    .designation {
        color: unset;
        font-size: unset;
    }
</style>
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Notice Board</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('notice.index')}}">Notices</a></li>
                        <li class="breadcrumb-item active">Notice Board</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_modal"><i
                            class="fa fa-plus"></i> Add Notice</a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        {!! Toastr::message() !!}
        @include('layouts.alerts')
        <form action="{{ route('notice.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Title <span class="text-danger">*</span></label>
                <p>
                    {{ $notice->title }}
                </p>
            </div>

            <div class="form-group">
                <label class="col-form-label">Description<sup class="text-danger">*</sup>:</label>
                <div>
                    {!! $notice->description !!}
                </div>
            </div>
            @if($notice->attachments->count())
                <div class="form-group">
                    <label for="">Attachments:</label>
                    <div class="document-container">
                        @foreach ($notice->attachments as $item)
                            <div class="mb-2">
                                <i class="fa fa-file{{ $item->is_pdf ? '-pdf-o' : '' }}"></i>
                                {{ $item->type }}
                                @if($item->is_pdf)
                                    <button type="button" class="btn btn-sm btn-info pdf-preview-btn" data-url="{{ $item->url }}" data-name="{{ $item->type }}">
                                        <i class="fa fa-eye"></i> Preview PDF
                                    </button>
                                @endif
                                <a class="link" href="/uploaded_files/{{ $item->path }}" download="{{ $item->file_name }}">
                                    <i class="fa fa-download"></i> Download
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </form>
        @include('notice.partials.pdf-preview-modal')
    </div>
</div>
@endsection
