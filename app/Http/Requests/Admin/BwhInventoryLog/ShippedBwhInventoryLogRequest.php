<?php

namespace App\Http\Requests\Admin\BwhInventoryLog;

use App\Http\Requests\BaseApiRequest;

class ShippedBwhInventoryLogRequest extends BaseApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'warehouse_code' => 'required',
            'plant_code' => 'required',
            'contract_code' => 'required',
            'bill_of_lading_code' => 'required',
            'container_code' => 'required',
            'case_code' => 'required',
            'invoice_code' => 'required'
        ];
    }
}
