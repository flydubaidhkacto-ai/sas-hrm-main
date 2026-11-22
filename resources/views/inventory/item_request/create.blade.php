@extends('layouts.master')
@section('title', 'Inventory Item Request')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <x-page-header :title="'Inventory Item Request'" :breadcrumbs="[
                ['name' => 'Inventory Item Request', 'route' => route('inventory_item_request.all')],
                ['name' => 'Create Inventory Item Request', 'active' => true],
            ]" />

            <!-- /Page Header -->
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            <form action="{{ route('inventory_item_request.store') }}" method="POST">
                @csrf
                @if (role_can('inventory'))
                    <div class="form-group">
                        <label class="col-form-label">Select Employee <sup class="text-danger"></sup>:</label>
                        <select name="employee_id" id="" class="form-control employee-select2">
                            @if (request()->employee)
                                <option value="{{ request()->employee }}" selected>
                                    {{ \App\Models\User::find(request()->employee)?->name }}
                                </option>
                            @endif
                        </select>
                    </div>
                @else
                    <input type="hidden" name="employee_id" value="{{ user()->id }}">
                @endif

                <div class="form-group">
                    <label class="col-form-label">Inventory Items<sup class="text-danger">*</sup>:</label>
                    <div id="input-group-container">
                        <div class="input-group mb-3">
                            <select name="inventory_items[0][id]" class="form-control i-select2" required>
                                <option value="" disabled selected>Select Item</option>
                                @foreach ($item_list as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="inventory_items[0][quantity]" class="form-control" placeholder="Quantity" min="1" required>
                            <button type="button" class="btn btn-danger remove-btn" style="display: none;">−</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary add-btn">+</button>
                </div>

                <div class="form-group">
                    <label class="col-form-label">Description <sup class="text-danger"></sup>:</label>
                    <br>
                    <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                </div>

                <div class="mt-2">
                    <button class="btn btn-primary">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- /Page Wrapper -->
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            $('.i-select2').select2();

            $(document).on('click', '.add-btn', function() {
                // Get the current number of item rows
                const itemCount = $('#input-group-container .input-group').length;

                const newGroup = `
                    <div class="input-group mb-3">
                        <select name="inventory_items[${itemCount}][id]" class="form-control i-select2" required>
                            <option value="" disabled selected>Select Item</option>
                            @foreach ($item_list as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="inventory_items[${itemCount}][quantity]" class="form-control" placeholder="Quantity" min="1" required>
                        <button type="button" class="btn btn-danger remove-btn">−</button>
                    </div>
                `;

                // Append the new group to the container
                $('#input-group-container').append(newGroup);

                // Initialize select2 for the new select element
                $('#input-group-container .input-group:last .i-select2').select2();

                // Show remove buttons when there's more than one item
                if ($('#input-group-container .input-group').length > 1) {
                    $('.remove-btn').show();
                }
            });

            $(document).on('click', '.remove-btn', function() {
                if ($('#input-group-container .input-group').length > 1) {
                    $(this).closest('.input-group').remove();

                    // Hide remove buttons when there's only one item left
                    if ($('#input-group-container .input-group').length <= 1) {
                        $('.remove-btn').hide();
                    }

                    // Re-index the remaining items
                    reindexItems();
                }
            });

            function reindexItems() {
                $('#input-group-container .input-group').each(function(index) {
                    $(this).find('select').attr('name', `inventory_items[${index}][id]`);
                    $(this).find('input').attr('name', `inventory_items[${index}][quantity]`);
                });
            }
        });
    </script>
@endpush
