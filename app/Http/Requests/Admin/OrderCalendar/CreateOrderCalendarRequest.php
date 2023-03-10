<?php

namespace App\Http\Requests\Admin\OrderCalendar;

use App\Http\Requests\BaseApiRequest;

class CreateOrderCalendarRequest extends BaseApiRequest
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
            'contract_code' => 'required|max:9|alpha_num_dash',
			'part_group' => 'required|alpha_num_dash|max:2|exists:part_groups,code,deleted_at,NULL',
			'etd' => 'required|date_format:Y-m-d',
			'eta  ' => 'required|date_format:Y-m-d',
			'lead_time' => 'required|integer|min:1|max:999',
			'ordering_cycle' => 'required|integer|min:1|max:9',
        ];
    }
}
