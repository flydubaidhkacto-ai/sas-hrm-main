<?php

namespace App\Services;

use App\Models\Roster;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RosterService
{
    public function __construct() {}

    public function index($super_department_id = null, $start = null, $end = null): LengthAwarePaginator
    {
        $query = Roster::active()->with('items', 'superDepartment')->orderBy('id', 'desc');

        if ($super_department_id) {
            $query->where('super_department_id', $super_department_id);
        }
        if ($start) {
            $query->whereDate('start', '>=', $start);
        }
        if ($end) {
            $query->whereDate('end', '<=', $end);
        }

        return $query->paginate();
    }

    public function draft($super_department_id = null, $start = null, $end = null): LengthAwarePaginator
    {
        $query = Roster::draft()->with('items', 'superDepartment')->orderBy('id', 'desc');

        if ($super_department_id) {
            $query->where('super_department_id', $super_department_id);
        }
        if ($start) {
            $query->whereDate('start', '>=', $start);
        }
        if ($end) {
            $query->whereDate('end', '<=', $end);
        }

        return $query->paginate();
    }

    public function store($formData): Roster
    {
        $roster = Roster::create([
            'name' => $formData['name'],
            'super_department_id' => $formData['super_department_id'],
            'created_by' => user()->id,
            'start' => array_keys($formData['data'][0]['shifts'])[0],
            'end' => array_keys($formData['data'][0]['shifts'])[count($formData['data'][0]['shifts']) - 1],
            'is_active' => $formData['is_active'] ?? false,
            'no_shift_duty' => $formData['no_shift_duty']
        ]);
        // if (false) {
        //     throw new \Exception('Not permitted to store');
        // }

        foreach ($formData['data'] as $i => $item) {
            foreach ($item['shifts'] as $date => $shifts) {
                foreach (($shifts ?? null) ?: [null] as $shift) {
                    foreach ($item['employees'] as $employee) {
                        $roster->items()->create([
                            'employee_id' => $employee,
                            'roster_shift_id' => $shift ? $shift['id'] : null,
                            'date' => $date,
                            'group' => $i,
                        ]);
                    }
                }
            }
        }

        return $roster;
    }
}
