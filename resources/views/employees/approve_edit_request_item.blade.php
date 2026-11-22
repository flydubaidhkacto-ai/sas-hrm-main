@extends('layouts.master')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">
                            Edit
                        </h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item active">Requested edit for employee information</li>
                        </ul>
                    </div>
                </div>
            </div>
            @php
                // if (isset($_SESSION)) {
                // dump($arrayData);
                // } else {
                //     echo "dataField is not set in the session.";
                // }
            @endphp

            <div class="row">
                <div class="col-6">
                    <h5>Requested From</h5>
                    <h3>Name: {{ $employee->name }}</h3>
                    <h4>Designation : {{ $employee->designation?->name }}</h4>
                    <h4>Department : {{ $employee->department?->name }}</h4>
                </div>
                <div class="col-6">
                    <button type="button" class="btn btn-info" id="add-all-btn" onclick="addAllRows()">Add All Rows</button>

                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <table class="table-striped custom-table table">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Requested Field</th>
                                <th>Old Value</th>
                                <th>New Value</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        @php
                            $i = 1;
                        @endphp

                        <tbody>
                            @if (isset($arrayData['name']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Name</td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $arrayData['name'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="name"
                                            data-old-value="{{ $employee->name }}"
                                            data-new-value="{{ $arrayData['name'] }}" onclick="rowdata(this)">Add
                                            Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['phone_number']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Phone Number</td>
                                    <td>{{ $employee->phone_number }}</td>
                                    <td>{{ $arrayData['phone_number'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="phone_number"
                                            data-old-value="{{ $employee->phone_number }}"
                                            data-new-value="{{ $arrayData['phone_number'] }}" onclick="rowdata(this)">Add
                                            Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['email']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Email</td>
                                    <td>{{ $employee->email ? $employee->email : '--' }}</td>
                                    <td>{{ $arrayData['email'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="email"
                                            data-old-value="{{ $employee->email }}"
                                            data-new-value="{{ $arrayData['email'] }}" onclick="rowdata(this)">Add
                                            Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['nid_or_passport']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Nid or Passport</td>
                                    <td>{{ $employee->nid_or_passport }}</td>
                                    <td>{{ $arrayData['nid_or_passport'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="nid_or_passport"
                                            data-old-value="{{ $employee->nid_or_passport }}"
                                            data-new-value="{{ $arrayData['nid_or_passport'] }}"
                                            onclick="rowdata(this)">Add Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['birth_date']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Birth Date</td>
                                    <td>{{ $employee->birth_date ? $employee->birth_date : '--' }}</td>
                                    <td>{{ $arrayData['birth_date'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="birth_date"
                                            data-old-value="{{ $employee->birth_date }}"
                                            data-new-value="{{ $arrayData['birth_date'] }}"
                                            onclick="rowdata(this)">Add Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['father_name']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Father Name</td>
                                    <td>{{ $employee->father_name ? $employee->father_name : '--' }}</td>
                                    <td>{{ $arrayData['father_name'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="father_name"
                                            data-old-value="{{ $employee->father_name }}"
                                            data-new-value="{{ $arrayData['father_name'] }}"
                                            onclick="rowdata(this)">Add Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['mother_name']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Mother Name</td>
                                    <td>{{ $employee->mother_name ? $employee->mother_name : '--' }}</td>
                                    <td>{{ $arrayData['mother_name'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="mother_name"
                                            data-old-value="{{ $employee->mother_name }}"
                                            data-new-value="{{ $arrayData['mother_name'] }}"
                                            onclick="rowdata(this)">Add Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['address']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Address</td>
                                    <td>{{ $employee->address ? $employee->address : '--' }}</td>
                                    <td>{{ $arrayData['address'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="address"
                                            data-old-value="{{ $employee->address }}"
                                            data-new-value="{{ $arrayData['address'] }}" onclick="rowdata(this)">Add
                                            Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['blood_group']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Blood group</td>
                                    <td>{{ $employee->blood_group ? $employee->blood_group : '--' }}</td>
                                    <td>{{ $arrayData['blood_group'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="blood_group"
                                            data-old-value="{{ $employee->blood_group }}"
                                            data-new-value="{{ $arrayData['blood_group'] }}"
                                            onclick="rowdata(this)">Add Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['gender']))
                            <tr>
                                <td>{{ $i }}</td>
                                <td>Gender</td>
                                <td>{{ $employee->gender ? $employee->gender : '--' }}</td>
                                <td>{{ $arrayData['gender'] }}</td>
                                <td>
                                    <button type="button" class="add-row-btn btn btn-info"
                                        data-id="{{ $i }}" data-field="gender"
                                        data-old-value="{{ $employee->gender }}"
                                        data-new-value="{{ $arrayData['gender'] }}"
                                        onclick="rowdata(this)">Add Row</button>
                                </td>
                            </tr>
                            @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['religion']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Religion</td>
                                    <td>{{ $employee->religion ? $employee->religion : '--' }}</td>
                                    <td>{{ $arrayData['religion'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="religion"
                                            data-old-value="{{ $employee->religion }}"
                                            data-new-value="{{ $arrayData['religion'] }}" onclick="rowdata(this)">Add
                                            Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['nationality']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Nationality</td>
                                    <td>{{ $employee->nationality ? $employee->nationality : '--' }}</td>
                                    <td>{{ $arrayData['nationality'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="nationality"
                                            data-old-value="{{ $employee->nationality }}"
                                            data-new-value="{{ $arrayData['nationality'] }}"
                                            onclick="rowdata(this)">Add Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['marital_status']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Marital Status</td>
                                    <td>{{ $employee->marital_status ? $employee->marital_status : '--' }}</td>
                                    <td>{{ $arrayData['marital_status'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="marital_status"
                                            data-old-value="{{ $employee->marital_status }}"
                                            data-new-value="{{ $arrayData['marital_status'] }}"
                                            onclick="rowdata(this)">Add Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['reference_name']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Reference Name</td>
                                    <td>{{ $employee->reference_name ? $employee->reference_name : '--' }}</td>
                                    <td>{{ $arrayData['reference_name'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="reference_name"
                                            data-old-value="{{ $employee->reference_name }}"
                                            data-new-value="{{ $arrayData['reference_name'] }}"
                                            onclick="rowdata(this)">Add Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['reference_phone']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Reference Phone</td>
                                    <td>{{ $employee->reference_phone ? $employee->reference_phone : '--' }}</td>
                                    <td>{{ $arrayData['reference_phone'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="reference_phone"
                                            data-old-value="{{ $employee->reference_phone }}"
                                            data-new-value="{{ $arrayData['reference_phone'] }}"
                                            onclick="rowdata(this)">Add Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['emergency_name']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Emergency Name</td>
                                    <td>{{ $employee->emergency_name ? $employee->emergency_name : '--' }}</td>
                                    <td>{{ $arrayData['emergency_name'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="emergency_name"
                                            data-old-value="{{ $employee->emergency_name }}"
                                            data-new-value="{{ $arrayData['emergency_name'] }}"
                                            onclick="rowdata(this)">Add Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['emergency_relation']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Emergency Relation</td>
                                    <td>{{ $employee->emergency_relation ? $employee->emergency_relation : '--' }}</td>
                                    <td>{{ $arrayData['emergency_relation'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="emergency_relation"
                                            data-old-value="{{ $employee->emergency_relation }}"
                                            data-new-value="{{ $arrayData['emergency_relation'] }}"
                                            onclick="rowdata(this)">Add Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['emergency_phone']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Emergency Phone</td>
                                    <td>{{ $employee->emergency_phone ? $employee->emergency_phone : '--' }}</td>
                                    <td>{{ $arrayData['emergency_phone'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="emergency_phone"
                                            data-old-value="{{ $employee->emergency_phone }}"
                                            data-new-value="{{ $arrayData['emergency_phone'] }}"
                                            onclick="rowdata(this)">Add Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif

                            @if (isset($arrayData['emergency_address']))
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>Emergency Address</td>
                                    <td>{{ $employee->emergency_address ? $employee->emergency_address : '--' }}</td>
                                    <td>{{ $arrayData['emergency_address'] }}</td>
                                    <td>
                                        <button type="button" class="add-row-btn btn btn-info"
                                            data-id="{{ $i }}" data-field="emergency_address"
                                            data-old-value="{{ $employee->emergency_address }}"
                                            data-new-value="{{ $arrayData['emergency_address'] }}"
                                            onclick="rowdata(this)">Add Row</button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endif
                        </tbody>
                    </table>

                </div>
            </div>
            <div class="row">
                <div class="col-6 d-flex flex-row justify-content-end">
                    <form action="{{route('employees.request.edit.final.approve', user()->id)}}" method="post">
                        @csrf
                        <input type="text" value="{{$id}}" id="edit-id" name="edit_id" hidden>
                        <input type="text" value="" id="data-value" name="data_value" id-value="{{$id}}" hidden>
                        <button type="submit" class="btn-lg btn-success mr-5" onclick="approve()" id="approve-btn">Approve</button>
                    </form>
                </div>
                <div class="col-6 mb-5">
                    <form action="{{ route('employees.request.edit.delete', $id) }}" method="post"
                        class="d-inline-block dropdown-item"
                        onclick="if(confirm('Are you sure to want to delete it? You will not be able to undo it!')) {event.preventDefault(); this.submit(); }"
                        >
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn-lg btn-danger" id="add-all-btn" >Reject</button>
                    </form>


                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
     let changesArray = {};

function rowdata(button) {
    const row = button.closest('tr');
    const field = button.getAttribute('data-field');
    const oldValue = button.getAttribute('data-old-value');
    const newValue = button.getAttribute('data-new-value');



    changesArray[field] = newValue;

    // Disable button and change text to "Added"
    button.disabled = true;
    button.textContent = "Added";

    checkAllAdded();
    console.log(changesArray);
}

function addAllRows() {
    changesArray = {};
    const buttons = document.querySelectorAll('.add-row-btn');
    buttons.forEach(button => {
        const field = button.getAttribute('data-field');
        const oldValue = button.getAttribute('data-old-value');
        const newValue = button.getAttribute('data-new-value');

        changesArray[field] = newValue;

        // Disable button and change text to "Added"
        button.disabled = true;
        button.textContent = "Added";
    });

    // Disable "Add All Rows" button and change text to "All Added"
    const addAllBtn = document.getElementById('add-all-btn');
    addAllBtn.disabled = true;
    addAllBtn.textContent = "All Added";

    console.log(changesArray);
}

function checkAllAdded() {
    const allButtons = document.querySelectorAll('.add-row-btn');
    const allAdded = Array.from(allButtons).every(button => button.disabled);

    if (allAdded) {
        const addAllBtn = document.getElementById('add-all-btn');
        addAllBtn.disabled = true;
        addAllBtn.textContent = "All Added";
    }
}

function approve() {

    const changesInput = document.getElementById('data-value');
    // const idValue = changesInput.getAttribute('id-value') ;
    // changesArray['id'] = idValue;
    // console.log(changesArray)
    changesInput.value = JSON.stringify(changesArray);
    // console.log(JSON.encode(changesArray));
}
    </script>
    </script>
@endsection
