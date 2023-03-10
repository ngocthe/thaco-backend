<?php

namespace App\Http\Requests\Admin\VietnamSourceLog;

use App\Http\Requests\BaseApiRequest;

class UpdateVietnamSourceLogRequest extends BaseApiRequest
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
			'box_quantity' => 'required|integer|min:1|max:9999',
			'part_quantity' => 'required|integer|min:1|max:9999',
			'unit' => 'required|unit_of_measure',
			'supplier_code' => 'required|string|max:8|alpha_num_dash',
			'delivery_date' => 'required|date|date_format:Y-m-d',
        ];
    }
}
