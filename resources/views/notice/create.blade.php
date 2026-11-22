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

    <!-- Page Content -->
    <div class="content container-fluid">

        <!-- Page Header -->
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
                <input name="title" class="form-control" type="text" value="{{ old('title') }}" required>
                @if ($errors->has('title'))
                <span class="text-danger">{{ $errors->first('title') }}</span>
                @endif
            </div>

            <div class="form-group">
                <label class="col-form-label">Description<sup class="text-danger">*</sup>:</label>
                <textarea id="editor1" name="description" class="form-control add_editor"
                    rows="6">{{ old('description') }}</textarea>
                @if ($errors->has('description'))
                <span class="text-danger">{{ $errors->first('description') }}</span>
                @endif
            </div>
            <div class="form-group">
                <label for="">Attachments:</label>
                <div class="document-container">

                </div>
                <button class="mt-2 btn btn-primary btn-sm" type="button" onclick="addDocument()">Add
                    Document</button>
                <div>
                {{-- show errors --}}
                @if ($errors->has('document'))
                <span class="text-danger">{{ $errors->first('document') }}</span>
                @endif
                </div>
            </div>

            <div class="submit-section">
                <button class="btn btn-primary submit-btn">Submit</button>
            </div>
        </form>
    </div>
</div>
@include('notice.partials.pdf-preview-modal')
<script>
    var documentBlobUrls = {};

    function addDocument() {
        let index = $('.document-container .d-flex').length;
        let docId = 'doc-' + Date.now() + '-' + index;
        $('.document-container').append(`
                <div class="d-flex mt-2 document-row" id="${docId}">
                    <div style="width: 50px">
                        <button class="btn btn-sm btn-danger" onclick="removeDocument('${docId}')">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                    <div style="width: 250px">
                        <input type="file" class="form-control file-input"
                            name="document[${index}][file]"
                            onchange="handleFileSelect(this, '${docId}')"
                            accept=".pdf,application/pdf"
                            required
                        >
                    </div>
                    <div style="width: 100%">
                        <input type="text" class="form-control doc-type-input"
                            name="document[${index}][type]" placeholder="Document type/name" required>
                    </div>
                    <div class="ml-2">
                        <button type="button" class="btn btn-sm btn-info pdf-preview-btn" data-url="" data-name="" disabled id="${docId}-preview">
                            <i class="fa fa-eye"></i> Preview PDF
                        </button>
                    </div>
                </div>
            `);
    }

    function removeDocument(docId) {
        if (documentBlobUrls[docId]) {
            URL.revokeObjectURL(documentBlobUrls[docId]);
            delete documentBlobUrls[docId];
        }
        $('#' + docId).remove();
    }

    function handleFileSelect(input, docId) {
        var previewBtn = $('#' + docId + '-preview');
        var typeInput = $('#' + docId + ' .doc-type-input');

        if (documentBlobUrls[docId]) {
            URL.revokeObjectURL(documentBlobUrls[docId]);
            documentBlobUrls[docId] = null;
        }

        if (input.files && input.files[0]) {
            var file = input.files[0];
            var isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');

            if (!isPdf) {
                alert('Please select a valid PDF file.');
                input.value = '';
                previewBtn.prop('disabled', true).data('url', '').data('name', '');
                return;
            }

            if (!typeInput.val()) {
                typeInput.val(file.name.split('.')[0]);
            }

            var blobUrl = URL.createObjectURL(file);
            documentBlobUrls[docId] = blobUrl;
            previewBtn.prop('disabled', false).data('url', blobUrl).data('name', file.name);
        } else {
            previewBtn.prop('disabled', true).data('url', '').data('name', '');
        }
    }
</script>
<script src="https://cdn.ckeditor.com/ckeditor5/34.0.0/classic/ckeditor.js"></script>
    {{-- update js --}}
<script>
    let editorInstance;

ClassicEditor.create(document.querySelector('#editor1'))
    .then(editor => {
        editorInstance = editor;

        // Populate preview on page load
        const initialContent = editor.getData();
        updatePreview(initialContent);

        // Update preview on content change
        editor.model.document.on('change:data', () => {
            const content = editor.getData();
            updatePreview(content);
        });
    })
    .catch(error => {
        console.error('Error initializing CKEditor:', error);
    });
</script>
@endsection
