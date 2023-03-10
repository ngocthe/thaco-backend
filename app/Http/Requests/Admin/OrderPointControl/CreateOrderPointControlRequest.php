<?php

namespace App\Http\Requests\Admin\OrderPointControl;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Create Order point control request",
 *     type="object",
 *     required={"part_code", "part_color_code", "box_type_code", "standard_stock", "plant_code"}
 * )
 */
class CreateOrderPointControlRequest extends BaseApiRequest
{
    /**
     * @var array
     */
    protected array $unique_fields = ['part_code', 'part_color_code', 'box_type_code', 'plant_code'];

    /**
     * @var string
     */
    protected string $unique_error_message = 'The codes: Part No., Part Color Code, Box Type Code and Plant Code have already been taken.';

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
    public string $box_type_code;

    /**
     * @OA\Property()
     * @var int
     */
    public int $standard_stock;

    /**
     * @OA\Property()
     * @var int
     */
    public int $ordering_lot;

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
            'part_code' => 'bail|required|alpha_num_dash|max:10|reference_check:parts,code,plant_code|unique:order_point_controls,part_code,NULL,part_code,deleted_at,NULL,part_color_code,'. $this->get('part_color_code'). ',box_type_code,' . $this->get('box_type_code'). ',plant_code,' . $this->get('plant_code'),
            'part_color_code' => 'bail|required|alpha_num_dash|max:2|reference_check:part_colors,code,part_code,plant_code',
            'box_type_code' => 'bail|required|alpha_num_dash|max:5|reference_check:box_types,code,part_code,plant_code',
			'standard_stock' => 'bail|required|integer|min:1|max:999',
            'ordering_lot' => 'bail|required|integer|min:1|max:99',
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
            'part_color_code.reference_check' => 'Part No, Part Color Code, Plant Code are not linked together',
            'box_type_code.reference_check' => 'Part No, Box Type Code, Plant Code are not linked together'
        ];
    }
}
