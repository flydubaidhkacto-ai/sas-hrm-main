@extends('layouts.master')
@section('title', 'Edit Approval Process')
@section('content')
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Edit Approval Process</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('approval-processes.index') }}">Approval
                                    Processes</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ul>
                    </div>
                </div>
            </div>
            {!! Toastr::message() !!}
            @include('layouts.alerts')

            <form action="{{ route('approval-processes.update', $approvalProcess->id) }}" method="POST" class="mb-4">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Process Name</label>
                            <input type="text" id="name" name="name" value="{{ $approvalProcess->name }}"
                                   class="form-control" required>
                        </div>
                    </div>
                    <input type="hidden" name="group" value="{{ $approvalProcess->group }}">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Type</label>
                            <select id="type" name="type[]" class="form-control select2" multiple>
                                @foreach (\App\Models\ApprovalProcess::$types as $type)
                                    <option value="{{ $type }}"
                                            @if (
                                                (in_array($type, $groupedTypes))
                                            ) selected @endif
                                    >
                                        {{ ucwords(str_replace('_', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">You can't
                                change the type.</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="for_users" class="form-label">Assign to Users:</label>
                            <select id="for_users" name="for_users[]" class="form-control select2" multiple>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @if (in_array($user->id, $approvalProcess->for_users ?: [])) selected @endif>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update Process</button>
            </form>

            <h3>Add Steps:</h3>
            <form action="{{ route('approval-processes.addStep', $approvalProcess->id) }}" method="POST" class="mb-4">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <label for="approver_id" class="form-label">Approver</label>
                        <select id="approver_id" name="approver_id" class="form-control select2" required>
                            <option value="">Select </option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="step_order" class="form-label">Step Order</label>
                        <input type="number" id="step_order" name="step_order" class="form-control" required>
                        @if(config('services.leave_has_publisher'))
                            <div class="mt-2">
                                <input type="checkbox" id="is_publisher" name="is_publisher"
                                       style="scale: 1.4"
                                >
                                <label for="is_publisher">&nbsp; Is Publisher?</label>
                            </div>
                        @endif
                    </div>
                </div>

                <button type="submit" class="btn btn-success mt-3">Add Step</button>
            </form>

            <h3>Steps:</h3>
            <ul class="list-group">
                @foreach ($approvalProcess->steps->sortBy('step_order') as $step)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            Step {{ $step->step_order }}: {{ $step->approver->name }}
                            @if($step->is_publisher)
                                <span
                                        class="badge bg-success text-white">
                            <i class="fa fa-eye"></i>
                                Publisher
                            </span>
                            @endif
                        </span>
                        <form
                                action="{{ route('approval-processes.removeStep', ['process' => $approvalProcess->id, 'step' => $step->id]) }}"
                                method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Select ",
                allowClear: true,
            });
        });
    </script>
@endsection
