<?php

namespace App\Http\Requests\Admin\Plant;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Update plant request",
 *     type="object"
 * )
 */
class UpdatePlantRequest extends BaseApiRequest
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
			'description' => 'nullable|string|max:255',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
