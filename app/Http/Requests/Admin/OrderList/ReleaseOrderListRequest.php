<?php

namespace App\Http\Requests\Admin\OrderList;

use App\Constants\MRP;
use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Create Order List Request",
 *     type="object"
 * )
 */
class ReleaseOrderListRequest extends BaseApiRequest
{

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
    public string $supplier_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $plant_code;


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
        $contractCodeWithPartGroup = '';

        if($this->get('part_group')) {
            $contractCodeWithPartGroup = ',part_group,' . $this->get('part_group');
        }

        return [
            'contract_code' => 'required|max:10|alpha_num_dash|exists:mrp_order_calendars,contract_code,deleted_at,NULL,status,1' . $contractCodeWithPartGroup,
			'part_group' => 'nullable|alpha_num_dash|max:2|exists:part_groups,code,deleted_at,NULL',
			'supplier_code' => 'required|alpha_num_dash|max:5|exists:suppliers,code,deleted_at,NULL',
        ];
    }

    public function messages()
    {
        return [
            'contract_code.exists' => 'The selected Contract No is invalid.',
            'part_group.exists' => 'The selected Part Group is invalid.'
        ];
    }

}
