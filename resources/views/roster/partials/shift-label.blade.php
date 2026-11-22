@php
    $employeeIds = array_values($employeeIds);
    $assignedIds = array_values(array_filter($employeeIds, fn ($id) =>
        ($roster->wfh_assignments[$id][$date] ?? null) === 'WFH'
    ));
    $shiftName = (int) $shift->id === 129 && $shift->name === 'DAC-DUMMY' ? 'DAC' : $shift->name;
    if (count($employeeIds) > 1 && count($assignedIds)) {
        $roster->loadMissing('items.employee');
    }
@endphp

@if (count($employeeIds) === 1 && count($assignedIds) === 1)
    @if ($roster->hasWfhAttendance((int) $assignedIds[0], $date, (int) $shift->id))
        WFH
    @else
        {{ $shiftName }} — Remote work pending attendance
    @endif
@else
    {{ $shiftName }}
    @foreach ($assignedIds as $employeeId)
        <div class="employee-wfh">
            {{ $roster->items->firstWhere('employee_id', $employeeId)?->employee?->name ?? 'Employee ' . $employeeId }}:
            {{ $roster->hasWfhAttendance((int) $employeeId, $date, (int) $shift->id)
                ? 'WFH' : 'Remote work pending attendance' }}
        </div>
    @endforeach
@endif
