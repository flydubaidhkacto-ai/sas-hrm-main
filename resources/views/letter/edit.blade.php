@extends('layouts.master')
@section('title', 'Edit Letter')

@push('style')
<style>
    .preview-section {
        border: 1px solid #ddd;
        padding: 15px;
        background: #f9f9f9;
        min-height: 400px;
        overflow: auto;
    }
    .editor-section {
        min-height: 400px;
    }
    .available-variables {
        margin-top: 20px;
        max-height: 150px;
        overflow-y: auto;
        border: 1px solid #ddd;
        padding: 10px;
        background-color: #f8f8f8;
    }
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
        min-height: 400px !important;
        max-height: 500px;
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
                        <h3 class="page-title">Edit Letter</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('letters.generate.index') }}">Generate Letters</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Form Section -->
                <div class="col-md-6 editor-section">
                    <form id="letter-template-form" action="{{ route('letters.generate.update', $generate->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="col-form-label">Template Title<sup class="text-danger">*</sup>:</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $generate->title) }}">
                            @if ($errors->has('title'))
                                <span class="text-danger">{{ $errors->first('title') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>Select Template</label>
                            <select id="template-dropdown" name="template_id" class="form-control select2">
                                <option value="">--Select Template--</option>
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}" {{ $template->id == $generate->template_id ? 'selected' : '' }}>
                                        {{ $template->title }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($errors->has('template_id'))
                                <span class="text-danger">{{ $errors->first('template_id') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>Select Employee</label>
                            <select id="employee-dropdown" name="employee_id" class="form-control select2">
                                <option value="">--Select Employee--</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ $employee->id == $generate->employee_id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($errors->has('employee_id'))
                                <span class="text-danger">{{ $errors->first('employee_id') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Letter Content<sup class="text-danger">*</sup>:</label>
                            <textarea id="editor" name="body" class="form-control" rows="6">{{ old('body', $generate->body) }}</textarea>
                            @if ($errors->has('body'))
                                <span class="text-danger">{{ $errors->first('body') }}</span>
                            @endif
                        </div>

                        <div class="available-variables">
                            <h5>Available Variables:</h5>
                            @foreach (['[name]', '[email]', '[join_date]', '[phone_number]', '[role]', '[department_name]', '[designation_name]'] as $variable)
                                <div class="variable" onclick="copyVariable('{{ $variable }}')">
                                    {{ $variable }}
                                    <i class="fas fa-copy copy-icon" title="Copy"></i>
                                </div>
                            @endforeach
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>

                <!-- Preview Section -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Live Preview</h4>
                        </div>
                        <div class="card-body">
                            <div id="preview" class="preview-section">
                                <!-- Live preview will be displayed here -->
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
    let editorInstance;

    ClassicEditor.create(document.querySelector('#editor'))
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

    function copyVariable(variable) {
        const tempInput = document.createElement('textarea');
        tempInput.value = variable;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        alert(variable + ' copied to clipboard!');
    }

    function updatePreview(content) {
        const employeeId = document.getElementById('employee-dropdown').value;

        if (employeeId) {
            const selectedEmployee = @json($employees).find(employee => employee.id == employeeId);

            if (selectedEmployee) {
                const variables = {
                    '[name]': selectedEmployee.name,
                    '[email]': selectedEmployee.email,
                    '[join_date]': selectedEmployee.join_date,
                    '[phone_number]': selectedEmployee.phone_number,
                    '[role]': selectedEmployee.role,
                    '[department_name]': selectedEmployee.department?.name || '',
                    '[designation_name]': selectedEmployee.designation?.name || '',
                };

                for (const [variable, value] of Object.entries(variables)) {
                    const regex = new RegExp(variable.replace(/[\[\]]/g, '\\$&'), 'g');
                    content = content.replace(regex, value);
                }
            }
        }

        document.getElementById('preview').innerHTML = content;
    }

    document.getElementById('employee-dropdown').addEventListener('change', function () {
        const content = editorInstance.getData();
        updatePreview(content);
    });

    document.getElementById('template-dropdown').addEventListener('change', function () {
        const templateId = this.value;
        if (templateId) {
            const selectedTemplate = @json($templates).find(template => template.id == templateId);
            if (selectedTemplate) {
                editorInstance.setData(selectedTemplate.description);
            }
        }
    });
</script>
@endpush
