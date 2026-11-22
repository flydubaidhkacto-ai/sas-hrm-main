@extends('layouts.master')
@section('title', 'View Employee')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">

        {{-- include employees tabs --}}
        {{-- <div class="mb-3">
            @include('tabs.employees')
        </div> --}}

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Employee: {{ $employee->name }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Employee</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        {!! Toastr::message() !!}
        <div>
            @include('layouts.alerts')
            <div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Employee Code:</label>
                                        {{ $employee->id }}

                                        <label style="padding-left: 30px;" class="col-form-label">Employee ID:</label>
                                        {{ $employee->employee_id ?? 'Not Assigned' }}
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">
                                            Father's name:
                                        </label>
                                        {{ $employee->father_name ?? '--' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Mother's name:
                                        </label>
                                        {{ $employee->mother_name ?? '--' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">
                                            Phone:
                                        </label>
                                        {{ $employee->phone_number ?? '--' }}
                                    </div>
                                </div>
                                @if(config('services.hide_personal_phone_mail') === false)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">
                                            Personal Phone:
                                        </label>
                                        {{ $employee->personal_phone ?? '--' }}
                                    </div>
                                </div>
                                @endif
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">
                                            Email:
                                        </label>
                                        {{ $employee->email ?? '--' }}
                                    </div>
                                </div>
                                @if(config('services.hide_personal_phone_mail') === false)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">
                                            Personal Email:
                                        </label>
                                        {{ $employee->personal_email ?? '--' }}
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Address:</label>
                                {{ $employee->address ?? '--' }}
                            </div>
                            @if(config('services.hide_personal_phone_mail') === false)
                            <div class="form-group">
                                <label class="col-form-label">City:</label>
                                {{ $employee->city ?? '--' }}
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Permanent Address:</label>
                                {{ $employee->permanent_address ?? '--' }}
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Permanent Address City:</label>
                                {{ $employee->permanent_city ?? '--' }}
                            </div>
                            @endif
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date of Birth:</label>
                                        {{ $employee->birth_date ? carbon($employee->birth_date)->format('d F, Y') : '--' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Blood group:</label>
                                        {{ $employee->blood_group ?? '--' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Gender: </label>
                                        {{ $employee->gender ?? '--' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Religion: </label>
                                        {{ $employee->religion ?? '--' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nationality: </label>
                                        {{ $employee->nationality ?? '--' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Marital Status: </label>
                                        {{ $employee->marital_status ?? '--' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>NID/Passport No: <sup class="text-danger"></sup></label>
                                        {{ $employee->nid_or_passport ?? '--' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Shift: <sup class="text-danger"></sup></label>
                                        {{ $employee->shift->name ?? '--' }}
                                    </div>
                                </div>
                                @if(config('app.customer') === 'vector')
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>From Management: </label>
                                            {{ $employee->from_management ? 'Yes' : 'No' }}
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-6">
                                    <div class="form-group">
                                        @if (!empty($selectedWarnings) && count($selectedWarnings) > 0)
                                        <label>Get SMS For :</label>
                                        <div>
                                            @foreach ($selectedWarnings as $warning)
                                            <span class="badge badge-info p-2 m-1">{{ ucfirst(str_replace('_', ' ',
                                                $warning)) }}</span>
                                            @endforeach
                                        </div>
                                        @else
                                        <label>Get SMS For :</label>
                                        <p class="text-muted">No warnings selected</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        @if (!empty($employee->admin_of_super_departments) && count($employee->admin_of_super_departments) > 0)
                                        <label>General Admin of Sup. Dept:</label>
                                        <div>
                                            @foreach (\App\Models\SuperDepartment::whereIn('id', $employee->admin_of_super_departments)->ordered()->get() as $item)
                                            <span class="badge badge-info p-2 m-1">
                                                {{ $item->name }}
                                            </span>
                                            @endforeach
                                        </div>
                                        @else
                                        <label>General Admin of Sup. Dept:</label>
                                        <p class="text-muted">No warnings selected</p>
                                        @endif
                                    </div>
                                </div>


                                <div class="col-md-6">
                                    <div class="form-group">
                                        @if ($employee->nid_or_passport_file)
                                        <label>
                                            NID/Passport Picture:
                                        </label>
                                        <img src="/uploaded_files/{{ $employee->nid_or_passport_file }}" alt=""
                                            class="mt-2 w-100 nid-img">
                                        @endif
                                    </div>
                                </div>


                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Department:</label>
                                        <br>
                                        <strong>
                                            {{ $employee->department?->super_department?->name ?? '' }}
                                        </strong>
                                        <br>
                                        <i>{{ $employee->department?->name }}</i>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Designation:</label>
                                        <br>
                                        {{ $employee->designation?->name }}
                                    </div>
                                    <div class="form-group">
                                        <label for="">Employee type: </label>
                                        {{ $employee->employee_type }}
                                    </div>
                                    <div class="form-group">
                                        <label for="">Overtime: </label>
                                        {{ $employee->can_overtime ? 'Yes' : 'No' }}
                                    </div>
                                    <div class="form-group">
                                        <label for="">Work from home request: </label>
                                        {{ $employee->work_from_home_request ? 'Yes' : 'No' }}
                                    </div>
                                    <div class="form-group">
                                        <label for="">Outside Duty: </label>
                                        {{ $employee->can_outside_attendence ? 'Yes' : 'No' }}
                                    </div>

                                    @if(Route::has('reports.live-location'))
                                    <div class="form-group">
                                        <label for="">Live Location Tracking: </label>
                                        {{ $employee->live_location_track ? 'Yes' : 'No' }}
                                    </div>
                                    @endif

                                    <div class="form-group">
                                        <label for="">Get Late Check-In warning via SMS: </label>
                                        {{ $employee->get_attendance_sms_notification ? 'Yes' : 'No' }}
                                    </div>
                                    <div class="form-group">
                                        <label for="">Joining Date :</label>
                                        {{ carbon($employee->join_date)->format('d F, Y') }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        @if ($employee->picture_file)
                                        <img src="/uploaded_files/{{ $employee->picture_file }}" alt="" class="mt-2"
                                            style="width: 200px; height: auto;">
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        @if ($employee->signature_file)
                                        <label for="">Signature:</label>
                                        <img src="/uploaded_files/{{ $employee->signature_file }}" alt="" class="mt-2"
                                            style="width: 100px; height: auto;">
                                        @endif
                                    </div>
                                </div>
                                @if(role_can('user') || role_can('user.self.super_department') || is_admin_of_super_department($employee->department?->super_department_id))
                                <div class="col-12">
                                    <hr>
                                    <h4>Reference:</h4>
                                    <div class="mt-2 row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Name:</label>
                                                {{ $employee->reference_name ?? '--' }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Phone:</label>
                                                {{ $employee->reference_phone ?? '--' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @if(role_can('user') || role_can('user.self.super_department') || is_admin_of_super_department($employee->department?->super_department_id))
                    <hr>
                    <h4>
                        Emergency Contact Info:
                    </h4>
                    <div class="mt-2 row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Person Name:</label>
                                        {{ $employee->emergency_name ?? '--' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Relation:</label>
                                        {{ $employee->emergency_relation ?? '--' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Phone :</label>
                                        {{ $employee->emergency_phone ?? '--' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Address:</label>
                                        {{ $employee->emergency_address ?? '--' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <b>Role:</b> {{ $employee->assinged_role?->name ?: 'None' }}
                        </div>
                    </div>

                    <hr>
                    <h4>Bank Details:</h4>
                    <div class="mt-2 row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-form-label">Account Holder Name:</label>
                                {{ $employee->account_holder_name ?? '--' }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-form-label">Account Number:</label>
                                {{ $employee->account_number ?? '--' }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-form-label">Bank Name:</label>
                                {{ $employee->bank_name ?? '--' }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-form-label">Branch Name:</label>
                                {{ $employee->branch_name ?? '--' }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-form-label">Routing Number:</label>
                                {{ $employee->routing_number ?? '--' }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-form-label">TIN Number:</label>
                                {{ $employee->tin_number ?? '--' }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-form-label">Tax Number:</label>
                                {{ $employee->tax_number ?? '--' }}
                            </div>
                        </div>
                    </div>
                @endif

                @if(config('services.have_attendance_location') && $employee->have_geo_location)
                <hr>
                <h4>Geo Location Information:</h4>
                <div class="mt-2 row">
                    <!-- Home Address Coordinates -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Home Address Coordinates</h5>
                            </div>
                            <div class="card-body">
                                @if($employee->home_latitude && $employee->home_longitude)
                                <div class="form-group">
                                    <label class="col-form-label">Latitude:</label>
                                    {{ $employee->home_latitude }},
                                    <label class="col-form-label">Longitude:</label>
                                    {{ $employee->home_longitude }}
                                </div>
                                <div id="employee-home-map" style="height: 250px; width: 100%;"></div>
                                @else
                                <p class="text-muted">No home address coordinates set</p>
                                @endif
                            </div>
                        </div>
                        @if($employee->locations_admin->count())
                            <div class="mt-2">
                                <b>Admin of Areas:</b>
                                <div>
                                    @foreach($employee->locations_admin as $l)
                                        <div class="mb-2">
                                            {{ $loop->iteration }}. <b>{{ $l->fullPath }}</b>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Assigned Locations -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Assigned Locations</h5>
                            </div>
                            <div class="card-body">
                                <b>Type: </b> {{ $employee->geo_location_type ?? 'Not Set' }}
                                <br>
                                <br>
                                @if($employee->assigned_locations && $employee->assigned_locations->count() > 0)
                                @foreach($employee->assigned_locations as $location)
                                <div class="assigned-location-item mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $location->name }}</h6>
                                            <small class="text-muted">
                                                {{ $location->getFullHierarchy() }}
                                            </small>
                                            <div class="mt-2">
                                                <strong>Distance:</strong> {{ $location->pivot->distance_km }} KM
                                            </div>
                                            @if($location->latitude && $location->longitude)
                                            <div class="mt-1">
                                                <small class="text-info">
                                                    <i class="fa fa-map-marker"></i>
                                                    {{ $location->latitude }}, {{ $location->longitude }}
                                                </small>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @else
                                <p class="text-muted">No locations assigned</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if ($employee->documents->count() > 0 && (role_can('user') || role_can('user.self.super_department') || is_admin_of_super_department($employee->department?->super_department_id)))
                    <h4>Documents</h4>
                    @foreach ($employee->documents as $document)
                    <div class="mt-2">
                        {{ $loop->iteration }}.
                        <a href="{{ asset('uploaded_files/' . $document->path) }}" target="_blank">
                            {{ $document->type }}
                            <i class="fa fa-download"></i>
                        </a>
                    </div>
                    @endforeach
                @endif
                <hr>
                @if ($employee->zk_fingerprints->count() > 0 && user()->is_super_admin())
                    <div>
                        <h5>Fingerprints from ZKTeco Terminal:</h5>
                        <hr>
                        @foreach ($employee->zk_fingerprints as $fingerprint)
                        <div class="mt-2">
                            <button
                                onclick="confirm('Are you sure you want to delete it?') ? location.href='{{ route('employees.active.delete-fingerprint', $fingerprint->id) }}' : event.preventDefault()"
                                class="btn btn-sm btn-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                            &nbsp;
                            <span>
                                {{ \Str::limit(json_encode($fingerprint->fingerprint_data), 100) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                @endif
                <div class="mt-5">
                    @if(role_can('user') || role_can('user.self.super_department')|| is_admin_of_super_department($employee->department?->super_department_id))
                    <a href="{{ route('employees.active.print', $employee->id) }}" class="btnprn"><button type="button"
                            class="btn btn-info">Print <i class="fa-solid fa-print"></i> </button></a>
                    @endif
                    <a class="btn btn-primary" href="{{ route('employees.active.edit', $employee->id) }}">
                        <i class="fa fa-edit"></i> Edit employee
                    </a>
                    @if ((role_can('user') || role_can('user.self.super_department') || is_admin_of_super_department($employee->department?->super_department_id)))
                        <form action="{{ route('employees.active.terminate') }}" method="post"
                            id="terminate-form-{{ $employee->id }}">
                            @csrf
                            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                        </form>
                        @if ($employee->is_active)
                        <button class="mt-3 btn btn-warning"
                            onclick="confirm('Are you sure you want to terminate it? The data will be deleted from the machines.') ? document.getElementById('terminate-form-{{ $employee->id }}').submit() : event.preventDefault()">
                            <i class="fa fa-times"></i> Terminate employee
                        </button>
                        @else
                        <button class="mt-3 btn btn-info"
                            onclick="document.getElementById('terminate-form-{{ $employee->id }}').submit()">
                            <i class="fa fa-check"></i> Activate employee
                        </button>
                        @endif
                        @if (!$employee->is_active)
                        <form action="{{ route('employees.active.delete', $employee->id) }}" method="post"
                            id="delete-form-{{ $employee->id }}">
                            @csrf
                            @method('Delete')
                        </form>
                        <button class="btn btn-danger btn-sm float-right"
                            onclick="if(confirm('Are you sure to want to delete it? You will not be able to undo it!')) {event.preventDefault(); document.getElementById('delete-form-{{ $employee->id }}').submit(); }">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                        @endif
                    @endif
                    {{-- <div class="col-3"> --}}

                        {{-- </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<script src="{{ asset('assets/js/jquery.printPage.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
            $('.btnprn').printPage();

            // Initialize home location map if coordinates exist
            @if($employee->home_latitude && $employee->home_longitude && config('services.have_attendance_location'))
                initializeHomeLocationMap();
            @endif
        });

        @if($employee->home_latitude && $employee->home_longitude && config('services.have_attendance_location'))
        function initializeHomeLocationMap() {
            const lat = {{ $employee->home_latitude }};
            const lng = {{ $employee->home_longitude }};

            // Initialize the map
            const homeMap = L.map('employee-home-map').setView([lat, lng], 15);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: 'ProHR'
            }).addTo(homeMap);

            // Add marker for home location
            L.marker([lat, lng])
                .addTo(homeMap)
                .bindPopup('{{ $employee->name }} - Home Address')
                .openPopup();

            // Add assigned location markers if any exist
            @if($employee->assigned_locations && $employee->assigned_locations->count() > 0)
                @foreach($employee->assigned_locations as $location)
                    @if($location->latitude && $location->longitude)
                        // Add marker for assigned location
                        const assignedMarker{{ $loop->index }} = L.marker([{{ $location->latitude }}, {{ $location->longitude }}], {
                            icon: L.icon({
                                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                                iconSize: [25, 41],
                                iconAnchor: [12, 41],
                                popupAnchor: [1, -34],
                                shadowSize: [41, 41]
                            })
                        }).addTo(homeMap);

                        assignedMarker{{ $loop->index }}.bindPopup(`
                            <strong>{{ $location->name }}</strong><br>
                            {{ $location->getFullHierarchy() }}<br>
                            <small>Distance: {{ $location->pivot->distance_km }} KM</small>
                        `);

                        // Add circle to show distance radius
                        L.circle([{{ $location->latitude }}, {{ $location->longitude }}], {
                            color: 'blue',
                            fillColor: '#blue',
                            fillOpacity: 0.1,
                            radius: {{ $location->pivot->distance_km * 1000 }} // Convert KM to meters
                        }).addTo(homeMap);
                    @endif
                @endforeach
            @endif
        }
        @endif
</script>
@endsection
