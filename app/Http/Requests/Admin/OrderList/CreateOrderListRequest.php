<?php

namespace App\Http\Requests\Admin\OrderList;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Create Order List Request",
 *     type="object"
 * )
 */
class CreateOrderListRequest extends BaseApiRequest
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
    public string $part_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $part_color_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $part_group;

    /**
     * @OA\Property()
     * @var int
     */
    public int $actual_quantity;

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
     * @OA\Property()
     * @var string
     */
    public string $remark;

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
			'part_code' => 'required|alpha_num_dash|max:10|reference_check:parts,code,plant_code',
			'part_color_code' => 'required|alpha_num_dash|max:2|reference_check:part_colors,code,part_code,plant_code',
			'part_group' => 'required|alpha_num_dash|max:2|exists:part_groups,code,deleted_at,NULL',
			'actual_quantity' => 'required|integer|min:1|max:999999',
			'supplier_code' => 'required|alpha_num_dash|max:5|exists:suppliers,code,deleted_at,NULL',
            'plant_code' => 'required|alpha_num_dash|max:5|exists:plants,code,deleted_at,NULL',
            'remark' => 'nullable|string|max:255'
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
            'contract_code.exists' => 'The selected Contract No is invalid.',
            'part_group.exists' => 'The selected Part Group is invalid.'
        ];
    }
}
