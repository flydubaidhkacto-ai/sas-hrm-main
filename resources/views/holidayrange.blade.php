@extends('layouts.master')
@section('title', 'Holidays')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper" onmousemove="calculateDuration()" onkeyup="calculateDuration()">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Holidays (
                            <select name="year" id=""
                                onchange="location = '{{ route('holidays.range') }}?year=' + this.value">
                                @foreach ([-3, -2, -1, 0, 1, 2, 3] as $i)
                                    <option value="{{ date('Y') + $i }}" {{ $year == date('Y') + $i ? 'selected' : '' }}>
                                        {{ date('Y') + $i }}
                                    </option>
                                @endforeach
                            </select>
                            ): <span id="year"></span>
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Holidays

                            </li>
                        </ul>
                    </div>
                    @if (role_can('holidays'))
                        <div class="col-auto float-right ml-auto">
                            <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_holiday"><i
                                    class="fa fa-plus"></i> Add Holiday</a>
                        </div>
                    @endif
                </div>
            </div>
            <!-- /Page Header -->
            {{-- message --}}
            {!! Toastr::message() !!}
            @include('layouts.alerts')
            @php
                $today_date = today()->format('d-m-Y');
            @endphp
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        @php
                            $total_day = 0;
                        @endphp
                        <table class="table table-striped custom-table datatable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Title </th>
                                    <th>Start Day</th>
                                    <th>End Day</th>
                                    <th>Total Day</th>
                                    <th>Branches</th>
                                    @if (role_can('holidays'))
                                        <th class="text-right">Action</th>
                                    @endif
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($holiday as $key => $items)
                                    @php
                                        $start_date = \Carbon\Carbon::parse($items->start_date);
                                        $end_date = \Carbon\Carbon::parse($items->end_date);
                                        $day = $start_date->diffInDays($end_date) + 1;
                                        $total_day += $day;
                                    @endphp

                                    <tr class="holiday-upcoming">
                                        <td hidden class="id">{{ $items->id }}</td>
                                        <td>{{ ++$key }}</td>
                                        <td class="holidayName">{{ $items->name }}</td>
                                        <td hidden class="startDate"
                                            date="{{ carbon($items->start_date)->format('d-m-Y') }}">
                                            {{ $items->start_date }}</td>

                                        <td>{{ \Carbon\Carbon::parse($items->start_date)->format('d F, Y ; l') }}
                                        </td>

                                        <td hidden class="endDate" date="{{ carbon($items->end_date)->format('d-m-Y') }}">
                                            {{ $items->end_date }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($items->end_date)->format('d F, Y ; l') }}
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($items->start_date)->diffInDays(\Carbon\Carbon::parse($items->end_date)) + 1 }}
                                            days

                                        </td>
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
                                        @if (role_can('holidays'))
                                            <td class="text-right">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle"
                                                        data-toggle="dropdown" aria-expanded="false"><i
                                                            class="material-icons">more_vert</i></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item userUpdate" data-toggle="modal"
                                                            data-id="'.$items->id.'" data-target="#edit_holiday"><i
                                                                class="fa fa-pencil m-r-5"></i> Edit</a>
                                                        <a class="dropdown-item" href="#" data-toggle="modal"
                                                            data-target="#delete_holiday"
                                                            onclick="$('#delete_holiday .d_id').val({{ $items->id }})">
                                                            <i class="fa fa-trash-o m-r-5"></i> Delete
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                    @if ($total_day != 0)
                                    <tr>
                                        <td colspan="4" style="text-align: right; padding: 8px; font-weight: bold;">
                                            Yearly Holiday
                                        </td>
                                        <td colspan="1" style="font-weight: bold;">
                                            {{ $total_day }} Days
                                        </td>
                                        @if (role_can('holidays'))
                                            <td></td>
                                        @endif
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                    <p>* Dependant on moon sighting</p>
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
                        <form action="{{ route('holidayRange.save') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Holiday Name <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" id="name" name="name">
                            </div>
                            <div class="form-group">
                                <label>Start Holiday Date <span class="text-danger">*</span></label>
                                <div class="cal-icon">
                                    <input class="form-control datetimepicker" type="text" id="start_date"
                                        name="start_date" onchange="calculateDuration()" onmousemove="calculateDuration()">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>End Holiday Date <span class="text-danger">*</span></label>
                                <div class="cal-icon">
                                    <input class="form-control datetimepicker" type="text" id="end_date_holiday_ranges"
                                        name="end_date" onchange="calculateDuration()">
                                </div>
                                <span id= "leave_duration"></span>
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
                        <form action="{{ route('holidaysRange.update') }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="id" id="e_id" value="">
                            <div class="form-group">
                                <label>Holiday Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="holidayName_edit" name="name"
                                    value="">
                            </div>

                            <div class="form-group">
                                <label>Holiday Start Date <span class="text-danger">*</span></label>
                                <div class="cal-icon">
                                    <input type="text" class="form-control datetimepicker" id="holidayStartDate_edit"
                                        name="start_date" value="" onchange="calculateDuration()">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Holiday End Date <span class="text-danger">*</span></label>
                                <div class="cal-icon">
                                    <input type="text" class="form-control datetimepicker" id="holidayEndDate_edit"
                                        name="end_date" value="" onchange="calculateDuration()">
                                </div>
                                <span id= "leave_duration"></span>
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
                                    <a href="javascript:void(0);" data-dismiss="modal"
                                        class="btn btn-primary cancel-btn">Cancel</a>
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
        // document.getElementById("year").innerHTML = new Date().getFullYear();
    </script>
    {{-- update js --}}
    <script>
        $(document).on('click', '.userUpdate', function() {
            var _this = $(this).parents('tr');
            $('#e_id').val(_this.find('.id').text());
            $('#holidayName_edit').val(_this.find('.holidayName').text());
            $('#holidayStartDate_edit').val(_this.find('.startDate').attr('date'));
            $('#holidayEndDate_edit').val(_this.find('.endDate').attr('date'));

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

        calculateDuration = () => {
            $('.modal-body').each((i, doc) => {
                let element = $(doc)
                let start_date = element.find('.datetimepicker[name="start_date"]').val();
                let end_date = element.find('.datetimepicker[name="end_date"]').val();
                if (!start_date || !end_date) return
                start_date = moment(start_date, 'DD-MM-YYYY');
                end_date = moment(end_date, 'DD-MM-YYYY');
                const duration = (end_date.diff(start_date, 'days'));
                const days = duration + 1;
                if (days < 1) {
                    return doc.querySelector('#leave_duration').innerText = 'Invalid date';
                }
                doc.querySelector('#leave_duration').innerText = days + ' ' + (days > 1 ? 'days' : 'day');
            })
        }
    </script>


@endsection
