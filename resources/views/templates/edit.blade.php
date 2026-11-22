@extends('layouts.master')
@section('title', 'Create Letter Template')

@push('style')

<style>
    .available-variables .variable {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 0;
        cursor: pointer;
    }

    .available-variables .variable:hover {
        background-color: #f9f9f9;
    }

    .copy-icon {
        color: #007bff;
        cursor: pointer;
        margin-left: 10px;
    }

    .ck-editor__editable {
        min-height: 300px !important; /* Adjust the height as needed */
        max-height: 500px; /* Optional: set a maximum height */
        overflow: auto; /* Add a scroll if the content exceeds the height */
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
                        <h3 class="page-title">Create Letter Template</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('letters.template.index') }}">Letter Templates</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Form Section -->
                <div class="col-md-8">
                    <form id="letter-template-form" action="{{ route('letters.template.update', $template->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label class="col-form-label">Template Title<sup class="text-danger">*</sup>:</label>
                            <input type="text" name="title" class="form-control" required value="{{ $template->title }}">
                            @if ($errors->has('title'))
                                <span class="text-danger">{{ $errors->first('title') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select class="select form-control" id="status" name="status">
                                <option value="">--Select--</option>
                                <option value="0" {{ $template->status == '0' ? 'selected' : '' }}>Deactive</option>
                                <option value="1" {{ $template->status == '1' ? 'selected' : '' }}>Active</option>
                            </select>
                            @if ($errors->has('status'))
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Letter Content<sup class="text-danger">*</sup>:</label>
                            <textarea name="description" class="form-control" rows="6">{{ $template->description }}</textarea>
                            @if ($errors->has('description'))
                                <span class="text-danger">{{ $errors->first('description') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Available Variables</h4>
                        </div>
                        <div class="card-body">
                            <p>Click on a variable to copy it:</p>
                            <div class="available-variables">
                                <div class="variable" onclick="copyVariable('[name]')">
                                    [name]
                                    <i class="fas fa-copy copy-icon" title="Copy"></i>
                                </div>
                                <div class="variable" onclick="copyVariable('[email]')">
                                    [email]
                                    <i class="fas fa-copy copy-icon" title="Copy"></i>
                                </div>
                                <div class="variable" onclick="copyVariable('[join_date]')">
                                    [join_date]
                                    <i class="fas fa-copy copy-icon" title="Copy"></i>
                                </div>
                                <div class="variable" onclick="copyVariable('[phone_number]')">
                                    [phone_number]
                                    <i class="fas fa-copy copy-icon" title="Copy"></i>
                                </div>
                                <div class="variable" onclick="copyVariable('[role]')">
                                    [role]
                                    <i class="fas fa-copy copy-icon" title="Copy"></i>
                                </div>
                                <div class="variable" onclick="copyVariable('[department_name]')">
                                    [department_name]
                                    <i class="fas fa-copy copy-icon" title="Copy"></i>
                                </div>
                                <div class="variable" onclick="copyVariable('[designation_name]')">
                                    [designation_name]
                                    <i class="fas fa-copy copy-icon" title="Copy"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('js')
    <script src="https://cdn.ckeditor.com/ckeditor5/34.0.0/classic/ckeditor.js"></script>
    <script>

        $(document).ready(function () {

            $('.variable').click(function () {
                const variable = $.trim($(this).text());
                const $tempTextarea = $('<textarea>')
                    .val(variable)
                    .appendTo('body')
                    .select();

                document.execCommand('copy');
                $tempTextarea.remove();

                alert(variable + ' copied to clipboard!');
            });

            // ckeditor
            ClassicEditor.create(document.querySelector('textarea'))
                        .then(editor => {
                            console.log('Editor was initialized', editor);
                        })
                        .catch(error => {
                            console.error('Error during initialization of the editor', error);
                        });
        });



    </script>
@endpush
