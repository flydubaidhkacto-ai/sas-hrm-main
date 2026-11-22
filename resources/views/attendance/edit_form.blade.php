<div  onmousemove="calculateDuration()" onkeyup="calculateDuration()">
    <form action="{{ route('attendance.update', $att) }}" method="POST">
        @csrf
        @method('PUT')
        <h4>Edit Attendance Details:</h4>
        <div class="row">
            <div class="mb-2 col-12">
                <div class="row">
                    <div class="col-md-3">
                        <img src="/uploaded_files/{{ $att->employee?->picture_file }}" alt="" class="w-100">
                    </div>
                    <div class="col-md-9">
                        <h3>
                            {{ $att->employee?->name }}
                        </h3>
                        <p>
                            {{ $att->employee?->designation?->name }}
                            <br>
                            <b>
                                {{ $att->employee?->department?->super_department->name }}:
                            </b>
                            {{ $att->employee?->department?->name ?? '' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12 mappedInOuts">
                <hr>
                <b>
                    Shift:
                </b>
                @if($att->shift)
                    <span class="shift-details">
                        {{ $att->shift->name }}:
                        <br>
                        - Start: {{ carbon($att->shift->start)->format('h:i A') }}
                        <br> - End: {{ carbon($att->shift->end) ->format('h:i A')}}
                        {{ $att->shift->is_night_shift ? '(Next day)' : '' }}
                    </span>
                @else
                    <i>Shift not assigned!</i>
                @endif
                <br>
                <br>
                @php
                    if($att->shift?->is_24_hour_shift)
                    {
                        $mappedInOuts = \App\Actions\EmployeePunched\Calculate24HourShift::mappedInOuts($att);
                    }else {
                        $mappedInOuts = \App\Actions\EmployeePunched\CalculateWorkingTimeOfAttendance::execute(
                            $att, true
                        )['mappedInOuts'] ?? [];
                    }
                @endphp
                {{-- @dump($inOuts) --}}
                @foreach(
                    ($mappedInOuts) as $inOut
                )
                    <div class="row">
                        <div class="mb-2 col-md-5">
                            <label for="">Check-In Date & Time:</label>
                            <div class="row">
                                <div class="col-7 pr-0">
                                    <input type="text" name="check_in_date" class="form-control datetimepicker" required value="{{ ($inOut[0] ?? null) ? carbon($inOut[0]->date_time)->format('d-m-Y') : '' }}"
                                    onchange="calculateDuration()"
                                    onkeyup="calculateDuration()"
                                    onmouseup="calculateDuration()"
                                    {{-- onmousemove="calculateDuration()" --}}
                                    >
                                </div>
                                <div class="col-5 pl-0">
                                    <input type="time" name="check_in" class="form-control" required value="{{ ($inOut[0] ?? null) ? carbon($inOut[0]->date_time)->format('H:i') : '' }}" onchange="calculateDuration()">
                                </div>
                            </div>
                        </div>
                        <div class="mb-2 col-md-5">
                            <label for="">Check-Out Date & Time:</label>
                            <div class="row">
                                <div class="col-7 pr-0">
                                    <input type="text" name="check_out_date" class="form-control datetimepicker" required value="{{ ($inOut[1] ?? null) ? carbon($inOut[1]->date_time)->format('d-m-Y') : '' }}"
                                    onchange="calculateDuration()"
                                    onkeyup="calculateDuration()"
                                    onmouseup="calculateDuration()"
                                    {{-- onmousemove="calculateDuration()" --}}
                                    >
                                </div>
                                <div class="col-5 pl-0">
                                    <input type="time" name="check_out" class="form-control" required value="{{ ($inOut[1] ?? null) ? carbon($inOut[1]->date_time)->format('H:i') : '' }}" onchange="calculateDuration()">
                                </div>
                            </div>
                            <span id="shift_duration"></span>
                        </div>
                        @if(!$loop->first)
                            <div class="col-md-2">
                                <label for="">&nbsp;<br></label>
                                <br>
                                <button class="btn btn-sm btn-danger"
                                    onclick="if(confirm('Are you sure you want to delete this?')){event.preventDefault(); this.parentElement.parentElement.remove(); }"
                                >
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="col-12">
                <hr>
            </div>
            <div class="col-md-6">
                <label for="">Notes: </label>
                <textarea name="notes" class="form-control"
                    rows="3"
                >{{ $att->notes ?? '' }}</textarea>
            </div>
            <div class="col-md-6">
                <label for="">Duty Type: </label>
                <select name="duty_type" class="form-control">
                    @if($att->duty_type) <option selected>{{ $att->duty_type }}</option> @endif
                    <option>Inside of Office</option>
                    <option>Outside of Office</option>
                    <option>Work from Home</option>
                </select>
            </div>
        </div>
        <button class="mt-3 btn btn-primary">
            Submit
        </button>
    </form>
</div>
