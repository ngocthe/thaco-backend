<?php

namespace App\Http\Requests\Admin\LogicalInventoryMscAdjustment;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Create logical inventory msc ajustment request",
 *     type="object",
 *     required={"msc_code", "adjustment_quantity", "production_date", "plant_code"}
 * )
 */
class CreateLogicalInventoryMscAdjustmentRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $msc_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $vehicle_color_code;

    /**
     * @OA\Property()
     * @var int
     */
    public int $adjustment_quantity;

    /**
     * @OA\Property()
     * @var string
     */
    public string $production_date;

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
            'msc_code' => 'required|alpha_num_dash|max:7|reference_check:mscs,code,plant_code',
			'adjustment_quantity' => 'required|integer|min:-9999|max:9999|not_in:0',
			'vehicle_color_code' => 'required|alpha_num_dash|max:4|reference_check:vehicle_colors,code,plant_code',
			'production_date' => 'required|date_format:Y-m-d|before:tomorrow',
			'plant_code' => 'required|alpha_num_dash|max:5',
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'msc_code.reference_check' => 'MSC, Plant Code are not linked together',
            'vehicle_color_code.reference_check' => 'Exterior Color Code, Plant Code are not linked together',
            'adjustment_quantity.integer' => 'The Adjustment Quantity must be an integer',
        ];
    }
}
