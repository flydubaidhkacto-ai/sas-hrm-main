<?php

namespace App\Http\Requests\StationLeave;

use Illuminate\Foundation\Http\FormRequest;

class StoreStationLeaveRequest extends FormRequest
{
    /**
     * Authorization check
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'super_department' => ['nullable'],
            'department' => ['nullable'],
            'employee_id' => ['required', 'exists:users,id'],

            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],

            'station' => ['required', 'string', 'max:255'],

            'present_address' => ['required', 'string', 'max:255'],
            'address_during_out_of_station' => ['required', 'string', 'max:255'],

            'contact_number' => ['required', 'string', 'max:20'],
            'alternative_number' => ['nullable', 'string', 'max:20'],

            'reason' => ['required', 'string'],
            'remarks_by_employee' => ['nullable', 'string'],

            'attachment' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png,zip',
                'max:10240', // 10MB
            ],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee selection is required.',
            'employee_id.exists'   => 'Selected employee is invalid.',

            'start_date.required' => 'Start date is required.',
            'end_date.required'   => 'End date is required.',
            'end_date.after_or_equal' => 'End date must be equal or after start date.',

            'station.required' => 'Station name is required.',

            'present_address.required' => 'Present address is required.',
            'address_during_out_of_station.required' => 'Out of station address is required.',

            'contact_number.required' => 'Contact number is required.',

            'reason.required' => 'Reason for station leave is required.',

            'attachment.mimes' => 'Attachment must be a valid file type.',
            'attachment.max'   => 'Attachment size must not exceed 10MB.',
        ];
    }

}
