<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryItemRequestRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:users,id',
            'inventory_items' => 'required|array|min:1',
            'inventory_items.*.id' => 'required|exists:inventory_items,id',
            'inventory_items.*.quantity' => 'required|integer|min:1',
            'description' => 'nullable|string'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'inventory_items.required' => 'At least one inventory item is required.',
            'inventory_items.*.id.required' => 'Item selection is required.',
            'inventory_items.*.id.exists' => 'Selected item does not exist.',
            'inventory_items.*.quantity.required' => 'Quantity is required.',
            'inventory_items.*.quantity.min' => 'Quantity must be at least 1.'
        ];
    }
}
