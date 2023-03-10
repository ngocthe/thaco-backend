<?php

namespace App\Http\Requests\Admin\PartUsageResult;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Update part usage result request",
 *     type="object",
 * )
 */
class UpdatePartUsageResultRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var int
     */
    public int $quantity;

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
			'quantity' => 'required|integer|min:1|max:9999'
        ];
    }
}
