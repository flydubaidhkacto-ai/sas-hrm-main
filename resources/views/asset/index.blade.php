@extends('layouts.master')
@section('title', "Asset")
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">
                            Asset
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                            <li class="breadcrumb-item active">
                                Asset
                            </li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a href="{{ route('asset_barcode_scanner') }}" class="btn btn-info mr-2"><i class="fa fa-qrcode"></i> Barcode Scanner</a>
                        <a href="{{ route('assets-management.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add New</a>
                    </div>
                </div>
            </div>

            <!-- /Page Header -->
            {!! Toastr::message() !!}
            @include('layouts.alerts')

            <!-- Filter Section -->
            <div class="mt-2">
                <form method="get" action="{{ route('assets-management.index') }}" class="row filter-row mb-4">
                    <div class="mb-2 col-sm-6 col-md-3">
                        <label for="">Tag</label>
                        <input type="text" name="tag" class="form-control" value="{{ request()->tag }}" placeholder="Search tag...">
                    </div>
                    <div class="mb-2 col-sm-6 col-md-3">
                        <label for="">Category</label>
                        <select name="category_id" class="form-control select2">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request()->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2 col-sm-6 col-md-3">
                        <label for="">User</label>
                        <input type="text" name="user" class="form-control" value="{{ request()->user }}" placeholder="Search user...">
                    </div>
                    <div class="mb-2 col-sm-6 col-md-3">
                        <label for="">Location</label>
                        <select name="location_id" class="form-control super-department-select2">
                            @if(request()->location_id)
                                <option value="{{ request()->location_id }}" selected>
                                    {{ \App\Models\SuperDepartment::find(request()->location_id)?->name }}
                                </option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-2 col-sm-6 col-md-3">
                        <label for="">Search</label>
                        <input type="text" name="search" class="form-control" value="{{ request()->search }}" placeholder="Keyword search...">
                    </div>
                    <div class="mb-2 col-sm-6 col-md-3">
                        <button type="submit" class="btn btn-success btn-block"> Search </button>
                    </div>
                    <div class="mb-2 col-sm-6 col-md-3">
                        <a href="{{ route('assets-management.index') }}" class="btn btn-secondary btn-block"> Reset </a>
                    </div>
                </form>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <form action="{{ route('asset_label_print') }}" class="print-form"  method="POST">
                        @csrf
                        <input type="hidden" class="form-control ids" name="ids" id="[]">
                        <button type="submit" class="btn btn-info btn-sm" disabled>
                            <i class="fa fa-print"></i>
                            Print Label of selected Assets
                        </button>
                    </form>
                    <div class="mt-2">
                        <div class="table-responsive">
                            <div class="exports">
                                <div style="text-align: end" class="mb-2">
                                    <a href="{{ add_query_to_current_route(['export' => 'xlsx']) }}" class="btn btn-sm btn-info" target="_blank">
                                        <i class="fa fa-file-excel"></i>
                                        Excel
                                    </a>
                                    <a href="{{ add_query_to_current_route(['export' => 'pdf']) }}" class="btn btn-sm btn-info" target="_blank">
                                        <i class="fa fa-file-pdf"></i>
                                        PDF
                                    </a>
                                    <a href="{{ add_query_to_current_route(['export' => 'print']) }}" class="btn btn-sm btn-info" target="_blank">
                                        <i class="fa fa-print"></i>
                                        Print
                                    </a>
                                </div>
                            </div>
                            <table class="table table-striped custom-table mb-0" id="asset_table" style="display:none;">
                                <thead>
                                    <tr>
                                        <th style="width: 20px;" data-toggle="tooltip" data-placement="top" title="Select All for Label Print">
                                            <input type="checkbox" id="select_all"
                                                style="scale: 1.3;"
                                                onclick="event.stopPropagation(); select_all(this)"
                                            >
                                        </th>
                                        <th style="width: 30px;">#</th>
                                        <th>Name</th>
                                        <th>Tag</th>
                                        <th>Category</th>
                                        <th>User</th>
                                        <th>Location</th>
                                        <th style="width: 120px;">Purchase Value</th>
                                        <th style="width: 100px;">Present Value</th>
                                        <th>Status</th>
                                        <th >Action</th>
                                        <th style="width: 80px;">Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $key => $item )
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="ids[]" value="{{ $item->id }}" style="scale: 1.3;">
                                        </td>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ route('assets-management.show', $item->id) }}">
                                                {{ $item->name }}
                                            </a>
                                        </td>
                                        <td>{{ $item->tag }}</td>
                                        <td>{{ $item->asset_category?->name }}</td>
                                        <td>{{ $item->user ?? '-' }}</td>
                                        <td>{{ $item->super_department?->name ?? ($item->location ?? '-') }}</td>
                                        <td>{{ $item->purchase_value }}</td>
                                        <td>{{ $item->present_value }}</td>
                                        <td>
                                            @if ($item->status == 1)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="width: 110px">
                                                <a href="{{ route('assets-management.show', $item->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> </a>
                                                <a href="{{ route('assets-management.edit', $item->id) }}" class="btn btn-info btn-sm"><i class="fa fa-edit"></i> </a>

                                                <a href="#"
                                                    onclick="event.preventDefault();
                                                    if(confirm('Are you sure?')) {
                                                        $(this).next('form').submit();
                                                    }" class="delete-link btn btn-danger btn-sm"><i class="fa fa-trash"></i>
                                                </a>

                                                <form method="POST" action="{{ route('assets-management.destroy', $item->id) }}" class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </div>
                                        </td>
                                        <td>
                                            {{ $item->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div id="asset_table_loading" class="text-center p-3">
                                <i class="fa fa-spinner fa-spin"></i> Loading assets...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Content -->
    </div>
    <!-- /Page Wrapper -->
    @section('script')
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        let table = new DataTable('#asset_table', {
            pageLength: 50,
            order: [[1, 'asc']],
            deferRender: true,
            initComplete: function() {
                $('#asset_table').show();
                $('#asset_table_loading').hide();
            }
        });
        $('.select2').select2({});
        function select_all(element) {
            let selectAll = element.checked;
            $('input[name="ids[]"]').prop('checked', selectAll);
            updatedSelected();
        }
        function updatedSelected() {
            let selcted = $('input[name="ids[]"]:checked');
            let values = selcted.map(function() {
                return $(this).val();
            }).get();
            $('.print-form button').attr('disabled', values.length > 0 ? false : true);
            $('.print-form input[name="ids"]').val(values);
        }
        $(document).ready(function() {
            $('table').on('click', 'input[name="ids[]"]', function() {
                updatedSelected();
            });
        });
    </script>
    @endsection
@endsection
