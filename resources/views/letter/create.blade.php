@extends('layouts.master')
@section('title', 'Generate Letters')

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
                    <h3 class="page-title">Create Letters</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('letters.generate.index') }}">Generate Letters</a>
                        </li>
                        <li class="breadcrumb-item active">Create</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Form Section -->
            <div class="col-md-6 editor-section">
                <form id="letter-template-form" action="{{ route('letters.generate.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="col-form-label">Template Title<sup class="text-danger">*</sup>:</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                        @if ($errors->has('title'))
                        <span class="text-danger">{{ $errors->first('title') }}</span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label>Select Template</label>
                        <select id="template-dropdown" name="template_id" class="form-control select2"
                            onchange="templateSelected()">
                            <option value="">--Select Template--</option>
                            @foreach ($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->title }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('template_id'))
                        <span class="text-danger">{{ $errors->first('template_id') }}</span>
                        @endif
                    </div>
                    @if(role_can('letters.templates'))
                    <div class="form-group">
                        <label>Select Employee</label>
                        <select id="employee-dropdown" name="employee_id" class="form-control select2"
                            onchange="empSelected()">
                            <option value="">--Select Employee--</option>
                            @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('employee_id'))
                        <span class="text-danger">{{ $errors->first('employee_id') }}</span>
                        @endif
                    </div>
                    @else
                    <input type="hidden" id="employee-dropdown" name="employee_id" value="{{ user()->id }}">
                    @endif
                    <div class="form-group">
                        <label class="col-form-label">Letter Content<sup class="text-danger">*</sup>:</label>
                        <textarea id="editor" name="body" class="form-control" rows="6">{{ old('body') }}</textarea>
                        @if ($errors->has('body'))
                        <span class="text-danger">{{ $errors->first('body') }}</span>
                        @endif
                    </div>

                    <div class="available-variables">
                        <h5>Available Variables:</h5>
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

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Save</button>
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

    // Initialize CKEditor
    ClassicEditor.create(document.querySelector('#editor'))
        .then(editor => {
            editorInstance = editor;

            // Update preview on content change
            editor.model.document.on('change:data', () => {
                const content = editor.getData();
                updatePreview(content);
            });
        })
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });

    // Copy variable to clipboard
    function copyVariable(variable) {
        const tempInput = document.createElement('textarea');
        tempInput.value = variable;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        alert(variable + ' copied to clipboard!');
    }

    // Update preview with employee data
    function updatePreview(content) {
        const employeeId = document.getElementById('employee-dropdown').value;

        if (employeeId) {
            const selectedEmployee = @json($employees).find(employee => employee.id == employeeId);

            if (selectedEmployee) {
                // Mapping variables to employee fields
                const variables = {
                    '[name]': selectedEmployee.name,
                    '[email]': selectedEmployee.email,
                    '[join_date]': selectedEmployee.join_date,
                    '[phone_number]': selectedEmployee.phone_number,
                    '[role]': selectedEmployee.role,
                    '[department_name]': selectedEmployee.department?.name || '',
                    '[designation_name]': selectedEmployee.designation?.name || '',
                };

                // Replace variables in content
                for (const [variable, value] of Object.entries(variables)) {
                    const regex = new RegExp(variable.replace(/[\[\]]/g, '\\$&'), 'g'); // Escape [ and ]
                    content = content.replace(regex, value);
                }
            }
        }

        // Update the preview section
        document.getElementById('preview').innerHTML = content;
    }

    // Handle employee selection
    function empSelected() {
        const content = editorInstance.getData(); // Get the content from the editor
        updatePreview(content); // Update preview dynamically
    }

    // Handle template selection
    function templateSelected() {
        const templateId = $('#template-dropdown').val();
        if (templateId) {
            const selectedTemplate = (@json($templates)).find(template => template.id == templateId);
            if (selectedTemplate) {
                editorInstance.setData(selectedTemplate.description); // Load template content
                document.getElementById('employee-dropdown').disabled = false; // Enable employee dropdown
            }
        } else {
            document.getElementById('employee-dropdown').disabled = true; // Disable employee dropdown
        }
    }

    $(document).ready(function() {
        $('#template-dropdown').select2({
            onchange: function() {
                templateSelected();
            }
        });
        @if(role_can('letters.templates'))
            $('#employee-dropdown').select2({
                onchange: function() {
                    empSelected();
                }
            });
        @endif
    });

</script>
@endpush