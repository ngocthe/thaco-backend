<?php

namespace App\Http\Requests\Admin\Procurement;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Create procurement request",
 *     type="object",
 *     required={"part_code", "part_color_code", "unit", "supplier_code", "plant_code"}
 * )
 */
class CreateProcurementRequest extends BaseApiRequest
{
    /**
     * @var array
     */
    protected array $unique_fields = ['part_code', 'part_color_code', 'plant_code'];

    /**
     * @var string
     */
    protected string $unique_error_message = 'The codes: part, part color and plant have already been taken.';

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
    public int $minimum_order_quantity;

    /**
     * @OA\Property()
     * @var int
     */
    public int $standard_box_quantity;

    /**
     * @OA\Property()
     * @var int
     */
    public int $part_quantity;

    /**
     * @OA\Property()
     * @var string
     */
    public string $unit;

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
        return [
            'part_code' => 'bail|required|alpha_num_dash|max:10|reference_check:parts,code,plant_code|unique:procurements,part_code,NULL,part_code,deleted_at,NULL,part_color_code,'. $this->get('part_color_code'). ',plant_code,' . $this->get('plant_code'),
            'part_color_code' => 'bail|required|alpha_num_dash|max:2|reference_check:part_colors,code,part_code,plant_code',
			'minimum_order_quantity' => 'bail|required|integer|min:1|max:99999',
			'standard_box_quantity' => 'bail|required|integer|min:1|max:9999',
			'part_quantity' => 'bail|required|integer|min:1|max:9999',
			'unit' => 'bail|required|unit_of_measure',
			'supplier_code' => 'bail|required|alpha_num_dash|max:5|exists:suppliers,code,deleted_at,NULL',
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
            'part_code.reference_check' => 'Part No, Plant Code are not linked together',
            'part_color_code.reference_check' => 'Part No, Part Color Code, Plant Code are not linked together'
        ];
    }
}
