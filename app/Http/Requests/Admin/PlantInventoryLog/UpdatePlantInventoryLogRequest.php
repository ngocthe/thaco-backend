<?php

namespace App\Http\Requests\Admin\PlantInventoryLog;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Update Plant Inventory Log Request",
 *     type="object"
 * )
 */
class UpdatePlantInventoryLogRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var int
     */
    public int $quantity;

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
			'quantity' => 'nullable|integer|min:1|max:9999',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
