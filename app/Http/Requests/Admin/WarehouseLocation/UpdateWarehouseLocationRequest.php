<?php

namespace App\Http\Requests\Admin\WarehouseLocation;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Create warehouse location request",
 *     type="object",
 *     required={"code", "warehouse_code", "description", "plant_code"}
 * )
 */
class UpdateWarehouseLocationRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $description;

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
			'description' => 'bail|nullable|string|max:30',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
