<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryPurchaseRequest extends FormRequest
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
            'inventory_supplier_id' => 'required',
            'challan_number' => 'required',
            'ref_no' => 'required',
            'total_cost' => 'required',
            'inventory_items' => 'required|array',
            'inventory_items.*' => 'exists:inventory_items,id',
            'quantity' => 'required|array',
            'quantity.*' => 'numeric|min:1'
        ];
    }
}
