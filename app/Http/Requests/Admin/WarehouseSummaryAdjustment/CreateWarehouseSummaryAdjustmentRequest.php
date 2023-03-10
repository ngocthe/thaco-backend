<?php

namespace App\Http\Requests\Admin\WarehouseSummaryAdjustment;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Create Warehouse summary ajustment request",
 *     type="object",
 *     required={"warehouse_code", "part_code", "part_color_code", "adjustment_quantity", "plant_code"}
 * )
 */
class CreateWarehouseSummaryAdjustmentRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $warehouse_code;


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
     * @var int
     */
    public int $adjustment_quantity;

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
        return [
            'warehouse_code' => 'bail|required|alpha_num_dash|max:8|reference_check:warehouses,code,plant_code',
            'part_code' => 'bail|required|alpha_num_dash|max:10|reference_check:parts,code,plant_code',
            'part_color_code' => 'bail|required|alpha_num_dash|max:2|reference_check:part_colors,code,part_code,plant_code',
            'adjustment_quantity' => 'bail|required|integer|min:-9999|max:9999|not_in:0',
            'plant_code' => 'bail|required|alpha_num_dash|max:5|exists:plants,code,deleted_at,NULL',
            'remark' => 'nullable|string|max:255'
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'warehouse_code.reference_check' => 'Warehouse Code, Plant Code are not linked together',
            'part_code.reference_check' => 'Part No, Plant Code are not linked together',
            'part_color_code.reference_check' => 'Part No, Part Color Code, Plant Code are not linked together',
            'adjustment_quantity.integer' => 'The Adjustment Quantity must be an integer',
        ];
    }
}
