('attachments')
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
        <form method="POST" action="{{ route('notice.update') }}" enctype="multipart/form-data">
            <div class="modal-body">
                @csrf
                <input type="hidden" name="d_id" value="{{ $notice->id }}">
                <div class="form-group">
                    <label>Title <span class="text-danger">*</span></label>
                    <input name="title" class="form-control title" required  type="text" value="{{ old('title',$notice->title) }}">
                    @if ($errors->has('title'))
                        <span class="text-danger">{{ $errors->first('title') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label class="col-form-label">Description<sup class="text-danger">*</sup>:</label>
                    <textarea id="editor1" name="description" class="form-control description" rows="6">{{ old('description',$notice->description) }}</textarea>
                    @if ($errors->has('description'))
                        <span class="text-danger">{{ $errors->first('description') }}</span>
                    @endif
                </div>
                <h3>Documents</h3>
                    <div class="mt-2">
                    <div class="document-container">
                        @if ($notice->attachments->count() > 0)
                            <h4>Already uploaded documents:</h4>
                            @foreach ($notice->attachments as $document)
                                <div class="mt-2 mb-3">
                                    <a class="btn btn-sm btn-danger"
                                        href="javascript:void(0)"
                                        onclick="if(confirm('Are you sure you want to delete this document?')){
                                            addDel('{{ $document->id }}');
                                            this.parentElement.remove();
                                        }">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    &nbsp;
                                    &nbsp;
                                    {{ $loop->iteration }}.
                                    <i class="fa fa-file{{ $document->is_pdf ? '-pdf-o' : '' }}"></i>
                                    {{ $document->type }}
                                    @if($document->is_pdf)
                                        <button type="button" class="btn btn-sm btn-info pdf-preview-btn" data-url="{{ $document->url }}" data-name="{{ $document->type }}">
                                            <i class="fa fa-eye"></i> Preview PDF
                                        </button>
                                    @endif
                                    <a href="{{ asset('uploaded_files/' . $document->path) }}"
                                        target="_blank">
                                        <i class="fa fa-download"></i> Download
                                    </a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <button class="mt-2 btn btn-primary btn-sm" type="button"
                        onclick="addDocument()">Add
                        Document</button>
                    <div>
                        {{-- show errors --}}
                        @if ($errors->has('document'))
                            <span
                                class="text-danger">{{ $errors->first('document') }}</span>
                        @endif
                    </div>
                </div>
                <script>
                    var editDocumentBlobUrls = {};

                    function addDel(id) {
                        $('form').prepend('<input type="hidden" name="document_delete[]" value="' + id + '">');
                    }

                    function addDocument() {
                        let index = $('.document-container .d-flex').length;
                        let docId = 'edit-doc-' + Date.now() + '-' + index;
                        $('.document-container').append(`
                            <div class="d-flex mt-2 document-row" id="${docId}">
                                <div style="width: 50px">
                                    <button class="btn btn-sm btn-danger" onclick="removeEditDocument('${docId}')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                                <div style="width: 250px">
                                    <input type="file" class="form-control file-input"
                                        name="document[${index}][file]"
                                        onchange="handleEditFileSelect(this, '${docId}')"
                                        accept=".pdf,application/pdf"
                                    >
                                </div>
                                <div style="width: 100%">
                                    <input type="text" class="form-control doc-type-input"
                                        name="document[${index}][type]" placeholder="Document type/name">
                                </div>
                                <div class="ml-2">
                                    <button type="button" class="btn btn-sm btn-info pdf-preview-btn" data-url="" data-name="" disabled id="${docId}-preview">
                                        <i class="fa fa-eye"></i> Preview PDF
                                    </button>
                                </div>
                            </div>
                            `);
                    }

                    function removeEditDocument(docId) {
                        if (editDocumentBlobUrls[docId]) {
                            URL.revokeObjectURL(editDocumentBlobUrls[docId]);
                            delete editDocumentBlobUrls[docId];
                        }
                        $('#' + docId).remove();
                    }

                    function handleEditFileSelect(input, docId) {
                        var previewBtn = $('#' + docId + '-preview');
                        var typeInput = $('#' + docId + ' .doc-type-input');

                        if (editDocumentBlobUrls[docId]) {
                            URL.revokeObjectURL(editDocumentBlobUrls[docId]);
                            editDocumentBlobUrls[docId] = null;
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
                            editDocumentBlobUrls[docId] = blobUrl;
                            previewBtn.prop('disabled', false).data('url', blobUrl).data('name', file.name);
                        } else {
                            previewBtn.prop('disabled', true).data('url', '').data('name', '');
                        }
                    }
                </script>
                <div class="submit-section">
                    <button class="btn btn-primary submit-btn">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@include('notice.partials.pdf-preview-modal')
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
