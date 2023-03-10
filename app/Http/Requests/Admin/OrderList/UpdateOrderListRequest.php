<?php

namespace App\Http\Requests\Admin\OrderList;

use App\Http\Requests\BaseApiRequest;


/**
 * @OA\Schema(
 *     title="Create Order List Request",
 *     type="object",
 * )
 */
class UpdateOrderListRequest extends BaseApiRequest
{

    /**
     * @OA\Property()
     * @var int
     */
    public int $actual_quantity;

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
			'actual_quantity' => 'required|integer|min:1|max:999999',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
