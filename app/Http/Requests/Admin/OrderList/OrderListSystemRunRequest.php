<?php

namespace App\Http\Requests\Admin\OrderList;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Order List System Run",
 *     type="object"
 * )
 */
class OrderListSystemRunRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $import_id;

    /**
     * @OA\Property()
     * @var string
     */
    public string $contract_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $part_group;

    /**
     * @OA\Property()
     * @var string
     */
    public string $mrp_run_date;
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
            'import_id' => 'required|exists:mrp_production_plan_imports,id,deleted_at,NULL',
            'contract_code' => 'required|max:10|alpha_num_dash|exists:mrp_order_calendars,contract_code,deleted_at,NULL,part_group,' . $this->get('part_group'),
			'part_group' => 'required|alpha_num_dash|max:2|exists:part_groups,code,deleted_at,NULL',
            'mrp_run_date' => 'required|date_format:n/d/Y'
        ];
    }

    public function messages(): array
    {
        return [
            'contract_code.exists' => 'Contract No., Part Group are not linked together'
        ];
    }
}
