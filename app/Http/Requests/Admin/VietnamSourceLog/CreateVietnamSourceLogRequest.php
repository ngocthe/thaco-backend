<?php

namespace App\Http\Requests\Admin\VietnamSourceLog;

use App\Http\Requests\BaseApiRequest;

class CreateVietnamSourceLogRequest extends BaseApiRequest
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
            'contract_code' => 'required|string|max:9|alpha_num_dash',
			'invoice_code' => 'nullable|string|max:10|alpha_num_dash',
			'bill_of_lading_code' => 'nullable|string|max:13|alpha_num_dash',
			'container_code' => 'required|string|max:11|alpha_num_dash',
			'case_code' => 'nullable|string|max:2|alpha_num_dash',
			'part_code' => 'required|string|max:10|alpha_num_dash|reference_check:parts,code,plant_code',
			'part_color_code' => 'required|string|max:2|alpha_num_dash|reference_check:part_colors,code,part_code,plant_code',
			'box_type_code' => 'required|string|max:5|alpha_num_dash|reference_check:box_types,code,part_code,plant_code',
			'box_quantity' => 'required|integer|min:1|max:9999',
			'part_quantity' => 'required|integer|min:1|max:9999',
			'unit' => 'required|unit_of_measure',
			'supplier_code' => 'required|string|max:8|alpha_num_dash|exists:suppliers,code,deleted_at,NULL',
			'delivery_date' => 'required|date|date_format:Y-m-d',
			'plant_code' => 'required|string|max:5|alpha_num_dash|exists:plants,code',
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'part_code.reference_check' => 'Part No, Plant Code are not linked together',
            'part_color_code.reference_check' => 'Part No, Part Color Code, Plant Code are not linked together',
            'box_type_code.reference_check' => ' Part No, Box Type Code, Plant Code are not linked together'
        ];
    }
}
