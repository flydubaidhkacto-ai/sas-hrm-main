@extends('layouts.master')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Holidays <span id="year"></span></h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Holidays</li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_holiday"><i class="fa fa-plus"></i> Add Holiday</a>
                    </div>
                </div>
            </div>
			<!-- /Page Header -->
            {{-- message --}}
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            @php
                use Carbon\Carbon;
                $today_date = Carbon::today()->format('d-m-Y');
            @endphp
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table datatable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Title </th>
                                    <th>Holiday Date</th>
                                    <th>Day</th>
                                    <th>Branches</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($holiday as $key=>$items )
                                    @if(($today_date > $items->date))
                                        <tr class="holiday-completed">
                                            <td>{{ ++$key }}</td>
                                            <td>{{ $items->name }}</td>
                                            <td>{{date('d F, Y',strtotime($items->date)) }}</td>
                                            <td>{{date('l',strtotime($items->date)) }}</td>
                                            <td></td>
                                        </tr>
                                    @endif
                                @endforeach
                                @foreach ($holiday as $key=>$items )
                                    @if(($today_date <= $items->date))
                                        <tr class="holiday-upcoming">
                                            <td hidden class="id">{{ $items->id }}</td>
                                            <td>{{ ++$key }}</td>
                                            <td class="holidayName">{{ $items->name }}</td>
                                            <td hidden class="holidayDate" date="{{ carbon($items->date)->format('d-m-Y') }}">{{$items->date }}</td>
                                            <td>{{date('d F, Y',strtotime($items->date)) }}</td>
                                            <td>{{date('l',strtotime($items->date)) }}</td>
                                            <td class="holidayBranches" data-branches="{{ htmlspecialchars(json_encode($items->branches ?? [])) }}">
                                                @if (!empty($items->branches))
                                                    @foreach ($items->branches as $branchId)
                                                        @php
                                                            $branch = $branches->firstWhere('id', $branchId);
                                                        @endphp
                                                        @if ($branch)
                                                            <span class="badge bg-info-light">{{ $branch->name }}</span>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <span class="badge bg-secondary-light">All Branches</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item userUpdate" data-toggle="modal" data-id="'.$items->id.'" data-target="#edit_holiday"><i class="fa fa-pencil m-r-5"></i> Edit</a>
                                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#delete_holiday"
                                                            onclick="$('#delete_holiday .d_id').val({{$items->id}})"
                                                        >
                                                            <i class="fa fa-trash-o m-r-5"></i> Delete
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Content -->
        <!-- Add Holiday Modal -->
        <div class="modal custom-modal fade" id="add_holiday" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Holiday</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('holidays.save') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Holiday Name <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" id="nameHoliday" name="nameHoliday">
                            </div>
                            <div class="form-group">
                                <label>Holiday Date <span class="text-danger">*</span></label>
                                <div class="cal-icon">
                                    <input class="form-control datetimepicker" type="text" id="holidayDate" name="holidayDate">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Super Departments</label>
                                <small class="text-muted d-block">Leave empty to apply to the entire organization.</small>
                                <select class="form-control select2-init" multiple="multiple" name="branches[]" id="branches" style="width: 100%;">
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Add Holiday Modal -->

        <!-- Edit Holiday Modal -->
        <div class="modal custom-modal fade" id="edit_holiday" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Holiday</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('holidays.update') }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="id" id="e_id" value="">
                            <div class="form-group">
                                <label>Holiday Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="holidayName_edit" name="name" value="">
                            </div>
                            <div class="form-group">
                                <label>Holiday Date <span class="text-danger">*</span></label>
                                <div class="cal-icon">
                                    <input type="text" class="form-control datetimepicker" id="holidayDate_edit" name="date" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Super Departments</label>
                                <small class="text-muted d-block">Leave empty to apply to the entire organization.</small>
                                <select class="form-control select2-init" multiple="multiple" name="branches[]" id="e_branches" style="width: 100%;">
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Edit Holiday Modal -->

        <!-- Delete Holiday Modal -->
        <div class="modal custom-modal fade" id="delete_holiday" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="form-header">
                            <h3>Delete Holiday</h3>
                            <p>Are you sure want to delete?</p>
                        </div>
                        <form class="modal-btn delete-action" action="{{ route('holidays.delete') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" class="d_id">
                            @method('DELETE')
                            <div class="row">
                                <div class="col-6">
                                    <button type="submit" class="btn btn-primary continue-btn w-100">Delete</button>
                                </div>
                                <div class="col-6">
                                    <a href="javascript:void(0);" data-dismiss="modal" class="btn btn-primary cancel-btn">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Delete Holiday Modal -->

    </div>
    <!-- /Page Wrapper -->
    @section('script')
    <script>
        document.getElementById("year").innerHTML = new Date().getFullYear();
    </script>
    {{-- update js --}}
    <script>
        $(document).on('click','.userUpdate',function()
        {
            var _this = $(this).parents('tr');
            $('#e_id').val(_this.find('.id').text());
            $('#holidayName_edit').val(_this.find('.holidayName').text());
            $('#holidayDate_edit').val(_this.find('.holidayDate').attr('date'));

            // reset and set selected branches
            var $select = $('#e_branches');
            $select.val(null).trigger('change');
            var branchAttr = _this.find('.holidayBranches').attr('data-branches');
            if (branchAttr) {
                try {
                    var ids = JSON.parse(branchAttr);
                    if (Array.isArray(ids) && ids.length) {
                        $select.val(ids.map(String)).trigger('change');
                    }
                } catch (e) {}
            }
        });
    </script>
    @endsection

@endsection
