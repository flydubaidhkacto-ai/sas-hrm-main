<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventorySupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => 'required',
            'email'         => 'nullable|unique:inventory_suppliers,email,' . $this->supplier->id,
            'phone_number'  => 'required|unique:inventory_suppliers,phone_number,' . $this->supplier->id,
            'status'        => 'required',
            'paid_amount'   => 'required'
        ];
    }
}
