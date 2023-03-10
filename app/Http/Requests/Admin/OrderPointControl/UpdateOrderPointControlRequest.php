<?php

namespace App\Http\Requests\Admin\OrderPointControl;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Update Order point control request",
 *     type="object"
 * )
 */
class UpdateOrderPointControlRequest extends BaseApiRequest
{
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
    public string $box_type_code;

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
			'standard_stock' => 'nullable|integer|min:1|max:999',
            'ordering_lot' => 'nullable|integer|min:1|max:99',
            'box_type_code' => 'bail|nullable|alpha_num_dash|max:5|reference_check_plant_code:box_types,code,order_point_controls',
            'remark' => 'nullable|string|max:255'
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'box_type_code.reference_check_plant_code' => 'Box Type Code, Plant Code are not linked together'
        ];
    }
}
