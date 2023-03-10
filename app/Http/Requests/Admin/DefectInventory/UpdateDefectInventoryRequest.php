<?php

namespace App\Http\Requests\Admin\DefectInventory;

use App\Http\Requests\BaseApiRequest;

class UpdateDefectInventoryRequest extends BaseApiRequest
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
            'part_defect_quantity' => 'required|integer|min:1|max:9999', 
        ];
    }
}
