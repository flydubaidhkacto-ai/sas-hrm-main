@extends('layouts.master')
@section('title', 'Generate Official Forms')

@push('style')
<style>

    .editor-section {
        min-height: 400px;
    }


    .ck-editor__editable {
        min-height: 400px !important;
        max-height: 500px;
        overflow: auto;
    }

     /* Hide the CKEditor notification area */
    #cke_notifications_area_editor {
        display: none !important;
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
                        <h3 class="page-title">Create Official Form</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('official-form-generate.index') }}">Official Forms</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Form Section -->
                <div class="col-md-12 editor-section">
                    <form id="official-form-template" action="{{ route('official-form-generate.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label class="col-form-label">Form Title<sup class="text-danger">*</sup>:</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                            @if ($errors->has('title'))
                                <span class="text-danger">{{ $errors->first('title') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>Select Official Form</label>
                            <select id="official-form-dropdown" name="" class="form-control select2" onchange="officialFormSelected()">
                                <option value="">--Select Official Form--</option>
                                @foreach ($officialForms as $form)
                                    <option value="{{ $form->id }}">{{ $form->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Form Content<sup class="text-danger">*</sup>:</label>
                            <textarea id="editor" name="body" class="form-control ckeditor" rows="6">{{ old('body') }}</textarea>
                            @if ($errors->has('body'))
                                <span class="text-danger">{{ $errors->first('body') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
<script src="{{ asset('assets/ckeditor/ckeditor.js') }}"></script>
<script>
    function officialFormSelected() {
        const formId = $('#official-form-dropdown').val();
        if (formId) {
            // Find the selected form from the list of official forms
            const selectedForm = @json($officialForms).find(form => form.id == formId);
            if (selectedForm) {
                // Set the form content in the textarea
                $('textarea[name="body"]').val(selectedForm.body);

                // Notify CKEditor to update its content
                if (typeof CKEDITOR !== 'undefined') {
                    const editorInstance = CKEDITOR.instances['editor'];
                    if (editorInstance) {
                        editorInstance.setData(selectedForm.body);
                    }
                }
            }
        }
    }

    $(document).ready(function() {
        $('#official-form-dropdown').select2({
            onchange: function() {
                officialFormSelected();
            }
        });
    });
</script>
@endpush
