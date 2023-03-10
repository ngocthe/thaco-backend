<?php

namespace App\Http\Requests\Admin\Procurement;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Create procurement request",
 *     type="object"
 * )
 */
class UpdateProcurementRequest extends BaseApiRequest
{
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
			'minimum_order_quantity' => 'bail|required|integer|min:1|max:99999',
			'standard_box_quantity' => 'bail|required|integer|min:1|max:9999',
			'part_quantity' => 'bail|required|integer|min:1|max:9999',
			'unit' => 'bail|required|unit_of_measure',
			'supplier_code' => 'bail|required|alpha_num_dash|max:5|exists:suppliers,code,deleted_at,NULL',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
